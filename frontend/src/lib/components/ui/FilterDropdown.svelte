<script lang="ts">
	interface FilterOption {
		label: string;
		value: string;
	}

	interface Props {
		label: string;
		options: FilterOption[];
		value?: string;
		onChange?: (value: string) => void;
		allLabel?: string;
		class?: string;
	}

	let {
		label,
		options,
		value = $bindable(''),
		onChange,
		allLabel = 'Semua',
		class: className = ''
	}: Props = $props();

	let inputId = $derived.by(() => `filter-${label.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`);

	function handleChange(event: Event) {
		const nextValue = (event.currentTarget as HTMLSelectElement).value;
		value = nextValue;
		onChange?.(nextValue);
	}
</script>

<div class="form-control min-w-40 {className}">
	<label class="label" for={inputId}>
		<span class="label-text text-sm">{label}</span>
	</label>
	<select id={inputId} class="select select-bordered select-sm" value={value} onchange={handleChange}>
		<option value="">{allLabel}</option>
		{#each options as option (option.value)}
			<option value={option.value}>{option.label}</option>
		{/each}
	</select>
</div>
