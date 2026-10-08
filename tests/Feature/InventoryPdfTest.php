<?php

namespace Tests\Feature;

use App\Models\InventoryEntry;
use App\Models\User;
use App\Support\InventoryForms;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class InventoryPdfTest extends TestCase
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

    public function test_admin_can_download_all_types_and_individual_entries_as_real_pdfs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (InventoryForms::names() as $form => $name) {
            $entry = InventoryEntry::factory()->create(['form' => $form]);
            $response = $this->get(route('inventory.export-pdf', $form))
                ->assertOk()->assertDownload('inventory-'.$form.'.pdf')
                ->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $response = $this->get(route('inventory.entries.export-pdf', ['form' => $form, 'entry' => $entry]))
                ->assertOk()->assertDownload('inventory-'.$form.'-'.$entry->id.'.pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->get(route('inventory.'.$form))->assertSeeText('Export All PDF')->assertSee(route('inventory.entries.export-pdf', ['form' => $form, 'entry' => $entry]), false);
        }
    }

    public function test_bulk_export_includes_all_entries_and_excludes_other_types(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        InventoryEntry::factory()->count(11)->create(['form' => 'walls']);
        InventoryEntry::factory()->create(['form' => 'exhaust-fan']);
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            return $view === 'inventory.export-pdf'
                && $data['name'] === 'Walls'
                && $data['entries']->count() === 11
                && $data['entries']->every(fn (InventoryEntry $entry): bool => $entry->form === 'walls');
        })->andReturn($pdf);
        $pdf->shouldReceive('setPaper')->with('a4', 'landscape')->andReturnSelf();
        $pdf->shouldReceive('download')->with('inventory-walls.pdf')->andReturn(response('PDF'));
        $this->get(route('inventory.export-pdf', 'walls'))->assertOk();
    }

    public function test_single_export_contains_only_the_selected_entry(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $entry = InventoryEntry::factory()->create(['form' => 'walls']);
        InventoryEntry::factory()->create(['form' => 'walls']);
        $pdf = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        Pdf::shouldReceive('loadView')->once()->withArgs(fn (string $view, array $data): bool => $data['entries']->count() === 1 && $data['entries']->sole()->is($entry)
        )->andReturn($pdf);
        $pdf->shouldReceive('setPaper')->with('a4', 'landscape')->andReturnSelf();
        $pdf->shouldReceive('download')->with('inventory-walls-'.$entry->id.'.pdf')->andReturn(response('PDF'));
        $this->get(route('inventory.entries.export-pdf', ['form' => 'walls', 'entry' => $entry]))->assertOk();
    }

    public function test_export_template_escapes_values_and_displays_inspections_and_maintenance(): void
    {
        $entry = InventoryEntry::factory()->create(['data' => [
            'building_name' => '<script>alert(1)</script>', 'room' => '101', 'item_number' => 'CO-1',
            'inspection_1_date' => '2026-10-08', 'inspection_1_condition' => 'Working',
            'inspection_1_cleaned' => 1, 'inspection_1_repaired' => 0, 'inspection_1_inspected_by' => 'Inspector',
        ]]);
        $html = view('inventory.export-pdf', [
            'form' => 'convenience-outlet', 'layout' => InventoryForms::pdfLayout('convenience-outlet'),
            'name' => 'Convenience Outlet', 'sections' => InventoryForms::sections('convenience-outlet'),
            'entries' => collect([$entry]),
        ])->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        foreach (['2026-10-08', 'Working', 'Inspector', 'Yes', 'No', 'INSPECTION', 'MAINTENANCE', 'FM-AD-ENG-09d', 'PANGASINAN STATE UNIVERSITY'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
    }

    public function test_empty_inventory_can_be_exported(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $response = $this->get(route('inventory.export-pdf', 'walls'))->assertOk()->assertDownload('inventory-walls.pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_exports_require_admin_and_valid_matching_form_and_entry(): void
    {
        $entry = InventoryEntry::factory()->create(['form' => 'walls']);
        $urls = [route('inventory.export-pdf', 'walls'), route('inventory.entries.export-pdf', ['form' => 'walls', 'entry' => $entry])];
        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        foreach (['user', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach ($urls as $url) {
                $this->get($url)->assertForbidden();
            }
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/inventory/unknown/export/pdf')->assertNotFound();
        $this->get('/inventory/walls/entries/999999/export/pdf')->assertNotFound();
        $this->get(route('inventory.entries.export-pdf', ['form' => 'exhaust-fan', 'entry' => $entry]))->assertNotFound();
    }

    public function test_original_form_headers_rows_and_column_mapping_are_preserved(): void
    {
        foreach (InventoryForms::names() as $form => $name) {
            $data = [];
            $expected = [];
            foreach (InventoryForms::sections($form) as $fields) {
                foreach ($fields as $key => $field) {
                    $data[$key] = $field['type'] === 'checkbox' ? 1 : 'value-'.$key;
                    $expected[] = $field['type'] === 'checkbox' ? 'Yes' : 'value-'.$key;
                }
            }
            $entry = InventoryEntry::factory()->make(['form' => $form, 'data' => $data]);
            $layout = InventoryForms::pdfLayout($form);
            $html = view('inventory.export-pdf', [
                'form' => $form, 'name' => $name, 'layout' => $layout,
                'sections' => InventoryForms::sections($form), 'entries' => collect([$entry]),
            ])->render();
            $this->assertStringContainsString('INVENTORY OF '.strtoupper($name), $html);
            $this->assertStringContainsString($layout['code'], $html);
            $source = file_get_contents(base_path('NEWHTML/inventory_'.str_replace('-', '_', $form).'/inventory_'.str_replace('-', '_', $form).'.php'));
            preg_match('/<thead>.*?<\/thead>/s', $source, $header);
            $this->assertStringContainsString($header[0], $html);
            preg_match('/<tbody>(.*?)<\/tbody>/s', $html, $body);
            preg_match_all('/<tr>(.*?)<\/tr>/s', $body[1], $rows);
            $this->assertCount($layout['rows'], $rows[1]);
            preg_match_all('/<td>(.*?)<\/td>/s', $rows[1][0], $cells);
            $this->assertSame($expected, $cells[1]);
            $this->assertStringNotContainsString('<input', $html);
        }
    }

    public function test_form_sheets_pad_empty_rows_and_continue_with_repeated_headers(): void
    {
        $entry = InventoryEntry::factory()->make(['data' => [
            'building_name' => 'Main', 'room' => '101', 'item_number' => 'CO-1',
        ]]);
        foreach ([0 => 1, 1 => 1, 30 => 1, 31 => 2] as $count => $pages) {
            $html = view('inventory.export-pdf', [
                'form' => 'convenience-outlet', 'name' => 'Convenience Outlet',
                'layout' => InventoryForms::pdfLayout('convenience-outlet'),
                'sections' => InventoryForms::sections('convenience-outlet'),
                'entries' => collect(array_fill(0, $count, $entry)),
            ])->render();
            $this->assertSame($pages, substr_count($html, 'class="sheet"'));
            $this->assertSame($pages, substr_count($html, 'FM-AD-ENG-09d'));
            preg_match_all('/<tbody>(.*?)<\/tbody>/s', $html, $bodies);
            foreach ($bodies[1] as $body) {
                $this->assertSame(30, substr_count($body, '<tr>'));
            }
            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $pdf->output();
            $this->assertSame($pages, $pdf->getDomPDF()->getCanvas()->get_page_count());
        }
    }
}
