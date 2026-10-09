<?php

namespace Tests\Feature;

use App\Http\Requests\StoreFacilityRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class FacilityRequestDropdownTest extends TestCase
{
    public function test_form_offers_the_requested_facilities_as_a_dropdown(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user']));
        $html = view('facility-requests.create', ['errors' => new ViewErrorBag])->render();
        $this->assertStringContainsString('<select id="facility" name="facility"', $html);
        $this->assertStringNotContainsString('<input id="facility"', $html);

        foreach (['Room 1', 'Room 6 (Speech Lab)', 'MIS', 'Room 16 A (Computer Lab)', 'Room 24 (Electrical)', 'Campus Public Information Office', 'Activity Center', 'Accreditation Room', 'Food Tech New (BTLED)', 'Covered Court', 'New Covered Court 1', 'New Covered Court 2'] as $facility) {
            $this->assertStringContainsString('<option value="'.$facility.'"', $html);
        }

        $this->assertStringNotContainsString('<option value="Acre"', $html);
        $this->assertStringNotContainsString('<option value="Sports/Culture"', $html);

        foreach (['Comfort Room', 'Guard House', 'Dorm Room', 'Dorm Kitchen', 'Dorm Manager Room', 'Student Services Office', 'Students and Alumni Services', 'Practice Teaching', 'ETEEAP', 'CED Office', "Dean's Office", "Administrative Officer's Office", 'Supply Office', "Registrar's Office", 'Guidance Office', 'Campus Clinic', 'Stock Room', 'Campus Library', 'Extension / GAD Office', 'Research Office', 'Planning', "Cashier's Office", 'Accounting Office', 'Faculty Room'] as $facility) {
            $this->assertStringNotContainsString('<option value="'.e($facility).'"', $html);
        }
    }

    public function test_facility_validation_accepts_available_choices_and_rejects_other_values(): void
    {
        $rules = ['facility' => (new StoreFacilityRequest)->rules()['facility']];

        foreach (['Room 1', 'Covered Court', 'New Covered Court 1', 'New Covered Court 2'] as $facility) {
            $this->assertTrue(Validator::make(['facility' => $facility], $rules)->passes());
        }

        foreach ([null, '', 'Comfort Room', 'Guard House', 'Faculty Room', 'Unlisted Facility', ['Room 1']] as $facility) {
            $this->assertTrue(Validator::make(['facility' => $facility], $rules)->fails());
        }
    }

    public function test_previous_selection_is_preserved_after_validation_failure(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'user']));
        session()->flashInput(['facility' => 'New Covered Court 2']);
        $this->app['request']->setLaravelSession(session()->driver());
        $html = view('facility-requests.create', ['errors' => new ViewErrorBag])->render();

        $this->assertMatchesRegularExpression('/<option value="New Covered Court 2"\s+selected/', $html);
    }
}
