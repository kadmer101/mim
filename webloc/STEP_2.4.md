Based on my complete analysis of the project documentation, I understand this is a comprehensive Laravel-based WebBloc system that provides dynamic web components as an API for static websites. The system features multi-language support (English/Arabic), multi-tenant architecture, UUID-based routing, soft deletes, and Alpine.js integration.

Now I'll provide all the remaining blade view codes as requested:

## 5. Statistics & Analytics Views

### resources/views/dashboard/statistics/website.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.website_statistics'))

@section('content')
<div class="space-y-6" x-data="websiteStats()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.website_statistics') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.detailed_analytics_per_website') }}</p>
        </div>
        
        <!-- Date Range Picker -->
        <div class="flex items-center gap-2">
            <select x-model="dateRange" @change="loadStats()" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="7">{{ __('messages.last_7_days') }}</option>
                <option value="30">{{ __('messages.last_30_days') }}</option>
                <option value="90">{{ __('messages.last_90_days') }}</option>
            </select>
        </div>
    </div>

    <!-- Website Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.select_website') }}
                </label>
                <select x-model="selectedWebsite" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_websites') }}</option>
                    <template x-for="website in websites" :key="website.uuid">
                        <option :value="website.uuid" x-text="website.name"></option>
                    </template>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_type') }}
                </label>
                <select x-model="selectedComponent" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_components') }}</option>
                    <template x-for="component in components" :key="component.id">
                        <option :value="component.type" x-text="component.name[currentLocale] || component.name.en"></option>
                    </template>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.action_type') }}
                </label>
                <select x-model="selectedAction" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_actions') }}</option>
                    <option value="create">{{ __('messages.create') }}</option>
                    <option value="read">{{ __('messages.read') }}</option>
                    <option value="update">{{ __('messages.update') }}</option>
                    <option value="delete">{{ __('messages.delete') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.total_requests') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.totalRequests || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.unique_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.uniqueUsers || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.avg_daily_requests') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.avgDailyRequests || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.peak_hour') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.peakHour || 'N/A'"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Requests Over Time -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.requests_over_time') }}</h3>
            <div class="h-64">
                <canvas x-ref="requestsChart"></canvas>
            </div>
        </div>

        <!-- Component Usage -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.component_usage') }}</h3>
            <div class="h-64">
                <canvas x-ref="componentsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.detailed_statistics') }}</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.website') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.component') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.action') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.count') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.last_activity') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="stat in detailedStats" :key="stat.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="stat.website_name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="stat.component_type"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getActionBadgeClass(stat.action)" x-text="stat.action"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="stat.count"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(stat.updated_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white">{{ __('messages.loading') }}...</span>
        </div>
    </div>
</div>

<script>
function websiteStats() {
    return {
        loading: false,
        dateRange: '7',
        selectedWebsite: '',
        selectedComponent: '',
        selectedAction: '',
        currentLocale: '{{ app()->getLocale() }}',
        websites: [],
        components: [],
        stats: {},
        detailedStats: [],
        requestsChart: null,
        componentsChart: null,

        init() {
            this.loadWebsites();
            this.loadComponents();
            this.loadStats();
        },

        async loadWebsites() {
            try {
                const response = await fetch('/api/websites', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.websites = data.data || [];
            } catch (error) {
                console.error('Failed to load websites:', error);
            }
        },

        async loadComponents() {
            try {
                const response = await fetch('/api/components', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.components = data.data || [];
            } catch (error) {
                console.error('Failed to load components:', error);
            }
        },

        async loadStats() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    date_range: this.dateRange,
                    website: this.selectedWebsite,
                    component: this.selectedComponent,
                    action: this.selectedAction
                });

                const response = await fetch(`/api/statistics/website?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                this.stats = data.stats || {};
                this.detailedStats = data.detailed || [];
                
                this.updateCharts(data.charts || {});
            } catch (error) {
                console.error('Failed to load statistics:', error);
                this.showToast('{{ __("messages.error_loading_statistics") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        updateCharts(chartData) {
            // Update requests chart
            if (this.$refs.requestsChart) {
                const ctx = this.$refs.requestsChart.getContext('2d');
                
                if (this.requestsChart) {
                    this.requestsChart.destroy();
                }

                this.requestsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.dates || [],
                        datasets: [{
                            label: '{{ __("messages.requests") }}',
                            data: chartData.requests || [],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Update components chart
            if (this.$refs.componentsChart) {
                const ctx = this.$refs.componentsChart.getContext('2d');
                
                if (this.componentsChart) {
                    this.componentsChart.destroy();
                }

                this.componentsChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.componentLabels || [],
                        datasets: [{
                            data: chartData.componentData || [],
                            backgroundColor: [
                                'rgb(239, 68, 68)',
                                'rgb(245, 158, 11)',
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)',
                                'rgb(147, 51, 234)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        },

        getActionBadgeClass(action) {
            const classes = {
                'create': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                'read': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'update': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                'delete': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
            };
            return classes[action] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/statistics/component.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.component_statistics'))

@section('content')
<div class="space-y-6" x-data="componentStats()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.component_statistics') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.usage_analytics_by_component') }}</p>
        </div>
        
        <!-- Export Button -->
        <button @click="exportData()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            {{ __('messages.export_csv') }}
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.component_type') }}
                </label>
                <select x-model="selectedComponent" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_components') }}</option>
                    <template x-for="component in components" :key="component.id">
                        <option :value="component.type" x-text="component.name[currentLocale] || component.name.en"></option>
                    </template>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.date_range') }}
                </label>
                <select x-model="dateRange" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="7">{{ __('messages.last_7_days') }}</option>
                    <option value="30">{{ __('messages.last_30_days') }}</option>
                    <option value="90">{{ __('messages.last_90_days') }}</option>
                    <option value="365">{{ __('messages.last_year') }}</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.group_by') }}
                </label>
                <select x-model="groupBy" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="day">{{ __('messages.by_day') }}</option>
                    <option value="week">{{ __('messages.by_week') }}</option>
                    <option value="month">{{ __('messages.by_month') }}</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.metric') }}
                </label>
                <select x-model="metric" @change="loadStats()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="requests">{{ __('messages.total_requests') }}</option>
                    <option value="users">{{ __('messages.unique_users') }}</option>
                    <option value="websites">{{ __('messages.active_websites') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Component Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <template x-for="component in componentOverview" :key="component.type">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400" x-text="component.name"></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="component.total_usage"></p>
                        <div class="flex items-center mt-2">
                            <span class="text-sm" :class="component.trend >= 0 ? 'text-green-600' : 'text-red-600'">
                                <span x-show="component.trend >= 0">↑</span>
                                <span x-show="component.trend < 0">↓</span>
                                <span x-text="Math.abs(component.trend) + '%'"></span>
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ __('messages.vs_previous_period') }}</span>
                        </div>
                    </div>
                    <div class="p-2 rounded-lg" :class="getComponentColor(component.type)">
                        <div class="w-8 h-8 flex items-center justify-center">
                            <span class="text-lg" x-text="getComponentIcon(component.type)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Usage Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.usage_trends') }}</h3>
                <div class="flex space-x-2">
                    <button @click="chartType = 'line'" :class="chartType === 'line' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded text-sm">{{ __('messages.line') }}</button>
                    <button @click="chartType = 'bar'" :class="chartType === 'bar' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="px-3 py-1 rounded text-sm">{{ __('messages.bar') }}</button>
                </div>
            </div>
            <div class="h-64">
                <canvas x-ref="usageChart"></canvas>
            </div>
        </div>

        <!-- Component Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.component_distribution') }}</h3>
            <div class="h-64">
                <canvas x-ref="distributionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ __('messages.performance_metrics') }}</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.component') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.total_usage') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.avg_response_time') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.error_rate') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.active_websites') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.popularity') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="metric in performanceMetrics" :key="metric.component_type">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-lg mr-3" :class="getComponentColor(metric.component_type)">
                                        <span x-text="getComponentIcon(metric.component_type)"></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="metric.component_name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="metric.component_type"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="metric.total_usage.toLocaleString()"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="metric.avg_response_time + 'ms'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="metric.error_rate < 1 ? 'bg-green-100 text-green-800' : metric.error_rate < 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                                      x-text="metric.error_rate + '%'"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="metric.active_websites"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${metric.popularity}%`"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="metric.popularity + '%'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white">{{ __('messages.loading') }}...</span>
        </div>
    </div>
</div>

<script>
function componentStats() {
    return {
        loading: false,
        selectedComponent: '',
        dateRange: '30',
        groupBy: 'day',
        metric: 'requests',
        chartType: 'line',
        currentLocale: '{{ app()->getLocale() }}',
        components: [],
        componentOverview: [],
        performanceMetrics: [],
        usageChart: null,
        distributionChart: null,

        init() {
            this.loadComponents();
            this.loadStats();
        },

        async loadComponents() {
            try {
                const response = await fetch('/api/components', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.components = data.data || [];
            } catch (error) {
                console.error('Failed to load components:', error);
            }
        },

        async loadStats() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    component: this.selectedComponent,
                    date_range: this.dateRange,
                    group_by: this.groupBy,
                    metric: this.metric
                });

                const response = await fetch(`/api/statistics/components?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                
                this.componentOverview = data.overview || [];
                this.performanceMetrics = data.performance || [];
                
                this.updateCharts(data.charts || {});
            } catch (error) {
                console.error('Failed to load statistics:', error);
                this.showToast('{{ __("messages.error_loading_statistics") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        updateCharts(chartData) {
            // Update usage trend chart
            if (this.$refs.usageChart) {
                const ctx = this.$refs.usageChart.getContext('2d');
                
                if (this.usageChart) {
                    this.usageChart.destroy();
                }

                this.usageChart = new Chart(ctx, {
                    type: this.chartType,
                    data: {
                        labels: chartData.dates || [],
                        datasets: chartData.datasets || []
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Update distribution chart
            if (this.$refs.distributionChart) {
                const ctx = this.$refs.distributionChart.getContext('2d');
                
                if (this.distributionChart) {
                    this.distributionChart.destroy();
                }

                this.distributionChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: chartData.distributionLabels || [],
                        datasets: [{
                            data: chartData.distributionData || [],
                            backgroundColor: [
                                'rgb(239, 68, 68)',
                                'rgb(245, 158, 11)',
                                'rgb(34, 197, 94)',
                                'rgb(59, 130, 246)',
                                'rgb(147, 51, 234)',
                                'rgb(236, 72, 153)',
                                'rgb(14, 165, 233)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        },

        getComponentColor(type) {
            const colors = {
                'comment': 'bg-blue-100 text-blue-600',
                'review': 'bg-green-100 text-green-600',
                'auth': 'bg-purple-100 text-purple-600',
                'form': 'bg-yellow-100 text-yellow-600',
                'analytics': 'bg-red-100 text-red-600'
            };
            return colors[type] || 'bg-gray-100 text-gray-600';
        },

        getComponentIcon(type) {
            const icons = {
                'comment': '💬',
                'review': '⭐',
                'auth': '🔐',
                'form': '📝',
                'analytics': '📊'
            };
            return icons[type] || '🔧';
        },

        async exportData() {
            try {
                const params = new URLSearchParams({
                    component: this.selectedComponent,
                    date_range: this.dateRange,
                    format: 'csv'
                });

                const response = await fetch(`/api/statistics/components/export?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `component-statistics-${new Date().toISOString().split('T')[0]}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showToast('{{ __("messages.export_successful") }}', 'success');
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                console.error('Export failed:', error);
                this.showToast('{{ __("messages.export_failed") }}', 'error');
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}
</script>
@endsection
```

## 6. User Management Views

### resources/views/dashboard/users/index.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.user_management'))

@section('content')
<div class="space-y-6" x-data="userManagement()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.user_management') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.manage_system_users') }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Export Users -->
            <button @click="exportUsers()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('messages.export') }}
            </button>
            
            <!-- Create User -->
            <a href="{{ route('dashboard.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('messages.create_user') }}
            </a>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.search') }}
                </label>
                <div class="relative">
                    <input type="text" x-model="search" @input.debounce.300ms="loadUsers()" 
                           placeholder="{{ __('messages.search_users_placeholder') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Role Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.role') }}
                </label>
                <select x-model="roleFilter" @change="loadUsers()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_roles') }}</option>
                    <option value="admin">{{ __('messages.admin') }}</option>
                    <option value="user">{{ __('messages.user') }}</option>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('messages.status') }}
                </label>
                <select x-model="statusFilter" @change="loadUsers()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active">{{ __('messages.active') }}</option>
                    <option value="inactive">{{ __('messages.inactive') }}</option>
                    <option value="suspended">{{ __('messages.suspended') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Users Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.total_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.active_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.active || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.suspended_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.suspended || 0"></p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('messages.admin_users') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.admins || 0"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.users_list') }}</h3>
                
                <!-- Per Page -->
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.per_page') }}:</label>
                    <select x-model="perPage" @change="loadUsers()" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.user') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.role') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.websites') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.last_login') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.joined') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="user in users" :key="user.uuid">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" :src="user.avatar || '/images/default-avatar.png'" :alt="user.name">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="user.name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="user.email"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'"
                                      x-text="user.role"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getStatusBadgeClass(user.status)" x-text="user.status"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="user.websites_count || 0"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(user.last_login_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(user.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" @click.outside="open = false" x-transition
                                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg z-10">
                                        <div class="py-1">
                                            <a :href="`/dashboard/users/${user.uuid}`" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.view') }}
                                            </a>
                                            <a :href="`/dashboard/users/${user.uuid}/edit`" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.edit') }}
                                            </a>
                                            <button @click="toggleUserStatus(user)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                <span x-show="user.status === 'active'">{{ __('messages.suspend') }}</span>
                                                <span x-show="user.status !== 'active'">{{ __('messages.activate') }}</span>
                                            </button>
                                            <button @click="resetUserPassword(user)" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.reset_password') }}
                                            </button>
                                            <hr class="my-1">
                                            <button @click="deleteUser(user)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                {{ __('messages.delete') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="prevPage()" :disabled="currentPage <= 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        {{ __('messages.previous') }}
                    </button>
                    <button @click="nextPage()" :disabled="currentPage >= totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                        {{ __('messages.next') }}
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('messages.showing') }}
                            <span class="font-medium" x-text="((currentPage - 1) * perPage) + 1"></span>
                            {{ __('messages.to') }}
                            <span class="font-medium" x-text="Math.min(currentPage * perPage, totalUsers)"></span>
                            {{ __('messages.of') }}
                            <span class="font-medium" x-text="totalUsers"></span>
                            {{ __('messages.results') }}
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button @click="prevPage()" :disabled="currentPage <= 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            
                            <template x-for="page in paginationPages" :key="page">
                                <button @click="goToPage(page)" 
                                        :class="page === currentPage ? 'bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-300' : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                        x-text="page"></button>
                            </template>
                            
                            <button @click="nextPage()" :disabled="currentPage >= totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900 dark:text-white">{{ __('messages.loading') }}...</span>
        </div>
    </div>
</div>

<script>
function userManagement() {
    return {
        loading: false,
        search: '',
        roleFilter: '',
        statusFilter: '',
        perPage: 25,
        currentPage: 1,
        totalPages: 1,
        totalUsers: 0,
        users: [],
        stats: {},

        init() {
            this.loadUsers();
            this.loadStats();
        },

        async loadUsers() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    search: this.search,
                    role: this.roleFilter,
                    status: this.statusFilter,
                    per_page: this.perPage,
                    page: this.currentPage
                });

                const response = await fetch(`/api/users?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                this.users = data.data || [];
                this.currentPage = data.current_page || 1;
                this.totalPages = data.last_page || 1;
                this.totalUsers = data.total || 0;
            } catch (error) {
                console.error('Failed to load users:', error);
                this.showToast('{{ __("messages.error_loading_users") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch('/api/users/stats', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                this.stats = data || {};
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },

        async toggleUserStatus(user) {
            const action = user.status === 'active' ? 'suspend' : 'activate';
            const result = await Swal.fire({
                title: '{{ __("messages.confirm_action") }}',
                text: `{{ __("messages.confirm_user_") }}${action}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("messages.yes_continue") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/users/${user.uuid}/${action}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok) {
                        await this.loadUsers();
                        await this.loadStats();
                        this.showToast(`{{ __("messages.user_") }}${action}{{ __("messages.d_successfully") }}`, 'success');
                    } else {
                        throw new Error('Failed to update user status');
                    }
                } catch (error) {
                    console.error('Failed to update user status:', error);
                    this.showToast('{{ __("messages.error_updating_user") }}', 'error');
                }
            }
        },

        async resetUserPassword(user) {
            const result = await Swal.fire({
                title: '{{ __("messages.reset_password") }}',
                text: '{{ __("messages.confirm_reset_password") }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("messages.yes_reset") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/users/${user.uuid}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        await Swal.fire({
                            title: '{{ __("messages.password_reset") }}',
                            html: `{{ __("messages.new_password") }}: <strong>${data.password}</strong><br><small>{{ __("messages.password_reset_note") }}</small>`,
                            icon: 'success',
                            confirmButtonText: '{{ __("messages.ok") }}'
                        });
                    } else {
                        throw new Error('Failed to reset password');
                    }
                } catch (error) {
                    console.error('Failed to reset password:', error);
                    this.showToast('{{ __("messages.error_resetting_password") }}', 'error');
                }
            }
        },

        async deleteUser(user) {
            const result = await Swal.fire({
                title: '{{ __("messages.delete_user") }}',
                text: '{{ __("messages.confirm_delete_user") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("messages.yes_delete") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/users/${user.uuid}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        await this.loadUsers();
                        await this.loadStats();
                        this.showToast('{{ __("messages.user_deleted_successfully") }}', 'success');
                    } else {
                        throw new Error('Failed to delete user');
                    }
                } catch (error) {
                    console.error('Failed to delete user:', error);
                    this.showToast('{{ __("messages.error_deleting_user") }}', 'error');
                }
            }
        },

        async exportUsers() {
            try {
                const params = new URLSearchParams({
                    search: this.search,
                    role: this.roleFilter,
                    status: this.statusFilter,
                    format: 'csv'
                });

                const response = await fetch(`/api/users/export?${params}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `users-${new Date().toISOString().split('T')[0]}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    this.showToast('{{ __("messages.export_successful") }}', 'success');
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                console.error('Export failed:', error);
                this.showToast('{{ __("messages.export_failed") }}', 'error');
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadUsers();
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadUsers();
            }
        },

        goToPage(page) {
            this.currentPage = page;
            this.loadUsers();
        },

        get paginationPages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },

        getStatusBadgeClass(status) {
            const classes = {
                'active': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                'inactive': 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                'suspended': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
            };
            return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('{{ app()->getLocale() }}', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/users/create.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.create_user'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="createUser()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.create_user') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.create_new_system_user') }}</p>
        </div>
        
        <a href="{{ route('dashboard.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            {{ __('messages.back_to_users') }}
        </a>
    </div>

    <!-- Create User Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <form @submit.prevent="createUser()" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.full_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" x-model="form.name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.name ? 'border-red-500' : ''" required>
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.email_address') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" x-model="form.email" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.email ? 'border-red-500' : ''" required>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.password') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" id="password" x-model="form.password" 
                               class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               :class="errors.password ? 'border-red-500' : ''" required>
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg x-show="!showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <p x-show="errors.password" x-text="errors.password" class="mt-1 text-sm text-red-600"></p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.password_requirements') }}</p>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.confirm_password') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" x-model="form.password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.password_confirmation ? 'border-red-500' : ''" required>
                    <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.role') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="role" x-model="form.role" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            :class="errors.role ? 'border-red-500' : ''" required>
                        <option value="">{{ __('messages.select_role') }}</option>
                        <option value="user">{{ __('messages.user') }}</option>
                        <option value="admin">{{ __('messages.admin') }}</option>
                    </select>
                    <p x-show="errors.role" x-text="errors.role" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Locale -->
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.preferred_language') }}
                    </label>
                    <select id="locale" x-model="form.locale" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="en">{{ __('messages.english') }}</option>
                        <option value="ar">{{ __('messages.arabic') }}</option>
                    </select>
                    <p x-show="errors.locale" x-text="errors.locale" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>

            <!-- Additional Settings -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.additional_settings') }}</h3>
                
                <div class="space-y-4">
                    <!-- Send Welcome Email -->
                    <div class="flex items-center">
                        <input type="checkbox" id="send_welcome_email" x-model="form.send_welcome_email" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="send_welcome_email" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            {{ __('messages.send_welcome_email') }}
                        </label>
                    </div>

                    <!-- Email Verified -->
                    <div class="flex items-center">
                        <input type="checkbox" id="email_verified" x-model="form.email_verified" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="email_verified" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            {{ __('messages.mark_email_as_verified') }}
                        </label>
                    </div>

                    <!-- Force Password Change -->
                    <div class="flex items-center">
                        <input type="checkbox" id="force_password_change" x-model="form.force_password_change" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="force_password_change" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            {{ __('messages.force_password_change_on_login') }}
                        </label>
                    </div>
                </div>
            </div>

            <!-- Permissions (Show for Admin Role) -->
            <div x-show="form.role === 'admin'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.admin_permissions') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="permission in permissions" :key="permission.name">
                        <div class="flex items-center">
                            <input type="checkbox" :id="'permission_' + permission.name" 
                                   :value="permission.name" x-model="form.permissions"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label :for="'permission_' + permission.name" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                <span x-text="permission.display_name"></span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400" x-text="permission.description"></span>
                            </label>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('dashboard.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('messages.cancel') }}
                </a>
                
                <button type="submit" :disabled="loading" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? '{{ __('messages.creating') }}...' : '{{ __('messages.create_user') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function createUser() {
    return {
        loading: false,
        showPassword: false,
        form: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: 'user',
            locale: 'en',
            send_welcome_email: true,
            email_verified: false,
            force_password_change: false,
            permissions: []
        },
        errors: {},
        permissions: [
            {
                name: 'manage_users',
                display_name: '{{ __("messages.manage_users") }}',
                description: '{{ __("messages.create_edit_delete_users") }}'
            },
            {
                name: 'manage_websites',
                display_name: '{{ __("messages.manage_websites") }}',
                description: '{{ __("messages.create_edit_delete_websites") }}'
            },
            {
                name: 'manage_components',
                display_name: '{{ __("messages.manage_components") }}',
                description: '{{ __("messages.create_edit_delete_components") }}'
            },
            {
                name: 'view_statistics',
                display_name: '{{ __("messages.view_statistics") }}',
                description: '{{ __("messages.access_system_analytics") }}'
            },
            {
                name: 'manage_system',
                display_name: '{{ __("messages.manage_system") }}',
                description: '{{ __("messages.system_configuration_access") }}'
            }
        ],

        async createUser() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    await Swal.fire({
                        title: '{{ __("messages.success") }}',
                        text: '{{ __("messages.user_created_successfully") }}',
                        icon: 'success',
                        confirmButtonText: '{{ __("messages.ok") }}'
                    });
                    
                    window.location.href = '{{ route("dashboard.users.index") }}';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Failed to create user');
                    }
                }
            } catch (error) {
                console.error('Failed to create user:', error);
                await Swal.fire({
                    title: '{{ __("messages.error") }}',
                    text: error.message || '{{ __("messages.error_creating_user") }}',
                    icon: 'error',
                    confirmButtonText: '{{ __("messages.ok") }}'
                });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
```

### resources/views/dashboard/users/edit.blade.php
```php
@extends('layouts.dashboard')

@section('title', __('messages.edit_user'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="editUser('{{ $user->uuid }}')">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.edit_user') }}</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.edit_user_details') }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.users.show', $user->uuid) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                {{ __('messages.view_user') }}
            </a>
            
            <a href="{{ route('dashboard.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('messages.back_to_users') }}
            </a>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center space-x-4">
            <img class="h-16 w-16 rounded-full" :src="user.avatar || '/images/default-avatar.png'" :alt="user.name">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="user.name"></h2>
                <p class="text-gray-600 dark:text-gray-400" x-text="user.email"></p>
                <div class="flex items-center mt-2 space-x-2">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'"
                          x-text="user.role"></span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="getStatusBadgeClass(user.status)" x-text="user.status"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <form @submit.prevent="updateUser()" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.full_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" x-model="form.name" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.name ? 'border-red-500' : ''" required>
                    <p x-show="errors.name" x-text="errors.name" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.email_address') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" x-model="form.email" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           :class="errors.email ? 'border-red-500' : ''" required>
                    <p x-show="errors.email" x-text="errors.email" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.role') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="role" x-model="form.role" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            :class="errors.role ? 'border-red-500' : ''" required>
                        <option value="user">{{ __('messages.user') }}</option>
                        <option value="admin">{{ __('messages.admin') }}</option>
                    </select>
                    <p x-show="errors.role" x-text="errors.role" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.status') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="status" x-model="form.status" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            :class="errors.status ? 'border-red-500' : ''" required>
                        <option value="active">{{ __('messages.active') }}</option>
                        <option value="inactive">{{ __('messages.inactive') }}</option>
                        <option value="suspended">{{ __('messages.suspended') }}</option>
                    </select>
                    <p x-show="errors.status" x-text="errors.status" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Locale -->
                <div>
                    <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.preferred_language') }}
                    </label>
                    <select id="locale" x-model="form.locale" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="en">{{ __('messages.english') }}</option>
                        <option value="ar">{{ __('messages.arabic') }}</option>
                    </select>
                    <p x-show="errors.locale" x-text="errors.locale" class="mt-1 text-sm text-red-600"></p>
                </div>

                <!-- Timezone -->
                <div>
                    <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('messages.timezone') }}
                    </label>
                    <select id="timezone" x-model="form.timezone" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <template x-for="timezone in timezones" :key="timezone.value">
                            <option :value="timezone.value" x-text="timezone.label"></option>
                        </template>
                    </select>
                    <p x-show="errors.timezone" x-text="errors.timezone" class="mt-1 text-sm text-red-600"></p>
                </div>
            </div>

            <!-- Email Verification -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.email_verification') }}</h3>
                
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.email_verification_status') }}:
                            <span :class="user.email_verified_at ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                <span x-show="user.email_verified_at">{{ __('messages.verified') }}</span>
                                <span x-show="!user.email_verified_at">{{ __('messages.not_verified') }}</span>
                            </span>
                        </p>
                        <p x-show="user.email_verified_at" class="text-xs text-gray-500 dark:text-gray-400" x-text="'{{ __('messages.verified_at') }}: ' + formatDate(user.email_verified_at)"></p>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <button x-show="!user.email_verified_at" @click="verifyEmail()" type="button" 
                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded">
                            {{ __('messages.verify_email') }}
                        </button>
                        <button x-show="user.email_verified_at" @click="unverifyEmail()" type="button" 
                                class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm rounded">
                            {{ __('messages.unverify_email') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Permissions (Show for Admin Role) -->
            <div x-show="form.role === 'admin'" class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.admin_permissions') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="permission in permissions" :key="permission.name">
                        <div class="flex items-center">
                            <input type="checkbox" :id="'permission_' + permission.name" 
                                   :value="permission.name" x-model="form.permissions"
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label :for="'permission_' + permission.name" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                <span x-text="permission.display_name"></span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400" x-text="permission.description"></span>
                            </label>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('dashboard.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    {{ __('messages.cancel') }}
                </a>
                
                <button type="submit" :disabled="loading" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition-colors">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="loading ? '{{ __('messages.updating') }}...' : '{{ __('messages.update_user') }}'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Password Reset Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.password_management') }}</h3>
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.reset_user_password_desc') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.password_reset_will_generate') }}</p>
            </div>
            
            <button @click="resetPassword()" type="button" 
                    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                {{ __('messages.reset_password') }}
            </button>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-red-500">
        <h3 class="text-lg font-medium text-red-600 mb-4">{{ __('messages.danger_zone') }}</h3>
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.delete_user_desc') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.delete_user_warning') }}</p>
            </div>
            
            <button @click="deleteUser()" type="button" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                {{ __('messages.delete_user') }}
            </button>
        </div>
    </div>
