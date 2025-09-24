@extends('dashboard.layouts.app')

@section('title', 'WebBlocs Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">WebBlocs Management</h1>
            <p class="text-muted">Manage available WebBloc components and their installations</p>
        </div>
        <div>
            @can('create', App\Models\WebBloc::class)
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createWebBlocModal">
                    <i class="fas fa-plus"></i> Create WebBloc
                </button>
            @endcan
            <button type="button" class="btn btn-outline-secondary" onclick="refreshWebBlocs()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total WebBlocs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active WebBlocs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Installations</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['installations'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-download fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Websites</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['websites'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.webblocs.index') }}" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Search name, type...">
                    </div>
                    <div class="col-md-2">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="auth" {{ request('type') === 'auth' ? 'selected' : '' }}>Authentication</option>
                            <option value="comments" {{ request('type') === 'comments' ? 'selected' : '' }}>Comments</option>
                            <option value="reviews" {{ request('type') === 'reviews' ? 'selected' : '' }}>Reviews</option>
                            <option value="notifications" {{ request('type') === 'notifications' ? 'selected' : '' }}>Notifications</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="development" {{ request('status') === 'development' ? 'selected' : '' }}>Development</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sort">Sort By</label>
                        <select class="form-control" id="sort" name="sort">
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Type</option>
                            <option value="installations" {{ request('sort') === 'installations' ? 'selected' : '' }}>Installations</option>
                            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Date Created</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('dashboard.webblocs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- WebBlocs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">WebBlocs ({{ $webblocs->total() }})</h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow">
                    @can('create', App\Models\WebBloc::class)
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkInstallModal">
                            <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                            Bulk Install to Websites
                        </a>
                        <a class="dropdown-item" href="#" onclick="exportWebBlocs()">
                            <i class="fas fa-file-export fa-sm fa-fw mr-2 text-gray-400"></i>
                            Export WebBlocs
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($webblocs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="webBlocsTable">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>WebBloc</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Installations</th>
                                <th>Version</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($webblocs as $webbloc)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_webblocs[]" value="{{ $webbloc->id }}" class="webbloc-checkbox">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="webbloc-icon me-3">
                                                <i class="fas {{ $webbloc->getIconClass() }} fa-lg text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $webbloc->name }}</div>
                                                <div class="text-muted small">{{ Str::limit($webbloc->description, 60) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $webbloc->getTypeBadgeClass() }}">
                                            {{ ucfirst($webbloc->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $webbloc->status === 'active' ? 'success' : ($webbloc->status === 'inactive' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($webbloc->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="me-2">{{ $webbloc->installation_count }}</span>
                                            @if($webbloc->installation_count > 0)
                                                <a href="{{ route('dashboard.webblocs.installations', $webbloc) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View installations">
                                                    <i class="fas fa-list"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $webbloc->version }}</code>
                                    </td>
                                    <td>
                                        <span title="{{ $webbloc->updated_at->format('Y-m-d H:i:s') }}">
                                            {{ $webbloc->updated_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('dashboard.webblocs.show', $webbloc) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $webbloc)
                                                <a href="{{ route('dashboard.webblocs.edit', $webbloc) }}" 
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('install', $webbloc)
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="showInstallModal({{ $webbloc->id }})" title="Install to Website">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            @endcan
                                            @can('delete', $webbloc)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteWebBloc({{ $webbloc->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $webblocs->firstItem() }} to {{ $webblocs->lastItem() }} of {{ $webblocs->total() }} results
                    </div>
                    <div>
                        {{ $webblocs->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-cubes fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No WebBlocs Found</h5>
                    <p class="text-muted">Start by creating your first WebBloc component.</p>
                    @can('create', App\Models\WebBloc::class)
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWebBlocModal">
                            <i class="fas fa-plus"></i> Create WebBloc
                        </button>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create WebBloc Modal -->
@can('create', App\Models\WebBloc::class)
<div class="modal fade" id="createWebBlocModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New WebBloc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dashboard.webblocs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="auth">Authentication</option>
                                    <option value="comments">Comments</option>
                                    <option value="reviews">Reviews</option>
                                    <option value="notifications">Notifications</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="version" class="form-label">Version</label>
                                <input type="text" class="form-control" id="version" name="version" value="1.0.0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="development">Development</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supported Operations</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_create" name="crud[create]" value="1" checked>
                                    <label class="form-check-label" for="crud_create">Create</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_read" name="crud[read]" value="1" checked>
                                    <label class="form-check-label" for="crud_read">Read</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_update" name="crud[update]" value="1" checked>
                                    <label class="form-check-label" for="crud_update">Update</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="crud_delete" name="crud[delete]" value="1" checked>
                                    <label class="form-check-label" for="crud_delete">Delete</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create WebBloc</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- Install to Website Modal -->
<div class="modal fade" id="installWebBlocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Install WebBloc to Website</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="installWebBlocForm">
                @csrf
                <input type="hidden" id="install_webbloc_id" name="webbloc_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="install_website_id" class="form-label">Select Website <span class="text-danger">*</span></label>
                        <select class="form-control" id="install_website_id" name="website_id" required>
                            <option value="">Choose Website...</option>
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}">{{ $website->name }} ({{ $website->domain }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Installation Settings</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_activate" name="auto_activate" value="1" checked>
                            <label class="form-check-label" for="auto_activate">Auto-activate after installation</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rebuild_cdn" name="rebuild_cdn" value="1">
                            <label class="form-check-label" for="rebuild_cdn">Rebuild CDN assets</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Install WebBloc</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-refresh functionality
let autoRefresh = false;
function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        setInterval(refreshWebBlocs, 30000); // Refresh every 30 seconds
    }
}

function refreshWebBlocs() {
    window.location.reload();
}

// Select all functionality
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.webbloc-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

// Show install modal
function showInstallModal(webBlocId) {
    document.getElementById('install_webbloc_id').value = webBlocId;
    new bootstrap.Modal(document.getElementById('installWebBlocModal')).show();
}

// Handle install form submission
document.getElementById('installWebBlocForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const webBlocId = formData.get('webbloc_id');
    const websiteId = formData.get('website_id');
    
    // Show loading state
    Swal.fire({
        title: 'Installing WebBloc...',
        text: 'Please wait while we install the WebBloc to your website.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Make AJAX request
    fetch(`/dashboard/webblocs/${webBlocId}/install`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            website_id: websiteId,
            auto_activate: formData.get('auto_activate') === '1',
            rebuild_cdn: formData.get('rebuild_cdn') === '1'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'WebBloc Installed!',
                text: data.message,
                confirmButtonText: 'Great!'
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('installWebBlocModal')).hide();
                refreshWebBlocs();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Installation Failed',
                text: data.message || 'An error occurred during installation.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    });
});

// Delete WebBloc
function deleteWebBloc(webBlocId) {
    Swal.fire({
        title: 'Delete WebBloc?',
        text: 'This action cannot be undone. All installations of this WebBloc will also be removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/dashboard/webblocs/${webBlocId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Export WebBlocs
function exportWebBlocs() {
    const selectedCheckboxes = document.querySelectorAll('.webbloc-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No WebBlocs Selected',
            text: 'Please select at least one WebBloc to export.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Create download link
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/dashboard/webblocs/export';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfToken);
    
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'webbloc_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

@push('styles')
<style>
.webbloc-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fc;
    border-radius: 8px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fc;
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
</style>
@endpush