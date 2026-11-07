<?php

namespace Tests\Feature;

use App\Mail\AccountCreated;
use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
            '2026_04_24_062233_create_work_requests_table.php',
            '2026_04_28_080517_add_user_id_to_work_requests_table.php',
            '2026_09_24_000000_create_facility_requests_table.php',
            '2026_10_08_105045_create_inventory_entries_table.php',
            '2026_11_07_122817_add_requester_snapshot_to_work_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        require_once database_path('migrations/2026_05_04_142741_create_inspection_reports_table.php');
        (new \CreateInspectionReportsTable)->up();
    }

    public function test_admin_can_list_users_with_role_controls_and_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(16)->create();
        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()->assertSee('Users</span>', false)->assertSee('Engineer')
            ->assertSee('name="role"', false)->assertSee('page=2', false);
        $this->assertSame(17, $response->viewData('users')->total());
        $this->get(route('users.index', ['page' => 2]))->assertOk();
    }

    public function test_admin_can_assign_each_role_without_changing_other_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $original = $user->only(['name', 'email', 'password', 'is_active']);

        foreach (['engineer', 'admin', 'user'] as $role) {
            $this->actingAs($admin)->from(route('users.index'))
                ->patch(route('users.role.update', $user), [
                    'role' => $role, 'name' => 'Changed', 'email' => 'changed@example.com',
                    'password' => 'changed', 'is_active' => false,
                ])->assertRedirect(route('users.index'))->assertSessionHas('success');
            $this->assertSame($role, $user->fresh()->role);
            $this->assertSame($original, $user->fresh()->only(array_keys($original)));
        }
    }

    public function test_autosave_returns_confirmation_after_persisting_the_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $this->actingAs($admin)->patchJson(route('users.role.update', $user), ['role' => 'engineer'])
            ->assertOk()->assertJson(['message' => 'Role saved.', 'role' => 'engineer']);
        $this->assertSame('engineer', $user->fresh()->role);

        $this->patchJson(route('users.role.update', $user), ['role' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->assertSame('engineer', $user->fresh()->role);
    }

    public function test_guests_and_non_admins_cannot_view_or_assign_roles(): void
    {
        $target = User::factory()->create();
        $this->get(route('users.index'))->assertRedirect(route('login'));
        $this->patch(route('users.role.update', $target), ['role' => 'admin'])->assertRedirect(route('login'));

        foreach (['user', 'engineer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('users.index'))->assertForbidden();
            $this->patch(route('users.role.update', $target), ['role' => 'admin'])->assertForbidden();
        }
        $this->assertSame('user', $target->fresh()->role);
    }

    public function test_invalid_roles_and_missing_accounts_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $user = User::factory()->create();

        foreach ([[], ['role' => 'personnel'], ['role' => 'manager'], ['role' => ['admin']]] as $payload) {
            $this->patch(route('users.role.update', $user), $payload)->assertSessionHasErrors('role');
            $this->assertSame('user', $user->fresh()->role);
        }
        $this->patch(route('users.role.update', 99999), ['role' => 'engineer'])->assertNotFound();
    }

    public function test_admin_can_create_user_and_engineer_accounts_without_password_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Mail::fake();
        $this->get(route('users.create'))->assertOk()->assertSee('Add Account')
            ->assertDontSee('value="admin"', false)->assertDontSee('name="password"', false)
            ->assertDontSee('name="password_confirmation"', false)->assertDontSee('name="phone_number"', false);

        foreach (['user', 'engineer'] as $role) {
            $this->post(route('users.store'), [
                'name' => 'New Account', 'email' => $role.'@psu.edu.ph',
                'role' => $role,
            ])->assertRedirect(route('users.index'))->assertSessionHas('success');
            $created = User::where('email', $role.'@psu.edu.ph')->firstOrFail();
            $this->assertSame($role, $created->role);
            $this->assertFalse(Hash::check('password123', $created->password));
            $this->assertNull($created->phone_number);
            $this->assertTrue($created->is_active);
            $this->assertFalse($created->is_verified);
            $this->assertTrue($created->must_change_password);
            $this->assertNull($created->email_verified_at);
            Mail::assertSent(AccountCreated::class, function (AccountCreated $mail) use ($created): bool {
                $this->assertMatchesRegularExpression('/\A[A-Za-z0-9]{8}\z/', $mail->generatedPassword);

                return $mail->hasTo($created->email)
                    && $mail->email === $created->email
                    && $mail->role === $created->role
                    && Hash::check($mail->generatedPassword, $created->password);
            });
        }
    }

    public function test_new_account_validation_rejects_duplicate_email_and_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Duplicate', 'email' => $admin->email,
            'role' => 'admin',
        ])->assertSessionHasErrors(['email', 'role']);
        $this->assertSame(1, User::count());
    }

    public function test_removed_fields_are_ignored_and_admin_creation_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post(route('users.store'), [
            'name' => 'New User', 'email' => 'new.user@psu.edu.ph', 'role' => 'user',
            'password' => 'injected-password', 'phone_number' => '123456789',
        ])->assertRedirect(route('users.index'));
        $created = User::where('email', 'new.user@psu.edu.ph')->firstOrFail();
        $this->assertFalse(Hash::check('injected-password', $created->password));
        $this->assertNull($created->phone_number);
        $this->post(route('users.store'), [
            'name' => 'New Admin', 'email' => 'new.admin@psu.edu.ph', 'role' => 'admin',
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'new.admin@psu.edu.ph']);
    }

    public function test_credentials_email_matches_the_existing_template_and_escapes_content(): void
    {
        $mail = new AccountCreated('Jane <User>', '22as1345_ms@psu.edu.ph', 'Random<Password>123!', 'engineer', route('password.reset', ['token' => 'test-token', 'email' => '22as1345_ms@psu.edu.ph']));
        $mail->assertHasSubject('Your PPMMIS Account Login Details');
        $mail->assertSeeInHtml('Pangasinan State University');
        $mail->assertSeeInHtml('22as1345_ms@psu.edu.ph');
        $mail->assertSeeInHtml('Random<Password>123!');
        $mail->assertSeeInHtml('Jane <User>');
        $mail->assertSeeInHtml('Sign In to PPMMIS');
        $mail->assertSeeInHtml('Engineer');
    }

    public function test_mail_failure_does_not_report_credentials_as_sent(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Mail::shouldReceive('to')->once()->with('new@psu.edu.ph')->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('SMTP unavailable'));

        $this->post(route('users.store'), [
            'name' => 'New Account', 'email' => 'new@psu.edu.ph', 'role' => 'user',
        ])->assertRedirect(route('users.index'))->assertSessionHas('error')->assertSessionMissing('success');
        $this->assertDatabaseHas('users', ['email' => 'new@psu.edu.ph']);
    }

    public function test_admin_can_remove_another_account_and_its_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        DB::table('sessions')->insert(['id' => 'target-session', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);
        $this->actingAs($admin)->delete(route('users.destroy', $target))->assertRedirect(route('users.index'));
        $this->assertModelMissing($target);
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->delete(route('users.destroy', $admin))->assertSessionHas('error');
        $this->assertModelExists($admin);
        $this->delete(route('users.destroy', 99999))->assertNotFound();
    }

    public function test_account_with_linked_records_cannot_be_removed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create();
        DB::table('facility_requests')->insert([
            'user_id' => $target->id, 'request_number' => 'FR-TEST', 'facility' => 'Hall',
            'category' => 'Meeting', 'requested_date' => '2026-11-07', 'purpose' => 'Meeting',
        ]);
        $this->actingAs($admin)->delete(route('users.destroy', $target))->assertSessionHas('error');
        $this->assertModelExists($target);
        $this->assertDatabaseHas('facility_requests', ['user_id' => $target->id]);
    }

    public function test_work_requests_keep_requester_details_after_account_removal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['department' => 'Engineering', 'phone_number' => '09123456789']);
        foreach ([false, true] as $deleted) {
            DB::table('work_requests')->insert([
                'user_id' => $target->id, 'requester_id' => $target->id, 'assigned_to' => $target->id,
                'title' => 'Repair', 'description' => 'Retain this description',
                'department' => 'Original department', 'request_number' => 'WR-'.(int) $deleted,
                'deleted_at' => $deleted ? now() : null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->actingAs($admin)->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'))->assertSessionHas('success');
        $this->assertModelMissing($target);
        $requests = WorkRequest::withTrashed()->get();
        $this->assertCount(2, $requests);
        foreach ($requests as $request) {
            $this->assertNull($request->user_id);
            $this->assertNull($request->requester_id);
            $this->assertNull($request->assigned_to);
            $this->assertNull($request->user);
            $this->assertSame($target->name, $request->requester_display_name);
            $this->assertSame($target->email, $request->requester_snapshot['email']);
            $this->assertSame('Engineering', $request->requester_snapshot['department']);
            $this->assertSame('09123456789', $request->requester_snapshot['phone_number']);
            $this->assertSame('Original department', $request->department);
            $this->assertSame('Retain this description', $request->description);
        }
        $this->get(route('work-requests.index'))->assertOk()->assertSee($target->name);
        $this->get(route('work-requests.index', ['search' => $target->name]))
            ->assertOk()->assertSee('WR-0');
    }

    public function test_legacy_requester_and_assignment_links_are_disconnected_without_changing_another_owner(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $target = User::factory()->create();
        $otherOwner = User::factory()->create();
        DB::table('work_requests')->insert([
            'requester_id' => $target->id, 'title' => 'Legacy', 'description' => 'Legacy request', 'request_number' => 'WR-LEGACY',
        ]);
        DB::table('work_requests')->insert([
            'user_id' => $otherOwner->id, 'requester_id' => $target->id, 'assigned_to' => $target->id,
            'title' => 'Other', 'description' => 'Other request', 'request_number' => 'WR-OTHER',
        ]);
        $this->delete(route('users.destroy', $target))->assertSessionHas('success');
        $this->assertModelMissing($target);
        $legacy = WorkRequest::where('request_number', 'WR-LEGACY')->firstOrFail();
        $this->assertSame($target->email, $legacy->requester_snapshot['email']);
        $other = WorkRequest::where('request_number', 'WR-OTHER')->firstOrFail();
        $this->assertSame($otherOwner->id, $other->user_id);
        $this->assertNull($other->requester_snapshot);
        $this->assertNull($other->requester_id);
        $this->assertNull($other->assigned_to);
    }

    public function test_inventory_records_still_prevent_account_removal(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $target = User::factory()->create();
        DB::table('inventory_entries')->insert(['user_id' => $target->id, 'form' => 'test', 'data' => '{}']);
        $this->delete(route('users.destroy', $target))->assertSessionHas('error');
        $this->assertModelExists($target);
    }

    public function test_only_admins_can_add_or_remove_accounts(): void
    {
        $target = User::factory()->create();
        $this->get(route('users.create'))->assertRedirect(route('login'));
        $this->post(route('users.store'), [])->assertRedirect(route('login'));
        $this->delete(route('users.destroy', $target))->assertRedirect(route('login'));
        foreach (['user', 'engineer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('users.create'))->assertForbidden();
            $this->post(route('users.store'), [])->assertForbidden();
            $this->delete(route('users.destroy', $target))->assertForbidden();
        }
        $this->assertModelExists($target);
    }

    public function test_admin_cannot_remove_their_own_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('users.role.update', $admin), ['role' => 'user'])
            ->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
