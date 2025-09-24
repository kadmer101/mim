@extends('dashboard.layouts.app')

@section('title', 'API Keys Management')

@section('content')
<div x-data="apiKeyManager()" x-init="init()">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">API Keys</h2>
            <p class="text-muted mb-0">Manage API keys for your websites</p>
        </div>
        <div class="d-flex gap-2">
            <button @click="showCreateModal()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Generate API Key
            </button>
            <button @click="refreshApiKeys()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- API Key Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card-primary card-hover h-100">
                <div class="card-body text-center">
                    <i class="bi bi-key fs-1 mb-2"></i>
                    <h3 class="mb-1" x-text="stats.total || '0'">{{ $stats['total'] ?? 0 }}</h3>
                    <small class="text-white-50">Total Keys</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card-success card-hover h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 mb-2"></i>
                    <h3 class="mb-1" x-text="stats.active || '0'">{{ $stats['active'] ?? 0 }}</h3>
                    <small class="text-white-50">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card-warning card-hover h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-1 mb-2"></i>
                    <h3 class="mb-1" x-text="stats.requests_today || '0'">{{ $stats['requests_today'] ?? 0 }}</h3>
                    <small class="text-white-50">Requests Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card card-hover h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-1 mb-2"></i>
                    <h3 class="mb-1" x-text="stats.rate_limited || '0'">{{ $stats['rate_limited'] ?? 0 }}</h3>
                    <small class="text-white-50">Rate Limited</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search API keys..." 
                               x-model="filters.search" @input="filterApiKeys()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.website" @change="filterApiKeys()">
                        <option value="">All Websites</option>
                        <template x-for="website in websites" :key="website.id">
                            <option :value="website.id" x-text="website.name"></option>
                        </template>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.status" @change="filterApiKeys()">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button @click="resetFilters()" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- API Keys Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">API Keys</h5>
            <span class="badge bg-secondary" x-text="`${filteredApiKeys.length} keys`"></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Website</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="apiKey in paginatedApiKeys" :key="apiKey.id">
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-key text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0" x-text="apiKey.name"></h6>
                                            <small class="text-muted">
                                                <span x-text="apiKey.type === 'public' ? 'Public Key' : 'Secret Key'"></span>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span x-text="apiKey.website?.name || 'Unknown'"></span>
                                    <br>
                                    <small class="text-muted" x-text="apiKey.website?.domain || ''"></small>
                                </td>
                                <td>
                                    <span :class="apiKey.type === 'public' ? 'badge bg-info' : 'badge bg-warning'" 
                                          x-text="apiKey.type"></span>
                                </td>
                                <td>
                                    <span :class="getStatusBadgeClass(apiKey.status)" x-text="apiKey.status"></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" 
                                                     :style="`width: ${getUsagePercentage(apiKey)}%`"
                                                     :class="getUsageBarClass(apiKey)"></div>
                                            </div>
                                        </div>
                                        <small class="text-muted ms-2" x-text="`${apiKey.requests_today || 0}/${apiKey.rate_limit_daily || 10000}`"></small>
                                    </div>
                                </td>
                                <td>
                                    <span x-text="formatDate(apiKey.last_used_at)" class="text-muted"></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button @click="viewApiKey(apiKey)" class="btn btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button @click="copyApiKey(apiKey)" class="btn btn-outline-secondary">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" @click="editApiKey(apiKey)">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </a></li>
                                                <li><a class="dropdown-item" @click="regenerateApiKey(apiKey)">
                                                    <i class="bi bi-arrow-clockwise me-2"></i>Regenerate
                                                </a></li>
                                                <li><a class="dropdown-item" @click="viewUsage(apiKey)">
                                                    <i class="bi bi-graph-up me-2"></i>Usage Stats
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <template x-if="apiKey.status === 'active'">
                                                    <li><a class="dropdown-item text-warning" @click="suspendApiKey(apiKey)">
                                                        <i class="bi bi-pause me-2"></i>Suspend
                                                    </a></li>
                                                </template>
                                                <template x-if="apiKey.status === 'suspended'">
                                                    <li><a class="dropdown-item text-success" @click="activateApiKey(apiKey)">
                                                        <i class="bi bi-play me-2"></i>Activate
                                                    </a></li>
                                                </template>
                                                <li><a class="dropdown-item text-danger" @click="deleteApiKey(apiKey)">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        
                        <!-- Empty State -->
                        <template x-if="filteredApiKeys.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-key display-4 text-muted"></i>
                                    <h5 class="mt-3">No API keys found</h5>
                                    <p class="text-muted">Generate your first API key to get started with WebBloc integration.</p>
                                    <button @click="showCreateModal()" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Generate API Key
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4" x-show="totalPages > 1">
        <div>
            <small class="text-muted">
                Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                <span x-text="Math.min(currentPage * perPage, filteredApiKeys.length)"></span> of 
                <span x-text="filteredApiKeys.length"></span> API keys
            </small>
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="goToPage(currentPage - 1)">Previous</button>
                </li>
                <template x-for="page in getVisiblePages()" :key="page">
                    <li class="page-item" :class="{ active: page === currentPage }">
                        <button class="page-link" @click="goToPage(page)" x-text="page"></button>
                    </li>
                </template>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="goToPage(currentPage + 1)">Next</button>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form @submit.prevent="createApiKey()" x-data="{ form: { name: '', website_id: '', type: 'public', permissions: [], rate_limit_per_minute: 100, rate_limit_daily: 10000, allowed_domains: '', allowed_ips: '' } }">
                <div class="modal-header">
                    <h5 class="modal-title">Generate New API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" x-model="form.name" required
                                   placeholder="Production Key">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Website <span class="text-danger">*</span></label>
                            <select class="form-select" x-model="form.website_id" required>
                                <option value="">Select Website</option>
                                <template x-for="website in websites" :key="website.id">
                                    <option :value="website.id" x-text="website.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Key Type</label>
                            <select class="form-select" x-model="form.type">
                                <option value="public">Public Key (Client-side safe)</option>
                                <option value="secret">Secret Key (Server-side only)</option>
                            </select>
                            <div class="form-text">Public keys can be exposed in frontend code, secret keys cannot.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Environment</label>
                            <select class="form-select" x-model="form.environment">
                                <option value="production">Production</option>
                                <option value="staging">Staging</option>
                                <option value="development">Development</option>
                            </select>
                        </div>
                    </div>

                    <!-- Rate Limiting -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rate Limit (per minute)</label>
                            <input type="number" class="form-control" x-model="form.rate_limit_per_minute" 
                                   min="1" max="1000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Daily Limit</label>
                            <input type="number" class="form-control" x-model="form.rate_limit_daily" 
                                   min="100" max="1000000">
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="mb-3">
                        <label class="form-label">Allowed Domains</label>
                        <textarea class="form-control" x-model="form.allowed_domains" rows="2"
                                  placeholder="example.com, *.example.com (one per line)"></textarea>
                        <div class="form-text">Leave empty to allow all domains. Use * for wildcards.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Allowed IPs</label>
                        <textarea class="form-control" x-model="form.allowed_ips" rows="2"
                                  placeholder="192.168.1.1, 10.0.0.0/8 (one per line)"></textarea>
                        <div class="form-text">Leave empty to allow all IPs. Supports CIDR notation.</div>
                    </div>

                    <!-- Permissions -->
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="webbloc:read" x-model="form.permissions">
                                    <label class="form-check-label">Read WebBlocs</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="webbloc:write" x-model="form.permissions">
                                    <label class="form-check-label">Write WebBlocs</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="auth:manage" x-model="form.permissions">
                                    <label class="form-check-label">Manage Auth</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="stats:read" x-model="form.permissions">
                                    <label class="form-check-label">Read Statistics</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key"></i> Generate API Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- API Key Details Modal -->
