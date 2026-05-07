package com.wirodev.tirtamoico.logbook;

import android.Manifest;
import android.content.Context;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.Build;
import android.provider.Settings;
import androidx.core.app.ActivityCompat;
import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;
import com.getcapacitor.annotation.Permission;
import com.google.android.gms.location.FusedLocationProviderClient;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.location.Priority;

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
    public void checkMockLocation(final PluginCall call) {
        Context context = getContext();
        
        boolean fallbackMock = false;
        try {
            String mockSetting = Settings.Secure.getString(context.getContentResolver(), "mock_location");
            if (mockSetting != null && !mockSetting.equals("0")) {
                fallbackMock = true;
            }
        } catch (Exception e) {}

        final boolean finalFallbackMock = fallbackMock;

        if (ActivityCompat.checkSelfPermission(context, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            JSObject ret = new JSObject();
            ret.put("isMock", finalFallbackMock);
            ret.put("debug", "no_perm");
            call.resolve(ret);
            return;
        }

        FusedLocationProviderClient fusedLocationClient = LocationServices.getFusedLocationProviderClient(context);
        fusedLocationClient.getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, null)
            .addOnSuccessListener(getActivity(), location -> {
                boolean isMock = finalFallbackMock;
                String debug = "success";
                if (location != null) {
                    if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.S) {
                        isMock = location.isMock();
                        debug = "isMock12:" + isMock;
                    } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR2) {
                        isMock = location.isFromMockProvider();
                        debug = "isMock4:" + isMock;
                    }
                } else {
                    debug = "loc_null";
                }
                
                JSObject ret = new JSObject();
                ret.put("isMock", isMock);
                ret.put("debug", debug);
                call.resolve(ret);
            })
            .addOnFailureListener(getActivity(), e -> {
                JSObject ret = new JSObject();
                ret.put("isMock", finalFallbackMock);
                ret.put("debug", "fail:" + e.getMessage());
                call.resolve(ret);
            });
    }
}
