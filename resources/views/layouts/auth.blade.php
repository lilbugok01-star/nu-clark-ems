<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NU Clark EMS') — National University Clark</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/NU_shield.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-bg-overlay"></div>

        <!-- Left Sidebar: Official NU Branding (Large Screens Only) -->
        <div class="auth-left-branding d-none d-lg-flex">
            <div class="branding-content text-start">
                <div class="nu-brand-logo mb-4">
                    <img src="{{ asset('assets/img/NU_shield.png') }}" alt="NU Logo" style="width:140px;filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));">
                </div>
                <h1 class="nu-brand-title text-white fw-900 mb-0">NATIONAL</h1>
                <h1 class="nu-brand-title text-white fw-900 mb-2">UNIVERSITY</h1>
                <div class="nu-brand-divider mb-3"></div>
                <p class="nu-brand-tagline text-white fw-600 mb-0">Education that works.</p>
            </div>
        </div>

        <!-- Center: Auth Card Holder -->
        <div class="auth-center-content">
            <!-- Auth Card -->
            <div class="auth-center-card">
                <div class="auth-card">
                    @yield('content')
                </div>
            </div>

            <!-- Back to home link for mobile/center positioning -->
            <div class="mt-4 text-center position-relative" style="z-index:3">
                <a href="{{ route('home') }}" class="small text-decoration-none" style="color:rgba(255,255,255,.6)">
                    <i class="bi bi-arrow-left me-1"></i>Back to Home
                </a>
            </div>
        </div>

        <!-- Right Sidebar: Campus Listings (Large Screens Only) -->
        <div class="auth-right-listing d-none d-lg-flex">
            <div class="listing-content text-end">
                <ul class="campus-list list-unstyled mb-5">
                    <li>NU Manila</li>
                    <li>NU Nazareth School</li>
                    <li>NU Laguna</li>
                    <li>NU Mall of Asia</li>
                    <li>NU Fairview</li>
                    <li>NU Baliwag</li>
                    <li>NU Dasmariñas</li>
                    <li>NU Lipa</li>
                    <li>NU Clark</li>
                    <li>NU East Ortigas</li>
                    <li>NU Bacolod</li>
                    <li>NU Cebu</li>
                    <li>NU Las Piñas</li>
                </ul>
                <div class="contact-info mt-auto">
                    <small class="d-block text-white-50">https://national-u.edu.ph/nu-clark/</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>