<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityAvailabilityTest extends TestCase
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

    public function test_availability_returns_only_reserved_facility_names_for_the_exact_slot(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        foreach (['pending' => 'Room 1', 'approved' => 'Room 2', 'finished' => 'Room 3', 'declined' => 'Room 4'] as $status => $facility) {
            $this->booking($owner, ['status' => $status, 'facility' => $facility]);
        }
        $this->booking($owner, ['facility' => 'Room 5', 'requested_time' => '10:30:00']);
        $this->booking($owner, ['facility' => 'Room 6 (Speech Lab)', 'requested_date' => now()->addDays(2)->toDateString()]);
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => ['Room 1', 'Room 2']])
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_conflicting_submissions_are_rejected_without_creating_requests_or_files(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $requester = User::factory()->create(['role' => 'user']);
        foreach (['pending', 'approved'] as $status) {
            $booking = $this->booking($owner, ['status' => $status]);
            $this->actingAs($requester)->post(route('request-facility.store'), $this->submission())
                ->assertSessionHasErrors('facility');
            $booking->delete();
        }
        $this->assertDatabaseCount('facility_requests', 0);
        $this->assertDatabaseCount('notifications', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_other_facilities_dates_and_times_can_be_requested(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->booking($owner);
        $this->actingAs(User::factory()->create(['role' => 'user']));
        foreach ([['facility' => 'Room 2'], ['requested_time' => '10:30'], ['requested_date' => now()->addDays(2)->toDateString()]] as $changes) {
            $this->post(route('request-facility.store'), array_replace($this->submission(), $changes))
                ->assertSessionHasNoErrors()->assertRedirect(route('request-facility.index'));
        }
        $this->assertDatabaseCount('facility_requests', 4);
    }

    public function test_declined_requests_do_not_block_a_slot_and_new_requests_update_availability(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->booking($owner, ['status' => 'declined']);
        $this->actingAs(User::factory()->create(['role' => 'user']));
        $this->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => []]);
        $this->post(route('request-facility.store'), $this->submission())->assertSessionHasNoErrors();
        $this->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => ['Room 1']]);
    }

    public function test_availability_rejects_invalid_inputs_and_unauthorized_roles(): void
    {
        $this->getJson(route('request-facility.availability', $this->slot()))->assertUnauthorized();
        foreach (['admin', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->getJson(route('request-facility.availability', $this->slot()))->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->getJson(route('request-facility.availability'))->assertUnprocessable()
            ->assertJsonValidationErrors(['requested_date', 'requested_time']);
        $this->getJson(route('request-facility.availability', ['requested_date' => 'invalid', 'requested_time' => '25:00']))
            ->assertUnprocessable()->assertJsonValidationErrors(['requested_date', 'requested_time']);
    }

    public function test_completing_photos_and_evaluation_releases_the_facility_for_another_user(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $booking = $this->booking($owner, ['status' => 'approved']);

        $this->actingAs($otherUser)->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => ['Room 1']]);

        $this->actingAs($owner)->post(route('request-facility.photos.store', $booking), [
            'before_photo' => UploadedFile::fake()->image('before.jpg'),
            'after_photo' => UploadedFile::fake()->image('after.jpg'),
        ])->assertSessionHasNoErrors();
        $this->assertSame('approved', $booking->fresh()->status);
        $this->actingAs($otherUser)->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => ['Room 1']]);

        $this->actingAs($owner)->post(route('request-facility.evaluation.store', $booking), [
            'name' => 'Test Respondent',
            'age' => 21,
            'gender' => 'Female',
            'client_category' => 'Students',
            'semester' => 'First',
            'academic_year' => '2026-2027',
            'ratings' => array_fill_keys(array_keys(FacilityRequest::EVALUATION_INDICATORS), 5),
        ])->assertSessionHasNoErrors();
        $this->assertSame('finished', $booking->fresh()->status);

        $this->actingAs($otherUser)->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => []]);
        $this->post(route('request-facility.store'), $this->submission())
            ->assertSessionHasNoErrors()->assertRedirect(route('request-facility.index'));
        $this->assertDatabaseHas('facility_requests', [
            'user_id' => $otherUser->id,
            'facility' => 'Room 1',
            'status' => 'pending',
        ]);
        $this->getJson(route('request-facility.availability', $this->slot()))
            ->assertOk()->assertExactJson(['unavailable' => ['Room 1']]);
    }

    /** @return array<string, string> */
    private function slot(): array
    {
        return ['requested_date' => now()->addDay()->toDateString(), 'requested_time' => '09:30'];
    }

    /** @param array<string, string> $changes */
    private function booking(User $owner, array $changes = []): FacilityRequest
    {
        return FacilityRequest::create(array_replace($this->slot(), [
            'user_id' => $owner->id,
            'request_number' => 'FR-EXISTING-'.(FacilityRequest::count() + 1),
            'facility' => 'Room 1',
            'requested_time' => '09:30:00',
            'category' => 'Facility Use',
            'purpose' => 'Existing booking',
            'status' => 'pending',
        ], $changes));
    }

    /** @return array<string, mixed> */
    private function submission(): array
    {
        return $this->slot() + [
            'facility' => 'Room 1',
            'program_image' => UploadedFile::fake()->image('program.png'),
            'purpose' => 'Meeting',
            'lead_person' => 'Event Lead',
            'contact_number' => '09123456789',
            'participants' => 'Participant - College',
            'requested_by' => 'Event Requester',
        ];
    }
}
