<?php

namespace Tests\Feature;

use App\Mail\AccountCreated;
use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AccountRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_11_03_141756_add_engineer_to_user_roles.php',
            '2026_05_13_205842_add_otp_fields_to_users_table.php',
            '2026_11_07_135137_add_must_change_password_to_users_table.php',
            '2026_11_07_140405_create_account_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function test_public_request_has_no_password_and_requires_admin_approval(): void
    {
        Mail::fake();
        $this->get('/register')->assertRedirect(route('register'));
        $this->get(route('register'))->assertOk()->assertSee('Request an Account')
            ->assertDontSee('name="password"', false)->assertDontSee('name="role"', false);
        $this->withSession(['pending_registration' => ['password' => 'legacy-secret']])
            ->post(route('register'), [
                'name' => 'Requesting User', 'email' => '  NEW@psu.edu.ph  ', 'department' => 'IT',
                'password' => 'injected', 'role' => 'admin', 'is_active' => true, 'is_verified' => true,
            ])->assertRedirect(route('register'))->assertSessionHas('success')->assertSessionMissing('pending_registration');
        $this->assertDatabaseHas('account_requests', ['email' => 'new@psu.edu.ph', 'department' => 'IT']);
        $this->assertSame(0, User::count());
        $this->assertGuest();
        Mail::assertNothingSent();
    }

    public function test_requests_validate_psu_email_and_prevent_duplicates(): void
    {
        AccountRequest::factory()->create(['email' => 'pending@psu.edu.ph']);
        User::factory()->create(['email' => 'existing@psu.edu.ph']);
        foreach (['external@example.com', 'pending@psu.edu.ph', 'existing@psu.edu.ph', ['bad']] as $email) {
            $this->post(route('register'), ['name' => 'Request', 'email' => $email])->assertSessionHasErrors('email');
        }
        $this->assertSame(1, AccountRequest::count());
    }

    public function test_admin_approval_sends_only_a_setup_link_then_password_setup_activates_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = AccountRequest::factory()->create(['email' => 'requester@psu.edu.ph']);
        Mail::fake();
        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee($request->email);
        $this->post(route('account-requests.approve', $request), ['role' => 'engineer'])
            ->assertRedirect(route('users.index'))->assertSessionHas('success');
        $this->assertModelMissing($request);
        $created = User::where('email', $request->email)->firstOrFail();
        $this->assertSame('engineer', $created->role);
        $this->assertFalse($created->is_active);
        $this->assertFalse($created->is_verified);
        $this->assertTrue($created->must_change_password);

        Mail::assertSent(AccountCreated::class, function (AccountCreated $mail) use ($created): bool {
            $this->assertFalse(property_exists($mail, 'generatedPassword'));
            $this->assertTrue($mail->hasTo($created->email));
            $token = basename(parse_url($mail->changePasswordUrl, PHP_URL_PATH));
            $this->assertTrue(Password::broker()->tokenExists($created, $token));
            $this->get($mail->changePasswordUrl)->assertOk();
            $this->post(route('password.update'), [
                'email' => $created->email, 'token' => $token,
                'password' => 'ChosenPassword123', 'password_confirmation' => 'ChosenPassword123',
            ])->assertRedirect(route('login'));
            $created->refresh();
            $this->assertTrue($created->isAccountActive());
            $this->assertFalse($created->must_change_password);
            $this->assertTrue(Hash::check('ChosenPassword123', $created->password));
            $this->assertFalse(Password::broker()->tokenExists($created, $token));

            return true;
        });
        $this->post(route('account-requests.approve', $request), ['role' => 'user'])->assertNotFound();
        Mail::assertSentCount(1);
    }

    public function test_approval_roles_and_existing_accounts_are_validated(): void
    {
        $request = AccountRequest::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['admin', 'unknown', ['user']] as $role) {
            $this->post(route('account-requests.approve', $request), ['role' => $role])->assertSessionHasErrors('role');
        }
        $this->assertModelExists($request);
        User::factory()->create(['email' => $request->email]);
        $this->post(route('account-requests.approve', $request), ['role' => 'user'])->assertSessionHasErrors('email');
        $this->assertModelExists($request);
    }

    public function test_non_admins_cannot_approve_or_decline(): void
    {
        $request = AccountRequest::factory()->create();
        $this->post(route('account-requests.approve', $request), ['role' => 'user'])->assertRedirect(route('login'));
        $this->delete(route('account-requests.decline', $request))->assertRedirect(route('login'));
        foreach (['user', 'engineer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->post(route('account-requests.approve', $request), ['role' => 'user'])->assertForbidden();
            $this->delete(route('account-requests.decline', $request))->assertForbidden();
        }
        $this->assertModelExists($request);
    }

    public function test_admin_can_decline_a_request_without_creating_an_account(): void
    {
        Mail::fake();
        $request = AccountRequest::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->delete(route('account-requests.decline', $request))->assertSessionHas('success');
        $this->assertModelMissing($request);
        $this->assertDatabaseMissing('users', ['email' => $request->email]);
        Mail::assertNothingSent();
    }

    public function test_legacy_otp_sessions_cannot_create_accounts_without_approval(): void
    {
        $this->withSession(['pending_registration' => [
            'name' => 'Legacy', 'email' => 'legacy@psu.edu.ph', 'password' => 'old-password',
            'otp' => '123456', 'otp_expires_at' => now()->addMinutes(10),
        ]])->postJson('/verify-otp', ['otp' => '123456'])->assertRedirect('/request-account');
        $this->assertSame(0, User::count());
        $this->assertGuest();
    }
}
