<script lang="ts">
	interface Props {
		/** Teks watermark yang ditampilkan */
		text?: string;
		/** Ukuran font dalam rem */
		fontSize?: number;
		/** Rotasi dalam derajat */
		rotation?: number;
		/** Opacity 0-1 */
		opacity?: number;
		/** Warna background */
		bgColor?: string;
		/** Warna teks */
		textColor?: string;
	}

	let {
		text = 'Demo Aplikasi #WiroDev',
		fontSize = 2,
		rotation = 45,
		opacity = 0.5,
		bgColor = '#dc2626',
		textColor = '#ffffff'
	}: Props = $props();
</script>

<div
	class="watermark"
	style="
        --font-size: {fontSize}rem;
        --rotation: {rotation}deg;
        --opacity: {opacity};
        --bg-color: {bgColor};
        --text-color: {textColor};
    "
>
	<span class="watermark-text">{text}</span>
</div>

<style>
	.watermark {
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%) rotate(var(--rotation));
		background-color: var(--bg-color);
		color: var(--text-color);
		opacity: var(--opacity);
		padding: 2rem 6rem;
		font-size: var(--font-size);
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.2em;
		white-space: nowrap;
		z-index: 9999;
		pointer-events: none;
		user-select: none;
		box-shadow: 0 0 0 100vmax var(--bg-color);
		clip-path: inset(0 -100vmax);
	}

	.watermark-text {
		display: inline-block;
		text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
	}
</style>
