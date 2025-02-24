<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false, settingsOpen: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('worker.dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" wire:navigate>
                            {{ __('Users') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.work-orders.index')" :active="request()->routeIs('admin.work-orders.*')" wire:navigate>
                            {{ __('Work Orders') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.service-templates.index')" :active="request()->routeIs('admin.service-templates.*')" wire:navigate>
                            {{ __('Service Templates') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.parts.index')" :active="request()->routeIs('admin.parts.*')" wire:navigate>
                            {{ __('Parts') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.index')" wire:navigate>
                            {{ __('Reports') }}
                        </x-nav-link>

                        <!-- Settings Dropdown -->
                        <div class="relative mt-5">
                            <x-nav-link 
                                @click="settingsOpen = !settingsOpen" 
                                @click.away="settingsOpen = false"
                                class="cursor-pointer"
                                :active="request()->routeIs('admin.settings.*')">
                                {{ __('Settings') }}
                                <svg class="ml-1 -mr-0.5 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </x-nav-link>
                            <div x-show="settingsOpen"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                 style="display: none;">
                                <div class="py-1">
                                    <x-dropdown-link :href="route('admin.settings.categories.index')" wire:navigate>
                                        {{ __('Template Categories') }}
                                    </x-dropdown-link>
                                    <!-- Add more settings links here -->
                                </div>
                            </div>
                        </div>
                    @else
                    {{-- Worker Navigation Links --}}
                        <x-nav-link :href="route('worker.dashboard')" :active="request()->routeIs('worker.dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </x-nav-link>

                        <x-nav-link :href="route('worker.work-orders.index')" :active="request()->routeIs('worker.work-orders.*')" wire:navigate>
                            {{ __('My Work Orders') }}
                        </x-nav-link>

                        <x-nav-link :href="route('worker.work-orders.time')" :active="request()->routeIs('worker.work-orders.time')" wire:navigate>
                            {{ __('Time Tracking') }}
                        </x-nav-link>

                        <x-nav-link :href="route('worker.work-orders.completed')" :active="request()->routeIs('worker.work-orders.completed')" wire:navigate>
                            {{ __('Completed Orders') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" wire:navigate>
                    {{ __('Users') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.work-orders.index')" :active="request()->routeIs('admin.work-orders.*')" wire:navigate>
                    {{ __('Work Orders') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.service-templates.index')" :active="request()->routeIs('admin.service-templates.*')" wire:navigate>
                    {{ __('Service Templates') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.index')" wire:navigate>
                    {{ __('Reports') }}
                </x-responsive-nav-link>

                <!-- Settings Section in Mobile Menu -->
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ __('Settings') }}</div>
                    </div>

                    <x-responsive-nav-link :href="route('admin.settings.categories.index')" :active="request()->routeIs('admin.settings.categories.*')" wire:navigate>
                        {{ __('Template Categories') }}
                    </x-responsive-nav-link>
                    <!-- Add more settings links here -->
                </div>
            @else
            <x-responsive-nav-link :href="route('worker.dashboard')" :active="request()->routeIs('worker.dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('worker.work-orders.index')" :active="request()->routeIs('worker.work-orders.*')" wire:navigate>
                {{ __('My Work Orders') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('worker.work-orders.time')" :active="request()->routeIs('worker.work-orders.time')" wire:navigate>
                {{ __('Time Tracking') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('worker.work-orders.completed')" :active="request()->routeIs('worker.work-orders.completed')" wire:navigate>
                {{ __('Completed Orders') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>