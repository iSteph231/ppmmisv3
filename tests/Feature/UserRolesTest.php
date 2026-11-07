<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_11_07_140405_create_account_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function test_migration_preserves_accounts_and_converts_removed_roles_to_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $formerPersonnel = User::factory()->create(['role' => 'personnel']);

        (require database_path('migrations/2026_11_03_141219_normalize_user_roles.php'))->up();
        (require database_path('migrations/2026_11_03_141314_restrict_user_roles.php'))->up();

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame('user', $formerPersonnel->fresh()->role);
        $this->assertSame(3, User::count());

        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $user->id)->update(['role' => 'personnel']);
    }

    public function test_user_creation_rejects_removed_and_unknown_roles(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['personnel', 'manager'] as $role) {
            $this->post(route('users.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => $role,
            ])->assertSessionHasErrors('role');
            $this->assertSame(1, User::count());
        }
    }

    public function test_user_updates_accept_admin_user_and_engineer(): void
    {
        (require database_path('migrations/2026_11_03_141756_add_engineer_to_user_roles.php'))->up();
        $user = User::factory()->create(['role' => 'user']);

        foreach (['admin', 'engineer', 'user', 'personnel', 'manager'] as $role) {
            $response = app(UserController::class)->update(Request::create('/users/'.$user->id, 'PUT', [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
            ]), $user->id);

            if (in_array($role, ['admin', 'user', 'engineer'])) {
                $this->assertSame($role, $user->fresh()->role);
            } else {
                $this->assertTrue($response->getSession()->get('errors')->has('role'));
                $this->assertSame('user', $user->fresh()->role);
            }
        }
    }

    public function test_engineer_role_can_be_stored_without_admin_privileges(): void
    {
        (require database_path('migrations/2026_11_03_141756_add_engineer_to_user_roles.php'))->up();
        $engineer = User::factory()->create(['role' => 'engineer']);

        $this->assertTrue($engineer->fresh()->isEngineer());
        $this->assertFalse($engineer->isAdmin());
        $this->actingAs($engineer)->get('/maintenance')->assertForbidden();
        $this->get('/users')->assertForbidden();
    }

    public function test_registration_never_assigns_the_requested_engineer_role(): void
    {
        (require database_path('migrations/2026_05_13_205842_add_otp_fields_to_users_table.php'))->up();
        (require database_path('migrations/2026_11_03_141756_add_engineer_to_user_roles.php'))->up();
        Mail::fake();

        $this->get(route('register'))->assertOk()->assertDontSee('Engineer');
        $this->post(route('register'), [
            'name' => 'New Registrant',
            'email' => 'registrant@psu.edu.ph',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'engineer',
        ])->assertRedirect(route('register'));

        $this->assertSame(0, User::count());
        $this->assertDatabaseHas('account_requests', ['email' => 'registrant@psu.edu.ph']);
        Mail::assertNothingSent();
    }

    public function test_regular_users_cannot_access_maintenance(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get('/maintenance')->assertForbidden();
    }
}
