<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityRequestReportsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_09_24_000000_create_facility_requests_table.php',
            '2026_10_06_052045_add_usage_photos_to_facility_requests_table.php',
            '2026_10_07_105141_add_evaluation_to_facility_requests_table.php',
            '2026_10_07_111240_add_use_details_to_facility_requests_table.php',
            '2026_10_08_112933_add_program_image_to_facility_requests_table.php',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true])->assertExitCode(0);
        }
    }

    private function facility(User $owner, string $number, string $status): FacilityRequest
    {
        return FacilityRequest::create([
            'user_id' => $owner->id,
            'request_number' => $number,
            'facility' => 'Conference Room',
            'category' => 'Room Setup',
            'requested_date' => now(),
            'purpose' => 'Meeting requirements.',
            'status' => $status,
            'before_photo_path' => 'before.jpg',
            'after_photo_path' => 'after.jpg',
            'evaluation' => [
                'name' => 'Evaluation Respondent',
                'age' => 25,
                'gender' => 'Female',
                'semester' => 'First',
                'academic_year' => '2026–2027',
                'client_category' => 'Faculty',
                'ratings' => array_fill_keys(array_keys(FacilityRequest::EVALUATION_INDICATORS), 4),
                'comments' => 'Improve ventilation <script>alert(1)</script>',
                'submitted_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function test_report_shows_only_finished_requests_with_photos_evaluation_and_export(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $finished = $this->facility($owner, 'FR-FINISHED', 'finished');
        $approved = $this->facility($owner, 'FR-APPROVED', 'approved');
        $pending = $this->facility($owner, 'FR-PENDING', 'pending');
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('reports.facility-requests'))
            ->assertOk()
            ->assertSee($finished->request_number)
            ->assertDontSee($approved->request_number)
            ->assertDontSee($pending->request_number)
            ->assertSee(route('request-facility.photo', [$finished, 'before']))
            ->assertSee(route('request-facility.photo', [$finished, 'after']))
            ->assertSee(route('request-facility.export-pdf', $finished))
            ->assertSee('Evaluation Respondent')
            ->assertSee('Improve ventilation &lt;script&gt;', false)
            ->assertSee('4/5');

        $approved->update(['status' => 'finished']);
        $this->get(route('reports.facility-requests'))->assertOk()->assertSee($approved->request_number);
        $this->assertStringContainsString(route('reports.facility-requests'), view('reports.index')->render());
    }

    public function test_report_is_admin_only_and_has_an_empty_state(): void
    {
        $this->get(route('reports.facility-requests'))->assertRedirect(route('login'));
        foreach (['user', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('reports.facility-requests'))->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('reports.facility-requests'))->assertOk()->assertSee('No completed facility requests yet.');
    }

    public function test_report_paginates_and_handles_old_requests_without_evaluation(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        foreach (range(1, 11) as $number) {
            $this->facility($owner, 'FR-'.$number, 'finished')->update(['evaluation' => null, 'before_photo_path' => null, 'after_photo_path' => null]);
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('reports.facility-requests'))->assertOk()
            ->assertViewHas('facilityRequests', fn ($requests) => $requests->total() === 11 && $requests->count() === 10)
            ->assertSee('No evaluation was recorded')
            ->assertSee('Photo unavailable.');
        $this->get(route('reports.facility-requests', ['page' => 2]))->assertOk()
            ->assertViewHas('facilityRequests', fn ($requests) => $requests->count() === 1);
    }

    public function test_pdf_uses_evaluation_layout_with_all_ratings_and_escaped_comments(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $finished = $this->facility($owner, 'FR-EVALUATION-PDF', 'finished');
        $html = view('facility-requests.export-pdf', [
            'facilityRequest' => $finished->load('user'),
            'photos' => ['before' => null, 'after' => null],
        ])->render();

        $this->assertStringContainsString('FACILITY EVALUATION FORM', $html);
        $this->assertStringContainsString('A. PROFILE OF THE RESPONDENT', $html);
        $this->assertStringContainsString('DEGREE OF SATISFACTION', $html);
        $this->assertStringContainsString('Evaluation Respondent', $html);
        $this->assertStringContainsString('[X] Faculty', $html);
        $this->assertSame(9, substr_count($html, '>X</td>'));
        foreach (FacilityRequest::EVALUATION_INDICATORS as $label) {
            $this->assertStringContainsString($label, $html);
        }
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('request-facility.export-pdf', $finished))
            ->assertOk()->assertDownload('facility-request-FR-EVALUATION-PDF.pdf')
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
