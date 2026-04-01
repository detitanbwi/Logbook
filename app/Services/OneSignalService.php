<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    protected $appId;
    protected $restKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->restKey = config('services.onesignal.rest_api_key');
    }

    /**
     * Kirim notifikasi ke user tertentu berdasarkan External User ID.
     *
     * @param int|string $userId ID User di database HRIS (sebagai External User ID)
     * @param string $title Judul notifikasi
     * @param string $message Isi pesan notifikasi
     * @param array $data Data tambahan (optional)
     * @return bool
     */
    public function sendToUser($userId, $title, $message, array $data = [])
    {
        if (empty($this->appId) || empty($this->restKey)) {
            Log::error('OneSignal configuration missing.');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->restKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'include_external_user_ids' => [(string) $userId],
            'channel_for_external_user_ids' => 'push',
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'small_icon' => 'ic_notification',
            'large_icon' => 'ic_notification_large',
            'android_accent_color' => '002A58', // Warna Brand (Biru Tua)
            'data' => $data,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('OneSignal notification failed: ' . $response->body());
        return false;
    }
}
