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
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    private function pendingUser(): User
    {
        $user = User::factory()->create(['email' => 'new@psu.edu.ph', 'password' => 'Temp1234', 'is_verified' => false]);
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
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
        $this->actingAs($user)->get(route('password.setup'))->assertRedirect(route('dashboard'));
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
        $this->assertNull($user->email_verified_at);
    }
}
