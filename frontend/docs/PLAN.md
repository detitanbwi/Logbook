# Development Plan: Logbook & KPI Management System

## Overview

This document outlines the structured development phases for the Logbook & KPI Management System, utilizing a modern tech stack: Svelte 5 (Frontend), svelte-openlayers (Maps/GPS), and Laravel (Backend).

---

## Phase 1: Foundation & Architecture Setup

**Objective:** Establish the core boilerplate, infrastructure, and directory structures.

- **Backend (Laravel):**
  - Initialize Laravel project.
  - Configure PostgreSQL database and Redis cache.
  - Set up Authentication (Laravel Sanctum or JWT).
  - Define Migrations and Seeders based on the DBML schema (`users`, `kpi_master`, `user_kpi_assignments`, `logbooks`, `logbook_kpi_details`, `audit_logs`, `notifications`).
  - Configure CORS and base API route structure (`/api/v1`).
- **Frontend (Svelte 5):**
  - Initialize SvelteKit with TypeScript.
  - Set up Tailwind CSS v4 and DaisyUI.
  - Configure the base API client (wrapper for fetch/axios) with request/response interceptors for auth tokens and error handling.
  - Scaffold the standard directory structure (`src/lib/components`, `src/lib/states`, `src/lib/types`, `src/routes`).

---

## Phase 2: Authentication & Role Management

**Objective:** Secure the application and implement Role-Based Access Control (RBAC).

- **Backend:**
  - Implement `AuthController` (Login, Logout, Me, Change Password).
  - Implement Role Middleware (`Admin`, `Manager`, `Staff`).
- **Frontend:**
  - Create the Login Page (`/login`).
  - Implement global Auth State using Svelte 5 Runes (`$state` in `auth.svelte.ts`).
  - Set up protected route logic and redirect rules based on user roles.
  - Develop the User Profile and "Change Password" UI.

---

## Phase 3: Master Data & KPI Management (Admin/Manager)

**Objective:** Enable Admins to manage users and KPIs, and Managers to assign KPIs to Staff.

- **Backend:**
  - Develop `UsersController` for full CRUD operations, hierarchical assignment (`manager_id`), and password resets.
  - Develop `MasterKPIController` for managing the organizational KPI dictionary.
  - Develop `KPIAssignmentController` to handle the assignment of KPIs to specific users.
- **Frontend:**
  - Build Admin/Manager layout shells (Sidebar, Header).
  - Develop User Management module (Data tables, Create/Edit Modals).
  - Develop Master KPI Management UI.
  - Develop the Manager's "Assign KPI" interface.

---

## Phase 4: Core Logbook Workflow (The State Machine)

**Objective:** Implement the daily operational workflow for Staff and the review process for Managers.

- **Backend:**
  - Implement `LogbookController` handling the State Machine:
    - `POST /logbooks/start` (Creates `DRAFT`, captures GPS).
    - `PATCH /logbooks/toggle` (Updates task checklist).
    - `POST /logbooks/submit` (Transitions to `SUBMITTED`, handles image uploads).
    - `PUT /logbooks/rate` (Manager rating, transitions to `REVIEWED`).
    - `POST /logbooks/revert` (Manager rejects, returns to `DRAFT`).
- **Frontend:**
  - **Staff:**
    - Dashboard with a "Start Work" CTA (capturing Geolocation and displaying live map via `svelte-openlayers`).
    - Daily Workspace view: Checklists for active KPIs, file upload dropzone for proof, and "End Work" button.
  - **Manager:**
    - Logbook Approval UI: View submitted logs, location verification map using `svelte-openlayers`, lightbox for evidence images, rating system (1-5 stars), and Submit/Revert actions.

---

## Phase 5: Notifications & Audit Trails

**Objective:** Ensure system transparency and proactive user communication.

- **Backend:**
  - Implement Model Observers / Event Listeners to auto-populate `audit_logs` on insert/update/delete.
  - Trigger `Notification` records on state changes (e.g., Logbook submitted, Logbook reviewed, KPI assigned).
  - Develop `NotificationController` to fetch and mark notifications as read.
- **Frontend:**
  - Create a real-time (or periodically polled) Notification Bell component in the Topbar.
  - Create an Audit Logs viewer page (Admin only) to trace system mutations.

---

## Phase 6: Dashboards & Analytics

**Objective:** Provide at-a-glance metrics and reporting capabilities.

- **Backend:**
  - Develop `AnalyticsController` utilizing Redis caching for heavy aggregation queries.
  - Endpoints for Admin (system-wide stats), Manager (team performance), and Staff (personal achievement).
- **Frontend:**
  - Build role-specific Dashboard widgets using DaisyUI components (Stat cards, Progress bars) and chart libraries (e.g., Chart.js or ApexCharts).
  - Implement "KPI Achievements Monitoring" screen with date/period filters, and administrative monitoring maps using `svelte-openlayers`.

---

## Phase 7: Testing & Quality Assurance

**Objective:** Ensure reliability, data integrity, and UI/UX consistency.

- **Backend:** Feature tests (PHPUnit/Pest) covering RBAC permissions, API responses, and Logbook state transitions.
- **Frontend:** Component testing (Vitest) and End-to-End testing (Playwright) for critical user journeys (e.g., Staff Check-in to Manager Approval).
- **UAT:** Conduct User Acceptance Testing to validate location tracking, offline behaviors, and role constraints.

---

## Phase 8: Deployment & Optimization

**Objective:** Ship the application to production with high availability.

- **Infrastructure:** Dockerize Laravel (PHP-FPM/Nginx) and SvelteKit (Node adapter).
- **PWA Setup:** Add `manifest.json` and Service Workers to support offline-first capabilities (allowing staff to check-in/out during poor connectivity).
- **CI/CD:** Setup GitHub Actions/GitLab CI for automated linting, testing, and deployment.
