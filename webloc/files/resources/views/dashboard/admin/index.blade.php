@extends('dashboard.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div x-data="adminDashboard()" x-init="init()">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Admin Dashboard</h2>
            <p class="text-muted mb-0">System overview and management</p>
        </div>
        <div class="d-flex gap-2">
            <button @click="refreshData()" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise" :class="{ 'rotating': loading }"></i> Refresh
            </button>
            <button @click="clearCache()" class="btn btn-outline-warning">
                <i class="bi bi-trash"></i> Clear Cache
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card card-hover h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Websites</h6>
                        <h3 class="card-title mb-0" x-text="stats.websites || '0'">{{ $stats['websites'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-globe fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card-primary card-hover h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-subtitle mb-2 text-white-50">API Requests (24h)</h6>
                        <h3 class="card-title mb-0" x-text="stats.apiRequests || '0'">{{ $stats['api_requests'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card-success card-hover h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-subtitle mb-2 text-white-50">Active Users</h6>
                        <h3 class="card-title mb-0" x-text="stats.activeUsers || '0'">{{ $stats['active_users'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card-warning card-hover h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="card-subtitle mb-2 text-white-50">WebBlocs Installed</h6>
                        <h3 class="card-title mb-0" x-text="stats.webBlocs || '0'">{{ $stats['webblocs'] ?? 0 }}</h3>
                    </div>
                    <i class="bi bi-puzzle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card card-hover h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">API Usage Trends</h5>
                    <select class="form-select form-select-sm" style="width: auto;" x-model="chartPeriod" @change="updateChart()">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
                <div class="card-body">
                    <canvas id="apiUsageChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card card-hover h-100">
                <div class="card-header">
                    <h5 class="mb-0">Response Format Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="formatDistributionChart"></canvas>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">HTML Responses</small>
                            <small class="fw-bold">75%</small>
                        </div>
                        <div class="progress mb-2" style="height: 4px;">
                            <div class="progress-bar bg-primary" style="width: 75%"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">JSON Responses</small>
                            <small class="fw-bold">15%</small>
                        </div>
                        <div class="progress mb-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: 15%"></div>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Other Formats</small>
                            <small class="fw-bold">10%</small>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-warning" style="width: 10%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and System Health -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card card-hover h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activities</h5>
                    <button @click="refreshActivities()" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="activity-list" style="max-height: 400px; overflow-y: auto;">
                        <template x-for="activity in activities" :key="activity.id">
                            <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        <i :class="getActivityIcon(activity.type)" class="text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1" x-text="activity.title"></h6>
                                        <small class="text-muted" x-text="formatTime(activity.created_at)"></small>
                                    </div>
                                    <p class="text-muted mb-0 small" x-text="activity.description"></p>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Fallback activities for demo -->
                        @foreach($activities ?? [] as $activity)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="bi {{ $activity['icon'] ?? 'bi-activity' }} text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                    <small class="text-muted">{{ $activity['time'] }}</small>
                                </div>
                                <p class="text-muted mb-0 small">{{ $activity['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card card-hover h-100">
                <div class="card-header">
                    <h5 class="mb-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="system-health">
                        <!-- Database Status -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Database Connection</span>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Online
                            </span>
                        </div>
                        
                        <!-- Cache Status -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Cache System</span>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Active
                            </span>
                        </div>
                        
                        <!-- Storage Usage -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Storage Usage</span>
                                <span class="text-muted">{{ $systemHealth['storage_usage'] ?? '65%' }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: {{ $systemHealth['storage_percentage'] ?? 65 }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Memory Usage -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Memory Usage</span>
                                <span class="text-muted">{{ $systemHealth['memory_usage'] ?? '45%' }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ $systemHealth['memory_percentage'] ?? 45 }}%"></div>
                            </div>
                        </div>
                        
                        <!-- API Response Time -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Avg Response Time</span>
                            <span class="badge bg-info">{{ $systemHealth['avg_response_time'] ?? '125ms' }}</span>
                        </div>
                        
                        <!-- Last Backup -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Last Backup</span>
                            <small class="text-muted">{{ $systemHealth['last_backup'] ?? '2 hours ago' }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">System Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button @click="clearCache()" class="btn btn-outline-warning w-100">
                                <i class="bi bi-trash"></i> Clear Cache
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button @click="optimizeDatabase()" class="btn btn-outline-info w-100">
                                <i class="bi bi-gear"></i> Optimize DB
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button @click="backupSystem()" class="btn btn-outline-success w-100">
                                <i class="bi bi-download"></i> Backup
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('dashboard.statistics.export') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-earmark-excel"></i> Export Data
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function adminDashboard() {
    return {
        loading: false,
        stats: @json($stats ?? []),
        activities: @json($activities ?? []),
        chartPeriod: '7',
        apiChart: null,
        formatChart: null,
        
        init() {
            this.initCharts();
            this.startRealTimeUpdates();
        },
        
        initCharts() {
            // API Usage Chart
            const apiCtx = document.getElementById('apiUsageChart').getContext('2d');
            this.apiChart = new Chart(apiCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'API Requests',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
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
            
            // Format Distribution Chart
            const formatCtx = document.getElementById('formatDistributionChart').getContext('2d');
            this.formatChart = new Chart(formatCtx, {
                type: 'doughnut',
                data: {
                    labels: ['HTML', 'JSON', 'Other'],
                    datasets: [{
                        data: [75, 15, 10],
                        backgroundColor: ['#0d6efd', '#198754', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },
        
        startRealTimeUpdates() {
            // Update stats every 30 seconds
            setInterval(() => {
                this.refreshData();
            }, 30000);
        },
        
        async refreshData() {
            this.loading = true;
            try {
                const response = await fetch('/dashboard/admin/stats');
                const data = await response.json();
                this.stats = data.stats;
                this.updateChart();
            } catch (error) {
                console.error('Failed to refresh data:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async refreshActivities() {
            try {
                const response = await fetch('/dashboard/admin/activities');
                const data = await response.json();
                this.activities = data.activities;
            } catch (error) {
                console.error('Failed to refresh activities:', error);
            }
        },
        
        updateChart() {
            if (this.apiChart) {
                // Update chart data based on period
                // This would typically fetch new data from the server
                this.apiChart.update();
            }
        },
        
        async clearCache() {
            try {
                const response = await fetch('/dashboard/admin/clear-cache', { method: 'POST' });
                if (response.ok) {
                    Swal.fire('Success!', 'Cache cleared successfully', 'success');
                }
            } catch (error) {
                Swal.fire('Error!', 'Failed to clear cache', 'error');
            }
        },
        
        async optimizeDatabase() {
            Swal.fire({
                title: 'Optimize Database?',
                text: 'This will optimize all database tables.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, optimize!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('/dashboard/admin/optimize-db', { method: 'POST' });
                        if (response.ok) {
                            Swal.fire('Success!', 'Database optimized successfully', 'success');
                        }
                    } catch (error) {
                        Swal.fire('Error!', 'Failed to optimize database', 'error');
                    }
                }
            });
        },
        
        async backupSystem() {
            try {
                const response = await fetch('/dashboard/admin/backup', { method: 'POST' });
                if (response.ok) {
                    Swal.fire('Success!', 'Backup created successfully', 'success');
                }
            } catch (error) {
                Swal.fire('Error!', 'Failed to create backup', 'error');
            }
        },
        
        getActivityIcon(type) {
            const icons = {
                'website': 'bi bi-globe',
                'api_key': 'bi bi-key',
                'webbloc': 'bi bi-puzzle',
                'user': 'bi bi-person',
                'system': 'bi bi-gear',
                'error': 'bi bi-exclamation-triangle'
            };
            return icons[type] || 'bi bi-activity';
        },
        
        formatTime(timestamp) {
            return new Date(timestamp).toLocaleString();
        }
    };
}

// Add rotating animation for refresh button
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .rotating {
            animation: rotate 1s linear infinite;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endpush