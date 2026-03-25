# Capacitor Android APK Build Guide for HRIS

## ✅ Completed Setup
- Capacitor initialized
- Android platform added
- Web assets synced to Android

## 📱 Next Steps to Build APK

### 1. Install Android Studio
Download from: https://developer.android.com/studio

### 2. Build APK from Command Line
```bash
cd android
./gradlew assembleRelease
```

The APK will be located at:
```
android/app/build/outputs/apk/release/app-release.apk
```

### 3. Build APK using Android Studio (Recommended)
1. Open Android Studio
2. Click "Open" → Select `d:\xampp\htdocs\hris\android` folder
3. Wait for Gradle sync to complete
4. Go to **Build** → **Build Bundle(s) / APK(s)** → **Build APK(s)**
5. Find your APK in: `android/app/build/outputs/apk/debug/app-debug.apk`

### 4. For Release APK (Production)
1. In Android Studio: **Build** → **Generate Signed Bundle / APK**
2. Create a signing key (keep it safe!)
3. Follow the wizard to generate release APK

## 📦 Project Structure
```
your-project/
├── capacitor.config.json       (Main config)
├── android/                     (Android native project)
├── public/                      (Web assets)
│   ├── index.html
│   └── build/
│       └── assets/
├── package.json
└── vite.config.js
```

## 🔧 Development Workflow

### During Development
```bash
# Terminal 1: Watch & build frontend
npm run dev

# Terminal 2: Copy changes to Android
npx cap copy android
npx cap sync
```

### After Frontend Changes
```bash
# Build frontend
npm run build

# Sync to Android
npx cap sync

# Build APK
cd android && ./gradlew assembleDebug
```

## 🚀 Additional Capacitor Plugins You Might Need

```bash
# Camera
npm install @capacitor/camera
npx cap sync

# File access
npm install @capacitor/filesystem
npx cap sync

# Geolocation
npm install @capacitor/geolocation
npx cap sync

# Network status
npm install @capacitor/network
npx cap sync
```

## ⚙️ Update Capacitor Config if Needed
File: `capacitor.config.json`
```json
{
  "appId": "com.wirodev.tirtamoico.logbook",
  "appName": "My Logbook",
  "webDir": "public",
  "server": {
    "cleartext": true
  }
}
```

## 🎯 Important Notes
- Keep `android/` folder in version control (.gitignore won't exclude it)
- Java 17 is already installed on your system
- Update frontend assets whenever you change code:
  - `npm run build` (build for production)
  - `npx cap sync` (copy to Android)
- For debugging, use Android Studio's built-in debugger

## 🔗 Useful Commands
```bash
# Sync without full rebuild
npx cap copy

# Update Capacitor core
npm install @capacitor/core@latest

# Check Capacitor status
npx cap status
```
