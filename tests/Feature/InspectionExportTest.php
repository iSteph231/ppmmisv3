<?php

namespace Tests\Feature;

use App\Models\InspectionReport;
use App\Models\User;
use App\Models\WorkRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class InspectionExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_062233_create_work_requests_table.php',
            '2026_04_28_080517_add_user_id_to_work_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        require_once database_path('migrations/2026_05_04_142741_create_inspection_reports_table.php');
        (new \CreateInspectionReportsTable)->up();
        Schema::table('inspection_reports', function (Blueprint $table): void {
            $table->string('status')->change();
        });
    }

    public function test_export_applies_search_and_dates_and_includes_only_approved_reports(): void
    {
        $owner = User::factory()->create();
        $workRequest = WorkRequest::factory()->create(['user_id' => $owner->id, 'title' => 'Repair ceiling']);
        $included = $this->report($workRequest, 'IR-INCLUDED', 'approved', '2026-11-15');
        $this->report($workRequest, 'IR-OLD', 'approved', '2026-10-15');
        $this->report($workRequest, 'IR-PENDING', 'pending', '2026-11-15');
        $otherWork = WorkRequest::factory()->create(['user_id' => $owner->id, 'title' => 'Install lights']);
        $this->report($otherWork, 'IR-OTHER', 'approved', '2026-11-15');
        $this->expectPdf([$included->id]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('inspections.export-all-pdf', [
                'search' => 'Repair ceiling', 'date_from' => '2026-11-01', 'date_to' => '2026-11-30',
            ]))->assertOk()->assertSee('PDF download');
    }

    public function test_export_preserves_owner_access_and_accepts_only_an_end_date(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownWork = WorkRequest::factory()->create(['user_id' => $owner->id]);
        $otherWork = WorkRequest::factory()->create(['user_id' => $other->id]);
        $included = $this->report($ownWork, 'IR-OWN', 'approved', '2026-11-15');
        $this->report($otherWork, 'IR-OTHER', 'approved', '2026-11-15');
        $this->report($ownWork, 'IR-FUTURE', 'approved', '2026-12-15');
        $this->expectPdf([$included->id]);

        $this->actingAs($owner)->get(route('inspections.export-all-pdf', ['date_to' => '2026-11-30']))->assertOk();
    }

    public function test_invalid_export_dates_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->getJson(route('inspections.export-all-pdf', ['date_from' => 'invalid']))
            ->assertUnprocessable()->assertJsonValidationErrors('date_from');
        $this->getJson(route('inspections.export-all-pdf', ['date_from' => '2026-11-30', 'date_to' => '2026-11-01']))
            ->assertUnprocessable()->assertJsonValidationErrors('date_to');
    }

    private function report(WorkRequest $workRequest, string $number, string $status, string $date): InspectionReport
    {
        return InspectionReport::create([
            'work_request_id' => $workRequest->id, 'report_number' => $number,
            'status' => $status, 'scheduled_date' => $date,
        ]);
    }

    private function expectPdf(array $expectedIds): void
    {
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data) use ($expectedIds): bool {
            $this->assertSame('inspection-reports.export-pdf', $view);
            $this->assertSame($expectedIds, $data['inspectionReports']->pluck('id')->all());

            return true;
        })->andReturn($pdf);
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        $pdf->shouldReceive('download')->once()->andReturn(response('PDF download'));
    }
}
