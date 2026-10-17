LAS PALMAS MEDICAL CENTER
=========================

NOTE: This is a plain-text copy of README.md. README.md has the same content and is easier to read,
with formatted tables, code blocks and clickable links. Open README.md on GitHub
(https://github.com/sureshhemal/dbms_1_las_palmas_project) or in any Markdown viewer if you can.

A web application for managing the Las Palmas Medical Center hospital database. With it you can
manage:

- physicians, nurses and on-call shifts
- departments and rooms
- patients
- medications, procedures and prescriptions
- hospital stays and the procedures performed during them

There are also profile pages for patients and physicians.

It is built with the PHP framework Laravel (https://laravel.com) on the server,
Vue (https://vuejs.org) with Inertia (https://inertiajs.com) in the browser, and MySQL as the
database.

Repository: https://github.com/sureshhemal/dbms_1_las_palmas_project

This guide assumes you have never set up a Laravel project before. Follow it from top to bottom.

----------------------------------------------------------------------------------------------------

CONTENTS
========

1.  What you need
2.  Install the tools
    - Option A: Laravel Herd (macOS and Windows, recommended)
    - Option B: Install everything yourself (macOS, Windows, Linux)
3.  Get the code
4.  Create the databases
5.  Configure the application
6.  Install dependencies, build and fill the database
7.  Run the application
8.  Create your login
9.  Using the application
10. See the SQL queries with Clockwork
11. Run the tests (optional)
12. Everyday commands
13. Troubleshooting
14. Remove the project
15. Report: percentage shares with `AVG()` versus what the app uses

----------------------------------------------------------------------------------------------------

1. WHAT YOU NEED
================

PHP

- Version: 8.4 or newer
- What it is for: Runs the application on the server

Composer

- Version: 2.x
- What it is for: Installs the PHP libraries the project uses

Node.js

- Version: 22.18+ (LTS) or 24.11+; 20.19+ also works
- What it is for: Builds the browser code (CSS and JavaScript)

npm

- Version: comes with Node.js
- What it is for: Installs the JavaScript libraries

MySQL

- Version: 8.0.16 or newer (8.4 LTS recommended)
- What it is for: The database

Git

- Version: any recent version
- What it is for: Downloads the code

Why MySQL 8 and not 5.7?
------------------------

The tables use `CHECK` constraints. For example:

- a stay's end date must be after its start date;
- a physician's position must be one of the allowed values.

MySQL only enforces `CHECK` constraints from 8.0.16 onwards. MySQL 5.7 accepts them but silently
ignores them, so invalid data would be saved instead of being rejected with a MySQL error. MySQL 5.7
is also no longer supported.

The project is developed and tested on MySQL 8.4. MariaDB has not been tested.

PHP extensions
--------------

PHP needs these extensions turned on:

- `pdo_mysql` — this is how PHP talks to MySQL, so the application can't reach the database without
  it
- `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `ctype`, `filter`, `hash`, `iconv`, `session`,
  `pcre`, `json`
- `dom`, `xml`, `xmlwriter`, `simplexml`, `libxml`
- `curl` and `zip` — strongly recommended, so Composer can download packages quickly

Herd turns all of these on for you. If you install PHP yourself, the instructions below show how to
add them. To see which extensions are loaded, run:

    php -m

----------------------------------------------------------------------------------------------------

2. INSTALL THE TOOLS
====================

Choose one option.

Option A: Laravel Herd (macOS and Windows, recommended)
-------------------------------------------------------

Laravel Herd (https://herd.laravel.com) is a free app that installs PHP, Composer and Node.js in one
go. It also serves the website at a nice address such as `http://las_palmas.test`, with no extra
setup.

1.  Download Herd from https://herd.laravel.com and install it. Open it once so it finishes the
    setup.

2.  In Herd's settings, make sure PHP 8.4 is selected (Settings → PHP).

3.  Make sure Node.js is installed. Herd can install it for you: Settings → Node. Pick version 22 or
    24.

4.  Install MySQL. The free version of Herd does not include MySQL, so pick one of these:

    - Herd Pro (paid): Services → add MySQL 8.4. It runs on port `3306` with user `root` and no
      password.
    - DBngin (free, macOS and Windows): download it from https://dbngin.com, create a MySQL 8.4
      service and start it. The default user is `root` with no password, on port `3306`.
    - MySQL Installer (Windows) or Homebrew (macOS): see Option B.

5.  Open a new terminal window. On Windows, use PowerShell. Check that everything is found:

        php -v          # should say PHP 8.4.x
        composer -V     # should say Composer version 2.x
        node -v         # should say v22.x or v24.x
        npm -v

Herd serves every folder inside its Herd folder as a website: `~/Herd` on macOS,
`%USERPROFILE%\Herd` on Windows. You will put the code there in step 3.

  Herd is not available for Linux. On Linux, use Option B.

Option B: Install everything yourself
-------------------------------------

macOS (with Homebrew)
---------------------

Install Homebrew (https://brew.sh) first if you don't have it. Then run:

    brew install php@8.4 composer node@22 mysql@8.4 git
    brew link php@8.4 --force --overwrite
    brew link node@22 --force --overwrite
    brew services start mysql@8.4

Homebrew's PHP already includes all the required extensions. The MySQL user is `root` with no
password.

Windows (without Herd)
----------------------

Herd is much easier on Windows. If you still want to install things by hand:

1.  PHP 8.4

    1.  Download the "VS17 x64 Non Thread Safe" zip from https://windows.php.net/download/.

    2.  Unzip it to `C:\php` and add `C:\php` to your `PATH`.

    3.  In `C:\php`, copy `php.ini-development` to `php.ini`. Open `php.ini` and remove the leading
        `;` from these lines:

            extension_dir = "ext"
            extension=curl
            extension=fileinfo
            extension=mbstring
            extension=openssl
            extension=pdo_mysql
            extension=zip

2.  Composer: run the installer from https://getcomposer.org/download/.

3.  Node.js: install the LTS version (22 or 24) from https://nodejs.org.

4.  MySQL 8.4: run the MySQL Installer from https://dev.mysql.com/downloads/installer/. Choose
    "Server only" and write down the `root` password you set.

5.  Git: install it from https://git-scm.com/download/win.

Open a new PowerShell window and check the versions with the commands from Option A, step 5.

Linux (Ubuntu / Debian)
-----------------------

    # PHP 8.4 and the required extensions
    sudo apt update
    sudo apt install -y software-properties-common
    sudo add-apt-repository -y ppa:ondrej/php     # Ubuntu; on Debian use https://packages.sury.org/php/
    sudo apt update
    sudo apt install -y php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip unzip git

    # Composer
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

    # Node.js 22 (via nvm)
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
    source ~/.bashrc
    nvm install 22

    # MySQL 8
    sudo apt install -y mysql-server
    sudo systemctl enable --now mysql

On Ubuntu, MySQL's `root` user logs in through the system account by default, not with a password.
The project needs a user that logs in with a password, so create one:

    sudo mysql -e "CREATE USER 'las_palmas'@'localhost' IDENTIFIED BY 'secret'; GRANT ALL PRIVILEGES ON *.* TO 'las_palmas'@'localhost'; FLUSH PRIVILEGES;"

Then use `DB_USERNAME=las_palmas` and `DB_PASSWORD=secret` in step 5.

----------------------------------------------------------------------------------------------------

3. GET THE CODE
===============

Clone the repository into a folder called `las_palmas`.

- With Herd, put it inside the Herd folder. Herd names the website after the folder, so a folder
  called `las_palmas` is served at `http://las_palmas.test`.

      cd ~/Herd                # Windows: cd $HOME\Herd
      git clone https://github.com/sureshhemal/dbms_1_las_palmas_project.git las_palmas
      cd las_palmas

- Without Herd, use any folder you like:

      git clone https://github.com/sureshhemal/dbms_1_las_palmas_project.git las_palmas
      cd las_palmas

Run every command from now on inside this `las_palmas` folder.

----------------------------------------------------------------------------------------------------

4. CREATE THE DATABASES
=======================

The project uses two databases:

  Database            Used for
  ------------------- -----------------------------------------------------------------
  `las_palmas`        the application itself
  `las_palmas_test`   the automated tests only; the tests wipe it every time they run

Create both with any MySQL client. That could be the `mysql` command line,
TablePlus (https://tableplus.com), DBeaver (https://dbeaver.io), MySQL Workbench or DBngin's "Open"
button. From the command line:

    mysql -u root -p -e "CREATE DATABASE las_palmas; CREATE DATABASE las_palmas_test;"

Press Enter at the password prompt if your `root` user has no password. On Linux, use the user you
created, for example `mysql -u las_palmas -p ...`.

You do not need to create any tables. The application creates them for you in step 6.

----------------------------------------------------------------------------------------------------

5. CONFIGURE THE APPLICATION
============================

Settings live in a file called `.env` in the project folder. Create it from the example file:

    cp .env.example .env              # macOS / Linux
    Copy-Item .env.example .env       # Windows PowerShell

Open `.env` in a text editor and check these lines:

    APP_URL=http://localhost:8000     # with Herd: http://las_palmas.test

    HOSPITAL_TIMEZONE=America/Denver  # the hospital's local time zone

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=las_palmas
    DB_TEST_DATABASE=las_palmas_test
    DB_USERNAME=root
    DB_PASSWORD=                      # your MySQL password; leave empty if there is none

    CLOCKWORK_ENABLE=true             # records the SQL queries of each request, see step 10

- `APP_URL` is the address you open in the browser. Use `http://las_palmas.test` with Herd, or keep
  `http://localhost:8000` otherwise.
- `HOSPITAL_TIMEZONE` decides what "today" means for the hospital. It affects which stays count as
  current, which procedures are scheduled rather than performed, and which dates can be picked. Las
  Palmas Medical Center is in Denver, so keep it at `America/Denver` (Mountain Time); `.env.example`
  already sets this. Otherwise, late in the evening, "today" would be the next day in UTC while it
  is still today in Denver. It only needs changing if the hospital were somewhere else; use a name
  from the list of time zones (https://en.wikipedia.org/wiki/List_of_tz_database_time_zones). Dates
  in the database are always stored in UTC, so you don't need to configure MySQL's time zone.
- `DB_*` tells the application how to reach MySQL. The defaults match a local MySQL with user `root`
  and no password.

Now generate the application's secret key. It is written into `.env` for you:

    composer install
    php artisan key:generate

----------------------------------------------------------------------------------------------------

6. INSTALL DEPENDENCIES, BUILD AND FILL THE DATABASE
====================================================

Install and build
-----------------

Run these commands one by one:

    composer install          # PHP libraries (skip if you already ran it in step 5)
    npm install               # JavaScript libraries
    npm run build             # builds the CSS and JavaScript the browser loads

Create the tables, and optionally fill them with sample data
------------------------------------------------------------

Laravel creates and fills the database with `php artisan` commands. A migration creates the tables.
A seeder fills them with made-up but realistic sample data. Pick what you want:

An empty application (all tables, no data), to enter data yourself

- Run: `php artisan migrate`

The tables and sample data to explore (recommended the first time)

- Run: `php artisan migrate --seed`

To add sample data to tables you already created with `migrate`

- Run: `php artisan db:seed`

To clear everything and start again with empty tables

- Run: `php artisan migrate:fresh`

To clear everything and start again with fresh sample data

- Run: `php artisan migrate:fresh --seed`

- `migrate` creates all the tables, such as `Physician`, `Patient`, `Stay` and `Undergoes`. The only
  data it adds is the hospital's three fixed departments. Running it again is safe; it only adds
  tables that are missing.

- `db:seed` (or `--seed`) fills the tables with sample data, which takes about 10 seconds. You get:

  - 75 physicians, 128 nurses and 75 rooms;
  - a catalogue of 2,000 medications and 300 procedures;
  - 10,000 patients, about 30,000 stays, about 92,000 performed procedures and about 48,000
    prescriptions.

  Seed only once. Running `db:seed` again adds a second set of physicians and nurses. If you want
  fresh sample data, use `migrate:fresh --seed` instead.

- `migrate:fresh` deletes every table and all its data in the `las_palmas` database, including your
  login, and recreates the tables empty. Add `--seed` to fill them with sample data again. After
  either one, register again as described in step 8.

There is also a shortcut that runs most of steps 5–6 at once. Run it after you have created `.env`
and the databases. It creates the tables but doesn't add sample data, so run `php artisan db:seed`
afterwards if you want it.

    composer setup            # composer install, key:generate, migrate, npm install, npm run build
    php artisan db:seed       # optional: sample data

----------------------------------------------------------------------------------------------------

7. RUN THE APPLICATION
======================

With Herd
---------

Nothing to start: Herd is already serving the site. Open http://las_palmas.test in your browser.

Without Herd
------------

Start the development server:

    composer run dev

This starts the PHP web server, a background queue worker and the frontend build watcher together.
Open http://localhost:8000 and keep the terminal open while you use the app. Press `Ctrl+C` to stop
it.

If you only want to run the app and not change code, this is enough after `npm run build`:

    php artisan serve

If port 8000 is already in use
------------------------------

When something else on your computer already uses port 8000, `php artisan serve` (and
`composer run dev`) automatically tries 8001, 8002 and so on, up to 8009. It prints the address it
picked, for example `Server running on [http://127.0.0.1:8001]`. Open that address, and set
`APP_URL` in `.env` to match.

To always use the same port, add it to `.env`:

    SERVER_PORT=8080
    APP_URL=http://localhost:8080

You can also pass the port for a single run: `php artisan serve --port=8080`.

With Herd there is no port to choose: Herd serves the site at `http://las_palmas.test` on the normal
web ports.

Do I need HTTPS (an SSL certificate)?
-------------------------------------

No, not to run the application. Everything works over plain `http://`, both with Herd and with
`php artisan serve`.

The only feature that needs a secure connection is passkeys, the optional password-less sign-in
under Settings → Security. Browsers only allow passkeys on `https://` addresses, or on
`http://localhost`. If you want passkeys:

- With Herd: run `herd secure las_palmas`, or click the lock icon next to the site in Herd's Sites
  list. The site is then served at `https://las_palmas.test` with a certificate your computer
  trusts. Change `APP_URL` in `.env` to `https://las_palmas.test`. To go back to HTTP, run
  `herd unsecure las_palmas`.
- Without Herd, on your own computer: use `http://localhost:8000`. Browsers already treat
  `localhost` as secure, so passkeys work without a certificate. Make sure `APP_URL` is
  `http://localhost:8000`, not `http://127.0.0.1:8000`, because passkeys only work on the exact
  address in `APP_URL`.
- On a public server with a domain name (for example a VPS): serve the app through a web server such
  as Nginx or Apache. Get a free Let's Encrypt certificate with certbot (https://certbot.eff.org),
  for example `sudo certbot --nginx -d your-domain.com`, and set `APP_URL=https://your-domain.com`.
  Certbot only works for real domain names on the public internet, not for `localhost` or `.test`
  addresses. Setting up a public server is beyond the scope of this guide.

----------------------------------------------------------------------------------------------------

8. CREATE YOUR LOGIN
====================

The application has no ready-made user, so register one:

1.  Open the site and click Register (or go to `/register`).
2.  Enter a name, an email address and a password. The email doesn't have to be real, because
    nothing is sent.
3.  You are logged in. Use the sidebar to open Staff, Hospital, Patients and Clinical.

----------------------------------------------------------------------------------------------------

9. USING THE APPLICATION
========================

A short tour
------------

After you log in, the sidebar on the left has five pages: Dashboard, Staff, Hospital, Patients and
Clinical. The numbers below assume you filled the database with sample data
(`php artisan migrate --seed`).

1.  Patients: 10,000 patients, 20 per page. Search by name, ID, phone or insurance number, or filter
    by primary physician. Click a patient's name to open their profile. It shows:
    - summary cards: next scheduled procedure, procedures performed, prescriptions issued and total
      revenue;
    - a pie chart of the medications they have been prescribed;
    - their personal details and current room;
    - their upcoming procedures, performed procedures and prescriptions.
2.  Clinical → Performed Procedures: click Record Procedure to record (past date) or schedule
    (future date) a procedure. The same form admits the patient into a room. Only rooms with a free
    bed for the whole stay are offered.
3.  Clinical → Stays: every hospital stay with its status (Scheduled, Admitted or Discharged). For a
    stay that hasn't ended, you can Add Procedure, Extend the stay or Discharge the patient.
4.  Hospital → Rooms: each room's free beds and the patients in it today. Click a patient's name to
    open their profile.
5.  Staff → On Call: a month calendar of the nurses' on-call periods. Drag across days to add a
    period, drag a period to move it, and click it to edit or remove it.
6.  Staff → Physicians: click a physician's name to open their profile: summary cards, a pie chart
    of the procedures they perform, their departments and their patient lists.
7.  Clinical → Medications and Procedures: each item's share of all prescriptions or performed
    procedures. Click the column headings to sort. The Procedures tab also shows the average
    procedure cost, calculated with `AVG()`.
8.  Clockwork (`/clockwork/app`): the exact SQL every page ran. See step 10.

Patient and physician names are links to their profiles in every table. Inactive patients and
offboarded physicians are shown struck through.

Pages, tabs and database tables
-------------------------------

"Seeded records" is roughly how many rows `php artisan db:seed` creates. Paginated lists show 20
rows per page by default, and you can choose 10, 20, 50 or 100 at the bottom of the list.

Dashboard

- Database table: –
- What you see, and how many at once: An empty starter page with no data
- Seeded records: –
- Add / edit / delete: –

Staff → Physicians

- Database table: `Physician` (departments from `AffiliatedWith`)
- What you see, and how many at once: All physicians at once, with search and filters by position
  and department
- Seeded records: 75
- Add / edit / delete: Add Physician; Edit on the row; Offboard on the row

Staff → Nurses

- Database table: `Nurse`
- What you see, and how many at once: Paginated, 20 per page; search and position filter
- Seeded records: 128
- Add / edit / delete: Add Nurse; Edit; Offboard

Staff → On Call

- Database table: `OnCall`
- What you see, and how many at once: A month calendar; filter by nurse
- Seeded records: 75 periods
- Add / edit / delete: Add On-Call Period, or drag across days; click a period to edit it, or drag
  it to move it; Remove on-call period in the edit form

Hospital → Departments

- Database table: `Department`
- What you see, and how many at once: All 3 departments at once
- Seeded records: 3 (created by `migrate`)
- Add / edit / delete: Can't be added or deleted; Edit changes the department ID and head

Hospital → Department page (click a department)

- Database table: `Department`, `AffiliatedWith`, `Physician`
- What you see, and how many at once: The head and all the department's physicians
- Seeded records: 91 memberships
- Add / edit / delete: Read-only; departments are assigned in the physician's Edit form

Hospital → Rooms

- Database table: `Room` (occupants from `Stay`)
- What you see, and how many at once: Paginated, 20 per page; free beds and today's patients;
  search, room-type and availability filters
- Seeded records: 75
- Add / edit / delete: Add Room; Edit; Decommission

Patients

- Database table: `Patient`
- What you see, and how many at once: Paginated, 20 per page; search and primary-physician filter
- Seeded records: 10,000
- Add / edit / delete: Add Patient; Edit; Deactivate

Patients → Patient profile (click a name)

- Database table: `Patient`, `Stay`, `Undergoes`, `Prescribes`
- What you see, and how many at once: Summary, charts and details; procedure and prescription lists
  10 per page
- Seeded records: –
- Add / edit / delete: Read-only

Staff → Physician profile (click a name)

- Database table: `Physician`, `Undergoes`, `Prescribes`
- What you see, and how many at once: Summary, charts and details; procedure and prescription lists
  10 per page
- Seeded records: –
- Add / edit / delete: Read-only

Clinical → Medications

- Database table: `Medication` (shares from `Prescribes`)
- What you see, and how many at once: Paginated, 20 per page; sorted by share of prescriptions
- Seeded records: 2,000
- Add / edit / delete: Add Medication; Edit; Remove

Clinical → Procedures

- Database table: `Procedure` (shares from `Undergoes`)
- What you see, and how many at once: Paginated, 20 per page; sorted by share of performed
  procedures; average cost
- Seeded records: 300
- Add / edit / delete: Add Procedure; Edit; Remove

Clinical → Prescriptions

- Database table: `Prescribes`
- What you see, and how many at once: Paginated, 20 per page, newest first; search and year filter
- Seeded records: ~48,000
- Add / edit / delete: Add Prescription only; prescriptions can't be edited or deleted

Clinical → Stays

- Database table: `Stay`
- What you see, and how many at once: Paginated, 20 per page, newest first; search and status filter
- Seeded records: ~30,800
- Add / edit / delete: Created by Record Procedure; Add Procedure, Extend and Discharge on the row;
  never deleted

Clinical → Performed Procedures

- Database table: `Undergoes` (rooms from `Stay`)
- What you see, and how many at once: Paginated, 20 per page, newest first; search and filters by
  year, status, procedure, physician and nurse
- Seeded records: ~92,000 (about 760 scheduled)
- Add / edit / delete: Record Procedure (also admits the patient); can't be edited or deleted

Settings (user menu) → Profile, Security, Appearance

- Database table: `users`
- What you see, and how many at once: Your name, email, password, two-factor sign-in, passkeys and
  light/dark theme
- Seeded records: Your own login
- Add / edit / delete: Change your details; delete your account

How adding, editing and removing work
-------------------------------------

- Add buttons sit above each list. Edit and the remove action (Offboard, Deactivate, Decommission or
  Remove) are at the end of each row. Forms open in a panel on the right.
- ID fields can be left blank. The next free ID is assigned automatically.
- Removing does not delete the row. Offboarded physicians and nurses, deactivated patients,
  decommissioned rooms and removed medications and procedures are hidden from the lists. Their row
  stays in MySQL with a `deleted_at` date, so their history is kept. For example, a past
  prescription still shows the medication it was for, struck through. The only true deletion is
  removing an on-call period.
- Medical history is never changed or deleted. Prescriptions, stays and performed procedures can
  only be added; a stay can only be extended or discharged. Correcting history by deleting it would
  hide what really happened.
- The app enforces the hospital's rules and shows a message when something isn't allowed. For
  example:
  - interns can't prescribe, perform procedures or be a primary physician;
  - a prescription can't be dated in the future or before the patient registered;
  - a patient can only have one stay at a time;
  - a room can't take more patients than it has beds;
  - a procedure must fall within its stay;
  - a nurse's on-call periods can't overlap.
- Database errors are shown as MySQL reports them. For example, saving a duplicate ID or leaving a
  required name blank shows MySQL's own error message. This needs `APP_DEBUG=true` in `.env`, which
  is the default in `.env.example`. With `APP_DEBUG=false`, Laravel hides the details and shows only
  "Server Error".

10. SEE THE SQL QUERIES WITH CLOCKWORK
======================================

The project includes Clockwork (https://underground.works/clockwork), a debugging tool that records
every request the application handles, along with every SQL query that request ran against MySQL.
Use it to see the exact SQL behind any screen.

How to use it
-------------

1.  Run the application and use it normally. For example, open Clinical → Medications or sort a
    table.
2.  In a second browser tab, open `<site>/clockwork/app`:
    - with Herd: http://las_palmas.test/clockwork/app
    - without Herd: http://localhost:8000/clockwork/app
3.  The left-hand list shows the recent requests. Each page loads its data with background (AJAX)
    requests to `/api/...`, such as `GET /api/medications` or `GET /api/patients/{id}/summary`.
    Those are the requests that run the interesting queries. Click one.
4.  Open the Database tab. It lists every query that request ran, with its bindings and how long it
    took.

Where the records are stored
----------------------------

The records are kept for 24 hours. This is set in `.env`:

    CLOCKWORK_ENABLE=true
    CLOCKWORK_STORAGE=sql
    CLOCKWORK_STORAGE_SQL_DATABASE=mysql      # the MySQL connection, i.e. the las_palmas database
    CLOCKWORK_STORAGE_SQL_TABLE=clockwork
    CLOCKWORK_STORAGE_EXPIRATION=1440         # minutes

With these settings the records go into a `clockwork` table in the `las_palmas` database. Clockwork
creates the table itself the first time it records something. You can store the records somewhere
else instead:

- JSON files: set `CLOCKWORK_STORAGE=files` and remove the `CLOCKWORK_STORAGE_SQL_*` lines. Each
  request is saved as a JSON file in `storage/clockwork`. No extra PHP extension is needed.
- A separate SQLite file: keep `CLOCKWORK_STORAGE=sql`, but set `CLOCKWORK_STORAGE_SQL_DATABASE` to
  a file path such as `/full/path/to/las_palmas/storage/clockwork.sqlite`. Clockwork creates the
  file. This needs the `pdo_sqlite` PHP extension:
  - Herd and Homebrew include it.
  - On Ubuntu/Debian, install `php8.4-sqlite3`.
  - On Windows, enable `extension=pdo_sqlite` in `php.ini`.

To turn Clockwork off, set `CLOCKWORK_ENABLE=false`.

Clockwork only records requests from the browser. Artisan commands and the automated tests are not
recorded. It is a development tool and is installed by `composer install` as a development
dependency.

11. RUN THE TESTS (OPTIONAL)
============================

The tests use the separate `las_palmas_test` database from step 4, and they wipe and rebuild it
every time.

    php artisan test

To run every check the CI pipeline runs (code style, static analysis, type checks and tests):

    composer ci:check

----------------------------------------------------------------------------------------------------

12. EVERYDAY COMMANDS
=====================

`composer run dev`

- What it does: Start the app for development (not needed with Herd)

`npm run dev`

- What it does: Rebuild the browser code automatically while you edit Vue/CSS files (with Herd)

`npm run build`

- What it does: Build the browser code once

`php artisan migrate`

- What it does: Create any missing tables (keeps your data)

`php artisan db:seed`

- What it does: Add the sample data (only once, on empty tables)

`php artisan migrate:fresh`

- What it does: Delete everything in `las_palmas` and recreate the tables empty

`php artisan migrate:fresh --seed`

- What it does: Delete everything in `las_palmas` and recreate it with fresh sample data

`php artisan test`

- What it does: Run the automated tests

`git pull && composer install && npm install && npm run build && php artisan migrate`

- What it does: Update to the latest code

----------------------------------------------------------------------------------------------------

13. TROUBLESHOOTING
===================

`could not find driver`

The `pdo_mysql` PHP extension isn't enabled. On Windows, enable `extension=pdo_mysql` in `php.ini`.
On Linux, install `php8.4-mysql`. Run `php -m` to check.

`SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`

`DB_USERNAME` or `DB_PASSWORD` in `.env` is wrong. On Ubuntu, create a password user as shown in
Option B → Linux.

`SQLSTATE[HY000] [2002] Connection refused`

MySQL isn't running, or it's on a different port. Start it from Herd, DBngin, Homebrew
(`brew services start mysql@8.4`) or systemd (`sudo systemctl start mysql`). Also check `DB_PORT`.

`SQLSTATE[HY000] [1049] Unknown database 'las_palmas'`

The database doesn't exist yet. Create it as shown in step 4.

`Unable to locate file in Vite manifest`, or a blank page

The browser code hasn't been built. Run `npm run build`, or keep `npm run dev` / `composer run dev`
running.

`npm run build` fails with an error about `php`

The build runs `php artisan` to generate the route helpers, so `php` must be available in the same
terminal. Open a new terminal and check that `php -v` works.

Node version errors during `npm install` or `npm run build`

Your Node.js is too old. Install Node 22.18+ or 24.11+, then run `npm install` again.

The site shows "419 Page Expired" after a while

Your login session expired. Refresh the page and log in again.

With Herd, `http://las_palmas.test` doesn't open

Check that the project folder is inside the Herd folder and is called `las_palmas`. If you used a
different folder name, the address is `http://<folder-name>.test`. Update `APP_URL` in `.env` to
match.

----------------------------------------------------------------------------------------------------

14. REMOVE THE PROJECT
======================

1.  Stop the app. Press `Ctrl+C` in the terminal running `composer run dev` or `php artisan serve`.
    With Herd there is nothing to stop. If you ran `herd secure las_palmas`, run
    `herd unsecure las_palmas` to remove its certificate.

2.  Delete the databases (this removes all the data):

        mysql -u root -p -e "DROP DATABASE las_palmas; DROP DATABASE las_palmas_test;"

    On Linux, use the MySQL user you created, and also remove it if you no longer need it:
    `sudo mysql -e "DROP USER 'las_palmas'@'localhost';"`.

3.  Delete the project folder `las_palmas`. With Herd, deleting the folder from the Herd folder also
    removes the `las_palmas.test` site.

4.  Optional: uninstall the tools from step 2 (Herd, PHP, Composer, Node.js, MySQL) if you don't
    need them for anything else.

----------------------------------------------------------------------------------------------------

15. REPORT: PERCENTAGE SHARES WITH `AVG()` VERSUS WHAT THE APP USES
===================================================================

What we tried
-------------

The Medications and Procedures tabs show each item's share of the whole:

- for a medication, the percentage of all prescriptions that were for it;
- for a procedure, the percentage of all performed procedures that were that procedure.

A share can be written with the aggregate function `AVG()`. In MySQL a comparison such as
`p.medication_id = m.id` gives `1` when it is true and `0` when it is false. The average of those 1s
and 0s over every prescription is exactly the fraction of prescriptions for that medication.

Medications, using `AVG()`

    SELECT m.medID,
           m.name,
           COALESCE(100 * (SELECT AVG(p.medication_id = m.id)
                           FROM Prescribes p
                           WHERE p.deleted_at IS NULL), 0) AS prescriptionShare
    FROM Medication m
    WHERE m.deleted_at IS NULL
    ORDER BY prescriptionShare DESC, m.name, m.medID
    LIMIT 20;

The same query in Laravel's query builder:

    Medication::query()
        ->select(['medID', 'name'])
        ->selectSub(
            Prescription::query()
                ->selectRaw('coalesce(100 * avg(Prescribes.medication_id = Medication.id), 0)'),
            'prescriptionShare',
        )
        ->orderByDesc('prescriptionShare')
        ->orderBy('name')
        ->orderBy('medID')
        ->limit(20)
        ->get();

Procedures, using `AVG()`

    SET @today = '2026-10-16';   -- the hospital's local date

    SELECT pr.procID,
           pr.name,
           COALESCE(100 * (SELECT AVG(u.procedure_id = pr.id)
                           FROM Undergoes u
                           WHERE u.deleted_at IS NULL
                             AND u.procDate <= @today), 0) AS performedShare
    FROM `Procedure` pr
    WHERE pr.deleted_at IS NULL
    ORDER BY performedShare DESC, pr.name, pr.procID
    LIMIT 20;

The same query in Laravel's query builder:

    $today = HospitalCalendar::today()->toDateString();

    Procedure::query()
        ->select(['procID', 'name'])
        ->selectSub(
            PerformedProcedure::query()
                ->selectRaw('coalesce(100 * avg(Undergoes.procedure_id = `Procedure`.id), 0)')
                ->where('procDate', '<=', $today),
            'performedShare',
        )
        ->orderByDesc('performedShare')
        ->orderBy('name')
        ->orderBy('procID')
        ->limit(20)
        ->get();

Both queries return the correct numbers. For example, Levothyroxine Sodium comes out at 7.0486% and
CT Lumbar Spine at 1.2669%. The query-builder versions generate the same SQL and return the same
numbers.

What the app uses instead
-------------------------

The app joins each prescription or performed procedure to its medication or procedure, counts them
per group, and divides by the total. The total is a scalar subquery that MySQL computes once.

Medications (what the app runs)

    SELECT Medication.*,
           COUNT(Prescribes.id) AS prescriptionsCount,
           COALESCE(ROUND(100 * COUNT(Prescribes.id)
                    / NULLIF((SELECT COUNT(*) FROM Prescribes WHERE deleted_at IS NULL), 0), 2), 0) AS prescriptionShare
    FROM Medication
    LEFT JOIN Prescribes
           ON Prescribes.medication_id = Medication.id
          AND Prescribes.deleted_at IS NULL
    WHERE Medication.deleted_at IS NULL
    GROUP BY Medication.id
    ORDER BY prescriptionsCount DESC, Medication.name, Medication.medID
    LIMIT 20;

The query builder code that produces it (`app/Clinical/UseCases/ListMedications.php`, shown for the
default view: no search, sorted by share):

    Medication::query()
        ->leftJoin('Prescribes', fn (JoinClause $prescriptions) => $prescriptions
            ->on('Prescribes.medication_id', '=', 'Medication.id')
            ->whereNull('Prescribes.deleted_at'))
        ->groupBy('Medication.id')
        ->select('Medication.*')
        ->selectRaw('count(Prescribes.id) as prescriptionsCount')
        ->selectRaw('coalesce(round(100 * count(Prescribes.id) / nullif((select count(*) from Prescribes where deleted_at is null), 0), 2), 0) as prescriptionShare')
        ->withCasts(['prescriptionsCount' => 'integer', 'prescriptionShare' => 'float'])
        ->orderBy('prescriptionsCount', 'desc')
        ->orderBy('Medication.name')
        ->orderBy('Medication.medID')
        ->paginate(20);

Procedures (what the app runs)

    SELECT `Procedure`.*,
           COUNT(Undergoes.id) AS performedCount,
           COALESCE(ROUND(100 * COUNT(Undergoes.id)
                    / NULLIF((SELECT COUNT(*) FROM Undergoes
                              WHERE deleted_at IS NULL AND procDate <= '2026-10-16'), 0), 2), 0) AS performedShare
    FROM `Procedure`
    LEFT JOIN Undergoes
           ON Undergoes.procedure_id = `Procedure`.id
          AND Undergoes.deleted_at IS NULL
          AND Undergoes.procDate <= '2026-10-16'
    WHERE `Procedure`.deleted_at IS NULL
    GROUP BY `Procedure`.id
    ORDER BY performedCount DESC, `Procedure`.name, `Procedure`.procID
    LIMIT 20;

The query builder code that produces it (`app/Clinical/UseCases/ListProcedures.php`, shown for the
default view: no search, sorted by share):

    $today = HospitalCalendar::today()->toDateString();

    Procedure::query()
        ->leftJoin('Undergoes', fn (JoinClause $performedProcedures) => $performedProcedures
            ->on('Undergoes.procedure_id', '=', 'Procedure.id')
            ->whereNull('Undergoes.deleted_at')
            ->where('Undergoes.procDate', '<=', $today))
        ->groupBy('Procedure.id')
        ->select('Procedure.*')
        ->selectRaw('count(Undergoes.id) as performedCount')
        ->selectRaw('coalesce(round(100 * count(Undergoes.id) / nullif((select count(*) from Undergoes where deleted_at is null and procDate <= ?), 0), 2), 0) as performedShare', [$today])
        ->withCasts(['performedCount' => 'integer', 'performedShare' => 'float'])
        ->orderBy('performedCount', 'desc')
        ->orderBy('Procedure.name')
        ->orderBy('Procedure.procID')
        ->paginate(20);

`paginate(20)` adds `LIMIT 20 OFFSET 0` for the first page. It also runs a second query that counts
all the rows, for the "Showing 1 – 20 of 300 results" line.

The results are the same, rounded to two decimal places for display. The table is sorted by the raw
count rather than the rounded percentage, so two items whose shares round to the same value still
appear in the right order.

Stress test
-----------

Both versions were run against a copy of the seeded database. The `Prescribes` and `Undergoes`
tables were then doubled three times, to 2×, 4× and 8× their seeded size, by copying their rows with
the dates shifted back. Every query was given a 30-second limit with MySQL's `MAX_EXECUTION_TIME`
hint, which matches PHP's default `max_execution_time` for a web request. A query that needs longer
than that would fail in the app.

The test ran on MySQL 8.4.3 on an Apple Silicon Mac. Each number is the time for the default view:
the first page, sorted by share from highest to lowest.

Medications

  Data size     Prescriptions   App query   `AVG()` query
  ------------- --------------- ----------- --------------------
  1× (seeded)   47,943          103 ms      18,074 ms
  2×            95,886          188 ms      failed (over 30 s)
  4×            191,772         399 ms      failed (over 30 s)
  8×            383,544         1,360 ms    failed (over 30 s)

Procedures

  Data size     Performed procedures   App query   `AVG()` query
  ------------- ---------------------- ----------- --------------------
  1× (seeded)   92,446                 199 ms      6,401 ms
  2×            184,892                378 ms      12,490 ms
  4×            369,784                1,133 ms    27,709 ms
  8×            739,568                3,879 ms    failed (over 30 s)

The `AVG()` queries failed the stress test. The medications version already took 18 seconds on the
seeded data and could not finish within 30 seconds once the data doubled. The procedures version
failed at 8×. The app's queries grew roughly in proportion to the data and finished every time.

Why `AVG()` is so slow here
---------------------------

The `AVG()` version runs its subquery once for every medication or procedure, and each run reads the
whole prescriptions or performed-procedures table again:

- Medications: 2,000 medications × 47,943 prescriptions ≈ 96 million row checks on the seeded data.
- Procedures: 300 procedures × 92,446 performed procedures ≈ 28 million row checks.

`LIMIT 20` does not help. To find the 20 highest shares, MySQL must first calculate the share for
every row. The work grows with (number of items) × (number of rows), so it gets worse on both sides
as the hospital adds medications, procedures, prescriptions and performed procedures.

The app's query reads each prescription or performed procedure once, groups them, and divides by a
total computed once. Its work grows with the number of rows only.

Where the app does use `AVG()`
------------------------------

`AVG()` is the right tool for a single aggregate over one numeric column, which is exactly what the
assignment's "Average" functionality asks for. The Clinical → Procedures tab shows the average
procedure cost above the table. The app asks Laravel for it with the query builder's aggregate
methods: `avg('cost')`, `count('cost')` and `whereNull('cost')->count()`. In the query builder
(`app/Clinical/UseCases/ShowProcedureAverageCost.php`):

    $averageCost = Procedure::query()->avg('cost');

    return new ProcedureAverageCost(
        averageCost: $averageCost === null ? null : (float) $averageCost,
        pricedProcedureCount: Procedure::query()->count('cost'),
        unpricedProcedureCount: Procedure::query()->whereNull('cost')->count(),
    );

`Procedure::query()` adds the `deleted_at IS NULL` filter automatically, because procedures are
soft-deleted. Laravel turns the three calls into these queries:

    SELECT AVG(cost)   FROM `Procedure` WHERE deleted_at IS NULL;                  -- the average
    SELECT COUNT(cost) FROM `Procedure` WHERE deleted_at IS NULL;                  -- procedures averaged
    SELECT COUNT(*)    FROM `Procedure` WHERE cost IS NULL AND deleted_at IS NULL; -- procedures left out

Each query reads the 300-row catalogue once and only averages or counts one column, so they stay
fast however large the hospital grows.

`AVG()` skips `NULL` values, so procedures whose cost isn't set yet are left out of the average
rather than counted as $0. `COUNT(cost)` skips them too, so it gives the number of procedures that
were averaged. The third query counts the ones that were left out. The tab shows both counts next to
the average. On the seeded data it reads "$4,116.49 across 296 procedures with a cost · 4 without a
cost are not counted".

Conclusion
----------

`AVG()` gives mathematically correct shares, but it does not scale, and on realistic growth it fails
within the request time limit. It would eventually break the Medications and Procedures tabs, so the
app does not use it. The app keeps the grouped `COUNT(...) / total` query shown above, which returns
the same numbers and stayed within the limit at every size tested. `AVG()` is used where it fits:
averaging one numeric column, as with the average procedure cost.
