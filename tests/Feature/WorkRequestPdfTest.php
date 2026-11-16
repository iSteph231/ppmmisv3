<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class WorkRequestPdfTest extends TestCase
{
    private function workRequest(string $type = 'repair'): WorkRequest
    {
        $workRequest = new WorkRequest([
            'request_number' => 'WR-202611-0001',
            'work_type' => $type,
            'department' => 'Engineering',
            'building_name' => 'Main Building',
            'office_room' => 'Room 101',
            'ocular_details' => 'Inspect the ceiling',
            'installation_details' => 'Install a fan',
            'repair_details' => 'Repair a leaking pipe',
            'replacement_details' => 'Replace a broken chair',
            'others_details' => 'Other maintenance',
        ]);
        $workRequest->created_at = '2026-11-16 09:00:00';
        $workRequest->setRelation('user', User::factory()->make(['name' => 'Sample Requestor']));

        return $workRequest;
    }

    public function test_each_work_type_uses_the_reference_form_and_fits_one_page(): void
    {
        foreach (['ocular_inspection', 'installation', 'repair', 'replacement', 'others'] as $type) {
            $html = view('work-requests.export-single-pdf', ['workRequest' => $this->workRequest($type)])->render();
            foreach (['FM-AD-ENG-02', 'Rev. 0', '03-Oct-2017', 'WORK REQUEST FORM', 'Engineering', 'Main Building', 'Room 101', 'Sample Requestor', 'Work Request Accomplished by:'] as $text) {
                $this->assertStringContainsString($text, $html);
            }
            $this->assertSame(1, substr_count($html, '✓'));
            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $this->assertStringStartsWith('%PDF-', $pdf->output());
            $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count(), $type);
        }
    }

    public function test_bulk_exports_use_one_form_per_request_and_handle_empty_results(): void
    {
        $html = view('work-requests.export-pdf', ['workRequests' => collect([$this->workRequest(), $this->workRequest('installation')])])->render();
        $this->assertSame(2, substr_count($html, 'class="request-sheet"'));
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $pdf->output();
        $this->assertSame(2, $pdf->getDomPDF()->getCanvas()->get_page_count());
        $this->view('work-requests.export-pdf', ['workRequests' => collect()])->assertSee('No work requests found for export.');
    }

    public function test_long_details_wrap_and_user_text_is_escaped(): void
    {
        $workRequest = $this->workRequest();
        $workRequest->repair_details = str_repeat('Repair the leaking pipe in the main office. ', 4).'<script>alert(1)</script>';
        $html = view('work-requests.export-single-pdf', compact('workRequest'))->render();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $pdf->output();
        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }
}
