<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import DataTable from '$lib/components/ui/DataTable.svelte';
	import Pagination from '$lib/components/ui/Pagination.svelte';
	import { SearchInput, SortableHeader } from '$lib/components/ui';

	let { data } = $props();
	let team = $derived(data.team);
	let meta = $derived(data.meta);

	let search = $derived($page.url.searchParams.get('search') || '');
	let sortBy = $derived($page.url.searchParams.get('sort_by') || 'nama');
	let sortDir = $derived(($page.url.searchParams.get('sort_dir') as 'asc' | 'desc') || 'asc');

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
</script>

<svelte:head>
	<title>My Team | Manager</title>
</svelte:head>

<div class="mb-6">
	<h1 class="text-2xl font-bold">My Team</h1>
	<p class="text-base-content/70">Daftar staf yang berada di bawah pengawasan Anda.</p>
</div>

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

	{#each team as member}
		<tr>
			<td>{member.id}</td>
			<td class="font-medium">{member.name}</td>
			<td>{member.email}</td>
			<td>
				<a href="/manager/assign?user={member.id}" class="btn btn-sm btn-primary"> Assign KPI </a>
			</td>
		</tr>
	{:else}
		<tr>
			<td colspan="4" class="py-4 text-center text-base-content/50"> Belum ada anggota tim. </td>
		</tr>
	{/each}
</DataTable>

<Pagination {meta} />
