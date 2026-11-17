<?php

namespace Tests\Feature;

use App\Models\MaintenanceSchedule;
use App\Models\Notification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MaintenancePermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_04_24_061654_add_role_and_active_to_users_table.php',
            '2026_04_24_062824_add_columns_to_users_table.php',
            '2026_11_03_141756_add_engineer_to_user_roles.php',
            '2026_11_07_135137_add_must_change_password_to_users_table.php',
            '2026_04_24_055441_create_notifications_table.php',
            '2026_05_05_003054_create_maintenance_schedules_table.php',
            '2026_05_12_131108_drop_campus_from_maintenance_schedules_table.php',
            '2026_05_12_193133_add_status_to_maintenance_schedules_table.php',
            '2026_05_12_211751_add_completion_notes_to_maintenance_schedules_table.php',
        ] as $migration) {
            if ($migration === '2026_05_05_003054_create_maintenance_schedules_table.php') {
                require_once database_path('migrations/'.$migration);
                (new \CreateMaintenanceSchedulesTable)->up();
            } else {
                (require database_path('migrations/'.$migration))->up();
            }
        }
    }

    public function test_only_engineers_can_create_maintenance_reports(): void
    {
        foreach (['admin', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('maintenance.create'))->assertForbidden();
            $this->post(route('maintenance.store'), $this->scheduleData())->assertForbidden();
        }
        $this->assertSame(0, MaintenanceSchedule::count());
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $this->get(route('maintenance.create'))->assertOk();
        $this->post(route('maintenance.store'), $this->scheduleData())->assertRedirect(route('maintenance.index'));
        $this->assertDatabaseHas('maintenance_schedules', ['activity' => 'Inspect air conditioners', 'status' => 'pending', 'month_year' => '2026-11']);
        $this->get(route('maintenance.index'))->assertOk()->assertSee('Add Schedule')->assertSee('Export PDF');
    }

    public function test_engineer_submission_still_requires_valid_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $this->post(route('maintenance.store'), [])->assertSessionHasErrors(['scheduled_date', 'activity']);
        $this->post(route('maintenance.store'), array_replace($this->scheduleData(), [
            'scheduled_date' => 'tomorrow',
            'maintenance_in_charge' => str_repeat('x', 101),
            'engineer_in_charge' => str_repeat('x', 101),
        ]))->assertSessionHasErrors(['scheduled_date', 'maintenance_in_charge', 'engineer_in_charge']);
        $this->assertSame(0, MaintenanceSchedule::count());
    }

    public function test_admin_and_engineer_can_print_pdf_but_regular_users_cannot(): void
    {
        MaintenanceSchedule::create($this->scheduleData() + ['status' => 'completed', 'month_year' => '2026-11']);
        foreach (['admin', 'engineer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('reports.maintenance'))->assertOk();
            $this->get(route('reports.maintenance.export-pdf', ['month' => '2026-11']))
                ->assertOk()->assertHeader('content-type', 'application/pdf');
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('maintenance.index'))->assertOk()->assertDontSee('Add Schedule')->assertSee('Export PDF');
        $this->actingAs(User::factory()->create(['role' => 'user']));
        $this->get(route('maintenance.index'))->assertForbidden();
        $this->get(route('reports.maintenance'))->assertForbidden();
        $this->get(route('reports.maintenance.export-pdf'))->assertForbidden();
    }

    public function test_guests_must_log_in_and_engineers_cannot_access_other_admin_reports(): void
    {
        $this->get(route('maintenance.create'))->assertRedirect(route('login'));
        $this->post(route('maintenance.store'), $this->scheduleData())->assertRedirect(route('login'));
        $this->get(route('reports.maintenance.export-pdf'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.work-requests'))->assertForbidden();
    }

    public function test_submitted_reports_cannot_be_edited_by_any_role(): void
    {
        foreach (['pending', 'in_progress', 'completed'] as $status) {
            $schedule = MaintenanceSchedule::create($this->scheduleData() + ['status' => $status, 'month_year' => '2026-11']);
            $originalAttributes = $schedule->fresh()->getAttributes();
            foreach (['admin', 'engineer', 'user'] as $role) {
                $this->actingAs(User::factory()->create(['role' => $role]));
                $this->get('/maintenance/'.$schedule->id.'/edit')->assertNotFound();
                $this->put('/maintenance/'.$schedule->id, array_replace($this->scheduleData(), ['activity' => 'Changed activity']))->assertStatus(405);
                $this->assertSame($originalAttributes, $schedule->fresh()->getAttributes());
                if ($role !== 'user') {
                    $this->get(route('maintenance.index', ['month' => '2026-11']))->assertOk()
                        ->assertDontSee('/maintenance/'.$schedule->id.'/edit', false);
                }
            }
        }
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_engineer_sets_the_date_without_approval_and_completes_the_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $engineer = User::factory()->create(['role' => 'engineer']);
        $this->actingAs($engineer);
        $this->post(route('maintenance.store'), array_replace($this->scheduleData(), ['scheduled_date' => '2027-02-12']))
            ->assertRedirect(route('maintenance.index'));
        $schedule = MaintenanceSchedule::firstOrFail();
        $this->assertSame('2027-02-12', $schedule->scheduled_date->format('Y-m-d'));
        $this->assertSame('2027-02', $schedule->month_year);
        $this->get(route('maintenance.show', $schedule->id))->assertOk()->assertSee('Scheduled')->assertSee('Mark as Complete');
        $this->put(route('maintenance.complete', $schedule->id), ['completion_notes' => 'Inspection finished', 'scheduled_date' => '2030-01-01'])
            ->assertRedirect(route('maintenance.index'));
        $schedule->refresh();
        $this->assertSame('completed', $schedule->status);
        $this->assertNotNull($schedule->completed_at);
        $this->assertSame('Inspection finished', $schedule->completion_notes);
        $this->assertSame('2027-02-12', $schedule->scheduled_date->format('Y-m-d'));
        $this->assertDatabaseHas('notifications', ['user_id' => $admin->id, 'title' => 'Maintenance Completed', 'related_id' => $schedule->id]);
        $this->get(route('maintenance.show', $schedule->id))->assertOk()->assertDontSee('Mark as Complete');
        $originalAttributes = $schedule->getAttributes();
        $this->put(route('maintenance.complete', $schedule->id), ['completion_notes' => 'Overwrite'])->assertStatus(409);
        $this->assertSame($originalAttributes, $schedule->fresh()->getAttributes());
    }

    public function test_admins_and_users_cannot_complete_or_set_dates_on_existing_reports(): void
    {
        $schedule = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'pending', 'month_year' => '2026-11']);
        $originalAttributes = $schedule->fresh()->getAttributes();
        foreach (['admin', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->put(route('maintenance.complete', $schedule->id), ['completion_notes' => 'Not permitted', 'scheduled_date' => '2030-01-01'])->assertForbidden();
            $this->assertSame($originalAttributes, $schedule->fresh()->getAttributes());
            if ($role === 'admin') {
                $this->get(route('maintenance.show', $schedule->id))->assertOk()->assertDontSee('Mark as Complete');
            } else {
                $this->get(route('maintenance.show', $schedule->id))->assertForbidden();
            }
        }
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_completion_validates_notes_and_rejects_cancelled_maintenance(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $schedule = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'in_progress', 'month_year' => '2026-11']);
        $this->put(route('maintenance.complete', $schedule->id), ['completion_notes' => str_repeat('x', 1001)])
            ->assertSessionHasErrors('completion_notes');
        $this->assertSame('in_progress', $schedule->fresh()->status);
        $this->put(route('maintenance.complete', $schedule->id), [])->assertRedirect(route('maintenance.index'));
        $this->assertSame('completed', $schedule->fresh()->status);
        $cancelled = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'cancelled', 'month_year' => '2026-11']);
        $this->put(route('maintenance.complete', $cancelled->id), [])->assertStatus(409);
        $this->get(route('maintenance.show', $cancelled->id))->assertOk()->assertDontSee('Mark as Complete');
    }

    public function test_pdf_exports_oldest_submissions_first_with_latest_work_at_the_bottom(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $latest = MaintenanceSchedule::create(array_replace($this->scheduleData(), ['scheduled_date' => '2026-11-01']) + ['status' => 'completed']);
        $latest->forceFill(['created_at' => '2026-11-17 12:00:00'])->save();
        $oldest = MaintenanceSchedule::create(array_replace($this->scheduleData(), ['scheduled_date' => '2026-11-30']) + ['status' => 'completed']);
        $oldest->forceFill(['created_at' => '2026-11-16 12:00:00'])->save();
        $sameTime = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'completed']);
        $sameTime->forceFill(['created_at' => '2026-11-17 12:00:00'])->save();
        MaintenanceSchedule::create($this->scheduleData() + ['status' => 'pending']);

        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data) use ($oldest, $latest, $sameTime): bool {
            $this->assertSame('maintenance.export-pdf', $view);
            $this->assertSame([$oldest->id, $latest->id, $sameTime->id], $data['maintenanceSchedules']->modelKeys());

            return true;
        })->andReturnSelf();
        Pdf::shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        Pdf::shouldReceive('download')->once()->andReturn(response('PDF'));
        $this->get(route('reports.maintenance.export-pdf'))->assertOk();
    }

    public function test_monthly_export_includes_the_whole_month_and_all_pages_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $included = collect();
        for ($index = 1; $index <= 15; $index++) {
            $included->push(MaintenanceSchedule::create(array_replace($this->scheduleData(), [
                'scheduled_date' => $index === 1 ? '2026-11-01' : '2026-11-30',
                'activity' => 'Monthly activity '.$index,
            ]) + ['status' => 'completed']));
        }
        foreach (['2026-10-31', '2026-12-01', '2025-11-17'] as $date) {
            MaintenanceSchedule::create(array_replace($this->scheduleData(), ['scheduled_date' => $date, 'activity' => 'Excluded record']) + ['status' => 'completed']);
        }
        MaintenanceSchedule::create($this->scheduleData() + ['status' => 'pending']);
        $html = view('maintenance.export-pdf', ['maintenanceSchedules' => $included, 'monthName' => 'November', 'yearSuffix' => '26'])->render();
        $this->assertSame(2, substr_count($html, '<table class="main">'));
        $this->assertStringContainsString('Monthly activity 15', $html);
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $pdf->output();
        $this->assertSame(2, $pdf->getDomPDF()->getCanvas()->get_page_count());

        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data) use ($included): bool {
            $this->assertSame($included->pluck('id')->all(), $data['maintenanceSchedules']->modelKeys());
            $this->assertSame('November', $data['monthName']);
            $this->assertSame('26', $data['yearSuffix']);

            return $view === 'maintenance.export-pdf';
        })->andReturnSelf();
        Pdf::shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        Pdf::shouldReceive('download')->once()->andReturn(response('PDF'));
        $this->get(route('reports.maintenance.export-pdf', ['month' => '2026-11', 'date_from' => '2026-11-10', 'date_to' => '2026-11-20']))->assertOk();
    }

    public function test_export_rejects_invalid_months(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['2026-13', 'invalid', '2026-11-01'] as $month) {
            $this->get(route('reports.maintenance.export-pdf', ['month' => $month]))->assertSessionHasErrors('month');
        }
    }

    public function test_invalid_export_date_filters_return_validation_errors(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([
            ['date_from' => 'invalid'],
            ['date_to' => '2026-02-30'],
            ['date_from' => '2026-11-30', 'date_to' => '2026-11-01'],
            ['status' => 'unknown'],
        ] as $filters) {
            $this->get(route('reports.maintenance.export-pdf', $filters))->assertSessionHasErrors();
        }
        $this->get(route('maintenance.index', ['month' => 'invalid']))->assertSessionHasErrors('month');
    }

    public function test_empty_month_still_exports_the_selected_month_heading(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        Pdf::shouldReceive('loadView')->once()->withArgs(function (string $view, array $data): bool {
            $this->assertCount(0, $data['maintenanceSchedules']);
            $this->assertSame('February', $data['monthName']);
            $this->assertSame('27', $data['yearSuffix']);
            $html = view($view, $data)->render();
            $this->assertSame(1, substr_count($html, '<table class="main">'));

            return true;
        })->andReturnSelf();
        Pdf::shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        Pdf::shouldReceive('download')->once()->andReturn(response('PDF'));
        $this->get(route('reports.maintenance.export-pdf', ['month' => '2027-02']))->assertOk();
    }

    public function test_failed_notification_rolls_back_creation_and_completion(): void
    {
        User::factory()->create(['role' => 'admin']);
        $this->actingAs(User::factory()->create(['role' => 'engineer']));
        $schedule = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'pending', 'month_year' => '2026-11']);
        Event::listen('eloquent.creating: '.Notification::class, function (): void {
            throw new \RuntimeException('Notification unavailable');
        });
        try {
            $this->post(route('maintenance.store'), $this->scheduleData())->assertStatus(500);
            $this->assertDatabaseCount('maintenance_schedules', 1);
            $this->put(route('maintenance.complete', $schedule->id), ['completion_notes' => 'Finished'])->assertStatus(500);
            $this->assertSame('pending', $schedule->fresh()->status);
            $this->assertNull($schedule->fresh()->completed_at);
            $this->assertDatabaseCount('notifications', 0);
        } finally {
            Event::forget('eloquent.creating: '.Notification::class);
        }
    }

    public function test_maintenance_requests_cannot_be_deleted_by_any_role(): void
    {
        $schedule = MaintenanceSchedule::create($this->scheduleData() + ['status' => 'pending', 'month_year' => '2026-11']);

        foreach (['admin', 'engineer', 'user'] as $role) {
            $this->actingAs(User::factory()->make(['id' => 1, 'role' => $role]));
            if ($role !== 'user') {
                $this->get(route('maintenance.index', ['month' => '2026-11']))->assertOk()
                    ->assertDontSee('value="DELETE"', false);
            }
            $this->delete(route('maintenance.destroy', $schedule->id))->assertForbidden();
            $this->assertModelExists($schedule);
        }
        $this->assertDatabaseCount('notifications', 0);
    }

    private function scheduleData(): array
    {
        return ['scheduled_date' => '2026-11-17', 'activity' => 'Inspect air conditioners', 'maintenance_in_charge' => null, 'engineer_in_charge' => null, 'remarks' => null];
    }
}
