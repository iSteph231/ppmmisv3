<?php

namespace Tests\Feature;

use App\Models\User;
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
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
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

    public function test_admin_cannot_remove_their_own_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch(route('users.role.update', $admin), ['role' => 'user'])
            ->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
