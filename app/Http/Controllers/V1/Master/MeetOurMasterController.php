<?php

namespace App\Http\Controllers\V1\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\QueryBuilder;

class MeetOurMasterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $collections = QueryBuilder::for(User::class)
            ->whereHas('subscriptions', fn ($query) => $query->active())
            ->whereHas('masterProfile')
            ->inRandomOrder()
            ->paginate($request->perPage());

        $collections->load([
            'address.state',
            'lessonPreferences',
            'masterProfile',
            'masterProfile.languages',
            'masterProfile.portfolio',
            'distinctLessonCategories',
        ]);

        return UserResource::collection($collections);
    }
}
