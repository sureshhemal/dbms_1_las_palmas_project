#!/usr/bin/env bash

set -Eeuo pipefail

APP_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

announce() {
    printf '\n==> %s\n' "$1"
}

stay_in_maintenance_on_failure() {
    printf '\n!! Deployment failed. The application stays in maintenance mode until a deployment succeeds or you run "php artisan up" in %s.\n' "$APP_DIRECTORY" >&2
}

check_out_requested_commit() {
    local requested_sha="${SSH_ORIGINAL_COMMAND:-${1:-}}"

    if [[ ! "$requested_sha" =~ ^[0-9a-f]{40}$ ]]; then
        printf 'Usage: deploy.sh <full 40-character commit SHA>\n' >&2
        exit 64
    fi

    announce "Fetching from origin"
    git fetch --prune --prune-tags --force origin

    if ! git cat-file -e "${requested_sha}^{commit}" 2>/dev/null; then
        printf 'Commit %s was not found on origin.\n' "$requested_sha" >&2
        exit 65
    fi

    if [[ -f vendor/autoload.php ]]; then
        announce "Entering maintenance mode"
        php artisan down --retry=60 --refresh=15
    fi

    trap stay_in_maintenance_on_failure ERR

    announce "Checking out ${requested_sha}"
    git checkout --force --detach "$requested_sha"

    trap - ERR
    exec "$APP_DIRECTORY/deploy.sh" --after-checkout "$requested_sha"
}

deploy_checked_out_commit() {
    local deployed_sha="$1"

    trap stay_in_maintenance_on_failure ERR

    announce "Installing PHP dependencies"
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

    announce "Clearing the cached configuration"
    php artisan config:clear

    announce "Running migrations"
    php artisan migrate --force

    announce "Building the frontend"
    npm ci
    npm run build

    announce "Removing node_modules (the built, minified assets are in public/build)"
    rm -rf node_modules

    announce "Caching configuration, routes, views and events"
    php artisan optimize

    announce "Reloading long-running services"
    php artisan reload

    announce "Leaving maintenance mode"
    php artisan up

    trap - ERR
    announce "Deployed $(git rev-parse --short "$deployed_sha")"
}

main() {
    cd "$APP_DIRECTORY"

    if [[ "${1:-}" == "--after-checkout" ]]; then
        deploy_checked_out_commit "${2:?missing commit SHA}"

        return
    fi

    check_out_requested_commit "${1:-}"
}

main "$@"
