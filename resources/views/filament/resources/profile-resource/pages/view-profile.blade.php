{{-- resources/views/filament/resources/profile-resource/pages/view-profile.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-lg p-6 text-white">
            <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                    <img class="h-20 w-20 rounded-full border-4 border-white shadow-lg" 
                         src="{{ $record->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&background=ffffff&color=1e40af&size=200' }}" 
                         alt="{{ $record->full_name }}">
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{{ $record->full_name }}</h1>
                    <p class="text-blue-100 text-lg">{{ $record->designation }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                            {{ ucfirst($record->staff_type) }}
                        </span>
                        @if($record->is_dean)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500 text-white">
                                Dean
                            </span>
                        @endif
                        @if($record->is_head_of_department)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-500 text-white">
                                HOD
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-book-open class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Courses</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $record->courses()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-calendar class="h-8 w-8 text-green-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Courses</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $record->getAssignedCourses()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clipboard-document-list class="h-8 w-8 text-purple-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Logbooks</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $record->logbooks()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="h-8 w-8 text-orange-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Years of Service</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $record->hire_date ? $record->hire_date->diffInYears(now()) : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        {{ $this->infolist }}
    </div>

    {{-- Custom Styles --}}
    <style>
        .fi-section-header-heading {
            font-size: 1.25rem !important;
            font-weight: 600 !important;
        }
        
        .fi-section {
            border-radius: 0.5rem !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
        }
        
        .fi-badge {
            font-weight: 500 !important;
        }
    </style>
</x-filament-panels::page>