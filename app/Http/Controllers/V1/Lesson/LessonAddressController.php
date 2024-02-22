<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Http\Controllers\Controller;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use App\Http\Resources\LessonAddressResource;

class LessonAddressController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, MasterLesson $masterLesson)
    {
        return LessonAddressResource::make($masterLesson);
    }
}
