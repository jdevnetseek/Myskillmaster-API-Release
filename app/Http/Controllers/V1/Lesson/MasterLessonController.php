<?php

namespace App\Http\Controllers\V1\Lesson;

use App\Enums\AddressType;
use App\Models\MasterLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\MasterLessonResource;
use App\Http\Requests\StoreMasterLessonRequest;
use App\Http\Requests\UpdateMasterLessonRequest;
use App\Http\Resources\MasterLessonWithLocationResource;
use Spatie\QueryBuilder\AllowedFilter;

class MasterLessonController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $collections = QueryBuilder::for(MasterLesson::class)
            ->isUserSubscribe()
            ->ownedBy($user)
            ->allowedFilters([
                AllowedFilter::exact('active')
            ])
            ->with([
                'cover',
                'category',
                'place',
                'place.country',
                'place.state',
                'user.address',
                'user.masterProfile',
                'user.masterProfile.portfolio',
                'user.lessonPreferences',
                'schedules',
                'schedules.lessonEnrollments'
            ])
            ->orderBy('title')
            ->paginate($request->perPage());

        return MasterLessonResource::collection($collections);
    }

    public function store(StoreMasterLessonRequest $request)
    {
        $user = $request->user();

        $this->authorize('store-lesson', $user);

        $masterLesson = DB::transaction(function () use ($request, $user) {
            $masterLesson = $user->createMasterLesson($request->validated());

            return $masterLesson;
        });

        return MasterLessonResource::make($masterLesson->load('cover', 'place', 'category', 'schedules'));
    }

    public function show($slug)
    {
        $masterLesson = MasterLesson::with(['schedules' => function ($query) {
            if (request()->query('exclude_past_schedules', false)) {
                $query->upcoming();
            }

            $query->with('lessonEnrollments');
            $query->withCount('lessonEnrollments');
        }])->whereSlug($slug)->firstOrFail();

        return MasterLessonResource::make(
            $masterLesson->load(
                'cover',
                'category',
                'place',
                'place.country',
                'place.state',
                'user.address',
                'user.masterProfile',
                'user.masterProfile.portfolio',
                'user.lessonPreferences'
            )
        );
    }

    public function update(UpdateMasterLessonRequest $request, MasterLesson $lesson)
    {
        $this->authorize('update', $lesson);

        $lesson = DB::transaction(function () use ($request, $lesson) {
            $lesson->update($request->validated());

            if ($request->has('tags')) {
                $lesson->syncTags($request->input('tags'));
            }

            return $lesson->fresh([
                'schedules',
                'schedules.lessonEnrollments',
                'cover',
                'category',
                'place',
                'place.country',
                'place.state',
                'user',
                'user.masterProfile'
            ]);
        });

        return MasterLessonResource::make($lesson);
    }

    public function destroy(MasterLesson $masterLesson)
    {
        $this->authorize('delete', $masterLesson);

        // Fire event to send email to each student enrolled
        //event(new MasterLessonDeleting($masterLesson)); will be use on month 3

        $masterLesson->delete();

        return response()->json([
            'message' => 'Lesson deleted and email sent to enrolled students'
        ], 200);
    }
}
