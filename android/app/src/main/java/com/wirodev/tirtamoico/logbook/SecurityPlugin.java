package com.wirodev.tirtamoico.logbook;

import android.Manifest;
import android.content.Context;
import android.location.Location;
import android.location.LocationManager;
import android.os.Build;
import android.provider.Settings;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;
import com.getcapacitor.annotation.Permission;

@CapacitorPlugin(
    name = "Security",
    permissions = {
        @Permission(
            alias = "location",
            strings = { Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION }
        )
    }
)
public class SecurityPlugin extends Plugin {

    @PluginMethod
    public void checkMockLocation(PluginCall call) {
        Context context = getContext();
        boolean isMock = false;

        try {
            LocationManager locationManager = (LocationManager) context.getSystemService(Context.LOCATION_SERVICE);
            Location location = null;
            
            // Try to get last known location from GPS or Network
            if (locationManager != null) {
                Location gpsLoc = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
                Location netLoc = locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                
                if (gpsLoc != null && netLoc != null) {
                    location = gpsLoc.getTime() > netLoc.getTime() ? gpsLoc : netLoc;
                } else {
                    location = gpsLoc != null ? gpsLoc : netLoc;
                }
            }

            if (location != null) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) { // Android 12 (API 31)
                    isMock = location.isMock();
                } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR2) {
                    isMock = location.isFromMockProvider();
                }
            }
            
            // Fallback: Check if Developer Options "Mock Location" setting is enabled (for older devices)
            if (!isMock) {
                String mockSetting = Settings.Secure.getString(context.getContentResolver(), "mock_location");
                if (mockSetting != null && !mockSetting.equals("0")) {
                    isMock = true;
                }
            }

        } catch (SecurityException e) {
            // Permission not granted, but we handle it in JS
        } catch (Exception e) {
            // Other errors
        }

        JSObject ret = new JSObject();
        ret.put("isMock", isMock);
        call.resolve(ret);
    }
}
