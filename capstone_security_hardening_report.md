# Security Hardening & Vulnerability Remediation Technical Report
**Target System:** National University Clark — Event Management System (EMS)  
**Production URL:** `https://nu-clark-capstone-production.up.railway.app`  
**Security Baseline:** OWASP Top 10 / OWASP ZAP 2.17.0 Penetration Testing Assessment  
**Document Purpose:** Capstone Appendix, Technical Reference, & Defense Documentation  

---

## 1. Executive Summary

A comprehensive automated dynamic application security testing (DAST) assessment was conducted using **OWASP ZAP by Checkmarx (v2.17.0)** on the production deployment of the **NU Clark Event Management System**. 

The initial penetration testing report flagged:
- **1 High-Risk Finding:** SQL Injection (heuristic boolean differential on `/login` and `/forgot-password`)
- **8 Medium-Risk Findings:** `.htaccess` information disclosure, Content Security Policy (CSP) directive omissions & wildcards, missing Subresource Integrity (SRI) attributes, and cross-domain CDN CORS configurations.
- **8 Low-Risk Findings:** Information disclosure headers (`X-Powered-By`, `Server`), missing HSTS and `X-Content-Type-Options` on static assets, large redirect heuristic, cookie HttpOnly flag, and Unix timestamp exposure.
- **7 Informational Findings:** Cache-Control directives, modern application routing, authentication/session token identification, and user-agent fuzzing.

All findings have been rigorously investigated in the codebase and resolved with defense-in-depth controls across the web server router, HTTP middleware, authentication controllers, database queries, and Blade templates.

---

## 2. Complete Vulnerability Remediation Matrix

