<?php

namespace Tests\Feature;

use App\Models\InventoryEntry;
use App\Models\User;
use App\Support\InventoryForms;
use Tests\TestCase;

class InventoryEntryTest extends TestCase
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

    public function test_admin_can_save_and_view_each_inventory_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (InventoryForms::names() as $form => $name) {
            $data = $this->entryData($form);
            $this->actingAs($admin)->get(route('inventory.'.$form))
                ->assertOk()->assertSeeText('New '.$name.' Entry')
                ->assertSee('data[building_name]', false)->assertDontSeeText('Print Inventory');
            $this->post(route('inventory.entries.store', $form), ['data' => $data, 'user_id' => 999])
                ->assertRedirect(route('inventory.'.$form))->assertSessionHas('success');
            $entry = InventoryEntry::where('form', $form)->sole();
            $this->assertSame($admin->id, $entry->user_id);
            $this->assertSame($data, $entry->data);
            $this->get(route('inventory.'.$form))->assertSeeText('Main Building')->assertSeeText('ITEM-001');
        }
        $this->assertDatabaseCount('inventory_entries', 6);
    }

    public function test_each_inventory_type_has_its_specific_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (InventoryForms::names() as $form => $name) {
            $response = $this->get(route('inventory.'.$form))->assertOk();
            foreach (InventoryForms::sections($form) as $fields) {
                foreach ($fields as $key => $field) {
                    $response->assertSee('name="data['.$key.']"', false)->assertSeeText($field['label']);
                }
            }
            if ($form !== 'walls') {
                $response->assertDontSee('data[height]', false);
            }
        }
    }

    public function test_required_fields_dates_dimensions_and_unknown_keys_are_validated(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post(route('inventory.entries.store', 'walls'), ['data' => []])
            ->assertSessionHasErrors(['data.building_name', 'data.room', 'data.item_number', 'data.height', 'data.width', 'data.material']);
        $data = array_merge($this->entryData('walls'), [
            'height' => -1, 'width' => 'invalid', 'inspection_1_date' => 'not-a-date',
            'inspection_1_repaired' => 'invalid',
        ]);
        $this->post(route('inventory.entries.store', 'walls'), ['data' => $data])
            ->assertSessionHasErrors(['data.height', 'data.width', 'data.inspection_1_date', 'data.inspection_1_repaired']);
        $this->post(route('inventory.entries.store', 'exhaust-fan'), [
            'data' => $this->entryData('exhaust-fan') + ['height' => 3],
        ])->assertSessionHasErrors('data');
        $this->post(route('inventory.entries.store', 'exhaust-fan'), [
            'data' => array_merge($this->entryData('exhaust-fan'), ['room' => str_repeat('x', 256)]),
        ])->assertSessionHasErrors('data.room');
        $this->assertDatabaseCount('inventory_entries', 0);
    }

    public function test_optional_dates_and_maintenance_can_be_empty_and_zero_is_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = $this->entryData('exhaust-fan');
        unset($data['inspection_1_date'], $data['inspection_2_date']);
        foreach (array_keys($data) as $key) {
            if (str_ends_with($key, '_cleaned') || str_ends_with($key, '_repaired') || str_ends_with($key, '_replaced')) {
                unset($data[$key]);
            }
        }
        $data['room'] = '0';
        $data['item_number'] = '0';
        $data['inspection_1_cleaned'] = '0';
        $this->post(route('inventory.entries.store', 'exhaust-fan'), ['data' => $data])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($data, InventoryEntry::sole()->data);
        $this->get(route('inventory.exhaust-fan'))->assertOk()->assertSeeText('No');
    }

    public function test_records_are_scoped_to_the_selected_inventory_type_and_output_is_escaped(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        InventoryEntry::factory()->create(['form' => 'walls', 'data' => [
            'building_name' => '<script>alert(1)</script>', 'room' => '101', 'item_number' => 'WALL-PRIVATE',
        ]]);
        $this->get(route('inventory.exhaust-fan'))->assertDontSeeText('WALL-PRIVATE');
        $this->get(route('inventory.walls'))->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_guests_and_non_admins_cannot_save_any_inventory_type(): void
    {
        foreach (array_keys(InventoryForms::names()) as $form) {
            $this->post(route('inventory.entries.store', $form), ['data' => $this->entryData($form)])
                ->assertRedirect(route('login'));
        }
        foreach (['user', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (array_keys(InventoryForms::names()) as $form) {
                $this->get(route('inventory.'.$form))->assertForbidden();
                $this->post(route('inventory.entries.store', $form), ['data' => $this->entryData($form)])->assertForbidden();
            }
        }
        $this->assertDatabaseCount('inventory_entries', 0);
    }

    public function test_unknown_form_cannot_be_saved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/inventory/unknown/entries', ['data' => $this->entryData('walls')])->assertNotFound();
        $this->assertDatabaseCount('inventory_entries', 0);
    }

    public function test_all_inventory_text_boxes_reject_missing_empty_and_whitespace_values(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (array_keys(InventoryForms::names()) as $form) {
            $response = $this->get(route('inventory.'.$form))->assertOk();
            preg_match_all('/<input\b[^>]*type="text"[^>]*>/s', $response->getContent(), $inputs);
            foreach ($inputs[0] as $input) {
                $this->assertMatchesRegularExpression('/\brequired\b/', $input);
            }
            $keys = ['building_name', 'room', 'item_number', 'inspection_1_condition', 'inspection_1_inspected_by', 'inspection_2_condition', 'inspection_2_inspected_by'];
            if ($form === 'walls') {
                $keys[] = 'material';
            }
            foreach (['missing', '', '   '] as $empty) {
                $data = $this->entryData($form);
                foreach ($keys as $key) {
                    if ($empty === 'missing') {
                        unset($data[$key]);
                    } else {
                        $data[$key] = $empty;
                    }
                }
                $this->post(route('inventory.entries.store', $form), ['data' => $data])
                    ->assertSessionHasErrors(array_map(fn (string $key): string => 'data.'.$key, $keys));
            }
        }
        $this->assertDatabaseCount('inventory_entries', 0);
    }

    /** @return array<string, mixed> */
    private function entryData(string $form): array
    {
        $data = [];
        foreach (InventoryForms::sections($form) as $fields) {
            foreach ($fields as $key => $field) {
                $data[$key] = match ($field['type']) {
                    'date' => '2026-10-08',
                    'number' => '3.5',
                    'checkbox' => '1',
                    default => 'Sample '.$field['label'],
                };
            }
        }

        return array_merge($data, ['building_name' => 'Main Building', 'item_number' => 'ITEM-001']);
    }
}
