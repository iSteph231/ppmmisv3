<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityRequestProgramImageTest extends TestCase
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

    public function test_missing_image_automatically_declines_and_explains_the_reason(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($owner)->get(route('request-facility.create'))
            ->assertOk()->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="program_image"', false)->assertSeeText('automatically declined');
        $this->post(route('request-facility.store'), $this->submission() + [
            'status' => 'approved', 'program_image_path' => 'spoof.png',
        ])->assertRedirect(route('request-facility.index'))->assertSessionHas('error');
        $facility = FacilityRequest::sole();
        $this->assertSame('declined', $facility->status);
        $this->assertNull($facility->program_image_path);
        $this->assertStringContainsString('no program or event planner image', $facility->decline_reason);
        $this->assertSame($owner->id, Notification::sole()->user_id);
        $this->get(route('request-facility.index', ['status' => 'declined']))->assertOk()->assertSeeText('Declined')->assertSee($facility->request_number);
        $this->get(route('request-facility.show', $facility))->assertOk()->assertSeeText($facility->decline_reason)->assertDontSeeText('awaiting admin approval');
        $this->get(route('request-facility.create'))->assertOk();
        $this->actingAs($admin)->patch(route('request-facility.approve', $facility))->assertStatus(409);
        $this->assertSame('declined', $facility->fresh()->status);
    }

    public function test_valid_image_is_stored_privately_and_request_remains_pending(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($owner)->post(route('request-facility.store'), $this->submission() + [
            'program_image' => UploadedFile::fake()->image('event-program.png'),
        ])->assertRedirect(route('request-facility.index'))->assertSessionHasNoErrors()->assertSessionHas('success');
        $facility = FacilityRequest::sole();
        $this->assertSame('pending', $facility->status);
        $this->assertNull($facility->decline_reason);
        Storage::disk('local')->assertExists($facility->program_image_path);
        $this->assertNotSame('event-program.png', basename($facility->program_image_path));
        $this->assertSame($admin->id, Notification::sole()->user_id);
        $url = route('request-facility.program-image', $facility);
        $this->get(route('request-facility.show', $facility))->assertOk()->assertSee($url);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->actingAs($admin)->get($url)->assertOk();
        $this->get(route('request-facility.show', $facility))->assertOk()->assertSeeText('Program or Event Planner');
        $this->patch(route('request-facility.approve', $facility))->assertRedirect();
        $this->assertSame('approved', $facility->fresh()->status);
        foreach (['user', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get($url)->assertForbidden();
        }
        auth()->forgetGuards();
        $this->get($url)->assertRedirect(route('login'));
    }

    public function test_invalid_and_oversized_uploads_are_rejected_without_saving_requests(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        foreach ([
            UploadedFile::fake()->create('program.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->create('program.png', 10, 'text/plain'),
            UploadedFile::fake()->image('program.png')->size(5121),
            UploadedFile::fake()->image('program.gif'),
            UploadedFile::fake()->image('program.txt'),
        ] as $upload) {
            $this->post(route('request-facility.store'), $this->submission() + ['program_image' => $upload])
                ->assertSessionHasErrors('program_image');
        }
        $this->assertDatabaseCount('facility_requests', 0);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_missing_or_deleted_attachment_returns_not_found(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner)->post(route('request-facility.store'), $this->submission())->assertRedirect();
        $facility = FacilityRequest::sole();
        $this->get(route('request-facility.program-image', $facility))->assertNotFound();
        $facility->update(['program_image_path' => 'missing.png']);
        $this->get(route('request-facility.program-image', $facility))->assertNotFound();
    }

    public function test_failed_transaction_removes_uploaded_image(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']));
        FacilityRequest::creating(function (): void {
            throw new \RuntimeException('Simulated persistence failure');
        });
        $this->withoutExceptionHandling();
        try {
            $this->post(route('request-facility.store'), $this->submission() + [
                'program_image' => UploadedFile::fake()->image('program.png'),
            ]);
            $this->fail('Expected persistence failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated persistence failure', $exception->getMessage());
        } finally {
            FacilityRequest::flushEventListeners();
        }
        $this->assertDatabaseCount('facility_requests', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    /** @return array<string, string> */
    private function submission(): array
    {
        return [
            'facility' => 'Activity Center',
            'requested_date' => now()->addDay()->toDateString(),
            'requested_time' => '09:30',
            'purpose' => 'Event program',
            'lead_person' => 'Event Lead',
            'contact_number' => '09123456789',
            'participants' => 'Participant - College',
            'requested_by' => 'Event Requester',
        ];
    }
}
