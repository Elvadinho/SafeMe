<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_citizen_can_register_and_cannot_choose_their_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Paul Citizen',
            'email'                 => 'paul@example.com',
            'phone'                 => '6 77 00 00 00',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'role'                  => 'super_admin', // attack attempt: must be ignored
        ]);

        $response->assertCreated()->assertJsonStructure(['user', 'token']);

        $user = User::where('email', 'paul@example.com')->sole();
        $this->assertSame(UserRole::Citizen, $user->role);
        $this->assertSame('+237677000000', $user->phone);
    }

    #[Test]
    public function login_gives_a_token_that_works(): void
    {
        User::factory()->create(['email' => 'marie@example.com', 'password' => 'secret123']);

        $token = $this->postJson('/api/v1/auth/login', ['email' => 'marie@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'marie@example.com')
            ->assertJsonMissingPath('user.password');
    }

    #[Test]
    public function a_wrong_password_is_refused(): void
    {
        User::factory()->create(['email' => 'marie@example.com', 'password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', ['email' => 'marie@example.com', 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    #[Test]
    public function only_the_super_admin_can_create_admins(): void
    {
        $citizen = User::factory()->create();
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => UserRole::SuperAdmin])->save();

        $payload = ['name' => 'Officer Ngo', 'email' => 'officer@example.com', 'password' => 'police-station-1'];

        $this->actingAs($citizen)->postJson('/api/v1/admin/users/admins', $payload)->assertForbidden();

        $this->actingAs($superAdmin)->postJson('/api/v1/admin/users/admins', $payload)->assertCreated();
        $this->assertSame(UserRole::Admin, User::where('email', 'officer@example.com')->sole()->role);
    }

    #[Test]
    public function the_super_admin_role_cannot_be_changed(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->forceFill(['role' => UserRole::SuperAdmin])->save();

        $this->actingAs($superAdmin)
            ->patchJson("/api/v1/admin/users/{$superAdmin->id}/role", ['role' => 'citizen'])
            ->assertForbidden();
    }
}
