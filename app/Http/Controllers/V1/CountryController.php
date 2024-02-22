<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Cache::remember('country_list', (60 * 60), function () {
            return Country::orderBy('name')->get();
        });

        return JsonResource::collection($countries);
    }
}
