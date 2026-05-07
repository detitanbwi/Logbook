package com.wirodev.tirtamoico.logbook;

import android.Manifest;
import android.app.DownloadManager;
import android.content.Context;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.webkit.CookieManager;
import android.webkit.DownloadListener;
import android.webkit.WebView;
import android.graphics.Color;
import android.os.Bundle;
import android.widget.Toast;
import androidx.activity.OnBackPressedCallback;
import androidx.appcompat.app.AlertDialog;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
	@Override
	public void onCreate(Bundle savedInstanceState) {
		registerPlugin(SecurityPlugin.class);
		super.onCreate(savedInstanceState);

		if (getWindow() != null) {
			getWindow().setStatusBarColor(Color.parseColor("#002A58"));
			WindowCompat.setDecorFitsSystemWindows(getWindow(), true);

			WindowInsetsControllerCompat controller = WindowCompat.getInsetsController(getWindow(), getWindow().getDecorView());
			if (controller != null) {
				// false => light icons/text on dark status bar background
				controller.setAppearanceLightStatusBars(false);
			}
		}

		// Minta izin notifikasi secara native (Wajib untuk Android 13 / API 33+)
		if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
			if (ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS)
					!= PackageManager.PERMISSION_GRANTED) {
				ActivityCompat.requestPermissions(
					this,
					new String[]{Manifest.permission.POST_NOTIFICATIONS},
					1001
				);
			}
		}

		getOnBackPressedDispatcher().addCallback(this, new OnBackPressedCallback(true) {
			@Override
			public void handleOnBackPressed() {
				handleNativeBackPress();
			}
		});
	}

	@Override
	public void onResume() {
		super.onResume();

		// DownloadListener agar file bisa terunduh via Android DownloadManager
		// Tanpa ini, Capacitor WebView akan diam saja saat link download diklik
		WebView webView = bridge != null ? bridge.getWebView() : null;
		if (webView != null) {
			webView.setDownloadListener(new DownloadListener() {
				@Override
				public void onDownloadStart(String url, String userAgent, String contentDisposition, String mimetype, long contentLength) {
					DownloadManager downloadManager = (DownloadManager) getSystemService(Context.DOWNLOAD_SERVICE);

					// Ambil nama file dari content-disposition atau URL
					String filename = null;
					if (contentDisposition != null && contentDisposition.contains("filename=")) {
						filename = contentDisposition.replace("filename=", "").replace("\"", "").trim();
						int lastSlash = filename.lastIndexOf('/');
						if (lastSlash != -1) filename = filename.substring(lastSlash + 1);
					}
					if (filename == null || filename.isEmpty()) {
						int lastSlash = url.lastIndexOf('/');
						filename = lastSlash != -1 ? url.substring(lastSlash + 1) : "download";
						// Hapus query string jika ada
						int queryIdx = filename.indexOf('?');
						if (queryIdx != -1) filename = filename.substring(0, queryIdx);
					}

					// Sertakan cookie session Laravel agar download terautentikasi
					String cookies = CookieManager.getInstance().getCookie(url);

					DownloadManager.Request request = new DownloadManager.Request(Uri.parse(url));
					request.setMimeType(mimetype);
					request.addRequestHeader("User-Agent", userAgent);
					if (cookies != null) {
						request.addRequestHeader("Cookie", cookies);
					}
					request.setDescription("Mengunduh file...");
					request.setTitle(filename);
					request.allowScanningByMediaScanner();
					request.setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED);
					request.setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, filename);

					downloadManager.enqueue(request);

					Toast.makeText(getApplicationContext(),
						"Mengunduh: " + filename,
						Toast.LENGTH_SHORT).show();
				}
			});
		}
	}

	private void handleNativeBackPress() {
		WebView webView = bridge != null ? bridge.getWebView() : null;

		if (webView == null) {
			finishAffinity();
			return;
		}

		String currentUrl = webView.getUrl();
		if (isDashboardLogsPage(currentUrl)) {
			showExitConfirmation();
			return;
		}

		if (webView.canGoBack()) {
			webView.goBack();
			return;
		}

		finishAffinity();
	}

	private boolean isDashboardLogsPage(String url) {
		if (url == null) {
			return false;
		}

		return url.contains("/dashboard") && !url.contains("/login");
	}

	private void showExitConfirmation() {
		new AlertDialog.Builder(this)
			.setTitle("Konfirmasi")
			.setMessage("Tutup aplikasi?")
			.setNegativeButton("Batal", null)
			.setPositiveButton("Tutup", (dialog, which) -> finishAffinity())
			.show();
	}
}
