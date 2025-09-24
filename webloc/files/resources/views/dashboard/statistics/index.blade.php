@extends('dashboard.layouts.app')

@section('title', 'Statistics & Analytics')

@section('content')
<div class="container-fluid" x-data="statisticsData()">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Statistics & Analytics</h1>
            <p class="text-muted">Monitor your WebBloc platform performance and usage</p>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary" @click="refreshData()">
                <i class="fas fa-sync-alt" :class="{'fa-spin': loading}"></i> Refresh
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click="exportData('csv')">CSV Format</a></li>
                    <li><a class="dropdown-item" href="#" @click="exportData('xlsx')">Excel Format</a></li>
                    <li><a class="dropdown-item" href="#" @click="exportData('json')">JSON Format</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-success" @click="toggleAutoRefresh()">
                <i class="fas fa-play" x-show="!autoRefresh"></i>
                <i class="fas fa-pause" x-show="autoRefresh"></i>
                <span x-text="autoRefresh ? 'Stop Auto' : 'Auto Refresh'"></span>
            </button>
        </div>
    </div>

    <!-- Time Range Selector -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Time Range</label>
                    <select class="form-select" x-model="timeRange" @change="loadData()">
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="90d">Last 90 Days</option>
                        <option value="1y">Last Year</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Custom Date Range</label>
                    <div class="input-group">
                        <input type="date" class="form-control" x-model="customDateFrom">
                        <span class="input-group-text">to</span>
                        <input type="date" class="form-control" x-model="customDateTo">
                        <button class="btn btn-outline-primary" @click="loadCustomRange()">Apply</button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Update Frequency</label>
                    <select class="form-select" x-model="refreshInterval">
                        <option value="0">Manual</option>
                        <option value="30">30 seconds</option>
                        <option value="60">1 minute</option>
                        <option value="300">5 minutes</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <!-- Total API Calls -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">API Calls (Total)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="formatNumber(stats.api_calls_total || 0)"></div>
                            <div class="text-xs text-success" x-show="stats.api_calls_change > 0">
                                <i class="fas fa-arrow-up"></i> <span x-text="stats.api_calls_change + '%'"></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Websites -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Websites</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="stats.active_websites || 0"></div>
                            <div class="text-xs text-success" x-show="stats.websites_change > 0">
                                <i class="fas fa-arrow-up"></i> <span x-text="stats.websites_change + ' new'"></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" x-text="formatNumber(stats.total_users || 0)"></div>
                            <div class="progress progress-sm mr-2">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     :style="`width: ${Math.min((stats.total_users / 10000) * 100, 100)}%`"></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Response Formats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Response Distribution</div>
                            <div class="small">
                                <div>HTML: <span x-text="(stats.format_distribution?.html || 75) + '%'"></span></div>
                                <div>JSON: <span x-text="(stats.format_distribution?.json || 15) + '%'"></span></div>
                                <div>Other: <span x-text="(stats.format_distribution?.other || 10) + '%'"></span></div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- API Usage Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">API Usage Over Time</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" @click="chartType = 'line'">Line Chart</a>
                            <a class="dropdown-item" href="#" @click="chartType = 'bar'">Bar Chart</a>
                            <a class="dropdown-item" href="#" @click="chartType = 'area'">Area Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="apiUsageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- WebBloc Usage Breakdown -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">WebBloc Usage</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="webBlocUsageChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <template x-for="(webbloc, index) in webBlocStats" :key="webbloc.type">
                            <span class="mr-2">
                                <i class="fas fa-circle" :class="`text-${getWebBlocColor(index)}`"></i>
                                <span x-text="webbloc.type + ' (' + webbloc.percentage + '%)'"></span>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Avg Response Time</div>
                                <div class="h6 mb-0" x-text="(stats.avg_response_time || 0) + 'ms'"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Success Rate</div>
                                <div class="h6 mb-0 text-success" x-text="(stats.success_rate || 0) + '%'"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Cache Hit Rate</div>
                                <div class="h6 mb-0 text-info" x-text="(stats.cache_hit_rate || 0) + '%'"></div>
                            </div>
                            <div class="mb-3">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">Database Size</div>
                                <div class="h6 mb-0" x-text="formatBytes(stats.database_size || 0)"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Error Analysis</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Error Type</th>
                                    <th>Count</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="error in errorStats" :key="error.type">
                                    <tr>
                                        <td x-text="error.type"></td>
                                        <td x-text="error.count"></td>
                                        <td x-text="error.rate + '%'"></td>
                                        <td>
                                            <i class="fas" 
                                               :class="error.trend > 0 ? 'fa-arrow-up text-danger' : 'fa-arrow-down text-success'"></i>
                                            <span x-text="Math.abs(error.trend) + '%'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="row">
        <!-- Top Websites -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Websites by Usage</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Website</th>
                                    <th>API Calls</th>
                                    <th>Users</th>
                                    <th>Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="website in topWebsites" :key="website.id">
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-globe text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold" x-text="website.name"></div>
                                                    <div class="text-muted small" x-text="website.domain"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td x-text="formatNumber(website.api_calls)"></td>
                                        <td x-text="website.users"></td>
                                        <td>
                                            <span class="badge" 
                                                  :class="website.growth > 0 ? 'badge-success' : 'badge-danger'"
                                                  x-text="(website.growth > 0 ? '+' : '') + website.growth + '%'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <template x-for="activity in recentActivity" :key="activity.id">
                        <div class="d-flex align-items-center py-2 border-bottom">
                            <div class="me-3">
                                <div class="icon-circle" :class="`bg-${getActivityColor(activity.type)}`">
                                    <i class="fas" :class="getActivityIcon(activity.type)" style="color: white;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small font-weight-bold" x-text="activity.title"></div>
                                <div class="text-muted small" x-text="activity.description"></div>
                                <div class="text-xs text-muted" x-text="activity.time"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Updates -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true" x-show="showToast">
            <div class="toast-header">
                <i class="fas fa-chart-line text-primary me-2"></i>
                <strong class="me-auto">Statistics Updated</strong>
                <small x-text="lastUpdate"></small>
                <button type="button" class="btn-close" @click="showToast = false"></button>
            </div>
            <div class="toast-body" x-text="toastMessage">
                Statistics have been refreshed with the latest data.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function statisticsData() {
    return {
        // Data properties
        loading: false,
        autoRefresh: false,
        refreshInterval: 0,
        timeRange: '24h',
        customDateFrom: '',
        customDateTo: '',
        chartType: 'line',
        
        // Statistics data
        stats: {},
        webBlocStats: [],
        errorStats: [],
        topWebsites: [],
        recentActivity: [],
        
        // Charts
        apiUsageChart: null,
        webBlocUsageChart: null,
        performanceChart: null,
        
        // Toast
        showToast: false,
        toastMessage: '',
        lastUpdate: '',
        
        // Initialize
        init() {
            this.loadData();
            this.initializeCharts();
            this.setupAutoRefresh();
        },
        
        // Load data from API
        async loadData() {
            this.loading = true;
            
            try {
                const response = await fetch(`/dashboard/admin/stats?range=${this.timeRange}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                this.stats = data.stats || {};
                this.webBlocStats = data.webbloc_stats || [];
                this.errorStats = data.error_stats || [];
                this.topWebsites = data.top_websites || [];
                this.recentActivity = data.recent_activity || [];
                
                this.updateCharts();
                this.showUpdateToast();
                
            } catch (error) {
                console.error('Failed to load statistics:', error);
                this.showErrorToast('Failed to load statistics data');
            } finally {
                this.loading = false;
            }
        },
        
        // Load custom date range
        loadCustomRange() {
            if (this.customDateFrom && this.customDateTo) {
                this.timeRange = 'custom';
                this.loadData();
            }
        },
        
        // Refresh data
        refreshData() {
            this.loadData();
        },
        
        // Setup auto refresh
        setupAutoRefresh() {
            this.$watch('refreshInterval', (newValue) => {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }
                
                if (newValue > 0) {
                    this.intervalId = setInterval(() => {
                        this.loadData();
                    }, newValue * 1000);
                    this.autoRefresh = true;
                } else {
                    this.autoRefresh = false;
                }
            });
        },
        
        // Toggle auto refresh
        toggleAutoRefresh() {
            if (this.autoRefresh) {
                this.refreshInterval = 0;
            } else {
                this.refreshInterval = 60; // 1 minute default
            }
        },
        
        // Initialize charts
        initializeCharts() {
            // API Usage Chart
            const ctx1 = document.getElementById('apiUsageChart').getContext('2d');
            this.apiUsageChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'API Calls',
                        data: [],
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // WebBloc Usage Chart
            const ctx2 = document.getElementById('webBlocUsageChart').getContext('2d');
            this.webBlocUsageChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Performance Chart
            const ctx3 = document.getElementById('performanceChart').getContext('2d');
            this.performanceChart = new Chart(ctx3, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Response Time (ms)',
                        data: [],
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        yAxisID: 'y'
                    }, {
                        label: 'Success Rate (%)',
                        data: [],
                        borderColor: '#36b9cc',
                        backgroundColor: 'rgba(54, 185, 204, 0.1)',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        },
        
        // Update charts with new data
        updateCharts() {
            if (this.stats.api_usage_timeline) {
                this.apiUsageChart.data.labels = this.stats.api_usage_timeline.labels;
                this.apiUsageChart.data.datasets[0].data = this.stats.api_usage_timeline.data;
                this.apiUsageChart.update();
            }
            
            if (this.webBlocStats.length > 0) {
                this.webBlocUsageChart.data.labels = this.webBlocStats.map(w => w.type);
                this.webBlocUsageChart.data.datasets[0].data = this.webBlocStats.map(w => w.count);
                this.webBlocUsageChart.update();
            }
            
            if (this.stats.performance_timeline) {
                this.performanceChart.data.labels = this.stats.performance_timeline.labels;
                this.performanceChart.data.datasets[0].data = this.stats.performance_timeline.response_time;
                this.performanceChart.data.datasets[1].data = this.stats.performance_timeline.success_rate;
                this.performanceChart.update();
            }
        },
        
        // Export data
        async exportData(format) {
            try {
                const response = await fetch(`/dashboard/admin/stats/export?format=${format}&range=${this.timeRange}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `webbloc-statistics-${this.timeRange}.${format}`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    throw new Error('Export failed');
                }
            } catch (error) {
                this.showErrorToast('Failed to export data');
            }
        },
        
        // Utility methods
        formatNumber(num) {
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        },
        
        formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        getWebBlocColor(index) {
            const colors = ['primary', 'success', 'info', 'warning', 'danger'];
            return colors[index % colors.length];
        },
        
        getActivityColor(type) {
            const colors = {
                'api_call': 'primary',
                'user_register': 'success',
                'error': 'danger',
                'webbloc_install': 'info',
                'website_add': 'warning'
            };
            return colors[type] || 'secondary';
        },
        
        getActivityIcon(type) {
            const icons = {
                'api_call': 'fa-chart-line',
                'user_register': 'fa-user-plus',
                'error': 'fa-exclamation-triangle',
                'webbloc_install': 'fa-download',
                'website_add': 'fa-globe'
            };
            return icons[type] || 'fa-info';
        },
        
        showUpdateToast() {
            this.lastUpdate = new Date().toLocaleTimeString();
            this.toastMessage = 'Statistics have been refreshed with the latest data.';
            this.showToast = true;
            
            setTimeout(() => {
                this.showToast = false;
            }, 3000);
        },
        
        showErrorToast(message) {
            this.toastMessage = message;
            this.showToast = true;
            
            setTimeout(() => {
                this.showToast = false;
            }, 5000);
        }
    }
}

// Initialize Alpine.js component when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Additional initialization if needed
});
</script>
@endpush

@push('styles')
<style>
.progress-sm {
    height: 0.5rem;
}

.icon-circle {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary { background-color: #4e73df !important; }
.bg-success { background-color: #1cc88a !important; }
.bg-info { background-color: #36b9cc !important; }
.bg-warning { background-color: #f6c23e !important; }
.bg-danger { background-color: #e74a3b !important; }
.bg-secondary { background-color: #858796 !important; }

.chart-area {
    position: relative;
    height: 300px;
}

.chart-pie {
    position: relative;
    height: 200px;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.card {
    border: none;
    border-radius: 10px;
}

.toast {
    min-width: 300px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}
</style>
@endpush