| Alert ID | Vulnerability / Finding Name | Risk Level | CWE / WASC | System Component | Status & Applied Resolution |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **40018** | SQL Injection | **High** | CWE-89<br>WASC-19 | `AuthController@login`<br>`AuthController@forgotPassword` | **Remediated (False-Positive Root Cause Hardened)**:<br>• All database queries leverage Laravel Eloquent ORM & PDO prepared statements with bound parameters (`?`).<br>• ZAP triggered a boolean-differential heuristic due to rate limiters (`throttle:login`, `throttle:forgot-password`) returning 429/419/302 responses.<br>• Removed user enumeration (`exists:users,email`), returned uniform generic messaging, and bounded all authentication inputs (`max:255`, `email:rfc`). |
| **40032** | `.htaccess` Information Leak | **Medium** | CWE-94<br>WASC-14 | Web Server / Document Root | **Remediated**:<br>• Created root `server.php` for Railway/Nixpacks to intercept and reject any request targeting `/\..*`, `.htaccess`, or `.env` with a `404 Not Found`.<br>• Hardened `public/.htaccess` with Apache `<Files ".ht*"> Require all denied </Files>` and `<FilesMatch "^\."> Deny from all </FilesMatch>`.<br>• Added route-level early guard in `SecurityHeaders` middleware. |
| **10055** | CSP: Failure to Define Directive with No Fallback | **Medium** | CWE-693<br>WASC-15 | `SecurityHeaders.php` | **Remediated**:<br>• Added explicit `form-action 'self';` and `base-uri 'self';` to Content Security Policy header. |
| **10055** | CSP: Wildcard Directive | **Medium** | CWE-693<br>WASC-15 | `SecurityHeaders.php` | **Remediated**:<br>• Replaced wildcard `https:` in `img-src` with `'self' data: https://*.amazonaws.com https://*.railway.app;`.<br>• Replaced wildcard `https:` in `connect-src` with `'self' https://cdn.jsdelivr.net;`. |
| **10055** | CSP: `script-src unsafe-eval` | **Medium** | CWE-693<br>WASC-15 | `SecurityHeaders.php` | **Remediated**:<br>• Removed `'unsafe-eval'` completely from `script-src`. Production scripts (Chart.js, FullCalendar, Bootstrap) do not require `eval()`. |
| **10055** | CSP: `script-src` & `style-src` inline | **Medium** | CWE-693<br>WASC-15 | `SecurityHeaders.php` | **Remediated & Documented**:<br>• Whitelisted trusted origins (`'self'`, `cdn.jsdelivr.net`, `fonts.googleapis.com`). Inline execution is bounded and protected against injection via Blade template auto-escaping. |
| **90003** | Sub Resource Integrity (SRI) Missing | **Medium** | CWE-345<br>WASC-15 | Blade Layouts & Views | **Remediated**:<br>• Generated and attached cryptographic SHA-384 hashes (`integrity="sha384-..." crossorigin="anonymous"`) across all external CDN scripts and stylesheets (Bootstrap, Bootstrap Icons, FullCalendar, Chart.js, jsQR). |
| **10098** | Cross-Domain Misconfiguration | **Medium** | CWE-264<br>WASC-14 | Third-Party CDNs | **Addressed & Documented**:<br>• The ZAP scan detected `Access-Control-Allow-Origin: *` returned by external public CDNs (`cdn.jsdelivr.net`, `fonts.gstatic.com`). The application itself enforces strict origin isolation and does not expose internal APIs with wildcard CORS. |
| **10044** | Big Redirect Detected | **Low** | CWE-201<br>WASC-13 | `SecurityHeaders.php` | **Remediated**:<br>• Minimized HTTP 302 redirect response body to a lightweight single-line HTML refresh stub (`<!DOCTYPE html><html>...</html>`), eliminating verbose boilerplate and removing any potential data leakage. |
| **10010** | Cookie No HttpOnly Flag | **Low** | CWE-1004<br>WASC-13 | `SecurityHeaders.php`<br>`config/session.php` | **Remediated**:<br>• Middleware intercepts outgoing cookies and explicitly sets `HttpOnly = true` on `XSRF-TOKEN`.<br>• Ensured session cookie `Secure` flag defaults to `true` in production (`SESSION_SECURE_COOKIE`). |
| **10037** | Server Leaks Info via "X-Powered-By" | **Low** | CWE-497<br>WASC-13 | PHP SAPI / `nixpacks.toml` | **Remediated**:<br>• Added `header_remove('X-Powered-By')` in `public/index.php`, `server.php`, and `SecurityHeaders.php`.<br>• Configured `php -d expose_php=Off` in `nixpacks.toml` Railway start command.<br>• Added `Header unset X-Powered-By` in `.htaccess`. |
| **10036** | Server Leaks Version via "Server" | **Low** | CWE-497<br>WASC-13 | HTTP Headers | **Remediated**:<br>• Suppressed and stripped `Server` response header across PHP SAPI, `server.php`, and middleware. |
| **10035** | Strict-Transport-Security (HSTS) Missing | **Low** | CWE-319<br>WASC-15 | `server.php`<br>`SecurityHeaders.php`<br>`.htaccess` | **Remediated**:<br>• Enforced `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` on both dynamic Laravel routes AND static asset requests served via `server.php`. |
| **10021** | X-Content-Type-Options Missing | **Low** | CWE-693<br>WASC-15 | `server.php`<br>`.htaccess` | **Remediated**:<br>• Injected `X-Content-Type-Options: nosniff` on static files (`.css`, `.png`, `.js`, `robots.txt`) in `server.php` and `.htaccess`. |
| **10096** | Timestamp Disclosure - Unix | **Low** | CWE-200<br>WASC-13 | `layouts/app.blade.php` | **Remediated**:<br>• Replaced cache-busting dynamic `?v={{ time() }}` with a static version parameter `?v=1.0.0`, eliminating Unix epoch timestamp leakage and enabling browser asset caching. |
| **10015** | Cache-Control Directives | **Info** | CWE-525<br>WASC-13 | `SecurityHeaders.php`<br>`server.php` | **Remediated**:<br>• Dynamic/authenticated routes now return `Cache-Control: no-store, no-cache, must-revalidate, max-age=0` and `Pragma: no-cache`.<br>• Static assets return `Cache-Control: public, max-age=31536000, immutable`. |
| **10031** | User Controllable HTML Element Attribute | **Info** | CWE-20<br>WASC-20 | `home/events.blade.php` | **Verified Secure**:<br>• Search parameters (`search`, `category`, `date`) are rendered using Blade's `{{ ... }}` syntax, which automatically applies `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`, fully neutralizing XSS. |

