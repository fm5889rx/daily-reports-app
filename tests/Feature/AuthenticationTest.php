<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_three_fields_and_login_is_the_default_action(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('>ログイン</button>', false)
            ->assertSee('formaction="'.route('register.store').'"', false)
            ->assertSee('>新規登録</button>', false);
    }

    public function test_registration_creates_user_and_logs_them_in(): void
    {
        $response = $this->post(route('register.store'), [
            'email' => 'new-user@example.com',
            'password' => ' password123 ',
            'password_confirmation' => ' password123 ',
        ]);

        $response->assertRedirect(route('daily-reports.index'));

        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('new-user', $user->name);
        $this->assertTrue(Hash::check(' password123 ', $user->password));
    }

    public function test_registration_validation_errors_are_in_japanese(): void
    {
        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'email' => 'invalid-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('メールアドレスには有効なメールアドレスを指定してください。')
            ->assertSee('パスワードは8文字以上にしてください。');
    }

    public function test_registration_rejects_mismatched_password_confirmation_in_japanese(): void
    {
        $response = $this->from(route('register'))
            ->post(route('register.store'), [
                'email' => 'new-user@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ]);

        $response->assertRedirect(route('register'))
            ->assertSessionHasErrors([
                'password' => 'パスワードとパスワード確認が一致しません。',
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'new-user@example.com',
        ]);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('パスワードとパスワード確認が一致しません。');
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('daily-reports.index'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_login_failure_message_is_in_japanese(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'missing@example.com',
                'password' => 'wrong-password',
                'password_confirmation' => 'wrong-password',
            ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('メールアドレスまたはパスワードが正しくありません。');
    }

    public function test_existing_user_cannot_log_in_when_password_confirmation_does_not_match(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $response = $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'member@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different123',
            ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'password_confirmation' => 'パスワードとパスワード確認が一致しません。',
            ]);

        $this->assertGuest();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('パスワードとパスワード確認が一致しません。');
    }

    public function test_existing_user_cannot_log_in_with_a_password_that_does_not_match(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password123',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'member@example.com',
                'password' => 'wrong-password',
                'password_confirmation' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_daily_reports_require_authentication(): void
    {
        $this->get(route('daily-reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_are_redirected_from_guest_pages_to_daily_reports(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect('/daily-reports');
    }
}
