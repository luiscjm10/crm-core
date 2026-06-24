<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Tickets\Actions\CloseTicketAction;
use App\Domains\Tickets\Actions\CreateTicketAction;
use App\Domains\Tickets\Actions\AssignTicketAction;
use App\Domains\Tickets\Actions\UpdateTicketStatusAction;
use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use App\Domains\Tickets\TicketType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tickets.read')->only('index', 'show');
        $this->middleware('permission:tickets.create')->only('create', 'store');
        $this->middleware('permission:tickets.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $perPage = in_array($request->input('perPage'), [10, 20, 50, 100]) ? (int) $request->input('perPage') : 10;

        $sort = in_array($request->input('sort'), ['status', 'requested_at', 'created_at', 'updated_at', 'ticket_type'])
            ? $request->input('sort') : 'requested_at';
        $direction = in_array($request->input('direction'), ['asc', 'desc'])
            ? $request->input('direction') : 'desc';

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'ticket_type_id' => $request->get('ticket_type_id'),
            'date_from' => $request->get('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->get('date_to', now()->endOfMonth()->format('Y-m-d')),
        ];

        $user = $request->user();
        $query = Ticket::with('company', 'ticketType', 'creator', 'requester', 'assignee')
            ->withSum('comments', 'time_spent_minutes');

        if ($user->hasRole('super-admin')) {
            // Nivel 1: todo el sistema, sin filtro
        } elseif ($user->can('tickets.view-all')) {
            // Nivel 2: compañías asignadas + propias
            $companyIds = $user->companies()->pluck('companies.id')->toArray();
            if ($user->company_id) {
                $companyIds[] = $user->company_id;
            }
            $companyIds = array_unique($companyIds);

            $query->where(function ($q) use ($user, $companyIds) {
                if (!empty($companyIds)) {
                    $q->whereIn('company_id', $companyIds);
                }
                $q->orWhere('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        } else {
            // Nivel 3: solo solicitudes propias
            $query->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        }

        if ($search = $filters['search']) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status']) {
            $query->where('status', $status);
        }

        if ($ticketTypeId = $filters['ticket_type_id']) {
            $query->where('ticket_type_id', $ticketTypeId);
        }

        $query->whereBetween('requested_at', [
            $filters['date_from'] . ' 00:00:00',
            $filters['date_to'] . ' 23:59:59',
        ]);

        if ($sort === 'ticket_type') {
            $query->leftJoin('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
                ->select('tickets.*')
                ->orderBy('ticket_types.name', $direction);
        } else {
            $query->orderBy('tickets.' . $sort, $direction);
        }

        $tickets = $query->paginate($perPage)->appends([
            'perPage' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'search' => $filters['search'],
            'status' => $filters['status'],
            'ticket_type_id' => $filters['ticket_type_id'],
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
        ]);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => $filters,
            'ticketTypes' => TicketType::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $pivotCompanies = $user->companies;

        if ($pivotCompanies->isNotEmpty()) {
            $companies = $pivotCompanies->where('is_active', true)->values();
        } elseif ($user->company_id) {
            $companies = Company::where('id', $user->company_id)->where('is_active', true)->get();
        } else {
            $companies = Company::where('is_active', true)->get();
        }

        return Inertia::render('Admin/Tickets/Create', [
            'companies' => $companies,
            'canAssignRequester' => $user->can('tickets.assign-requester'),
            'canSetRequestedAt' => $user->can('tickets.set-requested-at'),
        ]);
    }

    public function store(Request $request, CreateTicketAction $createTicket)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'ticket_type_id' => [
                'required',
                'exists:ticket_types,id',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = \DB::table('company_ticket_type')
                        ->where('company_id', $request->company_id)
                        ->where('ticket_type_id', $value)
                        ->exists();

                    if (!$exists) {
                        $fail('El tipo de solicitud seleccionado no está disponible para esta compañía.');
                    }
                },
            ],
            'description' => 'required|string|max:5000',
            'requester_id' => 'nullable|exists:users,id',
            'requested_at' => $request->user()->can('tickets.set-requested-at')
                ? 'nullable|date'
                : 'prohibited',
        ]);

        $requesterId = $validated['requester_id'] ?? $request->user()->id;

        $ticket = $createTicket->execute(
            $validated,
            creatorId: $request->user()->id,
            requesterId: $requesterId
        );

        return redirect()->route('admin.tickets.show', $ticket);
    }

    public function show(Request $request, Ticket $ticket)
    {
        $ticket->load([
            'company', 'ticketType', 'creator', 'requester', 'assignee',
            'comments' => fn($q) => $q->reorder()->oldest()->with('user'),
        ]);

        $user = $request->user();
        $canClose = $ticket->status !== 'closed'
            && ($user->hasRole('super-admin')
                || $user->id === $ticket->creator_id
                || $user->id === $ticket->requester_id
                || $user->can('tickets.close'));

        $canComment = $ticket->status !== 'closed'
            && ($user->hasRole('super-admin')
                || $user->id === $ticket->creator_id
                || $user->id === $ticket->requester_id
                || $user->can('tickets.comment'));

        $canLogTime = $user->can('tickets.log-time');
        $canViewResolutionTime = $user->can('tickets.view-resolution-time');

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'canClose' => $canClose,
            'canComment' => $canComment,
            'canLogTime' => $canLogTime,
            'canViewResolutionTime' => $canViewResolutionTime,
        ]);
    }

    public function close(Request $request, Ticket $ticket, CloseTicketAction $closeTicket)
    {
        $user = $request->user();
        $canClose = $ticket->status !== 'closed'
            && ($user->hasRole('super-admin')
                || $user->id === $ticket->creator_id
                || $user->id === $ticket->requester_id
                || $user->can('tickets.close'));

        abort_unless($canClose, 403, 'No tienes permiso para cerrar este ticket.');

        $closeTicket->execute($ticket, $user);

        return redirect()->back()->with('success', 'Ticket cerrado correctamente.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index');
    }
}
