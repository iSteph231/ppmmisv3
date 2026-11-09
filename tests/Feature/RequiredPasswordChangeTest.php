<?php

namespace Tests\Feature;

use App\Mail\AccountCreated;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RequiredPasswordChangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_05_13_205842_add_otp_fields_to_users_table.php',
            '2026_11_07_135137_add_must_change_password_to_users_table.php',
            '2026_11_07_140405_create_account_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function pendingUser(): User
    {
        $user = User::factory()->create(['email' => 'new@psu.edu.ph', 'password' => 'Temp1234', 'is_verified' => false, 'is_active' => false]);
        $user->forceFill(['must_change_password' => true, 'email_verified_at' => null])->save();

        return $user;
    }

    public function test_email_button_opens_a_valid_password_change_form(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Mail::fake();
        $this->post(route('users.store'), ['name' => 'New User', 'email' => 'new@psu.edu.ph', 'role' => 'user'])
            ->assertSessionHas('success');

        Mail::assertSent(AccountCreated::class, function (AccountCreated $mail): bool {
            $user = User::where('email', $mail->email)->firstOrFail();
            $token = basename(parse_url($mail->changePasswordUrl, PHP_URL_PATH));
            $this->assertTrue(Password::broker()->tokenExists($user, $token));
            $mail->assertSeeInHtml($mail->changePasswordUrl);
            $this->get($mail->changePasswordUrl)->assertOk()->assertSee('name="password"', false);

            return true;
        });
    }

    public function test_generated_password_login_requires_a_change_and_blocks_other_requests(): void
    {
        $user = $this->pendingUser();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'Temp1234'])
            ->assertRedirect(route('password.setup'));
        $this->get(route('dashboard'))->assertRedirect(route('password.setup'));
        $this->post('/work-requests', [])->assertRedirect(route('password.setup'));
        $this->getJson('/api/dashboard-stats')->assertForbidden();
        $this->get(route('password.setup'))->assertOk()->assertSee($user->email);
        $this->assertFalse($user->fresh()->is_verified);
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_valid_password_change_confirms_email_and_clears_the_requirement(): void
    {
        $user = $this->pendingUser();
        $token = Password::broker()->createToken($user);
        $this->post(route('password.update'), [
            'email' => $user->email, 'token' => $token,
            'password' => 'MyNewPassword123', 'password_confirmation' => 'MyNewPassword123',
        ])->assertRedirect(route('login'));
        $user->refresh();
        $this->assertTrue(Hash::check('MyNewPassword123', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertTrue($user->is_verified);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->isAccountActive());
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
        $this->actingAs($user)->get(route('password.setup'))->assertRedirect(route('dashboard'));
    }

    public function test_status_stays_inactive_until_email_is_verified_and_password_change_is_complete(): void
    {
        foreach ([
            [true, false, false, false],
            [true, true, true, false],
            [false, true, false, false],
            [true, true, false, true],
        ] as [$isActive, $isVerified, $mustChangePassword, $expected]) {
            $user = User::factory()->make(['is_active' => $isActive, 'is_verified' => $isVerified]);
            $user->must_change_password = $mustChangePassword;
            $this->assertSame($expected, $user->isAccountActive());
        }
    }

    public function test_admin_status_remains_active_without_email_verification(): void
    {
        $admin = User::factory()->make(['role' => 'admin', 'is_active' => true, 'is_verified' => false]);
        $this->assertTrue($admin->isAccountActive());
        $admin->is_active = false;
        $this->assertFalse($admin->isAccountActive());
    }

    public function test_password_reset_does_not_reactivate_a_previously_verified_disabled_account(): void
    {
        $user = User::factory()->create(['email' => 'disabled@psu.edu.ph', 'is_active' => false, 'is_verified' => true]);
        $token = Password::broker()->createToken($user);
        $this->post(route('password.update'), [
            'email' => $user->email, 'token' => $token,
            'password' => 'MyNewPassword123', 'password_confirmation' => 'MyNewPassword123',
        ])->assertRedirect(route('login'));
        $this->assertFalse($user->fresh()->is_active);
        $this->post(route('login'), ['email' => $user->email, 'password' => 'MyNewPassword123'])
            ->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_invalid_token_or_reusing_generated_password_does_not_confirm_email(): void
    {
        $user = $this->pendingUser();
        $this->post(route('password.update'), [
            'email' => $user->email, 'token' => 'invalid',
            'password' => 'Different123', 'password_confirmation' => 'Different123',
        ])->assertSessionHasErrors('email');
        $token = Password::broker()->createToken($user);
        $this->post(route('password.update'), [
            'email' => $user->email, 'token' => $token,
            'password' => 'Temp1234', 'password_confirmation' => 'Temp1234',
        ])->assertSessionHasErrors('password');
        $user->refresh();
        $this->assertTrue($user->must_change_password);
        $this->assertFalse($user->is_verified);
        $this->assertFalse($user->is_active);
        $this->assertNull($user->email_verified_at);
    }
}
