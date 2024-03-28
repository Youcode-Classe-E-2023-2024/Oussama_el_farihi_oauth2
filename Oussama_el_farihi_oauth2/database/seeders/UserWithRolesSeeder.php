<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolesTableSeeder;

class UserWithRolesSeeder extends Seeder
{
    public function run()
    {
        // Ensure the base roles and admin user are seeded
        $this->call([
            RolesTableSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Fetch roles to assign to users
        $roles = Role::all();

        // Create 10 additional users with random roles
        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
            ]);

            // Assign a random role to each user
            $role = $roles->random();
            $user->roles()->attach($role);
        }
    }
}
