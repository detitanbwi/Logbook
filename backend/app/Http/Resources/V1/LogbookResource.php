<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogbookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tanggal' => optional($this->tanggal)?->toDateString(),
            'start_kerja' => (string) ($this->start_kerja ?? ''),
            'end_kerja' => $this->end_kerja,
            'lokasi' => $this->lokasi,
            'lokasi_lat' => $this->lokasi_lat ? (float) $this->lokasi_lat : null,
            'lokasi_lng' => $this->lokasi_lng ? (float) $this->lokasi_lng : null,
            'status' => (string) ($this->status ?? ''),
            'rating' => $this->rating,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => optional($this->reviewed_at)?->toISOString(),
            'reviewer_comment' => $this->reviewer_comment,
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? new UserResource($this->user) : null;
            }),
            'reviewer' => $this->whenLoaded('reviewer', function () {
                return $this->reviewer ? new UserResource($this->reviewer) : null;
            }),
            'details' => $this->whenLoaded('kpiDetails', function () {
                return $this->kpiDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'kpi_id' => $detail->kpi_id,
                        'kpi_nama' => $detail->kpi_nama,
                        'target_angka' => (float) ($detail->target_angka ?? 0),
                        'satuan' => $detail->satuan,
                        'capaian_angka' => (float) ($detail->capaian_angka ?? 0),
                        'lampiran_file' => $detail->lampiran_file,
                        'finished_at' => optional($detail->finished_at)?->toISOString(),
                    ];
                });
            }),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
