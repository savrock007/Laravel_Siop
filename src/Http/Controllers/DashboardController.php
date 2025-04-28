<?php

namespace Savrock\Siop\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Savrock\Siop\Http\Middleware\Authenticate;
use Savrock\Siop\Models\SecurityEvent;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(Authenticate::class);
    }

    public function index()
    {
        return view('siop::dashboard');
    }

    public function showDashboardData(Request $request)
    {
        [$startDate, $endDate] = $this->resolvePeriod($request->input('period', 'this_week'), $request);

        $events = SecurityEvent::whereBetween('created_at', [$startDate, $endDate]);

        $eventTypes = $events->clone()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $eventSeverities = $events->clone()
            ->select('severity', DB::raw('count(*) as total'))
            ->groupBy('severity')
            ->pluck('total', 'severity');

        $eventsOverTime = $events->clone()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => Carbon::parse($row->date)->format('d.m.Y'),
                    'total' => $row->total,
                ];
            });

        return response()->json([
            'eventTypes' => [
                'labels' => $eventTypes->keys(),
                'data' => $eventTypes->values(),
            ],
            'severities' => [
                $eventSeverities['low'] ?? 0,
                $eventSeverities['medium'] ?? 0,
                $eventSeverities['high'] ?? 0,
            ],
            'eventsOverTime' => [
                'labels' => $eventsOverTime->pluck('date'),
                'data' => $eventsOverTime->pluck('total'),
            ],
        ]);
    }

    public function showEventDetails($id)
    {
        $event = SecurityEvent::findOrFail($id);

        return view('siop::event-details', [
            'event' => $event,
            'meta' => json_decode($event->meta, true),
        ]);
    }

    protected function resolvePeriod($period, Request $request)
    {
        switch ($period) {
            case 'custom':
                return [
                    Carbon::parse($request->input('start'))->startOfDay(),
                    Carbon::parse($request->input('end'))->endOfDay(),
                ];
            case 'this_week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'this_month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'today':
            default:
                return [Carbon::today(), Carbon::today()];
        }
    }
}
