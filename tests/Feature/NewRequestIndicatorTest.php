<?php

namespace Tests\Feature;

use App\Models\AccountRequest;
use App\Models\FacilityRequest;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkRequest;
use Tests\TestCase;

class NewRequestIndicatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_04_24_062233_create_work_requests_table.php',
            '2026_04_28_080517_add_user_id_to_work_requests_table.php',
            '2026_05_01_113103_add_scheduled_fields_to_work_requests_table.php',
            '2026_05_04_142741_create_inspection_reports_table.php',
            '2026_05_24_210550_make_work_requests_description_nullable.php',
            '2026_11_07_122817_add_requester_snapshot_to_work_requests_table.php',
            '2026_09_24_000000_create_facility_requests_table.php',
            '2026_10_06_052045_add_usage_photos_to_facility_requests_table.php',
            '2026_10_07_105141_add_evaluation_to_facility_requests_table.php',
            '2026_10_07_111240_add_use_details_to_facility_requests_table.php',
            '2026_10_08_112933_add_program_image_to_facility_requests_table.php',
            '2026_11_07_140405_create_account_requests_table.php',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true])->assertExitCode(0);
        }
    }

    public function test_request_dots_clear_only_for_the_admin_who_opens_the_details(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admins = User::factory()->count(2)->create(['role' => 'admin']);
        $workRequest = WorkRequest::factory()->create(['user_id' => $owner->id, 'status' => 'pending']);
        $facilityRequest = FacilityRequest::create([
            'user_id' => $owner->id, 'request_number' => 'FR-NEW-0001',
            'facility' => 'Conference Room', 'category' => 'Facility Use',
            'requested_date' => now()->addDay(), 'purpose' => 'Meeting', 'status' => 'pending',
        ]);

        foreach ([[$workRequest, 'work-requests', 'New Work Request'], [$facilityRequest, 'request-facility', 'New Facility Request']] as [$record, $route, $title]) {
            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id, 'title' => $title, 'message' => 'New request',
                    'type' => 'info', 'is_read' => false, 'related_type' => $record::class, 'related_id' => $record->id,
                ]);
            }

            $this->actingAs($owner)->get(route($route.'.index'))->assertOk()->assertDontSee('title="New request"', false);
            $this->get(route($route.'.show', $record))->assertOk();
            $outsider = User::factory()->create(['role' => 'user']);
            $this->actingAs($outsider)->get(route($route.'.show', $record))->assertForbidden();

            $this->actingAs($admins[0])->get(route($route.'.index'))->assertOk()->assertSee('title="New request"', false);
            $this->get(route($route.'.show', $record))->assertOk();
            $this->get(route($route.'.index'))->assertOk()->assertDontSee('title="New request"', false);
            $this->actingAs($admins[1])->get(route($route.'.index'))->assertOk()->assertSee('title="New request"', false);
            $this->assertDatabaseHas('notifications', ['user_id' => $admins[1]->id, 'related_type' => $record::class, 'is_read' => false]);
        }
    }

    public function test_work_submission_links_the_admin_notification_to_the_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner)->post(route('work-requests.store'), [
            'title' => 'Repair window', 'request_type' => 'repair', 'repair_item' => 'Window',
        ])->assertRedirect(route('work-requests.index'));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id, 'related_type' => WorkRequest::class,
            'related_id' => WorkRequest::sole()->id, 'is_read' => false,
        ]);
        $this->actingAs($admin)->get(route('work-requests.index'))->assertOk()->assertSee('title="New request"', false);
    }

    public function test_account_request_dot_remains_until_the_request_is_handled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $accountRequest = AccountRequest::factory()->create();
        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee('title="New request"', false);
        $this->delete(route('account-requests.decline', $accountRequest))->assertRedirect();
        $this->get(route('users.index'))->assertOk()->assertDontSee('title="New request"', false);
    }
}
