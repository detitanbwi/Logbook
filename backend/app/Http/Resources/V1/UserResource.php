<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'npp' => (string) ($this->npp ?? ''),
            'nama' => (string) ($this->nama ?? ''),
            'email' => (string) ($this->email ?? ''),
            'role' => (string) ($this->role ?? ''),
            'manager_id' => $this->manager_id,
            'foto' => $this->foto,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => optional($this->tanggal_lahir)?->toDateString(),
            'nik' => $this->nik,
            'npwp' => $this->npwp,
            'alamat' => $this->alamat,
            'status_kawin' => $this->status_kawin,
            'riwayat_pendidikan' => $this->riwayat_pendidikan,
            'riwayat_karir' => $this->riwayat_karir,
            'has_subordinates' => method_exists($this->resource, 'hasSubordinates') ? $this->hasSubordinates() : false,
            'manager' => new UserResource($this->whenLoaded('manager')),
            'last_password_change' => optional($this->last_password_change)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
