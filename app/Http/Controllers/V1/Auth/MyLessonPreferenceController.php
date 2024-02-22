<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\LessonPreferenceResource;
use App\Http\Requests\SetLessonPreferenceRequest;

class MyLessonPreferenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $lessonPreferences = request()->user()->lessonPreferences()->get();
        return LessonPreferenceResource::collection($lessonPreferences);
    }

    public function store(SetLessonPreferenceRequest $request)
    {
        $user = $request->user();

        $user->setLessonPreferences($request->input('category_ids'));

        return LessonPreferenceResource::collection(
            $user->lessonPreferences()->get()
        );
    }
}