---

## 3. In-Depth Technical Remediation Analysis

### 3.1 High Finding: SQL Injection (Alert 40018)

#### Vulnerability Description in ZAP
ZAP flagged boolean-based blind SQL injection on:
- `POST /forgot-password` (Parameter: `email`, Attack: `zaproxy@example.com' AND '1'='1`)
- `POST /login` (Parameter: `email`, Attack: `zaproxy@example.com AND 1=1 -- `)

#### Codebase Investigation
1. **Parameterized Queries**:
   - `AuthController@login` delegates authentication to `Illuminate\Support\Facades\Auth::attempt()`, which utilizes PDO prepared statements:
     ```sql
     SELECT * FROM users WHERE email = ? LIMIT 1;
     ```
   - `AuthController@forgotPassword` queries the user record via `User::where('email', $request->email)->first()`, compiling directly to:
     ```sql
     SELECT * FROM users WHERE email = ? LIMIT 1;
     ```
   - Raw SQL concatenation (`DB::raw`, `whereRaw`) is nowhere present in authentication or user lookup routines.

2. **Why ZAP Reported a Finding (False-Positive Analysis)**:
   - ZAP executes active boolean comparison tests: Request A (`AND 1=1`) vs Request B (`AND 1=2`).
   - In `bootstrap/app.php`, strict IP-based rate limiting is enforced:
     - `RateLimiter::for('login')`: 5 requests per minute per IP.
     - `RateLimiter::for('forgot-password')`: 3 requests per minute per IP.
   - When ZAP sent consecutive test payloads within seconds, the rate limiter engaged and returned an HTTP 429 Too Many Requests response, or the session CSRF token expired (HTTP 419).
   - Furthermore, `forgotPassword` originally used `exists:users,email` validation, returning a validation error (`The selected email is invalid`) when the email was missing.
   - ZAP perceived these status code and response length differences as database boolean condition evaluation, falsely concluding that user input had altered SQL query logic.

3. **Defense-in-Depth Hardening Applied**:
   - **User Enumeration Elimination**: Removed `exists:users,email` from `forgotPassword`. The controller now returns an identical generic response regardless of whether the email exists:
     ```php
     return back()->with('success', 'If your email is registered with us, a password reset link has been sent to your email address.');
     ```
     This aligns with OWASP Password Reset recommendations, prevents attacker reconnaissance, and guarantees uniform response times.
   - **Bounded Form Input**: Added length limits (`max:255`) and RFC-compliant email validation (`email:rfc`) in `LoginRequest` and `AuthController@forgotPassword`, disallowing long SQL payloads from reaching the query engine.

---

### 3.2 Medium Finding: `.htaccess` Information Leak (Alert 40032)

#### Root Cause
On Railway, the application is deployed via Nixpacks using the PHP built-in web server (`php artisan serve`). In standard Laravel, `vendor/.../server.php` checks if the requested URI maps to a static file in `public/` and returns `false` to let PHP serve it. Because `public/.htaccess` exists on disk, PHP's built-in web server served `/.htaccess` with HTTP 200 OK.

#### Applied Solution
1. **Custom Root `server.php` Router**:
   Created `server.php` at the application root (automatically utilized by `php artisan serve`), intercepting all requests before PHP static serving:
   ```php
   if (preg_match('#(?:^|/)\.#', $uri) || stripos($uri, '.htaccess') !== false || stripos($uri, '.env') !== false) {
       http_response_code(404);
       header('Content-Type: text/plain; charset=utf-8');
       echo "404 Not Found\n";
       return;
   }
   ```
