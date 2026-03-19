<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import { SearchInput, SortableHeader } from '$lib/components/ui';
	import { usersService } from '$lib/api/services/usersService';

	let { data } = $props();
	let initialLoad = $derived(data?.initialLoad ?? false);

	// State
	let team = $state<any[]>([]);
	let meta = $state<any>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	let currentPage = $derived(Number($page.url.searchParams.get('page')) || 1);
	let perPage = $derived(Number($page.url.searchParams.get('per_page')) || 15);
	let search = $derived($page.url.searchParams.get('search') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'nama');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'asc');

	async function fetchTeam() {
		loading = true;
		error = null;
		try {
			const params: Record<string, unknown> = {
				page: currentPage,
				per_page: perPage,
				role: 'Staff'
			};

			if (search) params.search = search;
			if (sortBy) params.sort_by = sortBy;
			if (sortDir) params.sort_dir = sortDir;

			const response = await usersService.getAll(params);
			team = Array.isArray(response) ? response : (response as any).data || [];
			meta = (response as any).meta || null;
		} catch (e: any) {
			console.error('Failed to fetch team', e);
			error = e.message || 'Gagal memuat data tim';
		} finally {
			loading = false;
		}
	}

	$effect(() => {
		fetchTeam();
	});

	function updateUrl(params: Record<string, string>) {
		const url = new URL($page.url);
		Object.entries(params).forEach(([key, value]) => {
			if (value) url.searchParams.set(key, value);
			else url.searchParams.delete(key);
		});
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}

	function handleSort(column: string, dir: 'asc' | 'desc') {
		updateUrl({ sort_by: column, sort_dir: dir });
	}

	function handlePageSizeChange(size: number) {
		const url = new URL($page.url);
		url.searchParams.set('per_page', size.toString());
		url.searchParams.set('page', '1');
		goto(url.toString(), { replaceState: true, noScroll: true });
	}
</script>

<svelte:head>
	<title>My Team | Manager</title>
</svelte:head>

<div class="mb-6">
	<h1 class="text-2xl font-bold">My Team</h1>
	<p class="text-base-content/70">Daftar staf yang berada di bawah pengawasan Anda.</p>
</div>

{#if error}
	<div class="alert alert-error mb-4">
		<span>{error}</span>
		<button class="btn btn-ghost btn-sm" onclick={() => fetchTeam()}>Coba Lagi</button>
	</div>
{/if}

<div class="mb-4">
	<SearchInput
		value={search}
		placeholder="Cari nama anggota tim..."
		onSearch={(v) => updateUrl({ search: v })}
		class="max-w-md"
	/>
</div>

<DataTable>
	{#snippet head()}
		<tr>
			<th>ID</th>
			<SortableHeader
				label="Nama Staf"
				column="name"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<SortableHeader
				label="Email"
				column="email"
				currentSort={sortBy}
				currentDir={sortDir}
				onSort={handleSort}
			/>
			<th>Aksi</th>
		</tr>
	{/snippet}

	{#if loading}
		{#each Array(5) as _}
			<tr>
				<td>
					<div class="h-4 w-24 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-32 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-4 w-48 animate-pulse rounded bg-base-300"></div>
				</td>
				<td>
					<div class="h-7 w-28 animate-pulse rounded bg-base-300"></div>
				</td>
			</tr>
		{/each}
	{:else if team.length === 0}
		<tr>
			<td colspan="4" class="py-4 text-center text-base-content/50"> Belum ada anggota tim. </td>
		</tr>
	{:else}
		{#each team as member}
			<tr>
				<td>{member.id}</td>
				<td class="font-medium">{member.name}</td>
				<td>{member.email}</td>
				<td>
					<a href="/manager/assign?user={member.id}" class="btn btn-sm btn-primary"> Assign KPI </a>
				</td>
			</tr>
		{/each}
	{/if}
</DataTable>

<Pagination {meta} onPageSizeChange={handlePageSizeChange} />
