import './bootstrap';

import Alpine from 'alpinejs';
import { Capacitor } from '@capacitor/core';
import { App } from '@capacitor/app';
import { SplashScreen } from '@capacitor/splash-screen';
import { StatusBar, Style } from '@capacitor/status-bar';
import { Geolocation } from '@capacitor/geolocation';

window.Alpine = Alpine;

Alpine.start();

const isNativePlatform = Capacitor.isNativePlatform();

async function configureNativeSplashScreen() {
	if (!isNativePlatform) {
		return;
	}

	try {
		// Fallback: never keep splash forever
		setTimeout(() => {
			SplashScreen.hide().catch(() => {});
		}, 5000);

		// Hide shortly after the page is ready
		window.addEventListener('load', () => {
			setTimeout(() => {
				SplashScreen.hide().catch(() => {});
			}, 300);
		}, { once: true });
	} catch (error) {
		console.warn('SplashScreen setup failed:', error);
	}
}

async function configureNativeStatusBar() {
	if (!isNativePlatform) {
		return;
	}

	try {
		await StatusBar.setOverlaysWebView({ overlay: false });
		await StatusBar.setBackgroundColor({ color: '#002a58' });
		await StatusBar.setStyle({ style: Style.Dark });
	} catch (error) {
		console.warn('StatusBar setup failed:', error);
	}
}

function isLikelyNotFoundPage() {
	const title = (document.title || '').toLowerCase();
	const path = (window.location.pathname || '').toLowerCase();
	const bodyText = (document.body?.innerText || '').slice(0, 1200).toLowerCase();

	return (
		title.includes('404') ||
		title.includes('not found') ||
		path.includes('/404') ||
		(bodyText.includes('404') && (bodyText.includes('not found') || bodyText.includes('tidak ditemukan')))
	);
}

function isDashboardLogsPage() {
	const normalizedPath = (window.location.pathname || '').replace(/\/+$/, '');
	return normalizedPath === '/dashboard';
}

async function configureAndroidBackButton() {
	if (!isNativePlatform) {
		return;
	}

	try {
		await App.addListener('backButton', ({ canGoBack }) => {
			if (isLikelyNotFoundPage()) {
				App.exitApp();
				return;
			}

			if (isDashboardLogsPage()) {
				const shouldExit = window.confirm('Tutup aplikasi?');
				if (shouldExit) {
					App.exitApp();
				}
				return;
			}

			if (canGoBack || window.history.length > 1) {
				window.history.back();
				return;
			}
		});
	} catch (error) {
		console.warn('Back button setup failed:', error);
	}
}

window.HRISNative = {
	isNativePlatform,
	async getCurrentPosition(options = {}) {
		const defaults = {
			enableHighAccuracy: true,
			timeout: 10000,
			maximumAge: 0,
		};

		if (isNativePlatform) {
			const permissionStatus = await Geolocation.requestPermissions();
			const locationGranted =
				permissionStatus.location === 'granted' ||
				permissionStatus.coarseLocation === 'granted';

			if (!locationGranted) {
				throw new Error('LOCATION_PERMISSION_DENIED');
			}

			return Geolocation.getCurrentPosition({ ...defaults, ...options });
		}

		return new Promise((resolve, reject) => {
			if (!navigator.geolocation) {
				reject(new Error('GEOLOCATION_NOT_SUPPORTED'));
				return;
			}

			navigator.geolocation.getCurrentPosition(
				resolve,
				reject,
				{ ...defaults, ...options },
			);
		});
	},
	viewLocation(lat, lng) {
		// Open map with the coordinates using Google Maps
		const maps = `https://www.google.com/maps?q=${lat},${lng}`;
		if (isNativePlatform) {
			window.open(maps, '_system');
		} else {
			window.open(maps, '_blank');
		}
	}
};

configureNativeStatusBar();
configureAndroidBackButton();
configureNativeSplashScreen();
