<?php

namespace App\Http\Controllers;

use App\Domains\Notifications\Events\TicketCommented;
use App\Domains\Shared\Actions\AddCommentAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tickets.read')->only('store');
    }

    public function store(Request $request, AddCommentAction $addComment)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string|in:ticket',
            'commentable_id' => 'required|integer',
            'content' => 'required|string|max:5000',
            'time_spent_minutes' => 'nullable|integer|min:1',
        ]);

        $modelClass = match ($validated['commentable_type']) {
            'ticket' => \App\Domains\Tickets\Ticket::class,
        };

        $model = $modelClass::findOrFail($validated['commentable_id']);

        if (! $this->userCanComment($request->user(), $model)) {
            abort(403, 'No tienes permiso para comentar en este recurso.');
        }

        $timeSpentMinutes = $request->user()->can('tickets.log-time')
            ? ($validated['time_spent_minutes'] ?? null)
            : null;

        $comment = $addComment->execute(
            $model,
            $request->user(),
            $validated['content'],
            timeSpentMinutes: $timeSpentMinutes
        );

        if ($model instanceof \App\Domains\Tickets\Ticket) {
            event(new TicketCommented($model, $request->user(), $comment));
        }

        return redirect()->back();
    }

    private function userCanComment(User $user, Model $model): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($model instanceof \App\Domains\Tickets\Ticket) {
            if ($model->creator_id === $user->id || $model->requester_id === $user->id) {
                return true;
            }

            return $user->can('tickets.comment');
        }

        return $user->can('tickets.read');
    }
}
