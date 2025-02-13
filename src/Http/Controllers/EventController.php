<?php

namespace Savrock\Siop\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Savrock\Siop\Facades\Siop;
use Savrock\Siop\Models\SecurityEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EventController extends Controller
{
    public function list(Request $request)
    {
        $query = SecurityEvent::query();

        if ($request->has('event_type') && $request->type !== null) {
            $query->where('category', $request->event_type);
        }

        if ($request->has('severity') && $request->severity !== null) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('ip') && !empty($request->ip)) {
            $query->where('ip_address', 'like', "%{$request->ip}%");
        }

        if ($request->has('route') && !empty($request->route)) {
            $query->where('route', 'like', "%{$request->route}%");
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sortColumn = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortColumn, $sortOrder);


        $events = $query->paginate(15);
        $eventTypes = SecurityEvent::pluck('category')->unique();

        return view('siop::event-list', compact('events', 'eventTypes'));
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
