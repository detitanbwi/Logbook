<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiAssignmentResource extends JsonResource
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
            'kpi_id' => $this->kpi_id,
            'assigned_by' => $this->assigned_by,
            'user' => new UserResource($this->whenLoaded('user')),
            'kpi' => new MasterKpiResource($this->whenLoaded('kpi')),
            'assigner' => new UserResource($this->whenLoaded('assigner')),
            'created_at' => optional($this->created_at)?->toISOString(),
            'updated_at' => optional($this->updated_at)?->toISOString(),
        ];
    }
}
