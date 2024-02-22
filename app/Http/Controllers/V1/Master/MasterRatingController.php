<?php

namespace App\Http\Controllers\V1\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterRatingRequest;
use App\Services\MasterRating\Exceptions\MasterRatingException;
use App\Services\Subscription\MasterRatingService;
use App\Exceptions\MasterRating\MasterRatingException as HttpMasterRatingException;
use Illuminate\Http\Response;

class MasterRatingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(MasterRatingRequest $request)
    {
        try {
            resolve(MasterRatingService::class, ['user' => $request->user()])
                ->setMasterRating($request->rating)
                ->setReferenceCode($request->reference_code)
                ->rate();

            return response()->json(['message' => 'Rating created successfully']);
        } catch (MasterRatingException $e) {
            throw new HttpMasterRatingException($e->getMessage());
        }
    }
}