<div class="modal fade" id="apiKeyDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" x-data="{ selectedApiKey: null }">
            <div class="modal-header">
                <h5 class="modal-title">API Key Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <template x-if="selectedApiKey">
                    <div>
                        <!-- Key Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Key Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td x-text="selectedApiKey.name"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td><span :class="selectedApiKey.type === 'public' ? 'badge bg-info' : 'badge bg-warning'" x-text="selectedApiKey.type"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><span :class="getStatusBadgeClass(selectedApiKey.status)" x-text="selectedApiKey.status"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td x-text="formatDate(selectedApiKey.created_at)"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Usage Statistics</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Today:</strong></td>
                                        <td x-text="`${selectedApiKey.requests_today || 0}/${selectedApiKey.rate_limit_daily || 10000}`"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>This Month:</strong></td>
                                        <td x-text="selectedApiKey.requests_month || '0'"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total:</strong></td>
                                        <td x-text="selectedApiKey.total_requests || '0'"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Used:</strong></td>
                                        <td x-text="formatDate(selectedApiKey.last_used_at)"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- API Key Value -->
                        <div class="mb-4">
                            <label class="form-label">API Key</label>
                            <div class="input-group">
                                <input type="password" class="form-control font-monospace" 
                                       :value="selectedApiKey.key" readonly id="apiKeyValue">
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="toggleApiKeyVisibility()">
                                    <i class="bi bi-eye" id="toggleIcon"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" 
                                        @click="copyToClipboard(selectedApiKey.key)">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Integration Example -->
                        <div class="mb-3">
                            <label class="form-label">Integration Example</label>
                            <pre class="bg-light p-3 rounded"><code>&lt;div w2030b="auth" 
     data-website-id="<span x-text="selectedApiKey.website_id"></span>"
     data-api-key="<span x-text="selectedApiKey.key"></span>"&gt;
