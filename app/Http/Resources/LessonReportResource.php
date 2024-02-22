<?php

namespace App\Http\Resources;

use App\Http\Resources\Enrollment\LessonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonReportResource extends JsonResource
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
            'id'            => $this->id,
            'description'   => $this->description,
            'status'        => $this->status,
            'report_type'   => $this->reportable_type,
            'reported_at'   => $this->created_at,
            'reported_by'   => $this->reported_by,
            'reported'      => LessonResource::make($this->whenLoaded('reportable')),
            'reporter'      => UserResource::make($this->whenLoaded('reporter')),
            'reasons'       => ReportCategoriesResource::collection($this->whenLoaded('reasons')),
            'attachments'   => MediaResource::collection($this->whenLoaded('attachments')),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
