<?php

namespace App\Http\Resources;

use App\Http\Resources\Enrollment\LessonResource;
use App\Models\Job;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Product;
use App\Http\Resources\Traits\HasMappedResource;
use App\Models\MasterLesson;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    use HasMappedResource;

    /**
     * The list of Models class to be mapped to a resource
     *
     * @return array
     */
    protected function mappedResource(): array
    {
        return [
            User::class         => UserResource::class,
            Post::class         => PostResource::class,
            Product::class      => ProductResource::class,
            Comment::class      => CommentResource::class,
            Job::class          => JobResource::class,
            MasterLesson::class => LessonResource::class,
            MasterLesson::class => MasterLessonResource::class,
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'status'      => $this->status,
            'report_type' => $this->report_type,
            'reported_by' => $this->reported_by,
            'description' => $this->description,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'reporter'    => UserResource::make($this->whenLoaded('reporter')),
            'reasons'     => ReportCategoriesResource::collection($this->whenLoaded('reasons')),
            'attachments' => MediaResource::collection($this->whenLoaded('attachments')),
            'reportable'  => $this->whenLoaded('reportable', function () {
                return $this->getMappedResource($this->reportable);
            }),
        ];
    }
}
