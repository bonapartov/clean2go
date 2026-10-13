<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\User;
use App\Support\Calls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * App-to-app calls (client ↔ executor) over our LiveKit server.
 * Flow: caller POST /calls → callee gets a push → callee POST
 * /calls/{id}/accept (or decline) → both join the LiveKit room with their
 * own token → either side POST /calls/{id}/end. Both apps poll GET
 * /calls/{id} for the other side's moves. See App\Support\Calls.
 *
 * Own code (not Fixit).
 */
class CallController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'callee_id' => ['required', 'integer', 'exists:users,id'],
            'booking_id' => ['nullable', 'integer', 'exists:bookings,id'],
            'type' => ['nullable', 'in:audio,video'],
        ]);

        if (! Calls::isEnabled()) {
            return $this->fail(__('static.calls.disabled'), 503);
        }

        $caller = $request->user();
        $callee = User::findOrFail($validated['callee_id']);
        $booking = Calls::sharedBooking($caller, $callee, $validated['booking_id'] ?? null);
        if (! $booking) {
            return $this->fail(__('static.calls.not_allowed'), 403);
        }

        $busy = Call::where('callee_id', $callee->id)->whereIn('status', ['ringing', 'accepted'])
            ->get()->map->expireIfUnanswered()->whereIn('status', ['ringing', 'accepted'])->isNotEmpty();
        if ($busy) {
            return $this->fail(__('static.calls.busy'), 409);
        }

        $call = Call::create([
            'booking_id' => $booking->id,
            'caller_id' => $caller->id,
            'callee_id' => $callee->id,
            'room' => 'call_'.Str::lower(Str::random(24)),
            'type' => $validated['type'] ?? 'audio',
            'status' => 'ringing',
        ]);

        Calls::notifyCallee($call, $caller);

        return $this->ok($call, Calls::joinInfo($call, $caller));
    }

    public function show(Request $request, Call $call): JsonResponse
    {
        $this->authorizeParticipant($request, $call);

        return $this->ok($call->expireIfUnanswered());
    }

    /** Latest call ringing for me — the app checks it when opened from the push. */
    public function incoming(Request $request): JsonResponse
    {
        $call = Call::whereIn('callee_id', Calls::identitiesOf($request->user()))
            ->where('status', 'ringing')->latest('id')->first();

        return $call && $call->expireIfUnanswered()->status === 'ringing'
            ? $this->ok($call)
            : response()->json(['success' => true, 'data' => null]);
    }

    public function accept(Request $request, Call $call): JsonResponse
    {
        $this->authorizeCallee($request, $call);
        if ($call->expireIfUnanswered()->status !== 'ringing') {
            return $this->fail(__('static.calls.not_ringing'), 409, $call);
        }

        $call->update(['status' => 'accepted', 'answered_at' => now()]);

        return $this->ok($call, Calls::joinInfo($call, $request->user()));
    }

    public function decline(Request $request, Call $call): JsonResponse
    {
        $this->authorizeCallee($request, $call);
        if ($call->expireIfUnanswered()->status === 'ringing') {
            $call->update(['status' => 'declined', 'ended_at' => now()]);
        }

        return $this->ok($call);
    }

    public function end(Request $request, Call $call): JsonResponse
    {
        $this->authorizeParticipant($request, $call);

        $call->expireIfUnanswered();
        if ($call->status === 'ringing') {
            $call->update(['status' => 'cancelled', 'ended_at' => now()]);
        } elseif ($call->status === 'accepted') {
            $call->update(['status' => 'ended', 'ended_at' => now()]);
        }

        return $this->ok($call);
    }

    /** The callee, or the freelancer whose shadow serviceman was called. */
    private function authorizeCallee(Request $request, Call $call): void
    {
        if (! in_array((int) $call->callee_id, Calls::identitiesOf($request->user()), true)) {
            abort(403);
        }
    }

    private function authorizeParticipant(Request $request, Call $call): void
    {
        $ids = Calls::identitiesOf($request->user());
        if (! in_array((int) $call->callee_id, $ids, true) && ! in_array((int) $call->caller_id, $ids, true)) {
            abort(403);
        }
    }

    private function ok(Call $call, ?array $join = null): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->present($call, $join)]);
    }

    private function fail(string $message, int $status, ?Call $call = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $call ? $this->present($call) : null,
        ], $status);
    }

    private function present(Call $call, ?array $join = null): array
    {
        $call->loadMissing(['caller:id,name', 'callee:id,name']);

        return array_filter([
            'id' => $call->id,
            'booking_id' => $call->booking_id,
            'type' => $call->type,
            'status' => $call->status,
            'caller' => ['id' => $call->caller_id, 'name' => $call->caller?->name],
            'callee' => ['id' => $call->callee_id, 'name' => $call->callee?->name],
            'answered_at' => $call->answered_at?->toIso8601String(),
            'livekit_url' => $join['livekit_url'] ?? null,
            'token' => $join['token'] ?? null,
        ], fn ($v) => $v !== null);
    }
}
