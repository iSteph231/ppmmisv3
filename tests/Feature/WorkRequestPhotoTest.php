<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkRequestPhotoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_062824_add_columns_to_users_table.php',
            '2026_11_07_135137_add_must_change_password_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_04_24_062233_create_work_requests_table.php',
            '2026_04_28_080517_add_user_id_to_work_requests_table.php',
            '2026_05_24_210550_make_work_requests_description_nullable.php',
            '2026_11_16_142622_add_photo_path_to_work_requests_table.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Storage::fake('local');
        config(['services.google_drive.folder_id' => null]);
        $this->actingAs(User::factory()->create());
    }

    private function requestData(): array
    {
        return ['title' => 'Repair a chair', 'request_type' => 'repair', 'repair_item' => 'Broken chair'];
    }

    public function test_request_can_include_a_private_photo(): void
    {
        $this->get(route('work-requests.create'))->assertOk()
            ->assertSee('name="photo"', false)->assertSee('capture="environment"', false)
            ->assertSee('Image (required)')->assertSee('Take Photo')->assertSee('Choose from Gallery');
        $this->post(route('work-requests.store'), $this->requestData() + [
            'photo' => UploadedFile::fake()->image('issue.jpg'),
        ])->assertRedirect(route('work-requests.index'))->assertSessionHasNoErrors()->assertSessionHas('success');
        $workRequest = WorkRequest::firstOrFail();
        Storage::disk('local')->assertExists($workRequest->photo_path);
        $this->get(route('work-requests.photo', $workRequest))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('work-requests.photo', $workRequest))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('work-requests.photo', $workRequest))->assertOk();
    }

    public function test_request_requires_a_photo(): void
    {
        $this->post(route('work-requests.store'), $this->requestData())->assertSessionHasErrors('photo');
        $this->assertSame(0, WorkRequest::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_submitted_work_request_cannot_be_edited_by_its_owner(): void
    {
        $this->post(route('work-requests.store'), $this->requestData() + ['photo' => UploadedFile::fake()->image('issue.jpg')])
            ->assertSessionHas('success');
        $workRequest = WorkRequest::firstOrFail();
        $originalAttributes = $workRequest->getAttributes();

        $this->get(route('work-requests.edit', $workRequest))->assertForbidden();
        foreach (['put', 'patch'] as $method) {
            $this->{$method}(route('work-requests.update', $workRequest), array_replace($this->requestData(), ['title' => 'Changed title']))
                ->assertForbidden();
        }
        $this->assertSame($originalAttributes, $workRequest->fresh()->getAttributes());
    }

    public function test_configured_drive_storage_is_used_for_upload_and_display(): void
    {
        config(['services.google_drive' => ['folder_id' => 'folder', 'client_id' => 'client', 'client_secret' => 'secret', 'refresh_token' => 'refresh']]);
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'www.googleapis.com/upload/drive/*' => Http::response(['id' => 'work-photo']),
            'www.googleapis.com/drive/v3/files/*' => Http::response(['id' => 'work-photo', 'trashed' => false, 'mimeType' => 'image/jpeg']),
        ]);
        $this->post(route('work-requests.store'), $this->requestData() + ['photo' => UploadedFile::fake()->image('issue.jpg')])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $workRequest = WorkRequest::firstOrFail();
        $this->assertSame('gdrive:work-photo', $workRequest->photo_path);
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->get(route('work-requests.photo', $workRequest))->assertOk();
    }

    public function test_submission_errors_are_visible_on_the_form(): void
    {
        $this->withSession(['error' => 'Unable to store the request photo.'])
            ->get(route('work-requests.create'))->assertOk()->assertSee('Unable to store the request photo.');
    }

    public function test_validation_errors_are_visible_on_the_form(): void
    {
        $this->from(route('work-requests.create'))->post(route('work-requests.store'), $this->requestData())
            ->assertSessionHasErrors('photo');
        $errors = session('errors');
        $this->view('work-requests.create', ['errors' => $errors])
            ->assertSee('Please correct the following')->assertSee('Please select your image again');
    }

    public function test_small_viewports_require_a_photo(): void
    {
        $this->post(route('work-requests.store'), $this->requestData() + ['photo_required' => '1'])
            ->assertSessionHasErrors('photo');
        $this->assertSame(0, WorkRequest::count());
    }

    public function test_old_optional_flag_cannot_bypass_photo_requirement(): void
    {
        $this->post(route('work-requests.store'), $this->requestData() + ['photo_required' => '0'])
            ->assertSessionHasErrors('photo');
        $this->assertSame(0, WorkRequest::count());
    }

    public function test_invalid_and_oversized_files_are_rejected(): void
    {
        foreach ([
            UploadedFile::fake()->create('issue.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->image('issue.jpg')->size(5121),
            UploadedFile::fake()->createWithContent('issue.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        ] as $photo) {
            $this->post(route('work-requests.store'), $this->requestData() + ['photo' => $photo])->assertSessionHasErrors('photo');
        }
        $this->assertSame(0, WorkRequest::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
