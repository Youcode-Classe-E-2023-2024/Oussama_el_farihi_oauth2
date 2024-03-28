<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCrudTest extends TestCase
{
    use WithFaker;
    /** @test */
    public function an_admin_can_create_a_user()
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', 'Admin')->first());
        $this->actingAs($admin, 'api');

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password', // Consider hashing this in your UserController or elsewhere
            'role' => 'user'
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201); // Created
        $this->assertDatabaseHas('users', ['email' => $userData['email']]);
    }

}
