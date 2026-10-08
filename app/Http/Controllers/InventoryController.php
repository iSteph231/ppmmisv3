<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryEntryRequest;
use App\Models\InventoryEntry;
use App\Support\InventoryForms;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function index(): View
    {
        return view('inventory', ['forms' => InventoryForms::names()]);
    }

    public function show(string $form): View
    {
        $sections = InventoryForms::sections($form);

        return view('inventory.entry', [
            'form' => $form,
            'name' => InventoryForms::names()[$form],
            'sections' => $sections,
            'entries' => InventoryEntry::where('form', $form)->latest('id')->paginate(10),
        ]);
    }

    public function store(StoreInventoryEntryRequest $request, string $form): RedirectResponse
    {
        InventoryEntry::create([
            'form' => $form,
            'user_id' => $request->user()->id,
            'data' => $request->validated('data'),
        ]);

        return redirect()->route('inventory.'.$form)->with('success', 'Inventory entry saved.');
    }

    public function exportPdf(string $form, ?InventoryEntry $entry = null): Response
    {
        $sections = InventoryForms::sections($form);
        abort_if($entry !== null && $entry->form !== $form, 404);

        return Pdf::loadView('inventory.export-pdf', [
            'form' => $form,
            'layout' => InventoryForms::pdfLayout($form),
            'name' => InventoryForms::names()[$form],
            'sections' => $sections,
            'entries' => $entry !== null ? collect([$entry]) : InventoryEntry::where('form', $form)->orderBy('id')->get(),
        ])->setPaper('a4', 'landscape')->download('inventory-'.$form.($entry !== null ? '-'.$entry->id : '').'.pdf');
    }
}