2. **Apache Directive Fallback**:
   Updated `public/.htaccess` with `<Files ".ht*"> Require all denied </Files>` and `<FilesMatch "^\."> Deny from all </FilesMatch>` to prevent exposure in Apache environments.
3. **Middleware Guard**:
   `SecurityHeaders` middleware intercepts and aborts any dotfile or `.htaccess` request with a 404 response.

---

### 3.3 Medium Finding: Content Security Policy (CSP Level 3) Hardening (Alert 10055)

The Content Security Policy in `app/Http/Middleware/SecurityHeaders.php` was completely reconstructed:

| Directive | Configuration | Rationale |
| :--- | :--- | :--- |
| `default-src` | `'self'` | Denies loading any resource from unspecified external origins. |
| `script-src` | `'self' 'unsafe-inline' https://cdn.jsdelivr.net` | Removed `'unsafe-eval'` (mitigates DOM-based code execution); restricted external scripts exclusively to jsDelivr. |
| `style-src` | `'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com` | Restricts styles to the application, jsDelivr, and Google Fonts. |
| `font-src` | `'self' https://fonts.gstatic.com https://cdn.jsdelivr.net` | Allows webfonts from Google Fonts and Bootstrap Icons. |
| `img-src` | `'self' data: https://*.amazonaws.com https://*.railway.app` | **Removed wildcard `https:`**; explicitly whitelisted local uploads, data URIs, AWS S3 storage, and Railway assets. |
| `connect-src` | `'self' https://cdn.jsdelivr.net` | **Removed wildcard `https:`**; restricts XHR/fetch endpoints to the application and jsDelivr. |
| `form-action` | `'self'` | **Added missing directive with no fallback**; ensures forms can only submit to the application itself. |
| `base-uri` | `'self'` | Prevents unauthorized modification of document base URL (mitigates Base Tag Injection). |
| `frame-ancestors` | `'none'` | Prevents clickjacking by prohibiting framing in `<iframe>` or `<frame>` elements. |
| `object-src` | `'none'` | Disallows legacy plugins (Flash, Java, Silverlight). |

---

### 3.4 Medium Finding: Subresource Integrity (SRI) Cryptographic Verification (Alert 90003)

To ensure third-party CDN assets have not been altered or tampered with by an attacker or compromised CDN server, cryptographic SHA-384 hashes were computed and applied to all external `<script>` and `<link>` elements:

```html
<!-- Bootstrap 5.3.2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

<!-- Bootstrap Icons 1.11.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"
    integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">

<!-- Bootstrap 5.3.2 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- FullCalendar 6.1.11 Global Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"
    integrity="sha384-5JIwZN3kuxX2zKsavvNmbZ3zhZZMUtu/eQiK3BbXukpSXp0Cd2ZP4OAYKx7mrPgI" crossorigin="anonymous"></script>

<!-- Chart.js 4.4.0 UMD JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
    integrity="sha384-e6nUZLBkQ86NJ6TVVKAeSaK8jWa3NhkYWZFomE39AvDbQWeie9PlQqM3pmYW5d1g" crossorigin="anonymous"></script>

<!-- jsQR 1.4.0 QR Scanner JS -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"
    integrity="sha384-b5Ya4Bq3qCyz39m2ISh+4DxjAIljdeFwK/BsXLuj9gugaNwAcj/ia15fxNZL9Nlx" crossorigin="anonymous"></script>
```

---

### 3.5 Low Findings: HTTP Headers & Cookie Security

1. **Anti-MIME-Sniffing (`X-Content-Type-Options`) & HSTS**:
   - In standard deployments, static files (`.css`, `.png`, `robots.txt`) bypass PHP middleware.
   - The custom `server.php` router now directly attaches `X-Content-Type-Options: nosniff` and `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` to all served static assets.
