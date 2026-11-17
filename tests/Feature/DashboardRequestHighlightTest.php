<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkRequest;
use Tests\TestCase;

class DashboardRequestHighlightTest extends TestCase
{
    public function test_only_admins_see_dots_for_recent_requests_without_row_highlights(): void
    {
        foreach (['admin', 'user', 'engineer'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role]));
            foreach (['dashboard.index', 'work-requests.index'] as $view) {
                $requests = collect();
                foreach (['pending', 'approved', 'completed'] as $index => $status) {
                    $requests->push($this->workRequest($index + 1, $status, now()->subHour()));
                }
                $scheduledRequest = $this->workRequest(4, 'pending', now()->subHour());
                $scheduledRequest->scheduled_date = now()->addDay();
                $requests->push($scheduledRequest);
                $html = view($view, ['workRequests' => $requests])->render();
                if ($role === 'admin') {
                    $this->assertStringContainsString('Designation: Facilities Office', $html);
                } else {
                    $this->assertStringNotContainsString('Designation:', $html);
                }
                $this->assertSame($role === 'admin' ? 1 : 0, substr_count($html, 'class="new-request-dot"'));
                $this->assertSame($role === 'admin' ? 1 : 0, substr_count($html, 'class="new-request-label"'));
                $this->assertStringNotContainsString('new-work-request', $html);
                $this->assertStringNotContainsString('new-request-badge', $html);
                $this->assertStringNotContainsString('background-color: #fffbeb', $html);
            }
        }
    }

    public function test_dot_expires_at_exactly_24_hours(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin']));
        $this->travelTo(now()->startOfSecond());
        $submittedAt = now()->subDay()->addSecond();
        $this->blade('<x-new-request-dot :created-at="$submittedAt" />', compact('submittedAt'))
            ->assertSee('class="new-request-dot"', false)->assertSee('class="new-request-label"', false)->assertSee('data-new-request-expires', false);
        $this->travel(1)->seconds();
        $this->blade('<x-new-request-dot :created-at="$submittedAt" />', compact('submittedAt'))
            ->assertDontSee('new-request-dot')->assertDontSee('new-request-label');
    }

    public function test_ineligible_recent_requests_do_not_have_dots(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin']));
        $submittedAt = now();
        $this->blade('<x-new-request-dot :created-at="$submittedAt" :eligible="false" />', compact('submittedAt'))
            ->assertDontSee('new-request-dot');
    }

    public function test_missing_future_and_old_dates_do_not_show_dots(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin']));
        foreach ([null, now()->addHour(), now()->subDays(2)] as $submittedAt) {
            $this->blade('<x-new-request-dot :created-at="$submittedAt" />', compact('submittedAt'))
                ->assertDontSee('new-request-dot');
        }
    }

    private function workRequest(int $id, string $status, \DateTimeInterface $createdAt): WorkRequest
    {
        $workRequest = new WorkRequest(['title' => 'Repair a chair', 'request_number' => 'WR-'.$id, 'status' => $status, 'department' => 'Facilities Office']);
        $workRequest->id = $id;
        $workRequest->created_at = $createdAt;
        $workRequest->setRelation('user', User::factory()->make());

        return $workRequest;
    }
}
