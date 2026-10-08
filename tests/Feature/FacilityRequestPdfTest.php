<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityRequestPdfTest extends TestCase
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

    private function finishedRequest(): FacilityRequest
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner);

        return FacilityRequest::create([
            'user_id' => $owner->id,
            'request_number' => 'FR-PDF-0001',
            'facility' => 'Conference Room',
            'category' => 'Room Setup',
            'requested_date' => now()->addDay(),
            'purpose' => 'Meeting setup <script>alert(1)</script>',
            'status' => 'finished',
            'before_photo_path' => UploadedFile::fake()->image('before.jpg')->store('facility-requests', 'local'),
            'after_photo_path' => UploadedFile::fake()->image('after.png')->store('facility-requests', 'local'),
        ]);
    }

    public function test_only_admin_can_download_the_evaluation_form(): void
    {
        $facility = $this->finishedRequest();
        foreach ([User::factory()->create(['role' => 'admin'])] as $user) {
            $this->actingAs($user);
            $index = $this->get(route('request-facility.index'))->assertOk();
            $index->assertSee(route('request-facility.export-pdf', $facility), false);
            $this->get(route('request-facility.show', $facility))->assertOk()->assertSee('Export PDF');
            $response = $this->get(route('request-facility.export-pdf', $facility));
            $response->assertOk()->assertDownload('facility-request-FR-PDF-0001.pdf')->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        }
        $html = view('facility-requests.export-pdf', [
            'facilityRequest' => $facility->load('user'),
            'photos' => ['before' => 'data:image/jpeg;base64,before', 'after' => 'data:image/png;base64,after'],
        ])->render();
        $this->assertStringNotContainsString('data:image/jpeg;base64,before', $html);
        $this->assertStringNotContainsString('data:image/png;base64,after', $html);
        $this->assertStringContainsString('FACILITY EVALUATION FORM', $html);
        $this->assertStringNotContainsString('Request Details', $html);
        $this->assertStringNotContainsString('Usage Photos', $html);
        $this->assertStringNotContainsString('Meeting setup', $html);
    }

    public function test_unfinished_requests_have_no_export_button_and_cannot_be_exported(): void
    {
        $facility = $this->finishedRequest();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['pending', 'approved'] as $status) {
            $facility->update(['status' => $status]);
            $this->get(route('request-facility.export-pdf', $facility))->assertStatus(409);
            $this->get(route('request-facility.index'))->assertOk()->assertDontSee('Export PDF');
            $this->get(route('request-facility.show', $facility))->assertOk()->assertDontSee('Export PDF');
        }
    }

    public function test_other_users_and_personnel_cannot_export_and_guests_must_log_in(): void
    {
        $facility = $this->finishedRequest();
        $this->get(route('request-facility.export-pdf', $facility))->assertForbidden();
        $this->get(route('request-facility.show', $facility))->assertOk()->assertDontSee('Export PDF');
        $this->get(route('request-facility.index'))->assertOk()->assertDontSee('<th scope="col">Action</th>', false);
        foreach (['user', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('request-facility.export-pdf', $facility))->assertForbidden();
        }
        auth()->forgetGuards();
        $this->get(route('request-facility.export-pdf', $facility))->assertRedirect(route('login'));
    }

    public function test_missing_photo_does_not_affect_evaluation_export(): void
    {
        $facility = $this->finishedRequest();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Storage::disk('local')->delete($facility->after_photo_path);
        $this->get(route('request-facility.export-pdf', $facility))->assertOk()->assertDownload();
    }
}
