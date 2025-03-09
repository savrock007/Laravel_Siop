@extends('siop::layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-semibold text-gray-800 dark:text-gray-200">Security Event Details</h2>
        @if (session('success'))
            <div class="mb-4 mt-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded-lg dark:bg-green-900 dark:border-green-600 dark:text-green-300">
                <strong>Success:</strong> {!! session('success') !!}
            </div>
        @endif
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg mt-6">
            <div class="grid grid-cols-2 gap-4 text-lg">
                <p><strong class="text-gray-700 dark:text-gray-300">Type:</strong> <span
                        class="text-gray-900 dark:text-gray-100">{{ $event->category }}</span></p>
                <p><strong class="text-gray-700 dark:text-gray-300">Severity:</strong> <span
                        class="text-{{ $event->severity === 'high' ? 'red-500' : ($event->severity === 'medium' ? 'yellow-500' : 'green-500') }}">{{ ucfirst($event->severity) }}</span>
                </p>
                <p><strong class="text-gray-700 dark:text-gray-300">Timestamp:</strong> <span
                        class="text-gray-900 dark:text-gray-100">{{ $event->created_at }}</span></p>
                <p><strong class="text-gray-700 dark:text-gray-300">IP Address:</strong> <span
                        class="text-gray-900 dark:text-gray-100">{{ $meta['IP'] ?? 'N/A' }} {{$ip_blocked ? "(blocked)" : ""}}</span>
                </p>
                <p><strong class="text-gray-700 dark:text-gray-300">Message:</strong> <span
                        class="text-gray-900 dark:text-gray-100">{{ $event->message }}</span></p>
                <p><strong class="text-gray-700 dark:text-gray-300">Route:</strong> <span
                        class="text-gray-900 dark:text-gray-100">{{ $meta['Route'] ?? 'Unknown' }}</span></p>
            </div>
            <div class="mt-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Raw Metadata</h3>
                <pre
                    class="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg text-sm text-gray-700 dark:text-gray-300 overflow-auto">{{ json_encode($meta, JSON_PRETTY_PRINT) }}</pre>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex space-x-4">
                <form action="{{ route('siop-events.destroy', $event->id) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this event?');">
                    @csrf
                    @method('DELETE')
                    <button class="px-6 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700">Delete Event
                    </button>
                </form>

                <form action="{{ route('siop-events.block-ip', $event->id) }}" method="POST"
                      onsubmit="return confirm('Block this IP?');">
                    @csrf
                    <button
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg shadow hover:bg-yellow-700" {{$ip_blocked ? 'hidden' : ''}}>
                        Block IP
                    </button>
                </form>

                <form action="{{ route('siop-events.whitelist-ip', $event->id) }}" method="POST"
                      onsubmit="return confirm('Whitelist this IP?');">
                    @csrf
                    <button
                        class="px-6 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700" {{!$ip_blocked ? 'hidden' : ''}}>Whitelist
                        IP
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
