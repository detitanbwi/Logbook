# UI Design System: Logbook & KPI System

## 1. Introduction

This document defines the UI Design System for the **Logbook & KPI Management System**. It establishes a consistent, professional, and accessible visual language using **Svelte 5**, **Tailwind CSS v4**, and **DaisyUI**.

The design prioritizes clarity, performance, and role-based workflows (Admin, Manager, Staff), ensuring users can intuitively navigate dashboards, submit daily logs, and manage organizational metrics.

---

## 2. Design Principles

- **Clarity Over Clutter**: Dashboards and tables must present data cleanly, minimizing cognitive load. Use generous whitespace and clear visual hierarchies.
- **Role-Centric Workflows**:
  - **Admin**: Focus on density and data management (data grids, system configurations).
  - **Manager**: Focus on review and analytical insights (split-pane views for approvals, team progress bars).
  - **Staff**: Focus on focus-driven execution (prominent Check-In/Out buttons, checklist-style task views).
- **Responsive & Progressive**: Mobile-first design optimized for Progressive Web Apps (PWA) to allow Staff to seamlessly report tasks via mobile devices on the field.
- **Status Visibility**: Distinct, color-coded indicators for the critical state machine (`DRAFT`, `SUBMITTED`, `REVIEWED`).

---

## 3. Theming & Colors (Tailwind v4 + DaisyUI)

