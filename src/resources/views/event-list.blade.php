@extends('siop::layouts.app')

@section('content')
    <div class="flex flex-col w-full">
        <h2 class="text-3xl font-semibold text-gray-800 dark:text-white">Security Events</h2>

        <!-- Filters -->
        <div>
            <button type="button" class="collapsible bg-blue-500 mt-4 flex px-2 py-1 rounded flex gap-2">
                Filters
                <svg xmlns="http://www.w3.org/2000/svg" shape-rendering="geometricPrecision" style="width: 20px"
                     text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd"
                     clip-rule="evenodd" viewBox="0 0 512 410.73">
                    <path fill-rule="nonzero"
                          d="M335.62 410.73H164.96V239.89L13.31 59.96C7.33 52.52 3.19 44.79 1.29 37.65c-1.79-6.72-1.76-13.28.34-19.1 2.3-6.44 6.92-11.63 13.91-14.9C20.35 1.41 26.3.13 33.4.1L472.7.04c7.93-.29 14.95.96 20.74 3.44 7.02 2.97 12.28 7.87 15.44 14.17 3.05 6.1 3.93 13.27 2.34 21.06-1.5 7.24-5.17 15.11-11.32 23.16l-151.94 178.1v170.76h-12.34zm95.61-347.71-69.16 81.05-18.67-16.01 69.16-81.05 18.67 16.01zm-84.8 99.39-24.45 28.66-18.68-16.01 24.45-28.66 18.68 16.01zM189.64 386.06h133.64V235.48l3-8L480.45 46.79c3.77-4.97 5.94-9.39 6.7-13.04.45-2.2.35-3.95-.24-5.12-.49-.97-1.58-1.87-3.19-2.55-2.53-1.13-6.06-1.64-10.44-1.42l-439.84.06c-3.33-.05-5.83.41-7.5 1.18-.68.32-1.09.65-1.18.92-.32.91-.2 2.48.33 4.46 1.05 3.96 3.61 8.57 7.38 13.28L186.7 227.59l2.94 7.89v150.58z"/>
                </svg>
            </button>
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow mt-4 content" style="display: none">
                <form id="filter-form" method="GET" action="{{ route('siop-events.list') }}"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start-date" class="block text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" id="start-date" name="start_date"
                               class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="end-date" class="block text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" id="end-date" name="end_date"
                               class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="severity" class="block text-gray-700 dark:text-gray-300">Severity</label>
                        <select id="severity" name="severity"
                                class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">All</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label for="event-type" class="block text-gray-700 dark:text-gray-300">Event Type</label>
                        <select id="event-type" name="event_type"
                                class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                            <option value="">All</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ip" class="block text-gray-700 dark:text-gray-300">IP Address</label>
                        <input type="text" id="ip" name="ip"
                               class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label for="user" class="block text-gray-700 dark:text-gray-300">User</label>
                        <input type="text" id="user" name="user"
                               class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit"
                                class="mt-6 py-2 px-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Event List -->
        <div class="flex flex-col w-full  bg-white dark:bg-gray-800 p-4 rounded-lg shadow mt-6">
            <table class="text-left border-collapse">
                <thead>
                <tr>
                    <th class="p-3 border-b" onclick="sortTable('id')">ID</th>
                    <th class="p-3 border-b cursor-pointer hidden lg:block" onclick="sortTable('created_at')">
                        Timestamp
                    </th>
                    <th class="p-3 border-b cursor-pointer" onclick="sortTable('category')">Type</th>
                    <th class="p-3 border-b cursor-pointer" onclick="sortTable('severity')">Severity</th>
                    <th class="p-3 border-b">IP Address</th>
                    <th class="p-3 border-b">User</th>
                </tr>
                </thead>
                <tbody>
                @foreach($events as $event)
                    <tr class="border-t hover:bg-gray-100 dark:hover:bg-gray-700"
                        onclick="window.location = '{{route('siop-events.show', $event->id)}}'">
                        <td class="p-3">{{ $event->id }}</td>
                        <td class="p-3 hidden lg:block">{{ $event->created_at }}</td>
                        <td class="p-3">{{ ucfirst($event->category) }}</td>
                        <td class="p-3 text-{{ $event->severity === 'high' ? 'red-500' : ($event->severity === 'medium' ? 'yellow-500' : 'green-500') }}">{{ ucfirst($event->severity) }}</td>
                        <td class="p-3">{{ $event->meta['IP'] ?? 'N/A' }}</td>
                        <td class="p-3">{{ $event->meta['User'] ?? 'Guest' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $events->links() }}
            </div>
        </div>
    </div>

    <script>
        function sortTable(column) {
            let url = new URL(window.location.href);
            let currentSort = url.searchParams.get('sort_by');
            let currentOrder = url.searchParams.get('order') || 'asc';
            let newOrder = (currentSort === column && currentOrder === 'asc') ? 'desc' : 'asc';

            url.searchParams.set('sort_by', column);
            url.searchParams.set('order', newOrder);
            window.location.href = url.toString();
        }
    </script>
    <script>
        var coll = document.getElementsByClassName("collapsible");
        var i;

        for (i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function () {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>

@endsection
