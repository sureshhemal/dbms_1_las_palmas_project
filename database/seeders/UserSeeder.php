<?php

namespace Database\Seeders;

use App\IAM\Entities\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private const string EMAIL = 'sureshhemal123@gmail.com';

    public function run(): void
    {
        if (User::query()->where('email', self::EMAIL)->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Suresh Hemal',
            'email' => self::EMAIL,
            'password' => 'FSA-Md=-q2`-646',
        ]);
    }
}
