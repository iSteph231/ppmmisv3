<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\InventoryForms;
use Tests\TestCase;

class InventoryFormsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_10_08_105045_create_inventory_entries_table.php',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true])->assertExitCode(0);
        }
    }

    public function test_admin_can_browse_the_numbered_inventory_list_in_name_order(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $user->id = 1;

        $response = $this->actingAs($user)->get(route('inventory.index'));

        $response->assertOk()->assertSee('<ol class="list-decimal', false);
        $response->assertSeeTextInOrder(array_values(InventoryForms::names()));

        foreach (InventoryForms::names() as $slug => $name) {
            $response->assertSee(route('inventory.'.$slug));
        }
    }

    public function test_all_inventory_links_open_data_entry_pages(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $user->id = 1;

        foreach (InventoryForms::names() as $slug => $name) {
            $this->actingAs($user)->get(route('inventory.'.$slug))
                ->assertOk()
                ->assertSeeText('New '.$name.' Entry')
                ->assertSeeText('Back to Inventory')
                ->assertSeeText('Save Entry')
                ->assertDontSee('class="cell-input"', false)
                ->assertDontSeeText('Print Inventory');
        }
    }

    public function test_guests_are_redirected_from_inventory_and_every_form(): void
    {
        foreach (array_merge(['index'], array_keys(InventoryForms::names())) as $slug) {
            $this->get(route('inventory.'.$slug))->assertRedirect(route('login'));
        }
    }

    public function test_non_admin_roles_cannot_access_inventory_or_any_form(): void
    {
        foreach (['user', 'personnel'] as $role) {
            $user = User::factory()->make(['role' => $role]);
            $user->id = 1;

            foreach (array_merge(['index'], array_keys(InventoryForms::names())) as $slug) {
                $this->actingAs($user)->get(route('inventory.'.$slug))->assertForbidden();
            }
        }
    }

    public function test_unknown_inventory_form_returns_not_found(): void
    {
        $user = User::factory()->make(['role' => 'admin']);
        $user->id = 1;

        $this->actingAs($user)->get('/inventory/unknown')->assertNotFound();
    }
}
