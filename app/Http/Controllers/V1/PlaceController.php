<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Place;

class PlaceController extends Controller
{
    public function index()
    {
        return PlaceResource::collection(
            Place::withWhereHas('state.country')->orderBy('city')->get()
        );
    }
}
