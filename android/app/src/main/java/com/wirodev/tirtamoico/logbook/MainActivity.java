package com.wirodev.tirtamoico.logbook;

import android.graphics.Color;
import android.os.Bundle;
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
	}
}
