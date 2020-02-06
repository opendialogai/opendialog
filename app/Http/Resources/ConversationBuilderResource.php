<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationBuilderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'name' => $this->name,
            'status' => $this->status,
            'yaml_validation_status' => $this->yaml_validation_status,
            'yaml_schema_validation_status' => $this->yaml_schema_validation_status,
            'scenes_validation_status' => $this->scenes_validation_status,
            'model_validation_status' => $this->model_validation_status,
            'notes' => $this->notes,
            'model' => $this->model,
            'version_number' => $this->version_number,
            'opening_intents' => $this->opening_intents,
            'outgoing_intents' => $this->outgoing_intents,
            'has_been_used' => $this->has_been_used,
            'is_draft' => $this->is_draft,
            'persisted_status' => $this->persisted_status,
        ];
    }
}
