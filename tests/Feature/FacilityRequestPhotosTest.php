<?php

namespace Tests\Feature;

use App\Models\FacilityRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacilityRequestPhotosTest extends TestCase
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

    private function approvedRequest(): FacilityRequest
    {
        $owner = User::factory()->create(['role' => 'user']);
        $this->actingAs($owner);

        return FacilityRequest::create([
            'user_id' => $owner->id,
            'request_number' => 'FR-PHOTOS-0001',
            'facility' => 'Conference Room',
            'category' => 'Room Setup',
            'requested_date' => now()->addDay(),
            'purpose' => 'Meeting setup',
            'status' => 'approved',
        ]);
    }

    private function evaluationAnswers(): array
    {
        return [
            'name' => 'Test Respondent',
            'age' => 21,
            'gender' => 'Female',
            'client_category' => 'Students',
            'semester' => 'First',
            'academic_year' => '2026–2027',
            'ratings' => array_fill_keys(array_keys(FacilityRequest::EVALUATION_INDICATORS), 5),
            'comments' => 'Thank you.',
        ];
    }

    public function test_evaluation_requires_photos_owner_and_valid_complete_ratings(): void
    {
        $facility = $this->approvedRequest();
        $owner = auth()->user();
        $answers = $this->evaluationAnswers();

        $this->post(route('request-facility.evaluation.store', $facility), $answers)->assertStatus(409);
        $facility->update(['before_photo_path' => 'before.jpg', 'after_photo_path' => 'after.jpg']);
        foreach (['user', 'admin', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->post(route('request-facility.evaluation.store', $facility), $answers)->assertForbidden();
        }
        $this->actingAs($owner);
        foreach ([0, 6, 'invalid'] as $rating) {
            $invalid = $answers;
            $invalid['ratings']['water'] = $rating;
            $this->post(route('request-facility.evaluation.store', $facility), $invalid)->assertSessionHasErrors('ratings.water');
        }
        $invalid = $answers;
        unset($invalid['ratings']['chairs']);
        $this->post(route('request-facility.evaluation.store', $facility), $invalid)->assertSessionHasErrors('ratings.chairs');
        $invalid = $answers;
        $invalid['client_category'] = 'Others';
        $this->post(route('request-facility.evaluation.store', $facility), $invalid)->assertSessionHasErrors('other_category');
        $this->assertNull($facility->fresh()->evaluation);
        $this->assertSame('approved', $facility->fresh()->status);

        $this->post(route('request-facility.evaluation.store', $facility), $answers)->assertRedirect();
        $this->get(route('request-facility.show', $facility))->assertOk()->assertSee('Thank you.');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('request-facility.show', $facility))->assertOk()->assertSee('Thank you.');
    }

    private function submission(): array
    {
        return ['facility' => 'Room 2', 'category' => 'Room Setup', 'requested_date' => now()->addDay()->toDateString(), 'purpose' => 'Another meeting', 'requested_time' => '09:30', 'lead_person' => 'Test Lead', 'contact_number' => '09123456789', 'participants' => 'Participant - College', 'requested_by' => 'Test Requestor'];
    }

    public function test_photos_and_evaluation_are_required_before_requesting_another_facility(): void
    {
        $facility = $this->approvedRequest();
        $this->get(route('request-facility.photos', $facility))->assertOk();
        $this->get(route('request-facility.create'))->assertRedirect(route('request-facility.photos', $facility));
        $this->post(route('request-facility.store'), $this->submission())->assertSessionHasErrors('facility');
        $this->post(route('request-facility.photos.store', $facility), ['before_photo' => UploadedFile::fake()->image('before.jpg')])->assertRedirect();
        $facility->refresh();
        $this->assertSame('approved', $facility->status);
        Storage::disk('local')->assertExists($facility->before_photo_path);
        $this->assertSame($facility->photoDirectory(), dirname($facility->before_photo_path));
        $this->assertSame($facility->request_number.'-before.jpg', basename($facility->before_photo_path));
        $this->assertNull($facility->after_photo_path);
        $this->post(route('request-facility.store'), $this->submission())->assertSessionHasErrors('facility');
        $this->post(route('request-facility.photos.store', $facility), ['after_photo' => UploadedFile::fake()->image('after.png')])->assertRedirect();
        $facility->refresh();
        $this->assertSame('approved', $facility->status);
        Storage::disk('local')->assertExists($facility->after_photo_path);
        $this->assertSame($facility->photoDirectory(), dirname($facility->after_photo_path));
        $this->assertSame($facility->request_number.'-after.png', basename($facility->after_photo_path));
        $this->get(route('request-facility.photos', $facility))->assertOk()->assertSee('Facility Evaluation Form')->assertDontSee('Request Another Facility');
        $this->get(route('request-facility.create'))->assertRedirect(route('request-facility.photos', $facility));
        $this->post(route('request-facility.store'), $this->submission())->assertSessionHasErrors('facility');
        $this->post(route('request-facility.evaluation.store', $facility), [])->assertSessionHasErrors('ratings');
        $answers = $this->evaluationAnswers();
        $this->post(route('request-facility.evaluation.store', $facility), $answers)->assertRedirect();
        $this->assertSame('finished', $facility->fresh()->status);
        $this->assertSame($answers['ratings'], $facility->fresh()->evaluation['ratings']);
        $this->post(route('request-facility.evaluation.store', $facility), $answers)->assertStatus(409);
        $this->get(route('request-facility.photos', $facility))->assertOk()->assertSee('Request Another Facility');
        $this->get(route('request-facility.create'))->assertOk();
        $this->post(route('request-facility.store'), $this->submission())->assertRedirect(route('request-facility.index'));
        $this->assertDatabaseCount('facility_requests', 2);
        $this->get(route('request-facility.index', ['status' => 'finished']))->assertOk()->assertSee('Finished');
    }

    public function test_both_photos_require_evaluation_and_cannot_be_replaced(): void
    {
        $facility = $this->approvedRequest();
        $this->post(route('request-facility.photos.store', $facility), [
            'before_photo' => UploadedFile::fake()->image('before.jpg'),
            'after_photo' => UploadedFile::fake()->image('after.jpg'),
        ])->assertRedirect();
        $this->assertSame('approved', $facility->fresh()->status);
        $this->post(route('request-facility.photos.store', $facility), ['before_photo' => UploadedFile::fake()->image('extra.jpg')])->assertSessionHasErrors('before_photo');
    }

    public function test_existing_numbered_folders_are_moved_and_saved_paths_updated(): void
    {
        $facility = $this->approvedRequest();
        $oldPath = 'facility-requests/'.$facility->id.'/before.jpg';
        Storage::disk('local')->put($oldPath, 'existing image');
        $facility->update(['before_photo_path' => $oldPath]);

        $this->artisan('facility-requests:rename-photo-folders')->assertExitCode(0);
        $newPath = $facility->photoDirectory().'/before.jpg';
        $this->assertSame($newPath, $facility->fresh()->before_photo_path);
        Storage::disk('local')->assertExists($newPath);
        Storage::disk('local')->assertMissing($oldPath);
        $this->assertSame('existing image', Storage::disk('local')->get($newPath));
        $this->artisan('facility-requests:rename-photo-folders')->assertExitCode(0);
    }

    public function test_non_images_multiple_files_and_oversized_files_are_rejected(): void
    {
        $facility = $this->approvedRequest();
        foreach ([
            UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->image('large.jpg')->size(5121),
            [UploadedFile::fake()->image('one.jpg'), UploadedFile::fake()->image('two.jpg')],
        ] as $file) {
            $this->post(route('request-facility.photos.store', $facility), ['before_photo' => $file])->assertSessionHasErrors('before_photo');
        }
        $this->post(route('request-facility.photos.store', $facility), [])->assertSessionHasErrors('before_photo');
        $this->assertNull($facility->fresh()->before_photo_path);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_only_owner_can_upload_and_owner_and_admin_can_view_photos(): void
    {
        $facility = $this->approvedRequest();
        $owner = auth()->user();
        $this->post(route('request-facility.photos.store', $facility), ['before_photo' => UploadedFile::fake()->image('before.jpg')])->assertRedirect();
        $originalPath = $facility->fresh()->before_photo_path;
        $this->post(route('request-facility.photos.store', $facility), ['before_photo' => UploadedFile::fake()->image('replacement.jpg')])->assertSessionHasErrors('before_photo');
        $this->assertSame($originalPath, $facility->fresh()->before_photo_path);
        foreach (['user', 'admin', 'personnel'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->post(route('request-facility.photos.store', $facility), ['after_photo' => UploadedFile::fake()->image('after.jpg')])->assertForbidden();
            $this->get(route('request-facility.photos', $facility))->assertForbidden();
            $response = $this->get(route('request-facility.photo', [$facility, 'before']));
            if ($role === 'admin') {
                $response->assertOk();
            } else {
                $response->assertForbidden();
            }
        }
        $this->actingAs($owner)->get(route('request-facility.photo', [$facility, 'before']))->assertOk();
        $facility->update(['status' => 'pending']);
        $this->get(route('request-facility.photos', $facility))->assertStatus(409);
        $this->post(route('request-facility.photos.store', $facility), ['after_photo' => UploadedFile::fake()->image('after.jpg')])->assertStatus(409);
    }
}
