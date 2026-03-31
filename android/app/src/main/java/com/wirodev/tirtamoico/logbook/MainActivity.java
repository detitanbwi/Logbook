package com.wirodev.tirtamoico.logbook;

import android.webkit.WebView;
import android.graphics.Color;
import android.os.Bundle;
import androidx.activity.OnBackPressedCallback;
import androidx.appcompat.app.AlertDialog;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsControllerCompat;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
	@Override
	public void onCreate(Bundle savedInstanceState) {
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

		getOnBackPressedDispatcher().addCallback(this, new OnBackPressedCallback(true) {
			@Override
			public void handleOnBackPressed() {
				handleNativeBackPress();
			}
		});
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
