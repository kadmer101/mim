<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'WebBloc') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.5rem;
            margin: 0.25rem 0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }
        .stats-card-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stats-card-success {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stats-card-warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .webbloc-component {
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            background: #f8f9fa;
        }
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 p-0">
                <div class="sidebar p-3">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-puzzle-fill fs-2 text-white me-2"></i>
                        <h4 class="text-white mb-0">WebBloc</h4>
                    </div>
                    
                    <nav class="nav flex-column">
                        @can('admin')
                        <a class="nav-link {{ request()->routeIs('dashboard.admin.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.admin.index') }}">
                            <i class="bi bi-speedometer2"></i>Admin Dashboard
                        </a>
                        @endcan
                        
                        <a class="nav-link {{ request()->routeIs('dashboard.websites.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.websites.index') }}">
                            <i class="bi bi-globe"></i>Websites
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('dashboard.api-keys.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.api-keys.index') }}">
                            <i class="bi bi-key"></i>API Keys
                        </a>
                        
                        @can('admin')
                        <a class="nav-link {{ request()->routeIs('dashboard.webblocs.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.webblocs.index') }}">
                            <i class="bi bi-puzzle"></i>WebBlocs
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('dashboard.statistics.*') ? 'active' : '' }}" 
                           href="{{ route('dashboard.statistics.index') }}">
                            <i class="bi bi-graph-up"></i>Statistics
                        </a>
                        @endcan
                        
                        <hr class="text-white-50">
                        
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person"></i>Profile
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                <i class="bi bi-box-arrow-right"></i>Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-0">
                <div class="main-content">
                    <!-- Top Navigation -->
                    <nav class="navbar navbar-expand-lg navbar-custom px-4">
                        <div class="navbar-nav ms-auto">
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                        <span class="text-white fw-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person me-2"></i>Profile
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- Page Content -->
                    <div class="p-4">
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    new bootstrap.Alert(alert).close();
                }
            });
        }, 5000);
        
        // Confirmation dialogs
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('confirm-delete')) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        e.target.closest('form').submit();
                    }
                });
            }
        });
        
        // Copy to clipboard functionality
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                Swal.fire({
                    title: 'Copied!',
                    text: 'Content copied to clipboard',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
        
        // WebBloc component preview
        function previewWebBloc(type, config) {
            const modal = new bootstrap.Modal(document.getElementById('webbloc-preview-modal'));
            document.getElementById('webbloc-preview-content').innerHTML = generateWebBlocPreview(type, config);
            modal.show();
        }
        
        function generateWebBlocPreview(type, config) {
            const templates = {
                auth: `
                    <div class="webbloc-component" data-webbloc="auth">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Authentication Component</h5>
                            <span class="badge bg-primary">${type}</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-outline-primary">Login</button>
                            <button class="btn btn-outline-secondary">Register</button>
                        </div>
                    </div>
                `,
                comments: `
                    <div class="webbloc-component" data-webbloc="comments">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Comments Component</h5>
                            <span class="badge bg-success">${type}</span>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" placeholder="Write a comment..."></textarea>
                            <div class="mt-2">
                                <button class="btn btn-primary btn-sm">Post Comment</button>
                            </div>
                        </div>
                        <div class="comment-item p-3 border rounded mb-2">
                            <strong>Sample User:</strong> This is a sample comment for preview.
                        </div>
                    </div>
                `,
                reviews: `
                    <div class="webbloc-component" data-webbloc="reviews">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Reviews Component</h5>
                            <span class="badge bg-warning">${type}</span>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="me-2">Rating:</span>
                                ${'★'.repeat(5)} 
                            </div>
                            <textarea class="form-control" placeholder="Write your review..."></textarea>
                            <div class="mt-2">
                                <button class="btn btn-warning btn-sm">Submit Review</button>
                            </div>
                        </div>
                    </div>
                `,
                notifications: `
                    <div class="webbloc-component" data-webbloc="notifications">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Notifications Component</h5>
                            <span class="badge bg-info">${type}</span>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-bell me-2"></i>Sample notification message
                        </div>
                    </div>
                `
            };
            
            return templates[type] || `<div class="webbloc-component">Unknown WebBloc type: ${type}</div>`;
        }
    </script>
    
    <!-- WebBloc Preview Modal -->
    <div class="modal fade" id="webbloc-preview-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">WebBloc Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="webbloc-preview-content">
                    <!-- Preview content will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>