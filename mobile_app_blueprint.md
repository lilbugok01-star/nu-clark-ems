# NU Clark EMS Mobile App Blueprint

This document outlines the implementation strategy to create a companion mobile application for the NU Clark Event Management System (EMS). It takes advantage of your existing Laravel REST API (`routes/api.php`) to connect the mobile frontend to your database.

---

## 1. Technical Stack Recommendations

For a system already built on Laravel for the web, it is highly recommended to use a **cross-platform mobile framework** so you can deploy to both iOS and Android with a single codebase.

*   **Framework:** **React Native** (using Expo) or **Flutter**. 
    *   *Recommendation:* React Native via Expo is often the fastest to set up for teams with web (JavaScript) experience.
*   **State Management:** Redux Toolkit or Zustand (if React Native) / Riverpod (if Flutter).
*   **API Client:** Axios (React Native) or the `http` package (Flutter) to consume your Laravel endpoints using Laravel Sanctum for API token authentication.

---

## 2. API & Feature Mapping

Your Laravel backend already contains a solid set of API routes (`routes/api.php`) that the mobile app will communicate with.

### Student Role
*   **Authentication:** `/api/login`, `/api/register`, `/api/forgot-password`
*   **Event Browsing:** `/api/events/upcoming`, `/api/events/{id}`
*   **Event Registration:** `/api/events/{id}/register`, `/api/my-registrations`
*   **Event Check-In (Core Mobile Feature):** View QR Code (`/api/qrcode/{id}`) and Self Check-in (`/api/attendance/checkin`)
*   **Notifications:** `/api/notifications`

### Organizer Role
*   **Event Management:** `/api/events` (Create, Update, Delete)
*   **Verification (Core Mobile Feature):** Scan QR codes and verify attendee presence (`/api/attendance/{id}/verify`)
*   **Analytics:** `/api/dashboard/organizer`

---

## 3. Required Laravel Backend Updates (Missing API Routes)

While your `routes/web.php` has these features, your `routes/api.php` currently lacks endpoints for them. To fully replicate the system on mobile, you need to add the following to `routes/api.php`:

1.  **Venue Reservations API:** Endpoints for the Student Department to view calendar events, submit venue reservation requests, and delete them.
2.  **Approver Flow API:** Endpoints for Approvers to get pending requests, open documents, and natively approve/reject events and venues.
3.  **File Hunting API:** Endpoints to manage and view file hunting signatories.

---

## 4. Prompt for AI Generation Tools

Paste the text below into an AI agent (such as Cursor, Windsurf, v0, etc.) to immediately bootstrap your mobile application:

```text
You are an expert mobile developer specializing in React Native with Expo. I need you to build a cross-platform mobile application for the "NU Clark Event Management System (EMS)". 

The backend is built in Laravel 11. It provides a RESTful API authenticated via Laravel Sanctum bearer tokens. 

Please initialize an Expo router-based app and create the following core screens and structure:

1. **Authentication Flow**:
   - Login Screen (collects email and password).
   - Register Screen (collects name, email, password, role).
   - Store the Sanctum token securely (using expo-secure-store) upon login.

2. **Navigation Structure (Tabs)**:
   - **Home (Student)**: A dashboard showing a list of upcoming events (fetch from `/api/events/upcoming`).
   - **My Events**: A list of events the user registered for (`/api/my-registrations`). Tapping an event should show its details and a QR Code component.
   - **Scanner (Organizer)**: A screen utilizing `expo-camera` to scan QR codes for attendance verification. When a QR is scanned, make a POST request to `/api/attendance/{registration_id}/verify`.
   - **Profile**: Displays user details and a "Logout" button.

3. **Design System & Styling**:
   - Use a clean, modern aesthetic with the NU Clark branding (Blue and Gold).
   - Implement NativeWind (Tailwind CSS for React Native) or use standard StyleSheet.
   - Ensure components look premium: add subtle drop shadows, rounded corners (Border radius 12-16px), and smooth transitions.

4. **API Integration Structure**:
   - Create an `api.js` or `axios.js` utility that automatically attaches the Sanctum Bearer token to all requests.
   - Set up basic error handling (e.g., if a 401 is returned, force logout the user).

Please start by providing the `package.json` dependencies we need, the folder structure, and the code for the Authentication Flow.
```
