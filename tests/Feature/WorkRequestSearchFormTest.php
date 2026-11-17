<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class WorkRequestSearchFormTest extends TestCase
{
    public function test_search_shares_the_filter_form_and_does_not_submit_the_current_page(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin']));
        $this->app->instance('request', Request::create('/work-requests', 'GET', [
            'search' => 'WR-2026-001',
            'status' => 'pending',
            'date_from' => '2026-11-01',
            'date_to' => '2026-11-18',
            'page' => 3,
        ]));

        $document = new DOMDocument;
        @$document->loadHTML(view('work-requests.index', ['workRequests' => collect()])->render());
        $xpath = new DOMXPath($document);
        $search = $xpath->query('//input[@id="tableSearch"]')->item(0);

        $this->assertNotNull($search);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//input[@name="search"]')->length);
        $this->assertSame('WR-2026-001', $search->getAttribute('value'));
        $this->assertFalse($search->hasAttribute('oninput'));
        $this->assertSame(1, $xpath->query('//form[@id="filterForm" and @method="GET"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//select[@name="status"]/option[@value="pending" and @selected]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//input[@name="date_from" and @value="2026-11-01"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//input[@name="date_to" and @value="2026-11-18"]')->length);
        $this->assertSame(0, $xpath->query('//input[@name="page"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//button[@type="submit" and normalize-space()="Apply Filters"]')->length);
        $this->assertSame(1, $xpath->query('//form[@id="filterForm"]//a[@href="'.route('work-requests.index').'" and normalize-space()="Reset"]')->length);
        $this->assertSame(0, $xpath->query('//form[@id="filterForm"]//*[@onchange or @onkeyup]')->length);
    }

    public function test_existing_filter_pages_use_explicit_submission_and_reset(): void
    {
        $this->actingAs(User::factory()->make(['id' => 1, 'role' => 'admin']));

        $pages = [
            ['inspection-reports.index', ['inspections' => collect(), 'totalInspections' => 0, 'pendingInspections' => 0, 'completedInspections' => 0], 'inspections.index'],
            ['maintenance.index', ['maintenanceSchedules' => collect()], 'maintenance.index'],
            ['reports.work-requests', ['workRequests' => collect()], 'reports.work-requests'],
            ['reports.maintenance', ['maintenanceSchedules' => collect()], 'reports.maintenance'],
            ['request-facility', ['facilityRequests' => new LengthAwarePaginator([], 0, 10)], 'request-facility.index'],
        ];

        foreach ($pages as [$view, $data, $route]) {
            $document = new DOMDocument;
            @$document->loadHTML(view($view, $data + ['errors' => new ViewErrorBag])->render());
            $xpath = new DOMXPath($document);
            $form = '//form[contains(concat(" ", normalize-space(@class), " "), " filter-bar ")]';

            $this->assertSame(1, $xpath->query($form.'[@method="GET" and @action="'.route($route).'"]')->length, $view);
            $this->assertSame(1, $xpath->query($form.'//button[@type="submit" and normalize-space()="Apply Filters"]')->length, $view);
            $this->assertSame(1, $xpath->query($form.'//a[@href="'.route($route).'" and normalize-space()="Reset"]')->length, $view);
            $this->assertSame(0, $xpath->query($form.'//*[@onchange or @oninput or @onkeyup]')->length, $view);
            $this->assertSame(0, $xpath->query($form.'//input[@name="page"]')->length, $view);
        }
    }
}
