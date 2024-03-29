<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Package</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['figtree', 'sans-serif'],
                }
            }
        }
    </script>
</head>
<body class="antialiased">
<div
    class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-center bg-gray-100 dark:bg-gray-900 selection:bg-red-500 selection:text-white">

    <div class="max-w-7xl w-full mx-auto px-6 text-center">
        <h1 class="text-gray-900 dark:text-gray-100 text-xl">Hi from package.blade.php</h1>
        <p class="mt-16 text-gray-900 dark:text-gray-100">Now you must configure the skeleton to make a great package :3 happy coding</p>
    </div>

</div>
</body>
</html>
