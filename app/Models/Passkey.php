<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Laravel\Passkeys\Passkey as BasePasskey;

/**
 * @property string $id
 * @property string $user_id
 */
class Passkey extends BasePasskey
{
    use HasUlids;
}
