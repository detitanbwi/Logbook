<script lang="ts">
	import { onMount, onDestroy } from 'svelte';
	import {
		Chart,
		type ChartConfiguration,
		type ChartTypeRegistry,
		type ChartData,
		type ChartOptions,
		registerables
	} from 'chart.js';

	// Register all Chart.js components
	Chart.register(...registerables);

	interface Props {
		type: keyof ChartTypeRegistry;
		data: ChartData;
		options?: ChartOptions;
		class?: string;
	}

	let { type, data, options = {}, class: className = 'w-full h-full' }: Props = $props();

	let canvas: HTMLCanvasElement;
	let chartInstance: Chart | null = null;

	// Render or update chart when data changes
	$effect(() => {
		// Only proceed if canvas exists
		if (!canvas) return;

		if (chartInstance) {
			// Update existing chart
			chartInstance.data = data;
			if (options) chartInstance.options = options;
			chartInstance.update();
		} else {
			// Create new chart
			chartInstance = new Chart(canvas, {
				type,
				data,
				options: {
					responsive: true,
					maintainAspectRatio: false,
					...options
				}
			});
		}
	});

	onDestroy(() => {
		if (chartInstance) {
			chartInstance.destroy();
		}
	});
</script>

<div class={className}>
	<canvas bind:this={canvas}></canvas>
</div>