We leverage **DaisyUI's semantic color system** mapped to **Tailwind CSS v4's** CSS-driven configuration. We use a professional aesthetic (similar to DaisyUI's `corporate` or `winter` themes) adapted for our dashboard needs.

### Tailwind v4 Configuration (`src/app.css`)

In Tailwind v4, configuration is handled directly in CSS using the `@theme` directive.

```css
@import 'tailwindcss';
@plugin 'daisyui';

/* Custom Theme Overrides for Logbook System */
@theme {
	--font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;

	/* Extending semantic colors */
	--color-status-draft: var(--color-gray-500);
	--color-status-submitted: var(--color-warning);
	--color-status-reviewed: var(--color-success);
}

/* DaisyUI Customization */
:root {
	--p: 214 32% 45%; /* Primary: Professional Blue */
	--pc: 0 0% 100%; /* Primary Content: White */
	--s: 210 16% 93%; /* Secondary: Light Gray for subtle actions */
	--sc: 215 28% 17%; /* Secondary Content: Dark Slate */
	--b1: 0 0% 100%; /* Base 100: White backgrounds */
	--b2: 210 20% 98%; /* Base 200: App shell background */
	--b3: 214 15% 91%; /* Base 300: Borders and dividers */
}
```

### Color Palette Usage

- **Primary (`bg-primary`)**: Primary actions (e.g., "Start Kerja", "Submit Logbook", "Simpan").
- **Secondary (`bg-secondary`)**: Secondary actions, table headers.
- **Success (`bg-success`)**: Approvals, High Ratings (4-5), `REVIEWED` status.
- **Warning (`bg-warning`)**: Pending actions, `SUBMITTED` status, Medium Ratings (3).
- **Error (`bg-error`)**: Deletions, Rejections, Low Ratings (1-2).
- **Base (`bg-base-100`, `bg-base-200`)**: Page backgrounds, Card backgrounds.

---

## 4. Typography

- **Primary Font**: `Inter` for exceptional legibility on data-heavy dashboards.
- **Headings**: Semi-bold, tight tracking.
- **Body**: Regular weight, optimal line height for readability.
- **Data Tables**: Tabular figures where necessary to align numbers cleanly.

---

## 5. Application Shell (Layout)

The main layout utilizes a Sidebar navigation and a Top Navbar.

### Svelte 5 Layout Architecture (`src/routes/+layout.svelte`)

Using Svelte 5's `{@render children()}` snippet instead of `<slot />`.

```svelte
<script lang="ts">
	import type { Snippet } from 'svelte';
	import Sidebar from '$lib/components/navigation/Sidebar.svelte';
	import Navbar from '$lib/components/navigation/Navbar.svelte';

	let { children }: { children: Snippet } = $props();

	// Svelte 5 rune for reactive state
	let isSidebarOpen = $state(false);
</script>

<div class="drawer min-h-screen bg-base-200 lg:drawer-open">
	<input id="app-drawer" type="checkbox" class="drawer-toggle" bind:checked={isSidebarOpen} />

	<div class="drawer-content flex flex-col items-center justify-start">
		<Navbar bind:isSidebarOpen />

		<main class="mx-auto w-full max-w-7xl p-4 md:p-6 lg:p-8">
			{@render children()}
		</main>
	</div>

	<div class="drawer-side z-40">
		<label for="app-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
		<Sidebar />
	</div>
</div>
```

---

## 6. Component Library (Svelte 5 & DaisyUI)

### 6.1. Cards

Used for dashboard widgets (KPI metrics, totals) and containing form groups.

```svelte
<!-- src/lib/components/ui/StatCard.svelte -->
<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		title,
		value,
		description,
		icon
	}: {
		title: string;
		value: string | number;
		description?: string;
		icon?: Snippet;
	} = $props();
</script>

<div class="stats border border-base-300 bg-base-100 shadow-sm">
	<div class="stat">
		{#if icon}
			<div class="stat-figure text-primary">
				{@render icon()}
			</div>
		{/if}
		<div class="stat-title">{title}</div>
		<div class="stat-value text-primary">{value}</div>
		{#if description}
			<div class="stat-desc">{description}</div>
		{/if}
	</div>
</div>
```

### 6.2. Status Badges

Consistent visual indicators for Logbook states.

```svelte
<!-- src/lib/components/ui/StatusBadge.svelte -->
<script lang="ts">
	type LogbookStatus = 'DRAFT' | 'SUBMITTED' | 'REVIEWED';
	let { status }: { status: LogbookStatus } = $props();

	let badgeClass = $derived.by(() => {
		switch (status) {
			case 'DRAFT':
				return 'badge-ghost';
			case 'SUBMITTED':
				return 'badge-warning';
			case 'REVIEWED':
				return 'badge-success';
		}
	});
</script>

<span class="badge {badgeClass} gap-1 font-medium">
	{status}
</span>
```

### 6.3. Data Tables

Used by Admin (Users, Master KPI) and Manager (Review List). Wrap DaisyUI tables in overflow containers.

```svelte
<div class="overflow-x-auto rounded-box border border-base-300 bg-base-100 shadow-sm">
	<table class="table w-full table-zebra">
		<!-- head -->
		<thead class="bg-base-200 text-base-content">
			<tr>
				<th>Tanggal</th>
				<th>Pegawai</th>
				<th>Tugas Selesai</th>
				<th>Status</th>
				<th>Aksi</th>
			</tr>
		</thead>
		<tbody>
			<!-- Iterate rows here -->
			<tr>
				<td>12 Okt 2023</td>
				<td>Budi Santoso</td>
				<td>4/5 (80%)</td>
				<td><StatusBadge status="SUBMITTED" /></td>
				<td>
					<button class="btn btn-outline btn-sm btn-primary">Review</button>
				</td>
			</tr>
		</tbody>
	</table>
</div>
```

### 6.4. Forms & Inputs

Forms should utilize DaisyUI's form controllers for proper labeling and error states.

```svelte
<!-- Example: Check-in / Start Kerja Form Context -->
<script lang="ts">
	let kpiList = $state([
		{ id: 1, name: 'Penyusunan Laporan Keuangan', done: false },
		{ id: 2, name: 'Review Dokumen Vendor', done: false }
	]);
</script>

<div class="form-control w-full max-w-md">
	<label class="label">
		<span class="label-text font-semibold">Tugas Hari Ini (KPI)</span>
	</label>

	<div class="flex flex-col gap-3 rounded-box border border-base-300 bg-base-100 p-4">
		{#each kpiList as kpi}
			<label
				class="label cursor-pointer justify-start gap-4 rounded-lg p-2 transition-colors hover:bg-base-200"
			>
				<input type="checkbox" class="checkbox checkbox-primary" bind:checked={kpi.done} />
				<span class="label-text {kpi.done ? 'text-base-content/50 line-through' : ''}">
					{kpi.name}
				</span>
			</label>
		{/each}
	</div>
</div>
```

### 6.5. Modals (Dialogs)

Use the native HTML `<dialog>` element styled with DaisyUI, controlled by Svelte 5 runes.

```svelte
<!-- src/lib/components/ui/Modal.svelte -->
<script lang="ts">
	import type { Snippet } from 'svelte';

	let {
		isOpen = $bindable(false),
		title,
		children,
		actions
	}: {
		isOpen: boolean;
		title: string;
		children: Snippet;
		actions?: Snippet;
	} = $props();

	let dialog: HTMLDialogElement;

	$effect(() => {
		if (isOpen && !dialog.open) dialog.showModal();
		else if (!isOpen && dialog.open) dialog.close();
	});

	function close() {
		isOpen = false;
	}
</script>

<dialog bind:this={dialog} class="modal modal-bottom sm:modal-middle" onclose={close}>
	<div class="modal-box">
		<h3 class="mb-4 text-lg font-bold">{title}</h3>

		<div class="py-2">
			{@render children()}
		</div>

		<div class="modal-action">
			<form method="dialog">
				<!-- if there is a button in form, it will close the modal -->
				<button class="btn btn-ghost" onclick={close}>Tutup</button>
				{#if actions}
					{@render actions()}
				{/if}
			</form>
		</div>
	</div>
	<form method="dialog" class="modal-backdrop">
		<button onclick={close}>close</button>
	</form>
</dialog>
```

### 6.6. Map / Location Display

All map and GPS location visualizations must strictly use `svelte-openlayers`.

- **Mini-Maps (Cards):** Used in Staff check-in/out summaries or Manager logbook approval cards. These should be non-interactive or lightly interactive (zoom/pan) to provide quick location context.
- **Full-Screen Maps (Modals/Dashboards):** Used in Admin monitoring dashboards or expanded Manager review modals for detailed location tracking and verification.

---

## 7. Role-Specific Guidelines

### Staff View

- **Focus**: Large, accessible buttons for Check-In ("Start Kerja") and Check-Out.
- **Mobile First**: Task lists and photo upload inputs must be easily tappable on mobile devices.
- **Feedback**: Immediate toast notifications upon successful check-in or task completion.

### Manager View

- **Focus**: Approval queues and team oversight.
- **Layout**: Use badge counters in the sidebar to indicate pending approvals (e.g., `Review (3)`).
- **Review UI**: Side-by-side or stacked card design allowing the manager to see the submitted task checklist and uploaded proof photos simultaneously before giving a Rating (1-5).

### Admin View

- **Focus**: Density and speed.
- **Layout**: Wide data tables for User Management and Master KPI.
- **Actions**: Bulk actions if necessary. Use subtle confirmation Modals before destructive actions (Soft Delete).

---

## 8. Svelte 5 & Tailwind v4 Best Practices

1. **State Management**: Use `$state()` for local reactivity and `$derived()` for computed values (e.g., deriving CSS classes based on status).
2. **Props**: Destructure props using `let { propName }: Props = $props();` instead of `export let`.
3. **Snippets**: Pass UI blocks using `{@render mySnippet()}` instead of slots for more flexible component composition.
4. **Tailwind v4 Variables**: Avoid arbitrarily hardcoded colors. Use `bg-primary`, `text-base-content`, `border-base-300` to ensure seamless Dark Mode compatibility if enabled in the future.
5. **DaisyUI Components**: Rely on semantic HTML elements configured with DaisyUI classes (e.g., `<dialog class="modal">`, `<input type="checkbox" class="toggle">`).
