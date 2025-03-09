<?php

namespace Savrock\Siop\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Savrock\Siop\Models\PatternRule;

class SettingsController extends Controller
{

    public function patterns()
    {
        $routes = collect(Route::getRoutes())
            ->map(fn($route) => $route->uri())
            ->filter(fn($uri) => !str_starts_with($uri, 'siop'))
            ->unique()
            ->values();

        $patterns = PatternRule::all();

        return view('siop::patterns', compact('routes', 'patterns'));
    }

    public function patternsStore(Request $request)
    {
        $request->validate([
            'route' => 'required|string',
            'previous_route' => 'nullable|string',
            'time_frame' => 'required|integer|min:1',
            'action' => 'required|in:log,alert,block',
        ]);

        PatternRule::create($request->all());

        return redirect()->route('siop.patterns.index')->with('success', 'Pattern rule added.');
    }

    public function patternsDestroy($id)
    {
        PatternRule::destroy($id);
        return redirect()->route('siop.patterns.index')->with('success', 'Pattern rule removed.');
    }
}
