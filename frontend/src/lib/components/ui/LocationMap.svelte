<script lang="ts">
	import { Map, Layer, Feature, View } from 'svelte-openlayers';
	import { createCircleStyle } from 'svelte-openlayers/utils';
	import 'svelte-openlayers/styles.css';

	let {
		lat,
		lng,
		zoom = 15,
		height = 'h-48'
	}: {
		lat: number;
		lng: number;
		zoom?: number;
		height?: string;
	} = $props();

	let coordinates = $derived<[number, number]>([lng, lat]);

	const markerStyle = createCircleStyle({
		radius: 8,
		fill: { color: '#ef4444' },
		stroke: { color: '#ffffff', width: 3 }
	});
</script>

<View center={coordinates} {zoom}>
	<Map class="w-full rounded-lg {height}">
		<Layer.Tile source="osm" />
		<Layer.Vector>
			<Feature.Point {coordinates} style={markerStyle} />
		</Layer.Vector>
	</Map>
</View>
