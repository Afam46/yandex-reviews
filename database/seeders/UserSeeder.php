<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Afam',
            'email' => 'test@test.com',
            'password' => bcrypt('12345678')
        ]);
    }
}
