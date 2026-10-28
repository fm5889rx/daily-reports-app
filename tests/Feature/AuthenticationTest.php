<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_and_starts_a_session(): void
    {
        $response = $this->postJson(route('register.store'), [
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('new-user', $user->name);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_registration_validation_errors_are_returned_in_japanese(): void
    {
        $this->postJson(route('register.store'), [
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password'])
            ->assertJsonFragment([
                'email' => ['メールアドレスには有効なメールアドレスを指定してください。'],
            ]);
    }

    public function test_registration_rejects_mismatched_password_confirmation(): void
    {
        $this->postJson(route('register.store'), [
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'password' => 'パスワードとパスワード確認が一致しません。',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'new-user@example.com',
        ]);
    }

    public function test_user_can_log_in_and_log_out_with_json_responses(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $this->postJson(route('login.store'), [
            'email' => 'member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertOk()
            ->assertJson(['two_factor' => false]);

        $this->assertAuthenticatedAs($user);

        $this->postJson(route('logout'))
            ->assertNoContent();

        $this->assertGuest();
    }

    public function test_login_failure_returns_japanese_validation_error(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $this->postJson(route('login.store'), [
            'email' => 'member@example.com',
            'password' => 'wrong-password',
            'password_confirmation' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);

        $this->assertGuest();
    }

    public function test_current_user_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/user')
            ->assertUnauthorized();
    }
}