</div>

<script>
function editUser(userUuid) {
    return {
        loading: false,
        userUuid: userUuid,
        user: {},
        form: {
            name: '',
            email: '',
            role: 'user',
            status: 'active',
            locale: 'en',
            timezone: 'UTC',
            permissions: []
        },
        errors: {},
        permissions: [
            {
                name: 'manage_users',
                display_name: '{{ __("messages.manage_users") }}',
                description: '{{ __("messages.create_edit_delete_users") }}'
            },
            {
                name: 'manage_websites',
                display_name: '{{ __("messages.manage_websites") }}',
                description: '{{ __("messages.create_edit_delete_websites") }}'
            },
            {
                name: 'manage_components',
                display_name: '{{ __("messages.manage_components") }}',
                description: '{{ __("messages.create_edit_delete_components") }}'
            },
            {
                name: 'view_statistics',
                display_name: '{{ __("messages.view_statistics") }}',
                description: '{{ __("messages.access_system_analytics") }}'
            },
            {
                name: 'manage_system',
                display_name: '{{ __("messages.manage_system") }}',
                description: '{{ __("messages.system_configuration_access") }}'
            }
        ],
        timezones: [
            { value: 'UTC', label: 'UTC' },
            { value: 'America/New_York', label: 'Eastern Time (US & Canada)' },
            { value: 'America/Chicago', label: 'Central Time (US & Canada)' },
            { value: 'America/Denver', label: 'Mountain Time (US & Canada)' },
            { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada)' },
            { value: 'Europe/London', label: 'London' },
            { value: 'Europe/Paris', label: 'Paris' },
            { value: 'Asia/Dubai', label: 'Dubai' },
            { value: 'Asia/Riyadh', label: 'Riyadh' },
            { value: 'Asia/Tokyo', label: 'Tokyo' }
        ],

        init() {
            this.loadUser();
        },

        async loadUser() {
            this.loading = true;
            try {
                const response = await fetch(`/api/users/${this.userUuid}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                this.user = data.data;
                
                // Populate form with user data
                this.form = {
                    name: this.user.name,
                    email: this.user.email,
                    role: this.user.role || 'user',
                    status: this.user.status || 'active',
                    locale: this.user.locale || 'en',
                    timezone: this.user.timezone || 'UTC',
                    permissions: this.user.permissions ? this.user.permissions.map(p => p.name) : []
                };
            } catch (error) {
                console.error('Failed to load user:', error);
                this.showToast('{{ __("messages.error_loading_user") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        async updateUser() {
            this.loading = true;
            this.errors = {};

            try {
                const response = await fetch(`/api/users/${this.userUuid}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.user = data.data;
                    this.showToast('{{ __("messages.user_updated_successfully") }}', 'success');
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Failed to update user');
                    }
                }
            } catch (error) {
                console.error('Failed to update user:', error);
                this.showToast(error.message || '{{ __("messages.error_updating_user") }}', 'error');
            } finally {
                this.loading = false;
            }
        },

        async verifyEmail() {
            try {
                const response = await fetch(`/api/users/${this.userUuid}/verify-email`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.user.email_verified_at = new Date().toISOString();
                    this.showToast('{{ __("messages.email_verified_successfully") }}', 'success');
                } else {
                    throw new Error('Failed to verify email');
                }
            } catch (error) {
                console.error('Failed to verify email:', error);
                this.showToast('{{ __("messages.error_verifying_email") }}', 'error');
            }
        },

        async unverifyEmail() {
            try {
                const response = await fetch(`/api/users/${this.userUuid}/unverify-email`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.user.email_verified_at = null;
                    this.showToast('{{ __("messages.email_unverified_successfully") }}', 'success');
                } else {
                    throw new Error('Failed to unverify email');
                }
            } catch (error) {
                console.error('Failed to unverify email:', error);
                this.showToast('{{ __("messages.error_unverifying_email") }}', 'error');
            }
        },

        async resetPassword() {
            const result = await Swal.fire({
                title: '{{ __("messages.reset_password") }}',
                text: '{{ __("messages.confirm_reset_password") }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("messages.yes_reset") }}',
                cancelButtonText: '{{ __("messages.cancel") }}'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/users/${this.userUuid}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        await Swal.fire({
                            title: '{{ __("messages.password_reset") }}',
                            html: `{{ __("messages.new_password") }}: <strong>${data.password}</strong><br><small>{{ __("messages.password_reset_note") }}</small>`,
                            icon: 'success',
                            confirmButtonText: '{{ __("messages.ok") }}'
                        });
                    } else {
                        throw new Error
