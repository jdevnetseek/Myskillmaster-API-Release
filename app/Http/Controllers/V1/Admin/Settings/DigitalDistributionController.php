<?php

namespace App\Http\Controllers\V1\Admin\Settings;

use App\Enums\Platform;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Settings\Traits\InteractsWithPlatformSettings;

class DigitalDistributionController extends Controller
{
    use InteractsWithPlatformSettings;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
    public function index(Request $request)
    {
        $data = $request->validate([
            'platform'   => ['required', 'string', Rule::in(Platform::getValues())]
        ]);

        $settings = $this->getPlatformSettings($data['platform']);

        return JsonResource::make([
            'platform'   => $data['platform'],
            'store_url' => data_get($settings, 'store_url')
        ]);
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'platform'   => ['required', 'string', Rule::in(Platform::getValues())],
            'store_url' => ['nullable']
        ]);

        $settings = $this->getPlatformSettings($data['platform']);

        $settings->store_url = $data['store_url'];
        $settings->save();

        return response()->noContent();
    }
}
