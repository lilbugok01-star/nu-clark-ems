<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NU Clark EMS') — National University Clark</title>
    <meta name="description"
        content="National University Clark Event Management System — manage events, reservations, and attendance.">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/NU_shield.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ time() }}" rel="stylesheet">
    @stack('styles')
</head>

<body>

    {{-- ══ TOP NAVBAR ══════════════════════════════════════════ --}}
    <nav class="nu-navbar navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-4">

            {{-- Brand --}}
            @php
                $brandUrl = (Auth::check() && Auth::user()->role === 'admin') 
                    ? route('admin.dashboard') 
                    : route('home');
            @endphp
            <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ $brandUrl }}">
                <div class="nu-logo-wrap flex-shrink-0">
                    <img src="{{ asset('assets/img/NU_shield.png') }}" alt="NU Logo">
                </div>
                <div class="d-none d-sm-block">
                    <div class="nu-site-name">National University</div>
                    <div class="nu-campus">Clark Campus · EMS</div>
                </div>
            </a>

            {{-- Mobile toggle --}}
            <button class="navbar-toggler d-lg-none ms-auto me-3 border-0 p-1" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" style="color:rgba(255,255,255,.8)">
                <i class="bi bi-list" style="font-size:1.5rem"></i>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">

                {{-- Centre nav links (Hidden for Admin to maintain clean executive sidebar layout) --}}
                @if(!Auth::check() || Auth::user()->role !== 'admin')
                <ul class="navbar-nav navbar-center-nav mx-auto gap-1 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    @if(!Auth::check() || Auth::user()->role !== 'organizer')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('events') ? 'active' : '' }}"
                            href="{{ route('events') }}">
                            <i class="bi bi-calendar3"></i> Events
                        </a>
                    </li>
                    @endif

                    @auth
                        @php $role = Auth::user()->role; @endphp

                        @if($role === 'student')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"
                                    href="{{ route('student.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student.my-events') ? 'active' : '' }}"
                                    href="{{ route('student.my-events') }}">
                                    <i class="bi bi-ticket-perforated"></i> My Events
                                </a>
                            </li>

                        @elseif($role === 'organizer' || $role === 'student_development')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}"
                                    href="{{ route('organizer.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('organizer.events*') ? 'active' : '' }}"
                                    href="{{ route('organizer.events') }}">
                                    <i class="bi bi-calendar-plus"></i> Events
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('organizer.attendees*') ? 'active' : '' }}"
                                    href="{{ route('organizer.attendees') }}">
                                    <i class="bi bi-person-check"></i> Attendees
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('proposals.*') || request()->routeIs('proposal.*') ? 'active' : '' }}"
                                    href="{{ route('proposals.index') }}">
                                    <i class="bi bi-file-earmark-text"></i> Proposals
                                </a>
                            </li>
                            @if($role === 'student_development')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('approver.dashboard') ? 'active' : '' }}"
                                    href="{{ route('approver.dashboard') }}">
                                    <i class="bi bi-ui-checks"></i> Approvals
                                </a>
                            </li>
                            @endif

                        @elseif($role === 'student_department')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student_department.dashboard') ? 'active' : '' }}"
                                    href="{{ route('student_department.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>

                        @elseif(in_array($role, ['adviser', 'department_head', 'dean', 'executive_director', 'program_chair']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('approver.dashboard') ? 'active' : '' }}"
                                    href="{{ route('approver.dashboard') }}">
                                    <i class="bi bi-ui-checks"></i> Approvals
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                @endif

                {{-- Right side --}}
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0 ms-auto">
                    @auth
                        {{-- Notification Bell --}}
                        @php
                            $bellCount = \App\Models\AppNotification::where('user_id', Auth::id())->whereNull('read_at')->count();
                            $bellNotifs = \App\Models\AppNotification::where('user_id', Auth::id())->orderByDesc('created_at')->take(5)->get();
                        @endphp
                        <div class="dropdown" id="notifDropdown">
                            <button class="btn position-relative p-2 rounded-circle"
                                style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:none;line-height:1"
                                data-bs-toggle="dropdown" aria-label="Notifications" title="Notifications">
                                <i class="bi bi-bell" style="font-size:1.05rem"></i>
                                @if($bellCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill notif-badge"
                                        style="background:var(--nu-gold);color:var(--nu-blue);font-size:.58rem;padding:2px 5px;line-height:1.3;font-weight:800">
                                        {{ $bellCount > 9 ? '9+' : $bellCount }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-lg-end shadow-lg rounded-3 notif-dropdown mt-1 p-0"
                                style="min-width:340px;max-width:380px;border:1px solid var(--gray-200);overflow:hidden">
                                <li class="px-3 py-2 d-flex justify-content-between align-items-center" style="background:var(--nu-blue);border-radius:12px 12px 0 0">
                                    <span class="fw-700 small text-white"><i class="bi bi-bell me-1"
                                            style="color:var(--nu-gold)"></i> Notifications</span>
                                    @if($bellCount > 0)
                                        <a href="#" class="text-white small fw-600 mark-all-read-link" style="font-size:.7rem;text-decoration:underline;opacity:.85">Mark all read</a>
                                    @endif
                                </li>
                                @forelse($bellNotifs as $nn)
                                    <li>
                                        <div class="dropdown-item py-2.5 px-3 border-bottom text-wrap" style="font-size:.82rem;color:{{ $nn->read_at ? 'var(--gray-600)' : 'var(--gray-800)' }};
                                                      background:{{ $nn->read_at ? '#ffffff' : 'rgba(0,48,135,.03)' }}">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                <div class="fw-700" style="font-size:.84rem;color:var(--nu-blue)">
                                                    @if(!$nn->read_at)
                                                        <span class="notif-unread-dot me-1"
                                                            style="color:var(--nu-gold);font-size:.65rem;vertical-align:middle">●</span>
                                                    @endif
                                                    {{ $nn->title }}
                                                </div>
                                                <span class="text-muted fw-400 flex-shrink-0" style="font-size:.7rem">
                                                    {{ $nn->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            @if($nn->message)
                                                <div class="text-secondary fw-400 mt-1" style="font-size:.78rem;line-height:1.4">
                                                    {{ $nn->message }}
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center py-4 text-muted small"><i class="bi bi-bell-slash me-1"></i>No notifications</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- User Avatar Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-gold btn-sm dropdown-toggle d-flex align-items-center gap-2 px-3"
                                data-bs-toggle="dropdown" style="font-size:.83rem">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-800"
                                    style="width:22px;height:22px;background:rgba(0,48,135,.18);color:var(--nu-blue);font-size:.68rem;flex-shrink:0">
                                    {{ strtoupper(substr(Auth::user()->first_name ?? Auth::user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="d-none d-sm-inline">{{ Auth::user()->first_name ?? Auth::user()->name ?? 'User' }}</span>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-lg-end shadow-lg rounded-3 mt-1 p-0"
                                style="min-width:215px;border:1px solid var(--gray-200);overflow:hidden">
                                <li style="background:var(--gray-50);border-radius:12px 12px 0 0">
                                    <div class="px-3 pt-3 pb-2">
                                        <div class="fw-700 small" style="color:var(--nu-blue)">{{ Auth::user()->full_name }}
                                        </div>
                                        <div class="text-muted" style="font-size:.75rem">{{ Auth::user()->email }}</div>
                                        <span class="d-inline-block mt-1 px-2 py-0 rounded-pill fw-600"
                                            style="background:var(--nu-blue);color:#fff;font-size:.62rem">
                                            {{ ucfirst(Auth::user()->role) }}
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>

                                @if(Auth::user()->role === 'student')
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('student.profile') }}"><i class="bi bi-person"
                                                style="color:var(--nu-blue)"></i> Profile</a></li>
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('student.history') }}"><i class="bi bi-clock-history"
                                                style="color:var(--nu-blue)"></i> Attendance History</a></li>
                                @elseif(Auth::user()->role === 'organizer')
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('organizer.analytics') }}"><i class="bi bi-bar-chart"
                                                style="color:var(--nu-blue)"></i> Analytics</a></li>
                                @elseif(in_array(Auth::user()->role, ['adviser', 'department_head', 'dean', 'executive_director', 'student_development', 'program_chair']))
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('approver.profile') }}"><i class="bi bi-pen"
                                                style="color:var(--nu-blue)"></i> E-Signature Profile</a></li>
                                @elseif(Auth::user()->role === 'admin')
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"
                                                style="color:var(--nu-blue)"></i> Admin Dashboard</a></li>
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.events') }}"><i class="bi bi-calendar-event"
                                                style="color:var(--nu-blue)"></i> Manage Events</a></li>
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.venues') }}"><i class="bi bi-building"
                                                style="color:var(--nu-blue)"></i> Venue Management</a></li>
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.users') }}"><i class="bi bi-people"
                                                style="color:var(--nu-blue)"></i> User Management</a></li>
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.notifications') }}"><i class="bi bi-bell"
                                                style="color:var(--nu-blue)"></i> Send Notification</a></li>
                                @endif

                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>
                                <li>
                                    <button class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 fw-600"
                                        style="color:#dc2626" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                                        <i class="bi bi-box-arrow-right"></i> Sign Out
                                    </button>
                                </li>
                            </ul>
                        </div>

                    @else
                        <a href="{{ route('login') }}" class="btn btn-gold btn-sm px-3">Log In</a>
                    @endauth
                </div>{{-- end right side --}}

            </div>{{-- end collapse --}}
        </div>{{-- end container --}}
    </nav>

    {{-- ══ FLASH MESSAGES ═══════════════════════════════════════ --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
        <div class="mx-4 mt-3">
            @if(session('success'))
                <div class="alert d-flex align-items-center gap-2 mb-2 rounded-3 py-2 px-3"
                    style="background:#dcfce7;border:1px solid #86efac;color:#166534;font-size:.875rem">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close ms-auto btn-sm" data-bs-dismiss="alert"
                        style="font-size:.7rem"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-2 rounded-3 py-2 px-3"
                    style="font-size:.875rem">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="btn-close ms-auto btn-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-2 rounded-3 py-2 px-3"
                    style="font-size:.875rem">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="btn-close ms-auto btn-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info d-flex align-items-center gap-2 mb-2 rounded-3 py-2 px-3"
                    style="font-size:.875rem">
                    <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                    <span>{{ session('info') }}</span>
                    <button type="button" class="btn-close ms-auto btn-sm" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="mx-4 mt-3">
            <div class="alert alert-danger d-flex align-items-start gap-2 mb-2 rounded-3 py-2 px-3"
                style="font-size:.875rem">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                <div>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close ms-auto btn-sm mt-1" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    {{-- ══ MAIN ══════════════════════════════════════════════════ --}}
    <main style="flex:1;min-height:calc(100vh - 280px)">
        @yield('content')
    </main>

    {{-- ══ FOOTER ════════════════════════════════════════════════ --}}
    <footer class="nu-footer">
        {{-- Top footer band --}}
        <div style="border-top:3px solid var(--nu-gold)"></div>
        <div class="py-5 px-4">
            <div class="container-fluid">
                <div class="row gy-4">

                    {{-- Brand Column --}}
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="nu-logo-wrap" style="width:50px;height:50px;border-radius:0;background:none;box-shadow:none">
                                <img src="{{ asset('assets/img/NU_shield.png') }}" alt="NU Logo">
                            </div>
                            <div>
                                <div class="text-white fw-700" style="font-size:1rem;line-height:1.2">National University</div>
                                <div style="color:#ffffff;font-size:.72rem;font-weight:600;letter-spacing:.06em">CLARK CAMPUS · EMS</div>
                            </div>
                        </div>
                        <p class="footer-description" style="color:#ffffff !important">
                            The official Event Management System of National University Clark. Dedicated to streamlining campus activities and student engagement.
                        </p>
                        <div class="d-flex gap-2 mt-4">
                            <a href="https://www.facebook.com/NationalUniversityClark" target="_blank" class="footer-social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/nationaluniversityclark" target="_blank" class="footer-social-link">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </div>
                    </div>

                    {{-- System Features --}}
                    <div class="col-lg-3 col-md-6">
                        <h6 class="footer-heading" style="color:#ffffff !important">System Features</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><span class="footer-link-static d-flex align-items-center" style="color:#ffffff !important;font-size:.82rem;"><i class="bi bi-qr-code-scan me-2 text-gold"></i>QR Attendance</span></li>
                            <li class="mb-2"><span class="footer-link-static d-flex align-items-center" style="color:#ffffff !important;font-size:.82rem;"><i class="bi bi-file-earmark-check me-2 text-gold"></i>File Hunting Approvals</span></li>
                            <li class="mb-2"><span class="footer-link-static d-flex align-items-center" style="color:#ffffff !important;font-size:.82rem;"><i class="bi bi-building me-2 text-gold"></i>Venue Reservations</span></li>
                            <li class="mb-2"><span class="footer-link-static d-flex align-items-center" style="color:#ffffff !important;font-size:.82rem;"><i class="bi bi-bar-chart-line me-2 text-gold"></i>Real-time Analytics</span></li>
                        </ul>
                    </div>

                    {{-- User Portals --}}
                    <div class="col-lg-3 col-md-6">
                        <h6 class="footer-heading" style="color:#ffffff !important">Quick Access</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><a href="{{ route('login') }}" class="footer-link-new" style="color:#ffffff !important;"><i class="bi bi-mortarboard me-2"></i>Student Portal</a></li>
                            <li class="mb-2"><a href="{{ route('login') }}" class="footer-link-new" style="color:#ffffff !important;"><i class="bi bi-person-badge me-2"></i>Organizer Access</a></li>
                            <li class="mb-2"><a href="{{ route('login') }}" class="footer-link-new" style="color:#ffffff !important;"><i class="bi bi-shield-check me-2"></i>Approver Login</a></li>
                            <li class="mb-2"><a href="{{ route('home') }}" class="footer-link-new" style="color:#ffffff !important;"><i class="bi bi-house me-2"></i>Back to Home</a></li>
                        </ul>
                    </div>


                    {{-- Contact & Hours --}}
                    <div class="col-lg-3 col-md-6">
                        <h6 class="footer-heading" style="color:#ffffff !important">Contact Us</h6>
                        <div class="mb-4">
                            <div class="d-flex gap-2 text-white">
                                <i class="bi bi-geo-alt-fill text-white flex-shrink-0 mt-1"></i>
                                <span style="font-size:.82rem;line-height:1.5;color:#ffffff">
                                    Clark Tech Hub 8, SM City Clark Expansion, Clark-Mabalacat-Angeles Road, Barangay Dau, Mabalacat City, Pampanga, 2010 Philippines
                                </span>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-white fw-600 mb-2" style="font-size:.78rem;letter-spacing:.03em;color:#ffffff">Office Hours</h6>
                            <div class="d-flex gap-2 text-white">
                                <i class="bi bi-clock-fill text-white flex-shrink-0 mt-1"></i>
                                <div style="font-size:.82rem;line-height:1.5;color:#ffffff">
                                    Monday to Friday (8:30AM - 5:30PM)<br>
                                    Saturday (8:30AM - 12:30PM)
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- end row --}}
            </div>{{-- end container --}}
        </div>

        {{-- Bottom strip --}}
        <div style="border-top:1px solid rgba(255,255,255,.1);padding:.9rem 1.5rem">
            <div class="container-fluid">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span style="color:rgba(255,255,255,.8);font-size:.78rem">
                        &copy; {{ date('Y') }} National University Clark. All rights reserved.
                    </span>
                    <span style="color:rgba(255,255,255,.65);font-size:.72rem">
                        NU Clark Event Management System · Built with Laravel
                    </span>
                </div>
            </div>
        </div>
    </footer>

    {{-- Footer link styles have been moved to public/css/app.css --}}

    @auth
    {{-- ══ LOGOUT CONFIRMATION MODAL ══════════════════════════════════ --}}
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:var(--radius-lg,16px);overflow:hidden;border:none">
                <div style="background:linear-gradient(135deg,var(--nu-blue-dk,#001d50),var(--nu-blue,#003087));padding:1.5rem;text-align:center">
                    <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle"
                         style="width:56px;height:56px;background:rgba(255,184,0,.2)">
                        <i class="bi bi-box-arrow-right" style="color:var(--nu-gold,#FFB800);font-size:1.5rem"></i>
                    </div>
                    <h6 class="text-white fw-800 mb-0" id="logoutConfirmLabel">Sign Out</h6>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0 text-muted" style="font-size:.92rem">Are you sure you want to sign out of your account?</p>
                </div>
                <div class="modal-footer justify-content-center gap-2 border-0 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0">
                        @csrf
                        <button type="submit" class="btn btn-sm px-4 rounded-pill fw-700" style="background:#dc2626;color:#fff">
                            <i class="bi bi-box-arrow-right me-1"></i>Yes, Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    {{-- ══ CALENDAR EVENT DETAIL MODAL (available to all, including guests) ═══ --}}
    <div class="modal fade" id="calendarEventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--radius-lg,16px);overflow:hidden;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
                <div id="calModalHeader" style="background:linear-gradient(135deg,var(--nu-blue-dk,#001d50),var(--nu-blue,#003087));padding:1.5rem 1.5rem 1.2rem">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span id="calModalBadge" class="badge mb-2" style="font-size:.68rem"></span>
                            <h5 id="calModalTitle" class="text-white fw-800 mb-0" style="line-height:1.3"></h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="margin:-4px -4px 0 12px"></button>
                    </div>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(0,48,135,.08);flex-shrink:0">
                                <i class="bi bi-calendar-event" style="color:var(--nu-blue);font-size:1.1rem"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.72rem;font-weight:600">DATE & TIME</div>
                                <div id="calModalDate" class="fw-600" style="font-size:.9rem;color:var(--gray-800,#1f2937)"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(0,48,135,.08);flex-shrink:0">
                                <i class="bi bi-geo-alt" style="color:var(--nu-blue);font-size:1.1rem"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.72rem;font-weight:600">VENUE</div>
                                <div id="calModalVenue" class="fw-600" style="font-size:.9rem;color:var(--gray-800,#1f2937)"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(0,48,135,.08);flex-shrink:0">
                                <i class="bi bi-info-circle" style="color:var(--nu-blue);font-size:1.1rem"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.72rem;font-weight:600">STATUS</div>
                                <div id="calModalStatus" class="fw-600" style="font-size:.9rem"></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3" id="calModalCategoryWrap" style="display:none!important">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:rgba(0,48,135,.08);flex-shrink:0">
                                <i class="bi bi-tag" style="color:var(--nu-blue);font-size:1.1rem"></i>
                            </div>
                            <div>
                                <div class="text-muted" style="font-size:.72rem;font-weight:600">CATEGORY</div>
                                <div id="calModalCategory" class="fw-600" style="font-size:.9rem;color:var(--gray-800,#1f2937)"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                    <a id="calModalViewBtn" href="#" class="btn btn-nu-blue btn-sm rounded-pill px-3 fw-700" style="display:none">
                        <i class="bi bi-eye me-1"></i>View Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>

    {{-- Global calendar event click handler --}}
    <script>
    function showCalendarEventModal(info) {
        var e = info.event;
        var p = e.extendedProps || {};
        var isEvent = (p.type === 'event');

        // Title
        document.getElementById('calModalTitle').textContent = e.title;

        // Badge
        var badge = document.getElementById('calModalBadge');
        if (isEvent) {
            badge.textContent = 'Published Event';
            badge.style.background = 'rgba(255,184,0,.25)';
            badge.style.color = '#FFB800';
        } else {
            badge.textContent = (p.status === 'approved' ? 'Approved Venue' : 'Pending Venue');
            badge.style.background = p.status === 'approved' ? 'rgba(40,167,69,.2)' : 'rgba(255,193,7,.25)';
            badge.style.color = p.status === 'approved' ? '#28a745' : '#c59100';
        }

        // Date & Time
        var start = e.start;
        var end = e.end;
        var dateStr = start ? start.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) : 'N/A';
        var timeStr = '';
        if (start) {
            timeStr = start.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });
            if (end) timeStr += ' — ' + end.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });
        }
        document.getElementById('calModalDate').textContent = dateStr + (timeStr ? '  •  ' + timeStr : '');

        // Venue
        document.getElementById('calModalVenue').textContent = p.venue || 'Not specified';

        // Status
        var statusEl = document.getElementById('calModalStatus');
        var statusText = (p.status || 'N/A').replace(/_/g, ' ');
        statusText = statusText.charAt(0).toUpperCase() + statusText.slice(1);
        statusEl.textContent = statusText;
        statusEl.style.color = p.status === 'published' ? '#003087' : (p.status === 'approved' ? '#28a745' : '#c59100');

        // Category
        var catWrap = document.getElementById('calModalCategoryWrap');
        if (p.category) {
            catWrap.style.display = 'flex';
            catWrap.classList.add('d-flex');
            document.getElementById('calModalCategory').textContent = p.category;
        } else {
            catWrap.style.display = 'none';
            catWrap.classList.remove('d-flex');
        }

        // View button — only for published events
        var viewBtn = document.getElementById('calModalViewBtn');
        if (isEvent) {
            var eventId = String(e.id).replace('event-', '');
            viewBtn.href = '/events/' + eventId;
            viewBtn.style.display = '';
        } else {
            viewBtn.style.display = 'none';
        }

        new bootstrap.Modal(document.getElementById('calendarEventModal')).show();
    }
    </script>
    @stack('scripts')

    @auth
    <script>
    (function(){
        function markAllRead() {
            fetch("{{ route('notifications.markRead') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            }).then(function(){
                // Hide badge
                var badge = document.querySelector('.notif-badge');
                if (badge) badge.style.display = 'none';
                // Remove unread dots
                document.querySelectorAll('.notif-unread-dot').forEach(function(d){ d.remove(); });
                // Hide "Mark all read" link
                var link = document.querySelector('.mark-all-read-link');
                if (link) link.style.display = 'none';
                // Reset font-weight on notification items
                document.querySelectorAll('#notifDropdown .dropdown-item').forEach(function(el){
                    el.style.fontWeight = '400';
                    el.style.color = 'var(--gray-600)';
                });
            });
        }

        // Mark as read when dropdown opens
        var notifDropdown = document.getElementById('notifDropdown');
        if (notifDropdown) {
            var btn = notifDropdown.querySelector('[data-bs-toggle="dropdown"]');
            if (btn) {
                btn.addEventListener('shown.bs.dropdown', function() {
                    @if($bellCount > 0) markAllRead(); @endif
                });
            }
        }

        // Also handle "Mark all read" link click
        var markLink = document.querySelector('.mark-all-read-link');
        if (markLink) {
            markLink.addEventListener('click', function(e) {
                e.preventDefault();
                markAllRead();
            });
        }
    })();
    </script>
    <style>
        /* Global FullCalendar Button Adjustments */
        .fc .fc-button-group > .fc-button {
            margin-right: 4px !important;
            border-radius: 6px !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active, 
        .fc .fc-button-primary:not(:disabled):active {
            background-color: var(--nu-gold) !important;
            border-color: var(--nu-gold) !important;
            color: var(--nu-blue) !important;
        }
        .fc-today-button {
            margin-left: 8px !important;
        }
    </style>
    @endauth
</body>

</html>