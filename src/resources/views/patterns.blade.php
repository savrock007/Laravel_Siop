@extends('siop::layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Pattern-Based Request Analysis</h2>

        <!-- Add New Pattern Rule -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Add New Pattern Rule</h3>
            <form method="POST" action="{{ route('siop.patterns.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @csrf
                <select name="route" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400" required>
                    <option value="" disabled selected>Select Target Route</option>
                    @foreach($routes as $route)
                        <option value="{{ $route }}">{{ $route }}</option>
                    @endforeach
                </select>

                <select name="previous_route" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400">
                    <option value="" selected>Optional: Select Previous Route</option>
                    @foreach($routes as $route)
                        <option value="{{ $route }}">{{ $route }}</option>
                    @endforeach
                </select>

                <input type="number" name="time_frame" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400" placeholder="Time Frame (Seconds)" required>

                <select name="action" class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400">
                    <option value="log">Log</option>
                    <option value="alert">Alert</option>
                    <option value="block">Block</option>
                </select>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow">Add Rule</button>
            </form>
        </div>

        <!-- Existing Pattern Rules Table -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Existing Pattern Rules</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <thead>
                    <tr class="bg-gray-100">
                        <th class="p-3 text-left border-b">Target Route</th>
                        <th class="p-3 text-left border-b">Previous Route</th>
                        <th class="p-3 text-left border-b">Time Frame (Sec)</th>
                        <th class="p-3 text-left border-b">Action</th>
                        <th class="p-3 text-left border-b text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($patterns as $pattern)
                        <tr class="border-b">
                            <td class="p-3">{{ $pattern->route }}</td>
                            <td class="p-3">{{ $pattern->previous_route ?? 'N/A' }}</td>
                            <td class="p-3">{{ $pattern->time_frame }}</td>
                            <td class="p-3">
                            <span class="px-2 py-1 rounded-md text-white
                                {{ $pattern->action == 'log' ? 'bg-blue-500' : ($pattern->action == 'alert' ? 'bg-yellow-500' : 'bg-red-500') }}">
                                {{ ucfirst($pattern->action) }}
                            </span>
                            </td>
                            <td class="p-3 text-center">
                                <form action="{{ route('siop.patterns.destroy', $pattern->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg shadow">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($patterns->isEmpty())
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">No pattern rules configured.</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
