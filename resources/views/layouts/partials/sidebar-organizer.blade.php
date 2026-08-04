<div class="dashboard-sidebar rounded-xl mb-4">
    <div class="text-white-50 small text-uppercase fw-semibold mb-3 ps-2" style="letter-spacing:1px">Organizer</div>
    <a href="{{ route('organizer.dashboard') }}" class="sidebar-link @if(request()->routeIs('organizer.dashboard')) active @endif">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('organizer.events') }}" class="sidebar-link @if(request()->routeIs('organizer.events*') && !request()->routeIs('organizer.event.create')) active @endif">
        <i class="bi bi-calendar3"></i> My Events
    </a>
    <a href="{{ route('organizer.event.create') }}" class="sidebar-link @if(request()->routeIs('organizer.event.create')) active @endif">
        <i class="bi bi-plus-circle"></i> New Event
    </a>
    <a href="{{ route('organizer.attendees') }}" class="sidebar-link @if(request()->routeIs('organizer.attendees*')) active @endif">
        <i class="bi bi-people"></i> Attendees
    </a>
    <a href="{{ route('organizer.analytics') }}" class="sidebar-link @if(request()->routeIs('organizer.analytics*')) active @endif">
        <i class="bi bi-bar-chart"></i> Analytics
    </a>
    <hr style="border-color:rgba(255,255,255,0.1)">
    <button type="button" class="sidebar-link w-100 border-0 bg-transparent text-start" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
        <i class="bi bi-box-arrow-right"></i> Logout
    </button>
</div>
