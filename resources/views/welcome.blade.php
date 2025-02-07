<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Work Order Management') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">WOM</span>
                </div>
                <div class="flex items-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Log in</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="relative min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Hero Section -->
        <div class="pt-24 pb-20 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-white">
                    <h1 class="text-4xl font-extrabold sm:text-5xl md:text-6xl">
                        <span class="block">Work Order Management</span>
                        <span class="block text-indigo-200">Internal Service System</span>
                    </h1>
                    <p class="mt-3 max-w-md mx-auto text-lg text-indigo-100 sm:text-xl md:mt-5 md:max-w-3xl">
                        Streamline your service operations with our comprehensive work order management system.
                    </p>
                    <div class="mt-10">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 md:text-lg">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-indigo-50 md:text-lg">
                                Access System
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Features Section -->
        <div class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        System Features
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                        Complete toolkit for service management excellence
                    </p>
                </div>

                <div class="mt-16">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Work Order Management -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <div class="text-indigo-600 dark:text-indigo-400 text-2xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Work Order Management</h3>
                            <ul class="mt-4 space-y-2">
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Create and assign work orders
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Track real-time progress
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Manage priorities
                                </li>
                            </ul>
                        </div>

                        <!-- Service Checklists -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <div class="text-indigo-600 dark:text-indigo-400 text-2xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Service Checklists</h3>
                            <ul class="mt-4 space-y-2">
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Customizable templates
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Required photo documentation
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Process standardization
                                </li>
                            </ul>
                        </div>

                        <!-- Parts Tracking -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <div class="text-indigo-600 dark:text-indigo-400 text-2xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Parts Management</h3>
                            <ul class="mt-4 space-y-2">
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Track parts usage
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Cost monitoring
                                </li>
                                <li class="flex items-center text-gray-500 dark:text-gray-400">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Usage history
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works Section -->
        <div class="py-16 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        Work Order Process
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                        Simple steps to manage service operations
                    </p>
                </div>

                <div class="mt-16">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                        <div class="text-center">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-600 text-white mx-auto">
                                1
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Create</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                Admin creates and assigns work orders
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-600 text-white mx-auto">
                                2
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Execute</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                Workers perform tasks and document progress
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-600 text-white mx-auto">
                                3
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Track</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                Monitor progress and parts usage
                            </p>
                        </div>

                        <div class="text-center">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-indigo-600 text-white mx-auto">
                                4
                            </div>
                            <h3 class="mt-4 text-xl font-medium text-gray-900 dark:text-white">Complete</h3>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">
                                Review and close completed work orders
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview Section -->
        <div class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        System Overview
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                        Key components of our work order management system
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Admin Features -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Administrator Tools</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Create and manage service templates with custom checklists</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Assign work orders and monitor progress</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Generate reports and analyze performance metrics</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Worker Features -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Worker Tools</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Access assigned work orders and track time</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Complete checklists and upload required photos</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">Record parts usage and add work notes</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <p class="text-center text-gray-500 dark:text-gray-400">
                    © {{ date('Y') }} Work Order Management System. All rights reserved.
                </p>
            </div>
        </footer>
    </div>
</body>
</html>