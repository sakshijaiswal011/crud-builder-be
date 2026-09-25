<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Default admin (user id 1) for audit logs and local development.
     */
    public function run(): void
    {
        $email = 'admin@gmail.com';

        $duplicate = User::query()
            ->where('email', $email)
            ->where('id', '!=', 1)
            ->first();

        if ($duplicate) {
            $duplicate->delete();
        }

        User::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'admin',
                'email' => $email,
                'password' => 'Test123',
                'email_verified_at' => now(),
            ]
        );
    }
}
