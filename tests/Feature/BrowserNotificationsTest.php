<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Tests\TestCase;

class BrowserNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    public function test_only_admins_can_poll_browser_notifications(): void
    {
        $this->getJson(route('notifications.browser'))->assertUnauthorized();
        foreach (['user', 'engineer'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role]))
                ->getJson(route('notifications.browser'))->assertForbidden();
        }
    }

    public function test_initial_poll_sets_a_baseline_without_replaying_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $old = $this->notification($admin, 'New Work Request');

        $this->actingAs($admin)->getJson(route('notifications.browser'))->assertOk()
            ->assertJsonPath('cursor', $old->id)->assertJsonPath('user_id', $admin->id)
            ->assertJsonCount(0, 'data')->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_poll_returns_only_the_current_admins_new_requests_in_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'admin']);
        $old = $this->notification($admin, 'New Work Request');
        $work = $this->notification($admin, 'New Work Request');
        $this->notification($other, 'New Work Request');
        $this->notification(null, 'New Work Request');
        $this->notification($admin, 'Request Approved');
        $facility = $this->notification($admin, 'New Facility Request');

        $this->actingAs($admin)->getJson(route('notifications.browser', ['after' => $old->id]))
            ->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $work->id)
            ->assertJsonPath('data.0.url', route('work-requests.index'))
            ->assertJsonPath('data.1.id', $facility->id)
            ->assertJsonPath('data.1.url', route('request-facility.index'))
            ->assertJsonPath('cursor', $facility->id);

        $this->assertFalse($work->fresh()->is_read);
    }

    public function test_batches_do_not_skip_requests_and_empty_polls_keep_the_cursor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $notifications = collect();
        for ($index = 0; $index < 51; $index++) {
            $notifications->push($this->notification($admin, 'New Work Request'));
        }

        $this->actingAs($admin)->getJson(route('notifications.browser', ['after' => 0]))
            ->assertOk()->assertJsonCount(50, 'data')->assertJsonPath('cursor', $notifications[49]->id);
        $this->getJson(route('notifications.browser', ['after' => $notifications[49]->id]))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('cursor', $notifications[50]->id);
        $this->getJson(route('notifications.browser', ['after' => $notifications[50]->id]))
            ->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('cursor', $notifications[50]->id);
    }

    public function test_invalid_cursors_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([-1, 'invalid', ['1']] as $cursor) {
            $this->getJson(route('notifications.browser', ['after' => $cursor]))
                ->assertUnprocessable()->assertJsonValidationErrors('after');
        }
    }

    private function notification(?User $user, string $title): Notification
    {
        return Notification::create([
            'user_id' => $user?->id,
            'title' => $title,
            'message' => 'A new request is ready for review.',
            'is_read' => false,
        ]);
    }
}
