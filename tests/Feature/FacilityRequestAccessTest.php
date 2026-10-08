<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FacilityRequestAccessTest extends TestCase
{
    public function test_only_users_see_facility_request_creation_links(): void
    {
        foreach (['admin', 'personnel', 'user'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role]));

            $html = view('request-facility', [
                'facilityRequests' => new LengthAwarePaginator([], 0, 10),
                'errors' => new ViewErrorBag,
            ])->render();

            if ($role === 'user') {
                $this->assertStringContainsString(route('request-facility.create'), $html);
            } else {
                $this->assertStringNotContainsString(route('request-facility.create'), $html);
                $this->assertStringNotContainsString('Add Request', $html);
            }
        }
    }

    public function test_admin_and_personnel_cannot_create_or_submit_facility_requests(): void
    {
        foreach (['admin', 'personnel'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role]));

            $this->get(route('request-facility.create'))->assertForbidden();
            $this->post(route('request-facility.store'), [
                'facility' => 'Conference Room A',
                'category' => 'Room Setup',
                'requested_date' => now()->addDay()->toDateString(),
                'purpose' => 'Prepare the room for a meeting.',
            ])->assertForbidden();
        }
    }

    public function test_users_can_open_the_form_and_reach_submission_validation(): void
    {
        foreach (['0001_01_01_000000_create_users_table.php', '2026_09_24_000000_create_facility_requests_table.php'] as $migration) {
            $this->artisan('migrate', ['--path' => 'database/migrations/'.$migration, '--no-interaction' => true])->assertExitCode(0);
        }
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user']));

        $this->get(route('request-facility.create'))->assertOk()->assertSee('Submit Request');
        $this->post(route('request-facility.store'), [])
            ->assertSessionHasErrors(['facility', 'requested_date', 'requested_time', 'purpose', 'lead_person', 'contact_number', 'participants', 'requested_by']);
    }

    public function test_guests_cannot_create_or_submit_facility_requests(): void
    {
        $this->get(route('request-facility.create'))->assertRedirect(route('login'));
        $this->post(route('request-facility.store'), [])->assertRedirect(route('login'));
    }
}
