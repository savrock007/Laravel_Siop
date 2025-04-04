<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
    <script>
        document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark');
    </script>

    <style>

        @media (min-width: 1024px) {
            #sidebar {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 250px;
                background-color: #2d3748;
                color: white;
                padding: 20px;

            }


            body {
                margin-left: 250px;
            }
        }

        @media (max-width: 1024px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                bottom: 0;
                width: 250px;
                background-color: #2d3748;
                color: white;
                padding: 20px;
                transition: left 0.3s ease;
                z-index: 1;
            }

            body.sidebar-open #sidebar {
                left: 0;
            }

            header h1 {
                z-index: 0;
                position: absolute;
                left: 25%;
            }

            body.sidebar-open {
                overflow-x: hidden;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

<div id="sidebar">
    <nav>
        <ul>
            <li><a href="{{route('siop-dashboard.index')}}" class="block px-4 py-2">Dashboard</a></li>
            <li><a href="{{route('siop-events.list')}}" class="block px-4 py-2">Events</a></li>
{{--            <li><a href="{{route('siop-settings.index')}}" class="block px-4 py-2">Settings</a></li>--}}
        </ul>
    </nav>
</div>

<div class="min-h-screen flex flex-col">
    <header class="bg-blue-500 dark:bg-gray-800 text-white p-4">
        <div class="container mx-auto flex items-center justify-between lg:justify-center">
            <button id="sidebar-toggle"
                    class="p-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white lg:hidden">
                &#9776;
            </button>
            <h1 class="text-2xl font-bold lg:ml-4">Security Dashboard</h1>
        </div>
    </header>

    <main class="flex">
        <div class="flex w-full p-4">
            @yield('content')
        </div>
    </main>
</div>

<script>
    document.getElementById('sidebar-toggle').addEventListener('click', function () {
        document.body.classList.toggle('sidebar-open');
    });

    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebar-toggle');

        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            document.body.classList.remove('sidebar-open');
        }
    });

</script>

</body>
</html>
