<?php

namespace Tests\Feature\Auth;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'admin@dentalcare.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dentalcare.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'clinic_id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $clinic = Clinic::factory()->create();

        User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'admin@dentalcare.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dentalcare.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_disabled_user_cannot_login(): void
    {
        $clinic = Clinic::factory()->create();

        User::factory()->create([
            'clinic_id' => $clinic->id,
            'email' => 'disabled@dentalcare.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'disabled@dentalcare.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'User account is disabled',
            ]);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me');

        $response
            ->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        $token = $user->createToken('test-token');

        $response = $this
            ->withToken($token->plainTextToken)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}