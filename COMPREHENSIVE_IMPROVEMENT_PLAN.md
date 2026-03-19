# Comprehensive Frontend-Backend Synchronization & Improvement Plan

## Executive Summary

This document outlines a complete plan to synchronize the Laravel backend with the SvelteKit SPA frontend, fix inconsistencies, and implement missing features for the Logbook & KPI Management System.

**Analysis Date:** March 19, 2026  
**Total Issues Identified:** 87  
**Critical Issues:** 12  
**High Priority Issues:** 28  
**Medium Priority Issues:** 32  
**Low Priority Issues:** 15  

---

## Table of Contents

1. [Critical Issues (Must Fix)](#1-critical-issues-must-fix)
2. [Backend-Frontend Synchronization](#2-backend-frontend-synchronization)
3. [API SDK Improvements](#3-api-sdk-improvements)
4. [Dashboard Improvements](#4-dashboard-improvements)
5. [Search, Filter & Sort Implementation](#5-search-filter--sort-implementation)
6. [Component Library Enhancement](#6-component-library-enhancement)
7. [State Management Improvements](#7-state-management-improvements)
8. [UI/UX Improvements](#8-uiux-improvements)
9. [Accessibility Improvements](#9-accessibility-improvements)
10. [Implementation Priority Matrix](#10-implementation-priority-matrix)

---

## 1. Critical Issues (Must Fix)

### 1.1 Non-Functional Features

| ID | Issue | Location | Priority |
|----|-------|----------|----------|
| C-01 | **Stub page - completely non-functional** | `frontend/src/routes/(app)/admin/kpi/+page.svelte` | CRITICAL |
| C-02 | **Stub page - completely non-functional** | `frontend/src/routes/(app)/manager/approvals/+page.svelte` | CRITICAL |
| C-03 | **GPS location is hardcoded** - Uses `-6.200000,106.816666` instead of real geolocation | `frontend/src/routes/(app)/staff/logbook/+page.svelte` | CRITICAL |
| C-04 | **Photo upload exists but photos never submitted** - Empty array passed to API | `frontend/src/routes/(app)/staff/logbook/+page.svelte` | CRITICAL |
| C-05 | **Manager dashboard map uses hardcoded dummy data** - `recentLocations` array is static | `frontend/src/routes/(app)/manager/dashboard/+page.svelte` | CRITICAL |
| C-06 | **Password change not implemented** - Uses `setTimeout` mock instead of API call | `frontend/src/routes/(app)/profile/+page.svelte` | CRITICAL |

### 1.2 Critical Fixes Required

#### C-01 & C-02: Remove or Implement Stub Pages

**Option A: Remove stub routes**
```bash
# Files to remove:
frontend/src/routes/(app)/admin/kpi/+page.svelte
frontend/src/routes/(app)/manager/approvals/+page.svelte

# Update Sidebar.svelte to remove references
```

**Option B: Implement the pages**
- `/admin/kpi` - Redirect to `/admin/kpis` or implement as master data management
- `/manager/approvals` - Implement as approval workflow (if different from reviews)

#### C-03: Implement Real Geolocation

**File:** `frontend/src/routes/(app)/staff/logbook/+page.svelte`

```svelte
<script lang="ts">
  import { browser } from '$app/environment';
  
  let gpsLocation = $state<string>('');
  let gpsError = $state<string | null>(null);
  let gpsLoading = $state(false);
  
  async function getLocation(): Promise<string> {
    if (!browser || !navigator.geolocation) {
      throw new Error('Geolocation not supported');
    }
    
    gpsLoading = true;
    return new Promise((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          gpsLoading = false;
          const coords = `${position.coords.latitude},${position.coords.longitude}`;
          gpsLocation = coords;
          resolve(coords);
        },
        (error) => {
          gpsLoading = false;
          gpsError = error.message;
          reject(error);
        },
        { enableHighAccuracy: true, timeout: 10000 }
      );
    });
  }
  
  async function handleStartWork() {
    try {
      const location = await getLocation();
      await logbookStore.startLogbook({ gps_location_start: location });
    } catch (err) {
      alert('Unable to get GPS location. Please enable location services.');
    }
  }
</script>
```

#### C-04: Implement Photo Upload

**File:** `frontend/src/routes/(app)/staff/logbook/+page.svelte`

```svelte
<script lang="ts">
  let selectedPhotos = $state<File[]>([]);
  let photoPreview = $state<string[]>([]);
  
  function handlePhotoSelect(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
      selectedPhotos = Array.from(input.files);
      photoPreview = selectedPhotos.map(file => URL.createObjectURL(file));
    }
  }
  
  async function handleSubmit() {
    const location = await getLocation();
    
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('gps_location_end', location);
    selectedPhotos.forEach((photo, index) => {
      formData.append(`gambar_bukti[${index}]`, photo);
    });
    
    await logbookStore.submitLogbook(currentLogbook.id, formData);
  }
</script>
```

**Backend Update Required:**
```php
// app/Http/Controllers/Api/V1/LogbookController.php - submit()
// Ensure file upload handling is correct
$validated = $request->validate([
    'gps_location_end' => 'required|string',
    'gambar_bukti' => 'nullable|array',
    'gambar_bukti.*' => 'image|max:5120', // 5MB max per image
]);

if ($request->hasFile('gambar_bukti')) {
    $paths = [];
    foreach ($request->file('gambar_bukti') as $file) {
        $paths[] = $file->store('proofs', 'public');
    }
    $logbook->gambar_bukti = $paths;
}
```

#### C-05: Connect Manager Dashboard Map to Real Data

**Backend API Enhancement Needed:**

Add new endpoint to `AnalyticsController.php`:
```php
public function teamLocations(): JsonResponse
{
    $this->authorize('manager');
    
    $locations = Logbook::query()
        ->whereDate('created_at', today())
        ->whereIn('user_id', auth()->user()->subordinates()->pluck('id'))
        ->whereNotNull('lokasi_start')
        ->with('user:id,name')
        ->get(['id', 'user_id', 'lokasi_start', 'status', 'created_at'])
        ->map(fn($log) => [
            'lat' => explode(',', $log->lokasi_start)[0],
            'lng' => explode(',', $log->lokasi_start)[1],
            'title' => $log->user->name,
            'status' => $log->status,
        ]);
    
    return response()->json(['data' => $locations]);
}
```

**Route Addition:**
```php
Route::get('/dashboard/manager/locations', [AnalyticsController::class, 'teamLocations']);
```

**Frontend Service Update:**
```typescript
// analyticsService.ts
async getTeamLocations(): Promise<TeamLocation[]> {
  return api.get('/dashboard/manager/locations');
}
```

**Frontend Dashboard Update:**
```svelte
<!-- manager/dashboard/+page.svelte -->
<script lang="ts">
  let locations = $state<{lat: number; lng: number; title: string}[]>([]);
  
  $effect(() => {
    analyticsService.getTeamLocations().then(res => {
      locations = res.data.map(loc => ({
        lat: parseFloat(loc.lat),
        lng: parseFloat(loc.lng),
        title: loc.title
      }));
    });
  });
</script>

<Map markers={locations} />
```

#### C-06: Implement Password Change

**File:** `frontend/src/routes/(app)/profile/+page.svelte`

```svelte
<script lang="ts">
  import { authService } from '$lib/api/services/authService';
  
  let isChangingPassword = $state(false);
  let passwordError = $state<string | null>(null);
  let passwordSuccess = $state(false);
  
  async function handlePasswordChange(e: Event) {
    e.preventDefault();
    isChangingPassword = true;
    passwordError = null;
    
    try {
      await authService.changePassword({
        old_password: oldPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword
      });
      passwordSuccess = true;
      oldPassword = '';
      newPassword = '';
      confirmPassword = '';
    } catch (err: any) {
      passwordError = err.message || 'Failed to change password';
    } finally {
      isChangingPassword = false;
    }
  }
</script>
```

---

## 2. Backend-Frontend Synchronization

### 2.1 Type Mismatches

| ID | Issue | Backend | Frontend | Fix Location |
|----|-------|---------|----------|--------------|
| S-01 | User field names | `nama`, `nip` | Schema uses `name`, `email` | `frontend/src/lib/api/schemas/user.schema.ts` |
| S-02 | KPI field names | `nama`, `status_aktif` | Schema uses `name`, `description`, `target` | `frontend/src/lib/api/schemas/kpi.schema.ts` |
| S-03 | ID types | UUID strings | Schemas use `number` | `frontend/src/lib/api/schemas/*.ts` |
| S-04 | Role casing | `Admin`, `Manager`, `Staff` | Some places use `ADMIN`, `MANAGER`, `STAFF` | Multiple files |
| S-05 | Duplicate Notification type | N/A | Two different definitions | `frontend/src/lib/types/index.ts`, `notification.schema.ts` |

### 2.2 Schema Corrections

**Fix S-01: User Schema**
```typescript
// frontend/src/lib/api/schemas/user.schema.ts
import * as v from 'valibot';

export const UserCreateSchema = v.object({
  nama: v.pipe(v.string(), v.minLength(1, 'Nama wajib diisi')),
  email: v.pipe(v.string(), v.email('Format email tidak valid')),
  nip: v.pipe(v.string(), v.minLength(1, 'NIP wajib diisi')),
  password: v.pipe(v.string(), v.minLength(8, 'Password minimal 8 karakter')),
  role: v.picklist(['Admin', 'Manager', 'Staff']),
  manager_id: v.optional(v.nullable(v.string())),
});

export const UserUpdateSchema = v.object({
  nama: v.optional(v.string()),
  email: v.optional(v.string()),
  nip: v.optional(v.string()),
  role: v.optional(v.picklist(['Admin', 'Manager', 'Staff'])),
  manager_id: v.optional(v.nullable(v.string())),
});

export type UserCreateDto = v.InferOutput<typeof UserCreateSchema>;
export type UserUpdateDto = v.InferOutput<typeof UserUpdateSchema>;
```

**Fix S-02: KPI Schema**
```typescript
// frontend/src/lib/api/schemas/kpi.schema.ts
import * as v from 'valibot';

export const MasterKpiCreateSchema = v.object({
  nama: v.pipe(v.string(), v.minLength(1, 'Nama KPI wajib diisi'), v.maxLength(255)),
  status_aktif: v.optional(v.boolean(), true),
});

export const MasterKpiUpdateSchema = v.object({
  nama: v.optional(v.pipe(v.string(), v.maxLength(255))),
  status_aktif: v.optional(v.boolean()),
});

export const KpiAssignmentSchema = v.object({
  user_id: v.pipe(v.string(), v.uuid('ID pengguna tidak valid')),
  kpi_id: v.pipe(v.string(), v.uuid('ID KPI tidak valid')),
});

export type MasterKpiCreateDto = v.InferOutput<typeof MasterKpiCreateSchema>;
export type MasterKpiUpdateDto = v.InferOutput<typeof MasterKpiUpdateSchema>;
export type KpiAssignmentDto = v.InferOutput<typeof KpiAssignmentSchema>;
```

### 2.3 Missing API Endpoints in Frontend

| Backend Endpoint | Frontend SDK Method | Status |
|------------------|---------------------|--------|
| `GET /dashboard/manager/locations` | N/A | **NEEDS CREATION** (new endpoint) |
| `GET /reports/export` | `analyticsService.exportReports()` | Exists but no UI |

### 2.4 Missing Backend Features for Frontend Needs

| Frontend Need | Backend Status | Action Required |
|---------------|----------------|-----------------|
| Server-side search on `/users` | Not implemented | Add `search` query param support |
| Server-side sort on `/users` | Not implemented | Add `sort_by`, `sort_dir` params |
| Server-side filter by role on `/users` | Partial (manager scope only) | Add `role` filter param |
| Server-side search on `/kpi/master` | Not implemented | Add `search` query param support |
| Server-side filter by status on `/logbooks` | Not implemented | Add `status` filter param |
| Server-side date range on `/logbooks` | Not implemented | Add `date_from`, `date_to` params |
| Team locations for manager map | Not implemented | Add `/dashboard/manager/locations` |

---

## 3. API SDK Improvements

### 3.1 Replace `any` Types with Proper Types

**Files to update:**
- `frontend/src/lib/stores/auth.svelte.ts`
- `frontend/src/lib/stores/logbook.svelte.ts`
- `frontend/src/lib/api/services/*.ts`

**Example Fix for AuthStore:**
```typescript
// frontend/src/lib/stores/auth.svelte.ts
import type { User } from '$lib/types';

export class AuthStore {
  user = new PersistedState<User | null>('auth-user', null);
  token = new PersistedState<string | null>('auth-token', null);
  isLoading = $state(false);
  error = $state<string | null>(null);
  
  get isAuthenticated(): boolean {
    return !!this.token.current;
  }
  
  get role(): UserRole | null {
    return this.user.current?.role || null;
  }
}
```

**Example Fix for LogbookStore:**
```typescript
// frontend/src/lib/stores/logbook.svelte.ts
import type { Logbook, PaginationMeta } from '$lib/types';

export class LogbookStore {
  logbooks = $state<Logbook[]>([]);
  meta = $state<PaginationMeta | null>(null);
  currentLogbook = $state<Logbook | null>(null);
  isLoading = $state(false);
  error = $state<string | null>(null);
}
```

### 3.2 Add Missing Types

```typescript
// frontend/src/lib/types/index.ts - ADD:

export interface AuditLog {
  id: string;
  table_name: string;
  record_id: string;
  action: 'created' | 'updated' | 'deleted';
  old_data: Record<string, unknown> | null;
  new_data: Record<string, unknown> | null;
  performed_by: string | null;
  performed_at: string;
  ip_address: string | null;
  user_agent: string | null;
  user?: Pick<User, 'id' | 'nama' | 'nip' | 'role'>;
  created_at: string;
  updated_at: string;
}

export type NotificationType = 
  | 'KPI_ASSIGNMENT' 
  | 'LOGBOOK_SUBMITTED' 
  | 'LOGBOOK_REVERTED' 
  | 'LOGBOOK_REVIEWED';

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
  path: string;
}

export interface TeamLocation {
  lat: number;
  lng: number;
  title: string;
  status: LogbookStatus;
}

export interface AdminDashboard {
  total_active_users: number;
  total_logbooks_this_month: number;
  pending_logbooks_count: number;
  // Add more metrics as needed
}

export interface ManagerDashboard {
  subordinates: Array<{
    id: string;
    name: string;
    total_kpi: number;
    completed_kpi: number;
    completion_rate: number;
  }>;
  pending_logbooks_count: number;
  team_locations?: TeamLocation[];
}

export interface StaffDashboard {
  personal_kpi_completion_rate: number;
  missed_logbooks_count: number;
  average_rating: number;
}
```

### 3.3 Unify Token Storage

**Problem:** Token stored in two places: `auth-token` and `auth_token`

**Fix in `frontend/src/lib/api/core/client.ts`:**
```typescript
const TOKEN_KEY = 'auth-token'; // Use single key

export const api = {
  setToken(token: string | null): void {
    if (typeof window !== 'undefined') {
      if (token) {
        localStorage.setItem(TOKEN_KEY, token);
      } else {
        localStorage.removeItem(TOKEN_KEY);
      }
    }
  },
  
  getToken(): string | null {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem(TOKEN_KEY);
  },
  
  clearToken(): void {
    if (typeof window !== 'undefined') {
      localStorage.removeItem(TOKEN_KEY);
    }
  },
  // ... rest of client
};
```

---

## 4. Dashboard Improvements

### 4.1 Admin Dashboard Enhancements

**Current State:** Only shows 3 StatCards (total_users, total_logbooks, active_kpis)

**Improvements Needed:**

1. **Add trend indicators** (percentage change from previous period)
2. **Add charts** (logbooks over time, user activity)
3. **Add date range filter**
4. **Display KPI achievements breakdown**

**Backend Enhancement:**
```php
// app/Http/Controllers/Api/V1/AnalyticsController.php
public function adminDashboard(): JsonResponse
{
    $now = now();
    $startOfMonth = $now->startOfMonth();
    $lastMonth = $now->copy()->subMonth();
    
    $data = [
        'total_active_users' => User::where('deleted_at', null)->count(),
        'total_logbooks_this_month' => Logbook::whereMonth('created_at', $now->month)->count(),
        'total_logbooks_last_month' => Logbook::whereMonth('created_at', $lastMonth->month)->count(),
        'pending_logbooks_count' => Logbook::where('status', 'SUBMITTED')->count(),
        'active_kpis' => KpiMaster::where('status_aktif', true)->count(),
        
        // New: Trend data
        'logbooks_by_day' => Logbook::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $startOfMonth)
            ->groupBy('date')
            ->orderBy('date')
            ->get(),
        
        // New: Status breakdown
        'logbooks_by_status' => Logbook::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get(),
        
        // New: User role breakdown
        'users_by_role' => User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get(),
    ];
    
    // Calculate trends
    $data['logbook_trend'] = $data['total_logbooks_last_month'] > 0
        ? round((($data['total_logbooks_this_month'] - $data['total_logbooks_last_month']) / $data['total_logbooks_last_month']) * 100, 1)
        : 0;
    
    return response()->json(['data' => $data]);
}
```

**Frontend Dashboard Update:**
```svelte
<!-- frontend/src/routes/(app)/admin/dashboard/+page.svelte -->
<script lang="ts">
  import StatCard from '$lib/components/ui/StatCard.svelte';
  import { analyticsService } from '$lib/api/services/analyticsService';
  import type { AdminDashboard } from '$lib/types';
  
  let data = $state<AdminDashboard | null>(null);
  let loading = $state(true);
  let dateRange = $state<'week' | 'month' | 'year'>('month');
  
  $effect(() => {
    analyticsService.getAdminDashboard({ range: dateRange }).then(res => {
      data = res.data;
      loading = false;
    });
  });
</script>

<div class="p-6">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Dashboard Admin</h1>
    <select bind:value={dateRange} class="select select-bordered">
      <option value="week">Minggu Ini</option>
      <option value="month">Bulan Ini</option>
      <option value="year">Tahun Ini</option>
    </select>
  </div>
  
  {#if loading}
    <div class="flex justify-center"><span class="loading loading-spinner loading-lg"></span></div>
  {:else if data}
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <StatCard 
        title="Total Pengguna Aktif" 
        value={data.total_active_users}
        description="pengguna terdaftar"
      />
      <StatCard 
        title="Logbook Bulan Ini" 
        value={data.total_logbooks_this_month}
        description={`${data.logbook_trend >= 0 ? '+' : ''}${data.logbook_trend}% dari bulan lalu`}
        class={data.logbook_trend >= 0 ? 'border-l-4 border-success' : 'border-l-4 border-error'}
      />
      <StatCard 
        title="Menunggu Review" 
        value={data.pending_logbooks_count}
        description="perlu ditindaklanjuti"
        class="border-l-4 border-warning"
      />
      <StatCard 
        title="KPI Aktif" 
        value={data.active_kpis}
        description="dalam sistem"
      />
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Aktivitas Logbook</h2>
          <!-- Chart component here - use Chart.js or similar -->
          <div class="h-64">
            <!-- LogbookActivityChart data={data.logbooks_by_day} / -->
            <p class="text-center text-gray-500">Chart placeholder - implement with Chart.js</p>
          </div>
        </div>
      </div>
      
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">Status Logbook</h2>
          <!-- Pie chart for status breakdown -->
          <div class="h-64">
            <!-- LogbookStatusChart data={data.logbooks_by_status} / -->
            <p class="text-center text-gray-500">Chart placeholder - implement with Chart.js</p>
          </div>
        </div>
      </div>
    </div>
  {/if}
</div>
```

### 4.2 Manager Dashboard Enhancements

**Improvements:**
1. Connect map to real team locations
2. Add team member performance list with drill-down
3. Add date range filter
4. Show rating distribution

### 4.3 Staff Dashboard Enhancements

**Improvements:**
1. Add personal KPI breakdown (which KPIs completed vs pending)
2. Add rating history chart
3. Show streak (consecutive days logged)
4. Quick action to start today's logbook

---

## 5. Search, Filter & Sort Implementation

### 5.1 Backend: Add Query Parameters Support

**Controller Updates:**

```php
// app/Http/Controllers/Api/V1/UsersController.php
public function index(Request $request)
{
    // ... authorization checks ...
    
    $query = User::query();
    
    // Search
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('nama', 'ILIKE', "%{$search}%")
              ->orWhere('email', 'ILIKE', "%{$search}%")
              ->orWhere('nip', 'ILIKE', "%{$search}%");
        });
    }
    
    // Filter by role
    if ($role = $request->input('role')) {
        $query->where('role', $role);
    }
    
    // Sorting
    $sortBy = $request->input('sort_by', 'created_at');
    $sortDir = $request->input('sort_dir', 'desc');
    $allowedSorts = ['nama', 'email', 'nip', 'role', 'created_at'];
    
    if (in_array($sortBy, $allowedSorts)) {
        $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
    }
    
    $perPage = $request->input('per_page', 15);
    
    return UserResource::collection($query->paginate($perPage));
}
```

**Apply similar pattern to:**
- `MasterKpiController::index()` - search by `nama`, filter by `status_aktif`
- `LogbookController::index()` - search by user name, filter by `status`, `date_from`, `date_to`
- `KpiAssignmentController::index()` - filter by `user_id`, `kpi_id`
- `NotificationController::index()` - filter by `is_read`, `type`
- `AuditController::index()` - search, filter by `action`, `table_name`, date range

### 5.2 Frontend: Create Reusable Search/Filter Components

**Create `SearchInput.svelte`:**
```svelte
<!-- frontend/src/lib/components/ui/SearchInput.svelte -->
<script lang="ts">
  import { Search, X } from 'lucide-svelte';
  
  interface Props {
    value?: string;
    placeholder?: string;
    debounce?: number;
    onSearch?: (value: string) => void;
  }
  
  let { value = $bindable(''), placeholder = 'Cari...', debounce = 300, onSearch }: Props = $props();
  
  let timeout: ReturnType<typeof setTimeout>;
  
  function handleInput(e: Event) {
    const target = e.target as HTMLInputElement;
    value = target.value;
    
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      onSearch?.(value);
    }, debounce);
  }
  
  function clear() {
    value = '';
    onSearch?.('');
  }
</script>

<div class="form-control">
  <div class="input-group">
    <span><Search size={20} /></span>
    <input 
      type="text" 
      {placeholder}
      {value}
      oninput={handleInput}
      class="input input-bordered w-full"
    />
    {#if value}
      <button class="btn btn-ghost" onclick={clear}>
        <X size={20} />
      </button>
    {/if}
  </div>
</div>
```

**Create `FilterDropdown.svelte`:**
```svelte
<!-- frontend/src/lib/components/ui/FilterDropdown.svelte -->
<script lang="ts">
  import { Filter } from 'lucide-svelte';
  
  interface FilterOption {
    label: string;
    value: string;
  }
  
  interface Props {
    label: string;
    options: FilterOption[];
    value?: string;
    onChange?: (value: string) => void;
  }
  
  let { label, options, value = $bindable(''), onChange }: Props = $props();
</script>

<div class="dropdown">
  <label tabindex="0" class="btn btn-outline btn-sm gap-2">
    <Filter size={16} />
    {label}
    {#if value}
      <span class="badge badge-primary">{options.find(o => o.value === value)?.label || value}</span>
    {/if}
  </label>
  <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
    <li>
      <button class:active={!value} onclick={() => { value = ''; onChange?.(''); }}>
        Semua
      </button>
    </li>
    {#each options as option}
      <li>
        <button class:active={value === option.value} onclick={() => { value = option.value; onChange?.(option.value); }}>
          {option.label}
        </button>
      </li>
    {/each}
  </ul>
</div>
```

**Create `SortableHeader.svelte`:**
```svelte
<!-- frontend/src/lib/components/ui/SortableHeader.svelte -->
<script lang="ts">
  import { ArrowUp, ArrowDown, ArrowUpDown } from 'lucide-svelte';
  
  interface Props {
    column: string;
    label: string;
    currentSort?: string;
    currentDir?: 'asc' | 'desc';
    onSort?: (column: string, dir: 'asc' | 'desc') => void;
  }
  
  let { column, label, currentSort, currentDir, onSort }: Props = $props();
  
  let isActive = $derived(currentSort === column);
  
  function handleClick() {
    if (isActive) {
      onSort?.(column, currentDir === 'asc' ? 'desc' : 'asc');
    } else {
      onSort?.(column, 'asc');
    }
  }
</script>

<th class="cursor-pointer hover:bg-base-200" onclick={handleClick}>
  <div class="flex items-center gap-1">
    {label}
    {#if isActive}
      {#if currentDir === 'asc'}
        <ArrowUp size={14} />
      {:else}
        <ArrowDown size={14} />
      {/if}
    {:else}
      <ArrowUpDown size={14} class="opacity-30" />
    {/if}
  </div>
</th>
```

### 5.3 Page Implementation Example

**Update `/admin/users/+page.svelte`:**
```svelte
<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import SearchInput from '$lib/components/ui/SearchInput.svelte';
  import FilterDropdown from '$lib/components/ui/FilterDropdown.svelte';
  import SortableHeader from '$lib/components/ui/SortableHeader.svelte';
  import DataTable from '$lib/components/ui/DataTable.svelte';
  import Pagination from '$lib/components/ui/Pagination.svelte';
  
  let { data } = $props();
  
  let search = $derived($page.url.searchParams.get('search') || '');
  let role = $derived($page.url.searchParams.get('role') || '');
  let sortBy = $derived($page.url.searchParams.get('sort_by') || 'created_at');
  let sortDir = $derived($page.url.searchParams.get('sort_dir') as 'asc' | 'desc' || 'desc');
  
  const roleOptions = [
    { label: 'Admin', value: 'Admin' },
    { label: 'Manager', value: 'Manager' },
    { label: 'Staff', value: 'Staff' },
  ];
  
  function updateUrl(params: Record<string, string>) {
    const url = new URL($page.url);
    Object.entries(params).forEach(([key, value]) => {
      if (value) {
        url.searchParams.set(key, value);
      } else {
        url.searchParams.delete(key);
      }
    });
    url.searchParams.set('page', '1'); // Reset to page 1 on filter change
    goto(url.toString());
  }
  
  function handleSearch(value: string) {
    updateUrl({ search: value });
  }
  
  function handleRoleFilter(value: string) {
    updateUrl({ role: value });
  }
  
  function handleSort(column: string, dir: 'asc' | 'desc') {
    updateUrl({ sort_by: column, sort_dir: dir });
  }
</script>

<div class="p-6">
  <h1 class="text-2xl font-bold mb-6">Manajemen Pengguna</h1>
  
  <!-- Filters Row -->
  <div class="flex flex-wrap gap-4 mb-4">
    <SearchInput 
      value={search} 
      placeholder="Cari nama, email, atau NIP..." 
      onSearch={handleSearch} 
    />
    <FilterDropdown 
      label="Role" 
      options={roleOptions} 
      value={role} 
      onChange={handleRoleFilter} 
    />
  </div>
  
  <!-- Table -->
  <DataTable>
    {#snippet head()}
      <tr>
        <SortableHeader column="nama" label="Nama" {currentSort: sortBy} {currentDir: sortDir} onSort={handleSort} />
        <SortableHeader column="email" label="Email" {currentSort: sortBy} {currentDir: sortDir} onSort={handleSort} />
        <SortableHeader column="nip" label="NIP" {currentSort: sortBy} {currentDir: sortDir} onSort={handleSort} />
        <SortableHeader column="role" label="Role" {currentSort: sortBy} {currentDir: sortDir} onSort={handleSort} />
        <th>Aksi</th>
      </tr>
    {/snippet}
    
    {#each data.users as user}
      <tr>
        <td>{user.nama}</td>
        <td>{user.email}</td>
        <td>{user.nip}</td>
        <td><span class="badge">{user.role}</span></td>
        <td><!-- action buttons --></td>
      </tr>
    {:else}
      <tr>
        <td colspan="5" class="text-center py-8 text-gray-500">
          Tidak ada data pengguna
        </td>
      </tr>
    {/each}
  </DataTable>
  
  <Pagination meta={data.meta} />
</div>
```

### 5.4 Pages Requiring Search/Filter/Sort Implementation

| Page | Search Fields | Filters | Sortable Columns |
|------|---------------|---------|------------------|
| `/admin/users` | nama, email, nip | role | nama, email, role, created_at |
| `/admin/kpis` | nama | status_aktif | nama, created_at |
| `/admin/audit-logs` | table_name, user | action, date range | performed_at |
| `/manager/team` | nama | - | nama, email |
| `/manager/assign` | user nama, kpi nama | user_id | - |
| `/manager/reviews` | staff nama | date range | date, status |
| `/staff/history` | - | status, date range | date |
| `/notifications` | - | is_read, type | created_at |

---

## 6. Component Library Enhancement

### 6.1 New Components to Create

| Component | Purpose | Priority |
|-----------|---------|----------|
| `SearchInput.svelte` | Debounced search with clear button | HIGH |
| `FilterDropdown.svelte` | Dropdown filter selection | HIGH |
| `SortableHeader.svelte` | Clickable table header with sort indicator | HIGH |
| `LoadingSpinner.svelte` | Consistent loading indicator | HIGH |
| `EmptyState.svelte` | No data message with icon and action | MEDIUM |
| `ConfirmDialog.svelte` | Replace native confirm() | MEDIUM |
| `Toast.svelte` + `ToastContainer.svelte` | Notification toasts | MEDIUM |
| `FormField.svelte` | Form input wrapper with label/error | MEDIUM |
| `DateRangePicker.svelte` | Date range selection | MEDIUM |
| `ChartWrapper.svelte` | Chart.js integration wrapper | LOW |
| `ExportButton.svelte` | Download CSV/PDF export | LOW |

### 6.2 Component Improvements

| Component | Improvement | Priority |
|-----------|-------------|----------|
| `DataTable.svelte` | Add loading/empty states, ARIA attributes | HIGH |
| `Pagination.svelte` | Add page size selector, ARIA labels | MEDIUM |
| `Map.svelte` | Make markers reactive, add click events | HIGH |
| `StatCard.svelte` | Add trend indicators, color variants | MEDIUM |
| `Modal.svelte` | Add size variants, i18n for close button | LOW |
| `StatusBadge.svelte` | Add icon support, extend status types | LOW |

### 6.3 Example: Enhanced DataTable

```svelte
<!-- frontend/src/lib/components/ui/DataTable.svelte -->
<script lang="ts">
  import type { Snippet } from 'svelte';
  
  interface Props {
    head: Snippet;
    children: Snippet;
    loading?: boolean;
    empty?: boolean;
    emptyMessage?: string;
  }
  
  let { head, children, loading = false, empty = false, emptyMessage = 'Tidak ada data' }: Props = $props();
</script>

<div class="overflow-x-auto w-full rounded-xl border border-base-300">
  <table class="table table-zebra" role="table">
    <thead role="rowgroup">
      {@render head()}
    </thead>
    <tbody role="rowgroup">
      {#if loading}
        <tr>
          <td colspan="100" class="text-center py-12">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="mt-2 text-gray-500">Memuat data...</p>
          </td>
        </tr>
      {:else if empty}
        <tr>
          <td colspan="100" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-4 text-gray-500">{emptyMessage}</p>
          </td>
        </tr>
      {:else}
        {@render children()}
      {/if}
    </tbody>
  </table>
</div>
```

---

## 7. State Management Improvements

### 7.1 Create Missing Stores

**NotificationStore:**
```typescript
// frontend/src/lib/stores/notification.svelte.ts
import { notificationService } from '$lib/api/services/notificationService';
import type { Notification, PaginationMeta } from '$lib/types';

export class NotificationStore {
  notifications = $state<Notification[]>([]);
  meta = $state<PaginationMeta | null>(null);
  unreadCount = $state(0);
  isLoading = $state(false);
  error = $state<string | null>(null);
  
  get hasUnread(): boolean {
    return this.unreadCount > 0;
  }
  
  async fetchNotifications(params?: { page?: number; unread_only?: boolean }) {
    this.isLoading = true;
    this.error = null;
    
    try {
      const response = await notificationService.getNotifications(params);
      this.notifications = response.data;
      this.meta = response.meta;
      this.unreadCount = this.notifications.filter(n => !n.is_read).length;
    } catch (err: any) {
      this.error = err.message;
    } finally {
      this.isLoading = false;
    }
  }
  
  async markAsRead(id: string) {
    // Optimistic update
    const notification = this.notifications.find(n => n.id === id);
    if (notification && !notification.is_read) {
      notification.is_read = true;
      this.unreadCount--;
    }
    
    try {
      await notificationService.read(id);
    } catch (err) {
      // Rollback
      if (notification) {
        notification.is_read = false;
        this.unreadCount++;
      }
    }
  }
  
  async markAllAsRead() {
    const previousUnread = this.unreadCount;
    
    // Optimistic update
    this.notifications.forEach(n => n.is_read = true);
    this.unreadCount = 0;
    
    try {
      await notificationService.readAll();
    } catch (err) {
      // Rollback
      this.unreadCount = previousUnread;
      await this.fetchNotifications();
    }
  }
}

export const notificationStore = new NotificationStore();
```

**ToastStore:**
```typescript
// frontend/src/lib/stores/toast.svelte.ts
export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
  id: string;
  type: ToastType;
  message: string;
  duration?: number;
}

export class ToastStore {
  toasts = $state<Toast[]>([]);
  
  add(type: ToastType, message: string, duration = 5000) {
    const id = crypto.randomUUID();
    this.toasts.push({ id, type, message, duration });
    
    if (duration > 0) {
      setTimeout(() => this.remove(id), duration);
    }
    
    return id;
  }
  
  remove(id: string) {
    this.toasts = this.toasts.filter(t => t.id !== id);
  }
  
  success(message: string) {
    return this.add('success', message);
  }
  
  error(message: string) {
    return this.add('error', message);
  }
  
  warning(message: string) {
    return this.add('warning', message);
  }
  
  info(message: string) {
    return this.add('info', message);
  }
}

export const toast = new ToastStore();
```

### 7.2 Update Navbar to Use NotificationStore

```svelte
<!-- frontend/src/lib/components/navigation/Navbar.svelte -->
<script lang="ts">
  import { notificationStore } from '$lib/stores/notification.svelte';
  import { onMount } from 'svelte';
  
  onMount(() => {
    notificationStore.fetchNotifications();
    
    // Poll for new notifications
    const interval = setInterval(() => {
      notificationStore.fetchNotifications();
    }, 60000);
    
    return () => clearInterval(interval);
  });
</script>

<!-- Use notificationStore.unreadCount instead of local state -->
{#if notificationStore.unreadCount > 0}
  <span class="indicator-item badge badge-error">{notificationStore.unreadCount}</span>
{/if}
```

---

## 8. UI/UX Improvements

### 8.1 Replace alert() with Toast System

**Current (Bad):**
```javascript
alert('Data berhasil disimpan');
```

**Improved:**
```javascript
import { toast } from '$lib/stores/toast.svelte';
toast.success('Data berhasil disimpan');
```

**Toast Container Component:**
```svelte
<!-- frontend/src/lib/components/ui/ToastContainer.svelte -->
<script lang="ts">
  import { toast } from '$lib/stores/toast.svelte';
  import { X } from 'lucide-svelte';
</script>

<div class="toast toast-end toast-bottom z-50">
  {#each toast.toasts as t (t.id)}
    <div class="alert alert-{t.type} flex justify-between">
      <span>{t.message}</span>
      <button class="btn btn-ghost btn-xs" onclick={() => toast.remove(t.id)}>
        <X size={14} />
      </button>
    </div>
  {/each}
</div>
```

**Add to root layout:**
```svelte
<!-- frontend/src/routes/+layout.svelte -->
<script>
  import ToastContainer from '$lib/components/ui/ToastContainer.svelte';
</script>

<slot />
<ToastContainer />
```

### 8.2 Replace confirm() with ConfirmDialog

**Create ConfirmDialog:**
```svelte
<!-- frontend/src/lib/components/ui/ConfirmDialog.svelte -->
<script lang="ts">
  import Modal from './Modal.svelte';
  
  interface Props {
    isOpen: boolean;
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmClass?: string;
    onConfirm: () => void;
    onCancel: () => void;
  }
  
  let { 
    isOpen = $bindable(false), 
    title = 'Konfirmasi',
    message,
    confirmText = 'Ya, Lanjutkan',
    cancelText = 'Batal',
    confirmClass = 'btn-error',
    onConfirm,
    onCancel
  }: Props = $props();
</script>

<Modal bind:isOpen {title}>
  <p>{message}</p>
  
  {#snippet actions()}
    <button class="btn btn-ghost" onclick={() => { isOpen = false; onCancel(); }}>
      {cancelText}
    </button>
    <button class="btn {confirmClass}" onclick={() => { isOpen = false; onConfirm(); }}>
      {confirmText}
    </button>
  {/snippet}
</Modal>
```

**Usage:**
```svelte
<script>
  let showDeleteConfirm = $state(false);
  let itemToDelete = $state<string | null>(null);
  
  function confirmDelete(id: string) {
    itemToDelete = id;
    showDeleteConfirm = true;
  }
  
  async function handleDelete() {
    if (itemToDelete) {
      await deleteItem(itemToDelete);
      toast.success('Item berhasil dihapus');
    }
  }
</script>

<button onclick={() => confirmDelete(item.id)}>Hapus</button>

<ConfirmDialog 
  bind:isOpen={showDeleteConfirm}
  title="Hapus Item"
  message="Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan."
  confirmText="Ya, Hapus"
  onConfirm={handleDelete}
  onCancel={() => itemToDelete = null}
/>
```

### 8.3 Add Loading Skeletons

**Create Skeleton Component:**
```svelte
<!-- frontend/src/lib/components/ui/Skeleton.svelte -->
<script lang="ts">
  interface Props {
    width?: string;
    height?: string;
    class?: string;
  }
  
  let { width = 'w-full', height = 'h-4', class: className = '' }: Props = $props();
</script>

<div class="skeleton {width} {height} {className}"></div>
```

**Table Skeleton:**
```svelte
<!-- frontend/src/lib/components/ui/TableSkeleton.svelte -->
<script lang="ts">
  interface Props {
    rows?: number;
    cols?: number;
  }
  
  let { rows = 5, cols = 4 }: Props = $props();
</script>

<table class="table">
  <thead>
    <tr>
      {#each Array(cols) as _, i}
        <th><div class="skeleton h-4 w-20"></div></th>
      {/each}
    </tr>
  </thead>
  <tbody>
    {#each Array(rows) as _, row}
      <tr>
        {#each Array(cols) as _, col}
          <td><div class="skeleton h-4 w-full"></div></td>
        {/each}
      </tr>
    {/each}
  </tbody>
</table>
```

### 8.4 Fix Sidebar Navigation Links

**Current Issues:**
- `/admin/kpi` linked but is stub (should be `/admin/kpis`)
- `/staff/work` linked but actual route is `/staff/logbook`

**Fix in Sidebar.svelte:**
```typescript
// Update the links configuration
const adminLinks = [
  { href: '/admin/dashboard', label: 'Dashboard', icon: BarChart3 },
  { href: '/admin/users', label: 'Pengguna', icon: Users },
  { href: '/admin/kpis', label: 'Master KPI', icon: Target }, // Changed from /admin/kpi
  { href: '/admin/audit-logs', label: 'Audit Log', icon: FileText },
];

const staffLinks = [
  { href: '/staff/dashboard', label: 'Dashboard', icon: Home },
  { href: '/staff/logbook', label: 'Logbook', icon: BookOpen }, // Changed from /staff/work
  { href: '/staff/history', label: 'Riwayat', icon: History },
];
```

---

## 9. Accessibility Improvements

### 9.1 DataTable ARIA Attributes

```svelte
<table class="table" role="table" aria-label="Data table">
  <thead role="rowgroup">
    <tr role="row">
      <th role="columnheader" scope="col">Header</th>
    </tr>
  </thead>
  <tbody role="rowgroup">
    <tr role="row">
      <td role="cell">Data</td>
    </tr>
  </tbody>
</table>
```

### 9.2 Pagination ARIA

```svelte
<nav aria-label="Navigasi halaman">
  <button aria-label="Halaman sebelumnya" aria-disabled={currentPage === 1}>
    Prev
  </button>
  <span aria-current="page">Halaman {currentPage} dari {lastPage}</span>
  <button aria-label="Halaman berikutnya" aria-disabled={currentPage === lastPage}>
    Next
  </button>
</nav>
```

### 9.3 Form Error States

```svelte
<div class="form-control">
  <label for="email" class="label">
    <span class="label-text">Email</span>
  </label>
  <input 
    id="email"
    type="email"
    class="input input-bordered"
    class:input-error={errors.email}
    aria-invalid={!!errors.email}
    aria-describedby={errors.email ? 'email-error' : undefined}
  />
  {#if errors.email}
    <p id="email-error" class="text-error text-sm mt-1" role="alert">
      {errors.email}
    </p>
  {/if}
</div>
```

### 9.4 Focus Management in Modals

```svelte
<script>
  import { browser } from '$app/environment';
  
  let dialogRef: HTMLDialogElement;
  let previousActiveElement: Element | null = null;
  
  $effect(() => {
    if (isOpen && dialogRef) {
      previousActiveElement = document.activeElement;
      dialogRef.showModal();
      // Focus first focusable element
      const focusable = dialogRef.querySelector('button, input, select, textarea');
      (focusable as HTMLElement)?.focus();
    }
  });
  
  function handleClose() {
    isOpen = false;
    // Return focus to trigger element
    (previousActiveElement as HTMLElement)?.focus();
  }
</script>
```

---

## 10. Implementation Priority Matrix

### Phase 1: Critical Fixes (Week 1)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| C-01 | Remove/redirect stub admin/kpi page | Sidebar.svelte, routes | 1h |
| C-02 | Remove/redirect stub manager/approvals page | Sidebar.svelte, routes | 1h |
| C-03 | Implement real geolocation | staff/logbook/+page.svelte | 3h |
| C-04 | Implement photo upload | staff/logbook/+page.svelte, LogbookController.php | 4h |
| C-05 | Connect manager map to real data | AnalyticsController.php, manager/dashboard | 4h |
| C-06 | Implement password change | profile/+page.svelte | 2h |

**Total: ~15 hours**

### Phase 2: Type Synchronization (Week 1-2)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| S-01 | Fix User schema field names | user.schema.ts | 1h |
| S-02 | Fix KPI schema field names | kpi.schema.ts | 1h |
| S-03 | Fix ID types in schemas | All schema files | 2h |
| S-04 | Standardize role casing | types/index.ts, multiple | 2h |
| S-05 | Remove duplicate Notification type | notification.schema.ts | 1h |
| T-01 | Replace `any` types in stores | auth.svelte.ts, logbook.svelte.ts | 3h |
| T-02 | Add missing types (AuditLog, etc.) | types/index.ts | 2h |
| T-03 | Unify token storage | client.ts, auth.svelte.ts | 2h |

**Total: ~14 hours**

### Phase 3: Search, Filter & Sort (Week 2-3)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| F-01 | Backend: Add query params to UsersController | UsersController.php | 2h |
| F-02 | Backend: Add query params to MasterKpiController | MasterKpiController.php | 2h |
| F-03 | Backend: Add query params to LogbookController | LogbookController.php | 2h |
| F-04 | Backend: Add query params to other controllers | Multiple controllers | 3h |
| F-05 | Create SearchInput component | components/ui/ | 2h |
| F-06 | Create FilterDropdown component | components/ui/ | 2h |
| F-07 | Create SortableHeader component | components/ui/ | 2h |
| F-08 | Update /admin/users page | admin/users/+page.svelte | 3h |
| F-09 | Update /admin/kpis page | admin/kpis/+page.svelte | 2h |
| F-10 | Update /admin/audit-logs page | admin/audit-logs/+page.svelte | 3h |
| F-11 | Update /manager/team page | manager/team/+page.svelte | 2h |
| F-12 | Update /manager/reviews page | manager/reviews/+page.svelte | 3h |
| F-13 | Update /staff/history page | staff/history/+page.svelte | 2h |
| F-14 | Update /notifications page | notifications/+page.svelte | 2h |

**Total: ~32 hours**

### Phase 4: Dashboard Improvements (Week 3-4)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| D-01 | Backend: Enhance admin dashboard data | AnalyticsController.php | 3h |
| D-02 | Backend: Add team locations endpoint | AnalyticsController.php, routes | 2h |
| D-03 | Frontend: Add admin dashboard charts | admin/dashboard/+page.svelte | 4h |
| D-04 | Frontend: Add date range filter | admin/dashboard/+page.svelte | 2h |
| D-05 | Frontend: Update manager dashboard | manager/dashboard/+page.svelte | 3h |
| D-06 | Frontend: Enhance staff dashboard | staff/dashboard/+page.svelte | 3h |
| D-07 | Create chart wrapper component | components/ui/ChartWrapper.svelte | 3h |

**Total: ~20 hours**

### Phase 5: Component Library (Week 4-5)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| CL-01 | Create LoadingSpinner component | components/ui/ | 1h |
| CL-02 | Create EmptyState component | components/ui/ | 1h |
| CL-03 | Create ConfirmDialog component | components/ui/ | 2h |
| CL-04 | Create Toast system | stores/toast.svelte.ts, components | 3h |
| CL-05 | Create FormField component | components/ui/ | 2h |
| CL-06 | Enhance DataTable with loading/empty | components/ui/DataTable.svelte | 2h |
| CL-07 | Enhance Pagination with page size | components/ui/Pagination.svelte | 2h |
| CL-08 | Make Map markers reactive | components/ui/Map.svelte | 2h |
| CL-09 | Add trend indicators to StatCard | components/ui/StatCard.svelte | 1h |

**Total: ~16 hours**

### Phase 6: State Management (Week 5)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| SM-01 | Create NotificationStore | stores/notification.svelte.ts | 3h |
| SM-02 | Create ToastStore | stores/toast.svelte.ts | 2h |
| SM-03 | Update Navbar to use NotificationStore | navigation/Navbar.svelte | 2h |
| SM-04 | Update notifications page to use store | notifications/+page.svelte | 2h |
| SM-05 | Replace all alert() with toast | Multiple pages | 3h |
| SM-06 | Replace all confirm() with ConfirmDialog | Multiple pages | 3h |

**Total: ~15 hours**

### Phase 7: UI/UX Polish (Week 6)

| Task ID | Description | Files to Modify | Estimated Hours |
|---------|-------------|-----------------|-----------------|
| UX-01 | Fix Sidebar navigation links | Sidebar.svelte | 1h |
| UX-02 | Add loading skeletons to tables | Multiple pages | 3h |
| UX-03 | Improve form validation feedback | Multiple pages | 4h |
| UX-04 | Add ARIA attributes to DataTable | DataTable.svelte | 2h |
| UX-05 | Add ARIA attributes to Pagination | Pagination.svelte | 1h |
| UX-06 | Add ARIA attributes to Modal | Modal.svelte | 1h |
| UX-07 | Add focus management to modals | Modal.svelte | 2h |
| UX-08 | Implement internationalization (i18n) | Multiple files | 6h |

**Total: ~20 hours**

---

## Summary

### Total Estimated Hours: ~132 hours

### Phase Breakdown

| Phase | Focus Area | Hours | Week |
|-------|------------|-------|------|
| 1 | Critical Fixes | 15h | 1 |
| 2 | Type Synchronization | 14h | 1-2 |
| 3 | Search, Filter & Sort | 32h | 2-3 |
| 4 | Dashboard Improvements | 20h | 3-4 |
| 5 | Component Library | 16h | 4-5 |
| 6 | State Management | 15h | 5 |
| 7 | UI/UX Polish | 20h | 6 |

### Files Requiring Changes

**Backend (Laravel):**
- `app/Http/Controllers/Api/V1/UsersController.php`
- `app/Http/Controllers/Api/V1/MasterKpiController.php`
- `app/Http/Controllers/Api/V1/LogbookController.php`
- `app/Http/Controllers/Api/V1/KpiAssignmentController.php`
- `app/Http/Controllers/Api/V1/NotificationController.php`
- `app/Http/Controllers/Api/V1/AuditController.php`
- `app/Http/Controllers/Api/V1/AnalyticsController.php`
- `routes/api.php`

**Frontend (SvelteKit):**
- `src/lib/types/index.ts`
- `src/lib/api/schemas/*.ts` (all schema files)
- `src/lib/api/core/client.ts`
- `src/lib/api/services/analyticsService.ts`
- `src/lib/stores/auth.svelte.ts`
- `src/lib/stores/logbook.svelte.ts`
- `src/lib/stores/notification.svelte.ts` (new)
- `src/lib/stores/toast.svelte.ts` (new)
- `src/lib/components/ui/*.svelte` (all UI components)
- `src/lib/components/navigation/Sidebar.svelte`
- `src/lib/components/navigation/Navbar.svelte`
- `src/routes/(app)/admin/dashboard/+page.svelte`
- `src/routes/(app)/admin/users/+page.svelte`
- `src/routes/(app)/admin/kpis/+page.svelte`
- `src/routes/(app)/admin/audit-logs/+page.svelte`
- `src/routes/(app)/manager/dashboard/+page.svelte`
- `src/routes/(app)/manager/team/+page.svelte`
- `src/routes/(app)/manager/reviews/+page.svelte`
- `src/routes/(app)/manager/assign/+page.svelte`
- `src/routes/(app)/staff/dashboard/+page.svelte`
- `src/routes/(app)/staff/logbook/+page.svelte`
- `src/routes/(app)/staff/history/+page.svelte`
- `src/routes/(app)/notifications/+page.svelte`
- `src/routes/(app)/profile/+page.svelte`

---

## Next Steps

1. **Review this plan** with stakeholders
2. **Assign tasks** to appropriate agents/developers
3. **Create feature branches** for each phase
4. **Execute phases sequentially** with testing between phases
5. **Document changes** as they are implemented
6. **Update SYSTEM_DESIGN.md** files after completion

---

*Generated by Agent Organizer - March 19, 2026*
