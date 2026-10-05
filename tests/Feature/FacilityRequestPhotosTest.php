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

    private function submission(): array
    {
        return ['facility' => 'Second Room', 'category' => 'Room Setup', 'requested_date' => now()->addDay()->toDateString(), 'purpose' => 'Another meeting'];
    }

    public function test_photos_can_be_uploaded_separately_and_unlock_requests_only_when_both_exist(): void
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
        $this->assertSame('finished', $facility->status);
        Storage::disk('local')->assertExists($facility->after_photo_path);
        $this->assertSame($facility->photoDirectory(), dirname($facility->after_photo_path));
        $this->assertSame($facility->request_number.'-after.png', basename($facility->after_photo_path));
        $this->get(route('request-facility.create'))->assertOk();
        $this->post(route('request-facility.store'), $this->submission())->assertRedirect(route('request-facility.index'));
        $this->assertDatabaseCount('facility_requests', 2);
        $this->get(route('request-facility.index', ['status' => 'finished']))->assertOk()->assertSee('Finished');
    }

    public function test_both_photos_can_finish_a_request_in_one_submission(): void
    {
        $facility = $this->approvedRequest();
        $this->post(route('request-facility.photos.store', $facility), [
            'before_photo' => UploadedFile::fake()->image('before.jpg'),
            'after_photo' => UploadedFile::fake()->image('after.jpg'),
        ])->assertRedirect();
        $this->assertSame('finished', $facility->fresh()->status);
        $this->post(route('request-facility.photos.store', $facility), ['before_photo' => UploadedFile::fake()->image('extra.jpg')])->assertStatus(409);
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