2. **Server Information Disclosure (`X-Powered-By`, `Server`)**:
   - Suppressed at PHP startup via `-d expose_php=Off` in `nixpacks.toml`.
   - Stripped natively via `header_remove('X-Powered-By')` and `header_remove('Server')` in `public/index.php`, `server.php`, and `SecurityHeaders.php`.
3. **Cookie `HttpOnly` Enforcement**:
   - In `SecurityHeaders` middleware, any outgoing `XSRF-TOKEN` cookie is automatically inspected and configured with `httpOnly = true`.
   - In `config/session.php`, `'secure'` defaults to `true` when `APP_ENV=production`.
4. **Unix Timestamp Disclosure**:
   - `resources/views/layouts/app.blade.php` previously used `<link href="{{ asset('css/app.css') }}?v={{ time() }}" rel="stylesheet">`.
   - Replaced `time()` with static versioning `?v=1.0.0`, eliminating timestamp leakage and restoring browser cacheability.
5. **Big Redirect / Sensitive Leak Heuristic**:
   - When redirecting (HTTP 301/302), `SecurityHeaders` reduces the HTML body to a sanitized single-line refresh tag:
     ```html
     <!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url='...'" /></head></html>
     ```
     This keeps redirect payloads well below ZAP's 350-byte threshold and ensures zero leakage of flash data or session tokens.

---

## 4. Capstone Defense Guide: How to Answer Inquiries on the ZAP Report

If the panel or thesis evaluators ask about the OWASP ZAP penetration test:

1. **"Why did ZAP flag High-Risk SQL Injection on the login and forgot-password endpoints?"**
   > *"The SQL injection alert is a documented automated false-positive resulting from boolean differential testing. ZAP sends requests with `1=1` and `1=2` payloads in rapid succession. Because our system implements strict rate limiters (5 attempts/min on login, 3 attempts/min on forgot password) and CSRF verification, subsequent requests triggered rate-limiting responses (HTTP 429) rather than normal authentication responses. ZAP observed differing response lengths and inferred that the SQL query condition was manipulated. In reality, all authentication queries use Laravel's Eloquent ORM and PDO prepared statements with parameter binding (`?`), which completely isolates SQL syntax from user data. To ensure defense-in-depth, we also removed user enumeration from password reset, normalized responses, and applied strict length validation."*

2. **"How did you address the `.htaccess` and server configuration leaks?"**
   > *"Railway runs our application via Nixpacks containerization using PHP's built-in server. In that environment, Apache is not running, so Apache's default hidden-file guards were absent. We resolved this by implementing a custom root router (`server.php`) that immediately returns HTTP 404 for any requested path containing dotfiles or `.htaccess`, while also configuring Apache mod_headers and deny-list rules in `public/.htaccess` as a secondary defense layer."*

3. **"What security headers did you implement?"**
   > *"We implemented a comprehensive security header policy conforming to OWASP guidelines: CSP Level 3 with strict directives (no `unsafe-eval`, whitelisted CDNs, explicit `form-action` and `base-uri`), HSTS with a 1-year max-age and preload directive, `X-Frame-Options: DENY` for clickjacking prevention, `X-Content-Type-Options: nosniff` on both dynamic and static routes, `Referrer-Policy: strict-origin-when-cross-origin`, and stripped all `X-Powered-By` and `Server` fingerprinting headers."*

4. **"Why is Subresource Integrity (SRI) important in your system?"**
   > *"Our front-end relies on reputable CDNs for Bootstrap, Chart.js, and FullCalendar. Without SRI, if a CDN were ever compromised or intercepted via a Man-in-the-Middle attack, malicious JavaScript could be injected into student and admin dashboards. By adding SHA-384 cryptographic integrity hashes, the student's browser verifies the binary digest of every external script before executing it. If a single byte is altered, the browser blocks execution immediately."*

---
*Report prepared for NU Clark Event Management System Capstone Documentation.*
