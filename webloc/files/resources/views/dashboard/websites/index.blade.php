@extends('dashboard.layouts.app')

@section('title', 'Websites Management')

@section('content')
<div x-data="websiteManager()" x-init="init()">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Websites</h2>
            <p class="text-muted mb-0">Manage your registered websites and integrations</p>
        </div>
        <div class="d-flex gap-2">
            <button @click="showCreateModal()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Website
            </button>
            <button @click="refreshWebsites()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search websites..." 
                               x-model="filters.search" @input="filterWebsites()">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.status" @change="filterWebsites()">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending Verification</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" x-model="filters.plan" @change="filterWebsites()">
                        <option value="">All Plans</option>
                        <option value="free">Free</option>
                        <option value="pro">Pro</option>
                        <option value="enterprise">Enterprise</option>
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

    <!-- Websites Grid -->
    <div class="row">
        <template x-for="website in filteredWebsites" :key="website.id">
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card card-hover h-100">
                    <!-- Website Status Badge -->
                    <div class="position-absolute top-0 end-0 m-3">
                        <span :class="getStatusBadgeClass(website.status)" x-text="website.status"></span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Website Info -->
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px;">
                                    <i class="bi bi-globe text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="card-title mb-1" x-text="website.name"></h5>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-link-45deg"></i>
                                    <a :href="'https://' + website.domain" target="_blank" 
                                       x-text="website.domain" class="text-decoration-none"></a>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Website Stats -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="border-end">
                                    <h6 class="mb-1" x-text="website.api_calls_today || '0'"></h6>
                                    <small class="text-muted">Today</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h6 class="mb-1" x-text="website.webblocs_count || '0'"></h6>
                                    <small class="text-muted">WebBlocs</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h6 class="mb-1" x-text="website.api_keys_count || '0'"></h6>
                                <small class="text-muted">API Keys</small>
                            </div>
                        </div>
                        
                        <!-- Installed WebBlocs -->
                        <div class="mb-3">
                            <small class="text-muted">Installed WebBlocs:</small>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <template x-for="webbloc in website.webblocs" :key="webbloc">
                                    <span class="badge bg-light text-dark" x-text="webbloc"></span>
                                </template>
                                <template x-if="!website.webblocs || website.webblocs.length === 0">
                                    <span class="badge bg-light text-muted">None installed</span>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <button @click="viewWebsite(website)" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="bi bi-eye"></i> View
                            </button>
                            <button @click="manageWebBlocs(website)" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-puzzle"></i> WebBlocs
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                        data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" @click="editWebsite(website)">
                                        <i class="bi bi-pencil me-2"></i>Edit
                                    </a></li>
                                    <li><a class="dropdown-item" @click="viewStats(website)">
                                        <i class="bi bi-graph-up me-2"></i>Statistics
                                    </a></li>
                                    <li><a class="dropdown-item" @click="regenerateToken(website)">
                                        <i class="bi bi-arrow-clockwise me-2"></i>Regenerate Token
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" @click="deleteWebsite(website)">
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <!-- Empty State -->
        <template x-if="filteredWebsites.length === 0">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-globe display-1 text-muted"></i>
                    <h4 class="mt-3">No websites found</h4>
                    <p class="text-muted mb-4">Get started by adding your first website to integrate WebBloc components.</p>
                    <button @click="showCreateModal()" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Your First Website
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4" x-show="totalPages > 1">
        <div>
            <small class="text-muted">
                Showing <span x-text="((currentPage - 1) * perPage) + 1"></span> to 
                <span x-text="Math.min(currentPage * perPage, totalWebsites)"></span> of 
                <span x-text="totalWebsites"></span> websites
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

