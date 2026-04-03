<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ route('home') }}">
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

                {{-- Centre nav links --}}
                <ul class="navbar-nav mx-auto gap-1 align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('events') ? 'active' : '' }}"
                            href="{{ route('events') }}">
                            <i class="bi bi-calendar3"></i> Events
                        </a>
                    </li>

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

                        @elseif($role === 'organizer')
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

                        @elseif($role === 'student_department')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('student_department.dashboard') ? 'active' : '' }}"
                                    href="{{ route('student_department.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>

                        @elseif(in_array($role, ['adviser', 'department_head', 'dean', 'executive_director', 'student_development', 'program_chair']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('approver.dashboard') ? 'active' : '' }}"
                                    href="{{ route('approver.dashboard') }}">
                                    <i class="bi bi-ui-checks"></i> Approvals
                                </a>
                            </li>

                        @elseif($role === 'admin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                    href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}"
                                    href="{{ route('admin.users') }}">
                                    <i class="bi bi-people"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.venues*') ? 'active' : '' }}"
                                    href="{{ route('admin.venues') }}">
                                    <i class="bi bi-building"></i> Venues
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
                                    href="{{ route('admin.reports') }}">
                                    <i class="bi bi-bar-chart-line"></i> Reports
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.file-hunting*') ? 'active' : '' }}"
                                    href="{{ route('admin.file-hunting') }}">
                                    <i class="bi bi-file-earmark-person"></i> File Hunting
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.import*') ? 'active' : '' }}"
                                    href="{{ route('admin.import') }}">
                                    <i class="bi bi-upload"></i> Import
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                {{-- Right side --}}
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                    @auth
                        {{-- Notification Bell --}}
                        @php
                            $bellCount = \App\Models\AppNotification::where('user_id', Auth::id())->whereNull('read_at')->count();
                            $bellNotifs = \App\Models\AppNotification::where('user_id', Auth::id())->orderByDesc('created_at')->take(5)->get();
                        @endphp
                        <div class="dropdown">
                            <button class="btn position-relative p-2 rounded-circle"
                                style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:none;line-height:1"
                                data-bs-toggle="dropdown" aria-label="Notifications" title="Notifications">
                                <i class="bi bi-bell" style="font-size:1.05rem"></i>
                                @if($bellCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                                        style="background:var(--nu-gold);color:var(--nu-blue);font-size:.58rem;padding:2px 5px;line-height:1.3;font-weight:800">
                                        {{ $bellCount > 9 ? '9+' : $bellCount }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 notif-dropdown mt-1 p-0"
                                style="min-width:300px;border:1px solid var(--gray-200);overflow:hidden">
                                <li class="px-3 py-2" style="background:var(--nu-blue);border-radius:12px 12px 0 0">
                                    <span class="fw-700 small text-white"><i class="bi bi-bell me-1"
                                            style="color:var(--nu-gold)"></i> Notifications</span>
                                </li>
                                @forelse($bellNotifs as $nn)
                                    <li>
                                        <a class="dropdown-item py-2 px-3 border-bottom" href="#" style="white-space:normal;font-size:.82rem;color:{{ $nn->read_at ? 'var(--gray-600)' : 'var(--gray-800)' }};
                                                      font-weight:{{ $nn->read_at ? '400' : '600' }}">
                                            @if(!$nn->read_at)
                                                <span
                                                    style="color:var(--nu-gold);font-size:.6rem;vertical-align:middle">●</span>&nbsp;
                                            @endif
                                            {{ Str::limit($nn->title, 42) }}
                                            <div class="text-muted fw-400" style="font-size:.72rem">
                                                {{ $nn->created_at->diffForHumans() }}</div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-center py-4 text-muted small">No notifications</li>
                                @endforelse
                            </ul>
                        </div>

                        {{-- User Avatar Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-gold btn-sm dropdown-toggle d-flex align-items-center gap-2 px-3"
                                data-bs-toggle="dropdown" style="font-size:.83rem">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-800"
                                    style="width:22px;height:22px;background:rgba(0,48,135,.18);color:var(--nu-blue);font-size:.68rem;flex-shrink:0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="d-none d-sm-inline">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 mt-1 p-0"
                                style="min-width:215px;border:1px solid var(--gray-200);overflow:hidden">
                                <li style="background:var(--gray-50);border-radius:12px 12px 0 0">
                                    <div class="px-3 pt-3 pb-2">
                                        <div class="fw-700 small" style="color:var(--nu-blue)">{{ Auth::user()->name }}
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
                                @elseif(in_array(Auth::user()->role, ['adviser', 'department_head', 'dean', 'executive_director']))
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('approver.profile') }}"><i class="bi bi-pen"
                                                style="color:var(--nu-blue)"></i> E-Signature Profile</a></li>
                                @elseif(Auth::user()->role === 'admin')
                                    <li><a class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2"
                                            href="{{ route('admin.notifications') }}"><i class="bi bi-bell"
                                                style="color:var(--nu-blue)"></i> Send Notification</a></li>
                                @endif

                                <li>
                                    <hr class="dropdown-divider m-0">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button class="dropdown-item small py-2 px-3 d-flex align-items-center gap-2 fw-600"
                                            style="color:#dc2626">
                                            <i class="bi bi-box-arrow-right"></i> Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>

                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-gold btn-sm px-3">Log In</a>
                        <a href="{{ route('register') }}" class="btn btn-gold btn-sm px-3">Sign Up</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    @stack('scripts')
</body>

</html>