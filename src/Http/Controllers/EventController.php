<?php

namespace Savrock\Siop\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Savrock\Siop\Models\Ip;
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
        return view('siop::event-details', ['event' => $event, 'meta' => $event->meta, true]);
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


        if (!isset($event->meta['IP'])) {
            return back()->with('error', 'No IP found for this event.');
        }

        Ip::updateOrCreate([
            'ip_hash' => Hash::make($event->meta['IP'])
        ], [
            'ip' => $event->meta['IP'],
            'status' => 'blocked',
            'expires_at' => now()->add(config('siop.block_time'), config('siop.block_time_unit')),
            'meta' => $event->meta

        ]);
        return back()->with('success', 'IP blocked successfully.');
    }

    public function whitelistIp($event)
    {
        $event = SecurityEvent::find($event);
        if (!$event) {
            throw new NotFoundHttpException();
        }


        if (!isset($event->meta['IP'])) {
            return back()->with('error', 'No IP found for this event.');
        }

//        BlockedIp::where('ip', $event->meta['IP'])->delete();
        return back()->with('success', 'IP whitelisted successfully.');
    }
}
