<?php

namespace Savrock\Siop\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Savrock\Siop\Http\Middleware\Authenticate;
use Savrock\Siop\Models\SecurityEvent;
use Savrock\Siop\Siop;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authenticate::class);
    }

    public function list(Request $request)
    {
        $query = SecurityEvent::query();

        if ($request->has('start_date') && $request->start_date != null) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != null) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('event_type') && $request->event_type != null) {
            $query->where('category', $request->event_type);
        }

        if ($request->has('severity') && $request->severity != null) {
            $query->where('severity', $request->severity);
        }

        $events = $query->orderBy('id')->get();

        $events = $events->filter(function ($event) use ($request) {
            if ($request->input('ip') != null && ($event->meta['IP'] ?? null) !== $request->input('ip')) {
                return false;
            }

            if ($request->input('route') != null && ($event->meta['Route'] ?? null) !== $request->input('route')) {
                return false;
            }
            return true;
        });

//        $sortColumn = $request->get('sort', 'created_at');
//        $sortOrder = $request->get('order', 'desc');
//        $events->sortBy($sortColumn, $sortOrder);


        $events = self::paginateCollection($events, 15);
        $eventTypes = SecurityEvent::pluck('category')->unique();

        return view('siop::event-list', compact('events', 'eventTypes'));
    }

    private static function paginateCollection(Collection $items, int $perPage = 10, ?int $page = null)
    {
        $page = $page ?: request()->get('page', 1);
        $total = $items->count();

        $paginatedItems = $items->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function show($event)
    {
        $event = SecurityEvent::find($event);
        if (!$event) {
            throw new NotFoundHttpException();
        }

        $ip_blocked = $event->ip_blocked;

        return view('siop::event-details', ['event' => $event, 'meta' => $event->meta, 'ip_blocked' => $ip_blocked, true]);
    }

    public function destroy($event)
    {
        $event = SecurityEvent::find($event);

        if (!$event) {
            throw new NotFoundHttpException();
        }

        $event->delete();
        return redirect()->route('siop-dashboard.index')->with('success', 'Event deleted successfully.');
    }

    public function blockIp($event)
    {
        $event = SecurityEvent::find($event);
        if (!$event) {
            throw new NotFoundHttpException();
        }
        $ip = $event->meta['IP'];


        if (!isset($ip)) {
            return back()->with('error', 'No IP found for this event.');
        }

        Siop::blockIP($ip);

        return back()->with('success', 'IP blocked successfully.');
    }

    public function whitelistIp($event)
    {
        $event = SecurityEvent::find($event);
        if (!$event) {
            throw new NotFoundHttpException();
        }
        $ip = $event->meta['IP'];


        if (!isset($ip)) {
            return back()->with('error', 'No IP found for this event.');
        }

        Siop::unblockIP($ip);

        return back()->with('success', 'IP whitelisted successfully.');
    }
}
