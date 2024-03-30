<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    

    /** @test */
    public function an_admin_can_create_a_user()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        Passport::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'user'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function an_admin_can_view_all_users()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        Passport::actingAs($admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['id', 'name', 'email', 'roles']
                 ]);
    }

    /** @test */
    public function an_admin_can_update_a_user()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        Passport::actingAs($admin);

        $user = User::where('email', '!=', 'admin@gmail.com')->first();

        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email, // Assuming email remains the same
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name']);
    }

    /** @test */
    public function an_admin_can_delete_a_user()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();
        // Passport::actingAs($admin);

        $user = User::where('email', '!=', 'admin@gmail.com')->first();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    // Add more tests as necessary for other functionalities and failure cases
}
