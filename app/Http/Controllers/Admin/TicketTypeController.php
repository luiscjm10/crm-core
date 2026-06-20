<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Actions\CreateTicketTypeAction;
use App\Domains\Tickets\Actions\DeleteTicketTypeAction;
use App\Domains\Tickets\Actions\UpdateTicketTypeAction;
use App\Domains\Tickets\TicketType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ticket-types.read')->only('index');
        $this->middleware('permission:ticket-types.create')->only('store');
        $this->middleware('permission:ticket-types.update')->only('update');
        $this->middleware('permission:ticket-types.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $ticketTypes = TicketType::with('companies:id,name')
            ->latest()
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        return Inertia::render('Admin/TicketTypes/Index', [
            'ticketTypes' => $ticketTypes,
            'companies' => Company::select('id', 'name', 'is_active')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateTicketTypeAction $createTicketType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $createTicketType->execute(
            array_filter($validated, fn($key) => $key !== 'company_ids', ARRAY_FILTER_USE_KEY),
            $validated['company_ids'] ?? []
        );

        return redirect()->route('admin.ticket-types.index');
    }

    public function update(Request $request, TicketType $ticketType, UpdateTicketTypeAction $updateTicketType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name,' . $ticketType->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        $updateTicketType->execute(
            $ticketType,
            array_filter($validated, fn($key) => $key !== 'company_ids', ARRAY_FILTER_USE_KEY),
            $validated['company_ids'] ?? []
        );

        return redirect()->route('admin.ticket-types.index');
    }

    public function destroy(TicketType $ticketType, DeleteTicketTypeAction $deleteTicketType)
    {
        $deleteTicketType->execute($ticketType);

        return redirect()->route('admin.ticket-types.index');
    }
}
