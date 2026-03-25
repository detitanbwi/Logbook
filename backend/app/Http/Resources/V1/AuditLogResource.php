<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = (string) ($this->action ?? '-');
        $auditableType = (string) ($this->table_name ?? '-');
        $auditableId = (string) ($this->record_id ?? '-');

        return [
            'id' => (string) $this->id,

            // Canonical frontend-safe aliases
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $this->old_data ?? [],
            'new_values' => $this->new_data ?? [],

            // Existing backend fields (for backward compatibility)
            'table_name' => $auditableType,
            'record_id' => $auditableId,
            'action' => $event,
            'old_data' => $this->old_data,
            'new_data' => $this->new_data,
            'performed_by' => $this->performed_by,
            'performed_at' => optional($this->performed_at)?->toISOString(),

            'url' => null,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => optional($this->performed_at)?->toISOString() ?? optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),

            'user' => $this->whenLoaded('user', function () {
                if (! $this->user) {
                    return null;
                }

                return [
                    'id' => (string) $this->user->id,
                    'nama' => (string) ($this->user->nama ?? ''),
                    'email' => (string) ($this->user->email ?? ''),
                    'npp' => (string) ($this->user->npp ?? ''),
                    'role' => (string) ($this->user->role ?? ''),
                ];
            }),
        ];
    }
}