<!-- Add Website Modal -->
<div class="modal fade" id="addWebsiteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form @submit.prevent="createWebsite()" x-data="{ form: { name: '', domain: '', description: '', plan: 'free' } }">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Website</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Website Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" x-model="form.name" required
                               placeholder="My Awesome Website">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Domain <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" x-model="form.domain" required
                               placeholder="example.com">
                        <div class="form-text">Enter your domain without http:// or https://</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" x-model="form.description" rows="3"
                                  placeholder="Brief description of your website"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Plan</label>
                        <select class="form-select" x-model="form.plan">
                            <option value="free">Free (10K requests/month)</option>
                            <option value="pro">Pro ($9/month - 100K requests)</option>
                            <option value="enterprise">Enterprise (Custom limits)</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        After adding your website, you'll need to verify domain ownership by adding a verification token to your site.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Website
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WebBloc Management Modal -->
<div class="modal fade" id="webBlocModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage WebBlocs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" x-data="{ selectedWebsite: null }" x-init="selectedWebsite = websiteManager().selectedWebsite">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Available WebBlocs</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">Authentication</h6>
                                            <span class="badge bg-primary">auth</span>
                                        </div>
                                        <p class="card-text small text-muted">User login, registration, and profile management</p>
                                        <button class="btn btn-sm btn-success w-100">
                                            <i class="bi bi-check"></i> Installed
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">Comments</h6>
                                            <span class="badge bg-success">comments</span>
                                        </div>
                                        <p class="card-text small text-muted">User comments and discussions</p>
                                        <button class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-download"></i> Install
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">Reviews</h6>
                                            <span class="badge bg-warning">reviews</span>
                                        </div>
                                        <p class="card-text small text-muted">Product and service reviews with ratings</p>
                                        <button class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-download"></i> Install
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title">Notifications</h6>
                                            <span class="badge bg-info">notifications</span>
                                        </div>
                                        <p class="card-text small text-muted">Real-time notifications and alerts</p>
                                        <button class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-download"></i> Install
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6>Integration Code</h6>
                        <div class="card">
                            <div class="card-body">
                                <p class="small text-muted">Add these files to your website:</p>
                                <div class="mb-3">
                                    <label class="form-label small">JavaScript (in &lt;head&gt;)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control font-monospace" 
                                               value="<script src='https://example.com/cdn/webbloc.min.js'></script>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="copyToClipboard(this.previousElementSibling.value)">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">CSS (in &lt;head&gt;)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control font-monospace" 
                                               value="<link rel='stylesheet' href='https://example.com/cdn/webbloc.min.css'>" readonly>
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="copyToClipboard(this.previousElementSibling.value)">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </div>
                                <hr>
                                <p class="small text-muted">Example WebBloc usage:</p>
                                <pre class="small"><code>&lt;div w2030b="auth" 
     data-website-id="123"
     data-api-key="your-public-key"&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function websiteManager() {
    return {
        websites: @json($websites ?? []),
        filteredWebsites: [],
        filters: {
            search: '',
            status: '',
            plan: ''
        },
        currentPage: 1,
        perPage: 9,
        selectedWebsite: null,
        
        init() {
            this.filteredWebsites = this.websites;
            this.filterWebsites();
        },
        
        filterWebsites() {
            this.filteredWebsites = this.websites.filter(website => {
                const matchesSearch = !this.filters.search || 
                    website.name.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                    website.domain.toLowerCase().includes(this.filters.search.toLowerCase());
                
                const matchesStatus = !this.filters.status || website.status === this.filters.status;
                const matchesPlan = !this.filters.plan || website.plan === this.filters.plan;
                
                return matchesSearch && matchesStatus && matchesPlan;
            });
            
            this.currentPage = 1;
        },
        
        resetFilters() {
            this.filters = { search: '', status: '', plan: '' };
            this.filterWebsites();
        },
        
        get totalWebsites() {
            return this.filteredWebsites.length;
        },
        
        get totalPages() {
            return Math.ceil(this.totalWebsites / this.perPage);
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
                'pending': 'badge bg-warning',
                'suspended': 'badge bg-danger',
                'inactive': 'badge bg-secondary'
            };
            return classes[status] || 'badge bg-secondary';
        },
        
        showCreateModal() {
            const modal = new bootstrap.Modal(document.getElementById('addWebsiteModal'));
            modal.show();
        },
        
        async createWebsite() {
            // Implementation for creating website
            Swal.fire('Success!', 'Website added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addWebsiteModal')).hide();
        },
        
        viewWebsite(website) {
            window.location.href = `/dashboard/websites/${website.id}`;
        },
        
        editWebsite(website) {
            window.location.href = `/dashboard/websites/${website.id}/edit`;
        },
        
        manageWebBlocs(website) {
            this.selectedWebsite = website;
            const modal = new bootstrap.Modal(document.getElementById('webBlocModal'));
            modal.show();
        },
        
        viewStats(website) {
            window.location.href = `/dashboard/websites/${website.id}/statistics`;
        },
        
        regenerateToken(website) {
            Swal.fire({
                title: 'Regenerate Token?',
                text: 'This will invalidate the current verification token.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, regenerate!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementation for token regeneration
                    Swal.fire('Success!', 'Verification token regenerated', 'success');
                }
            });
        },
        
        deleteWebsite(website) {
            Swal.fire({
                title: 'Delete Website?',
                text: `This will permanently delete "${website.name}" and all associated data.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementation for website deletion
                    Swal.fire('Deleted!', 'Website has been deleted.', 'success');
                }
            });
        },
        
        async refreshWebsites() {
            try {
                const response = await fetch('/dashboard/websites/refresh');
                const data = await response.json();
                this.websites = data.websites;
                this.filterWebsites();
            } catch (error) {
                console.error('Failed to refresh websites:', error);
            }
        }
    };
}
</script>
@endpush