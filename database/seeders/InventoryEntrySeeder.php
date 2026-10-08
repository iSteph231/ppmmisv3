<?php

namespace Database\Seeders;

use App\Models\InventoryEntry;
use App\Support\InventoryForms;
use Illuminate\Database\Seeder;

class InventoryEntrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (array_keys(InventoryForms::names()) as $form) {
            InventoryEntry::factory()->create([
                'form' => $form,
                'data' => $form === 'walls'
                    ? ['building_name' => 'Main Building', 'room' => '101', 'item_number' => 'W-001', 'height' => 3, 'width' => 4, 'material' => 'Concrete']
                    : ['building_name' => 'Main Building', 'room' => '101', 'item_number' => '001'],
            ]);
        }
    }
}
