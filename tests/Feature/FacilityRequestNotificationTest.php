<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use Tests\TestCase;

class FacilityRequestNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_09_24_000000_create_facility_requests_table.php',
            '2026_10_06_052045_add_usage_photos_to_facility_requests_table.php',
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
            'facility' => 'Conference Room A',
            'category' => 'Room Setup',
            'requested_date' => now()->addDay()->toDateString(),
            'purpose' => 'Prepare for a meeting.',
        ])->assertRedirect(route('request-facility.index'));

        $facilityRequest = FacilityRequest::sole();
        $this->assertDatabaseCount('notifications', 2);

        foreach ($admins as $admin) {
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
            ->assertOk()->assertSee($facilityRequest->request_number)->assertDontSee('Add Request');
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
            ->assertOk()->assertSee($ownRequest->request_number)->assertDontSee($otherRequest->request_number);
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
