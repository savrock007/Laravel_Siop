@extends('siop::layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex flex-col lg:flex-row justify-start items-center lg:items-end lg:space-x-4">
            <div class="w-full lg:w-auto">
                <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" id="start-date" name="start_date"
                       class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
            </div>

            <div class="w-full lg:w-auto">
                <label for="end-date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" id="end-date" name="end_date"
                       class="mt-1 block w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
            </div>

            <button id="apply-date-range"
                    class="mt-4 lg:mt-0 w-full lg:w-auto py-2.5 px-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Apply
            </button>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <div class="bg-white shadow-sm rounded-lg p-4">
                <h3 class="text-xl font-semibold mb-4">Event Type Breakdown</h3>
                <canvas id="eventTypeChart"></canvas>
            </div>

            <div class="bg-white shadow-md rounded-lg p-4">
                <h3 class="text-xl font-semibold mb-4">Event Severity Breakdown</h3>
                <canvas id="eventSeverityChart"></canvas>
            </div>

            <div class="bg-white shadow-md rounded-lg p-4">
                <h3 class="text-xl font-semibold mb-4">Events Over Time</h3>
                <canvas id="eventsOverTimeChart"></canvas>
            </div>

        </div>
    </div>


    <script>
        let eventTypeChart, eventSeverityChart, eventsOverTimeChart;

        // Function to clear existing charts
        function clearCharts() {
            // Destroy chart instances only if they exist
            if (eventTypeChart) {
                eventTypeChart.destroy();
            }
            if (eventSeverityChart) {
                eventSeverityChart.destroy();
            }
            if (eventsOverTimeChart) {
                eventsOverTimeChart.destroy();
            }
        }

        // Function to format date as dd.mm.yyyy
        function formatDate(date) {
            let d = new Date(date);
            let day = String(d.getDate()).padStart(2, '0');
            let month = String(d.getMonth() + 1).padStart(2, '0');
            let year = d.getFullYear();
            return `${day}.${month}.${year}`;
        }

        // Set default date range for "This Week"
        const startDateDefault = "{{ \Carbon\Carbon::now()->startOfWeek()->toDateString() }}";
        const endDateDefault = "{{ \Carbon\Carbon::now()->endOfWeek()->toDateString() }}";

        // Set values to the input fields for "This Week"
        document.getElementById('start-date').value = formatDate(startDateDefault);
        document.getElementById('end-date').value = formatDate(endDateDefault);

        // Initialize charts with default data
        fetchChartData(startDateDefault, endDateDefault);

        // Event listener for Apply button
        document.getElementById('apply-date-range').addEventListener('click', function () {
            var startDate = document.getElementById('start-date').value;
            var endDate = document.getElementById('end-date').value;

            // Convert dates back to yyyy-mm-dd format for the API request
            const startDateFormatted = startDate.split('.').reverse().join('-');
            const endDateFormatted = endDate.split('.').reverse().join('-');

            // Fetch data based on the selected date range
            fetchChartData(startDateFormatted, endDateFormatted);
        });

        // Function to fetch chart data and update charts
        function fetchChartData(startDate, endDate) {
            let route = "{{route('siop-dashboard.data')}}"
            fetch(route + `?period=custom&start=${startDate}&end=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    clearCharts(); // Clear existing charts before updating
                    updateCharts(data); // Update charts with new data
                });
        }

        // Function to update charts with the fetched data
        function updateCharts(data) {
            // Event Type Breakdown (Pie Chart)
            eventTypeChart = new Chart(document.getElementById('eventTypeChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: data.eventTypes.labels,
                    datasets: [{
                        data: data.eventTypes.data,
                        backgroundColor: ['#ff0000', '#ff8c00', '#32cd32'],
                    }]
                }
            });

            // Event Severity Breakdown (Bar Chart)
            eventSeverityChart = new Chart(document.getElementById('eventSeverityChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Low', 'Medium', 'High'],
                    datasets: [{
                        data: data.severities,
                        backgroundColor: ['#32cd32', '#ff8c00', '#ff0000'],
                        borderColor: ['#32cd32', '#ff8c00', '#ff0000'],
                        borderWidth: 1
                    }]
                }
            });

            // Events Over Time (Line Chart)
            eventsOverTimeChart = new Chart(document.getElementById('eventsOverTimeChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.eventsOverTime.labels,
                    datasets: [{
                        label: 'Events Detected',
                        data: data.eventsOverTime.data,
                        fill: false,
                        borderColor: '#4e73df',
                        tension: 0.1
                    }]
                }
            });
        }
    </script>
@endsection
