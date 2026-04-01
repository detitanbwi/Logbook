# Panduan Build Android APK - HRIS Logbook

Dokumen ini berisi langkah-langkah untuk melakukan build atau kompilasi file APK untuk aplikasi HRIS Logbook menggunakan Capacitor dan Android SDK lokal.

## Prerequisites (Prasyarat)
Pastikan path berikut tersedia di sistem kamu:
- **Android SDK**: `D:\android`
- **JDK 17**: `D:\android\jdk\jdk17` (Penting: Gradle membutuhkan JDK ini untuk build yang stabil)
- **Node.js**: Sudah terinstall dengan Capacitor CLI.

## Langkah-Langkah Build

### 1. Sinkronisasi Web Assets (Root Folder)
Pastikan semua perubahan terbaru di sisi Web (Laravel/Vite) sudah masuk ke folder Android:
```powershell
# Jalankan di d:\xampp\htdocs\hris\
npx cap sync android
```

### 2. Set Environment Java (PowerShell)
Agar proses build menggunakan JDK yang benar, jalankan perintah ini di terminal PowerShell:
```powershell
$env:JAVA_HOME = "D:\android\jdk\jdk17"
```

### 3. Masuk ke Folder Android
```powershell
cd android
```

### 4. Eksekusi Gradle Build
Jalankan perintah build untuk menghasilkan file APK (Debug):
```powershell
.\gradlew.bat assembleDebug
```

---

## Output APK
Jika proses berhasil, file APK hasil build akan tersedia di:
`D:\xampp\htdocs\hris\android\app\build\outputs\apk\debug\app-debug.apk`

## Troubleshooting
- **Tidak Terjadi Apa-apa Saat Download**: Pastikan `MainActivity.java` sudah memiliki `DownloadListener` (sudah saya tambahkan di versi terbaru).
- **Gradle Error**: Jika ada error "Java Home not set", pastikan langkah nomor 2 sudah dijalankan dengan benar di session terminal yang sama.
- **Sync Error**: Jika muncul "android platform has not been added", jalankan `npx cap sync` tepat di folder root (`hris`).

---
*Dibuat oleh Antigravity Assistant - 31 Maret 2026*
