# Frontend Implementation Plan

Based on `PLAN.md` and our completed Phase 1 (API SDK & State Management), this document outlines the granular step-by-step implementation for the UI components and pages using Svelte 5, Tailwind CSS v4, and DaisyUI.

## 🏗️ Phase 2: Authentication & Layouts (Up Next)

**Goal:** Build the authentication flow, route protection, and main application layouts.

- [x] **Components & Shell:**
  - Create Auth Layout (`src/routes/(auth)/+layout.svelte`) - Simple centered card layout.
  - Create App Layout (`src/routes/(app)/+layout.svelte`) - Implements Sidebar and Navbar as per `UI_DESIGN_SYSTEM.md`.
  - Finalize `Sidebar.svelte` (Dynamic links based on Role: Admin, Manager, Staff).
  - Create `Navbar.svelte` (User profile dropdown, Notification bell).
- [x] **Pages:**
  - Login Page (`/login` or `/auth/login`): Bind to `authStore.login()`, handle errors, redirect on success.
  - Profile Page (`/profile`): Show user details and "Change Password" form.
- [x] **Route Protection:**
  - Implement SvelteKit load functions in `+layout.ts`/`+page.ts` (or `+layout.svelte` via runes) to redirect unauthenticated users to `/login` and role-restricted users away from unauthorized routes.

## 👥 Phase 3: Master Data (Admin & Manager) (COMPLETED)

**Goal:** Admin CRUD interfaces for Users/KPIs and Manager assignment screens.

- [x] **Components:**
  - Reusable `DataTable.svelte` wrapper.
  - Reusable `Modal.svelte` for forms.
  - Reusable `StatusBadge.svelte`.
- [x] **Admin Pages:**
  - Master KPI List (`/admin/kpis`): View, Create, Edit.
  - User Management (`/admin/users`): View paginated users, Create (Assign Role), Edit, Deactivate.
- [x] **Manager Pages:**
  - Team List (`/manager/team`): View subordinate staff.
  - KPI Assignment (`/manager/assign`): Select Staff, select KPI, and Assign.

## 📝 Phase 4: Core Logbook Workflow (Staff & Manager) (COMPLETED)

**Goal:** The daily state machine (Draft -> Submitted -> Reviewed).

- [x] **Staff Pages:**
  - **Start Work:** Screen with `svelte-openlayers` map capturing current GPS. Button "Start Kerja" creates `DRAFT` logbook.
  - **Active Logbook (Workspace):** Checklist of KPIs. Reactive integration with `logbookStore.toggleKpi()`.
  - **Submit Work:** Image upload dropzone, capture end GPS, submit to Manager.
  - **History:** List of past logbooks and their statuses.
- [x] **Manager Pages:**
  - **Pending Reviews (`/manager/reviews`):** List of logbooks pending review (`SUBMITTED`).
  - **Review Detail Modal/Page:** View Staff GPS on map, view uploaded proof photos, Checklists done.
  - **Rating Action:** 1-5 Star rating input (Submit) or Revert to Draft button.

## 🔔 Phase 5: Notifications & Audit (Global & Admin) (COMPLETED)

**Goal:** Expose system notifications and audit trails.

- [x] **Components:**
  - Dropdown Notification list in Navbar.
  - Real-time polling or periodic fetch from `notificationService`.
- [x] **Pages:**
  - All Notifications Page (`/notifications`): Full list, mark all as read.
  - Audit Logs (`/admin/audit-logs`): Admin-only table tracking system actions.

## 📊 Phase 6: Dashboards & Analytics (COMPLETED)

**Goal:** Visual overviews based on roles.

- [x] **Components:**
  - `StatCard.svelte`
  - Integration with Chart.js or similar for charts (Optional/Later).
- [x] **Dashboards (`/dashboard`):**
  - **Staff:** Today's progress, monthly KPI completion rate.
  - **Manager:** Team completion rate, pending review count.
  - **Admin:** Total active users, system usage metrics.

---

## 🤖 Execution Protocol for Subagents

When a subagent is assigned a Phase or Task:

1. **Read `UI_DESIGN_SYSTEM.md`**: Strictly follow DaisyUI classes and Svelte 5 Snippets.
2. **Use API SDK**: Connect UI directly to `src/lib/api/services/` and `src/lib/stores/`. Do not write raw `fetch` calls.
3. **Use Runes**: Use `$state`, `$derived`, `$effect`, and `$props()` exclusively.
4. **Update PROGRESS**: Check off the items in `docs/PROGRESS.md` once complete.
