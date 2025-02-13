<?php

namespace Savrock\Siop\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Savrock\Siop\Models\SecurityEvent;

class DashboardController extends Controller
{

    public function index()
    {
        return view('siop::dashboard');
    }

    public function showDashboardData(Request $request)
    {
        $period = $request->input('period', 'today');
        $startDate = null;
        $endDate = null;

        if ($period === 'custom') {
            $startDate = $request->input('start');
            $endDate = $request->input('end');
        } else {
            switch ($period) {
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'today':
                default:
                    $startDate = Carbon::today();
                    $endDate = Carbon::today();
                    break;
            }
        }


        $eventTypes = SecurityEvent::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('category, count(*) as total'))
            ->groupBy('category')
            ->get();

        $eventTypesData = $eventTypes->pluck('total');
        $eventTypesLabels = $eventTypes->pluck('category');

        $eventSeverities = SecurityEvent::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('severity, count(*) as total'))
            ->groupBy('severity')
            ->get();

        $eventSeverityData = [
            $eventSeverities->where('severity', 'low')->first()->total ?? 0,
            $eventSeverities->where('severity', 'medium')->first()->total ?? 0,
            $eventSeverities->where('severity', 'high')->first()->total ?? 0,
        ];

        $eventsOverTime = SecurityEvent::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'), 'asc')
            ->get();

        $eventsOverTimeLabels = $eventsOverTime->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('d.m.Y');
        });

        $eventsOverTimeData = $eventsOverTime->pluck('total');


        // Return the data as JSON
        return response()->json([
            'eventTypes' => ['labels' => $eventTypesLabels, 'data' => $eventTypesData],
            'severities' => $eventSeverityData,
            'eventsOverTime' => ['labels' => $eventsOverTimeLabels, 'data' => $eventsOverTimeData],
        ]);
    }

    public function showEventDetails($id)
    {
        $event = SecurityEvent::findOrFail($id);

        $meta = json_decode($event->meta, true);

        return view('siop::event-details', compact('event', 'meta'));
    }


}
