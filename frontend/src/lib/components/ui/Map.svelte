<script lang="ts">
	import { onMount } from 'svelte';
	import type { Map as LeafletMap, Marker } from 'leaflet';
	import 'leaflet/dist/leaflet.css';

	let {
		lat = -6.2,
		lng = 106.816666,
		zoom = 13,
		markers = []
	}: {
		lat?: number;
		lng?: number;
		zoom?: number;
		markers?: Array<{ lat: number; lng: number; title?: string }>;
	} = $props();

	let mapElement: HTMLDivElement;
	let map: LeafletMap | null = $state(null);
	let leaflet: any = null;
	let currentMarkers: Marker[] = [];

	onMount(() => {
		let isMounted = true;
		import('leaflet').then((L) => {
			if (!isMounted) return;
			leaflet = L;

			// Fix default icon path issues in leaflet with webpack/vite
			delete (L.Icon.Default.prototype as any)._getIconUrl;
			L.Icon.Default.mergeOptions({
				iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
				iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
				shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png'
			});

			const m = L.map(mapElement).setView([lat, lng], zoom);

			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; OpenStreetMap contributors'
			}).addTo(m);

			map = m;
			updateMarkers(markers);
		});

		return () => {
			isMounted = false;
			if (map) {
				map.remove();
			}
		};
	});

	function updateMarkers(newMarkers: Array<{ lat: number; lng: number; title?: string }>) {
		if (!map || !leaflet) return;

		// Clear existing markers
		currentMarkers.forEach((m) => m.remove());
		currentMarkers = [];

		// Add new markers
		newMarkers.forEach((markerData) => {
			const marker = leaflet.marker([markerData.lat, markerData.lng]).addTo(map);
			if (markerData.title) {
				marker.bindPopup(markerData.title);
			}
			currentMarkers.push(marker);
		});
	}

	$effect(() => {
		if (map) {
			map.setView([lat, lng], zoom);
		}
	});

	$effect(() => {
		if (map && markers) {
			updateMarkers(markers);
		}
	});
</script>

<div bind:this={mapElement} class="z-0 h-full w-full rounded-xl" style="min-height: 300px;"></div>
