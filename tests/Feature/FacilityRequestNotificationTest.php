<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityRequestNotificationTest extends TestCase
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
            $this->artisan('migrate', [
                '--path' => 'database/migrations/'.$migration,
                '--no-interaction' => true,
            ])->assertExitCode(0);
        }
    }

    public function test_submitted_facility_requests_notify_each_admin_only(): void
    {
        $admins = User::factory()->count(2)->create(['role' => 'admin']);
        $requester = User::factory()->create(['role' => 'user']);
        $personnel = User::factory()->create(['role' => 'personnel']);

        $this->actingAs($requester)->post(route('request-facility.store'), [
            'program_image' => UploadedFile::fake()->image('program.png'),
            'facility' => 'Conference Room A',
            'category' => 'Room Setup',
            'requested_date' => now()->addDay()->toDateString(),
            'purpose' => 'Prepare for a meeting.',
            'requested_time' => '09:30',
            'lead_person' => 'Test Lead',
            'contact_number' => '09123456789',
            'participants' => 'Participant - College',
            'requested_by' => 'Test Requestor',
            'status' => 'approved',
            'user_id' => $personnel->id,
        ])->assertRedirect(route('request-facility.index'));

        $facilityRequest = FacilityRequest::sole();
        $this->assertSame('09:30', substr($facilityRequest->requested_time, 0, 5));
        $this->assertSame('Test Lead', $facilityRequest->lead_person);
        $this->assertSame('09123456789', $facilityRequest->contact_number);
        $this->assertSame('Participant - College', $facilityRequest->participants);
        $this->assertSame('Test Requestor', $facilityRequest->requested_by);
        $this->assertSame('Facility Use', $facilityRequest->category);
        $this->assertSame('pending', $facilityRequest->status);
        $this->assertSame($requester->id, $facilityRequest->user_id);
        $this->assertDatabaseCount('notifications', 2);

        foreach ($admins as $admin) {
            $this->actingAs($admin)->get(route('request-facility.show', $facilityRequest))->assertOk()->assertSee('Test Lead')->assertSee('Participant - College')->assertSee('09123456789');
            $notification = Notification::where('user_id', $admin->id)->sole();
            $this->assertFalse($notification->is_read);
            $this->assertSame(FacilityRequest::class, $notification->related_type);
            $this->assertSame($facilityRequest->id, $notification->related_id);
            $this->assertStringContainsString($facilityRequest->request_number, $notification->message);
            $this->assertStringContainsString($requester->name, $notification->message);

            $this->actingAs($admin)->getJson(route('notifications.index'))
                ->assertOk()->assertJsonPath('unread_count', 1)
                ->assertJsonPath('data.0.title', 'New Facility Request');
        }

        foreach ([$requester, $personnel] as $user) {
            $this->actingAs($user)->getJson(route('notifications.index'))
                ->assertOk()->assertJsonPath('unread_count', 0)->assertJsonCount(0, 'data');
        }

        $this->actingAs($admins[0])->postJson(route('notifications.mark-read', $notification->id))
            ->assertNotFound();
        $ownNotification = Notification::where('user_id', $admins[0]->id)->sole();
        $this->postJson(route('notifications.mark-read', $ownNotification->id))->assertOk();
        $this->getJson(route('notifications.index'))->assertJsonPath('unread_count', 0);
        $this->actingAs($admins[1])->getJson(route('notifications.index'))->assertJsonPath('unread_count', 1);
    }

    public function test_invalid_requests_do_not_create_notifications(): void
    {
        User::factory()->create(['role' => 'admin']);
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->post(route('request-facility.store'), [])->assertSessionHasErrors('facility');

        $this->assertDatabaseCount('facility_requests', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_admin_can_review_and_approve_a_request_and_notify_its_owner_once(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $facilityRequest = $this->createFacilityRequest($owner, 'FR-REVIEW-0001');

        $this->actingAs($admin)->get(route('request-facility.index'))
            ->assertOk()->assertSee($facilityRequest->request_number)->assertDontSee('Add Request')->assertSeeText('Action')->assertSeeText('Review Request');
        $this->get(route('request-facility.show', $facilityRequest))
            ->assertOk()->assertSee($facilityRequest->purpose)->assertSee('Approve Request');
        $this->patch(route('request-facility.approve', $facilityRequest))
            ->assertRedirect(route('request-facility.show', $facilityRequest));
        $this->assertSame('approved', $facilityRequest->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'title' => 'Facility Request Approved',
            'related_id' => $facilityRequest->id,
            'is_read' => false,
        ]);
        $this->patch(route('request-facility.approve', $facilityRequest))->assertStatus(409);
        $this->assertDatabaseCount('notifications', 1);
        $this->get(route('request-facility.show', $facilityRequest))
            ->assertOk()->assertDontSee('Approve Request')->assertSee('Approved');
        $this->actingAs($owner)->get(route('request-facility.show', $facilityRequest))
            ->assertOk()->assertSee('Approved')->assertDontSee('Approve Request');
    }

    public function test_users_see_only_their_requests_and_cannot_approve_any_request(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $ownRequest = $this->createFacilityRequest($owner, 'FR-OWN-0001');
        $otherRequest = $this->createFacilityRequest($otherUser, 'FR-OTHER-0001');

        $this->actingAs($owner)->get(route('request-facility.index'))
            ->assertOk()->assertSee($ownRequest->request_number)->assertDontSee($otherRequest->request_number)
            ->assertDontSee('<th scope="col">Action</th>', false)
            ->assertDontSee('facility-export-actions')->assertDontSee('Approve Request');
        $this->get(route('request-facility.show', $otherRequest))->assertForbidden();
        foreach ([$ownRequest, $otherRequest] as $facilityRequest) {
            $this->patch(route('request-facility.approve', $facilityRequest))->assertForbidden();
            $this->assertSame('pending', $facilityRequest->fresh()->status);
        }

        $this->actingAs(User::factory()->create(['role' => 'personnel']))
            ->get(route('request-facility.index'))->assertForbidden();
        $this->get(route('request-facility.show', $ownRequest))->assertForbidden();
        $this->patch(route('request-facility.approve', $ownRequest))->assertForbidden();
    }

    public function test_admin_filters_requests_by_status_and_facility(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $pending = $this->createFacilityRequest($owner, 'FR-PENDING-0001');
        $approved = $this->createFacilityRequest($owner, 'FR-APPROVED-0001');
        $approved->update(['status' => 'approved', 'facility' => 'Science Hall']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('request-facility.index', ['status' => 'pending']))
            ->assertOk()->assertSee($pending->request_number)->assertDontSee($approved->request_number);
        $this->get(route('request-facility.index', ['search' => 'Science']))
            ->assertOk()->assertSee($approved->request_number)->assertDontSee($pending->request_number);
        $this->get(route('request-facility.index', ['status' => 'invalid']))
            ->assertSessionHasErrors('status');
    }

    public function test_empty_user_request_table_has_six_columns_and_no_actions(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('request-facility.index'))
            ->assertOk()
            ->assertSee('colspan="6"', false)
            ->assertDontSee('<th scope="col">Action</th>', false)
            ->assertSeeText('No facility requests found.');
    }

    public function test_user_actions_appear_only_after_admin_approval(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $pending = $this->createFacilityRequest($owner, 'FR-PENDING-ACTIONS');
        $approved = $this->createFacilityRequest($owner, 'FR-APPROVED-ACTIONS');
        $approved->update(['status' => 'approved']);

        $this->actingAs($owner)->get(route('request-facility.index'))
            ->assertOk()
            ->assertSee('<th scope="col">Action</th>', false)
            ->assertSee(route('request-facility.show', $approved))
            ->assertSee(route('request-facility.photos', $approved))
            ->assertDontSee(route('request-facility.show', $pending))
            ->assertDontSee('Approve Request');
        $this->get(route('request-facility.index', ['status' => 'pending']))
            ->assertOk()->assertDontSee('<th scope="col">Action</th>', false);

        $approved->update(['status' => 'finished']);
        $this->get(route('request-facility.index'))
            ->assertOk()
            ->assertDontSee('<th scope="col">Action</th>', false)
            ->assertDontSee(route('request-facility.export-pdf', $approved))
            ->assertDontSee(route('request-facility.photos', $approved));
    }

    public function test_reference_form_fields_are_required_and_invalid_values_are_rejected(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner)->get(route('request-facility.create'))
            ->assertOk()->assertSee('Facility Use Request Form')->assertSee('List of Participants')
            ->assertSee('Lead / Focal Person')->assertDontSee('Request Category');
        $this->post(route('request-facility.store'), [
            'facility' => 'Conference Room',
            'requested_date' => now()->subDay()->toDateString(),
            'requested_time' => '25:90',
            'purpose' => 'Meeting.',
            'contact_number' => 'invalid-number',
        ])->assertSessionHasErrors(['requested_date', 'requested_time', 'contact_number', 'lead_person', 'participants', 'requested_by']);
        $this->assertDatabaseCount('facility_requests', 0);
    }

    private function createFacilityRequest(User $owner, string $number): FacilityRequest
    {
        return FacilityRequest::create([
            'user_id' => $owner->id,
            'request_number' => $number,
            'facility' => 'Conference Room A',
            'category' => 'Room Setup',
            'requested_date' => now()->addDay()->toDateString(),
            'purpose' => 'Prepare seating for twenty attendees.',
            'status' => 'pending',
        ]);
    }
}