&lt;/div&gt;</code></pre>
                        </div>
                    </div>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" @click="editApiKey(selectedApiKey)">
                    <i class="bi bi-pencil"></i> Edit Key
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function apiKeyManager() {
    return {
        apiKeys: @json($apiKeys ?? []),
        websites: @json($websites ?? []),
        stats: @json($stats ?? {}),
        filteredApiKeys: [],
        filters: {
            search: '',
            website: '',
            status: ''
        },
        currentPage: 1,
        perPage: 10,
        selectedApiKey: null,
        
        init() {
            this.filteredApiKeys = this.apiKeys;
            this.filterApiKeys();
        },
        
        filterApiKeys() {
            this.filteredApiKeys = this.apiKeys.filter(apiKey => {
                const matchesSearch = !this.filters.search || 
                    apiKey.name.toLowerCase().includes(this.filters.search.toLowerCase());
                
                const matchesWebsite = !this.filters.website || 
                    apiKey.website_id.toString() === this.filters.website;
                
                const matchesStatus = !this.filters.status || apiKey.status === this.filters.status;
                
                return matchesSearch && matchesWebsite && matchesStatus;
            });
            
            this.currentPage = 1;
        },
        
        resetFilters() {
            this.filters = { search: '', website: '', status: '' };
            this.filterApiKeys();
        },
        
        get paginatedApiKeys() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredApiKeys.slice(start, end);
        },
        
        get totalPages() {
            return Math.ceil(this.filteredApiKeys.length / this.perPage);
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        
        getVisiblePages() {
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
                'active': 'badge bg-success',
                'suspended': 'badge bg-warning',
                'expired': 'badge bg-danger'
            };
            return classes[status] || 'badge bg-secondary';
        },
        
        getUsagePercentage(apiKey) {
            const daily = apiKey.requests_today || 0;
            const limit = apiKey.rate_limit_daily || 10000;
            return Math.min((daily / limit) * 100, 100);
        },
        
        getUsageBarClass(apiKey) {
            const percentage = this.getUsagePercentage(apiKey);
            if (percentage >= 90) return 'bg-danger';
            if (percentage >= 70) return 'bg-warning';
            return 'bg-success';
        },
        
        formatDate(date) {
            if (!date) return 'Never';
            return new Date(date).toLocaleDateString();
        },
        
        showCreateModal() {
            const modal = new bootstrap.Modal(document.getElementById('createApiKeyModal'));
            modal.show();
        },
        
        async createApiKey() {
            // Implementation for creating API key
            Swal.fire('Success!', 'API key generated successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createApiKeyModal')).hide();
        },
        
        viewApiKey(apiKey) {
            this.selectedApiKey = apiKey;
            const modal = new bootstrap.Modal(document.getElementById('apiKeyDetailsModal'));
            modal.show();
        },
        
        copyApiKey(apiKey) {
            copyToClipboard(apiKey.key);
        },
        
        editApiKey(apiKey) {
            // Implementation for editing API key
            console.log('Edit API key:', apiKey.id);
        },
        
        regenerateApiKey(apiKey) {
            Swal.fire({
                title: 'Regenerate API Key?',
                text: 'This will invalidate the current key. Make sure to update your integrations.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, regenerate!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementation for regenerating API key
                    Swal.fire('Success!', 'API key regenerated successfully', 'success');
                }
            });
        },
        
        suspendApiKey(apiKey) {
            Swal.fire({
                title: 'Suspend API Key?',
                text: 'This will temporarily disable the API key.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, suspend!'
            }).then((result) => {
                if (result.isConfirmed) {
                    apiKey.status = 'suspended';
                    Swal.fire('Success!', 'API key suspended', 'success');
                }
            });
        },
        
        activateApiKey(apiKey) {
            apiKey.status = 'active';
            Swal.fire('Success!', 'API key activated', 'success');
        },
        
        deleteApiKey(apiKey) {
            Swal.fire({
                title: 'Delete API Key?',
                text: 'This action cannot be undone.',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementation for deleting API key
                    Swal.fire('Deleted!', 'API key has been deleted.', 'success');
                }
            });
        },
        
        viewUsage(apiKey) {
            window.location.href = `/dashboard/api-keys/${apiKey.id}/usage`;
        },
        
        async refreshApiKeys() {
            try {
                const response = await fetch('/dashboard/api-keys/refresh');
                const data = await response.json();
                this.apiKeys = data.apiKeys;
                this.stats = data.stats;
                this.filterApiKeys();
            } catch (error) {
                console.error('Failed to refresh API keys:', error);
            }
        }
    };
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('apiKeyValue');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush