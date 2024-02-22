<?php

namespace App\Http\Controllers\V1;

use App\Enums\Platform;
use App\Enums\UpgradeType;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppVersionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Settings\Traits\InteractsWithPlatformSettings;

class AppVersionCheckController extends Controller
{
    use InteractsWithPlatformSettings;

    /**
     * Handle the incoming request.
     *
     * When the version for the current platform does not exist,
     * we will set the upgrade_guide value to DEFAULT
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', Rule::in(Platform::getValues())],
            'version'  => 'required'
        ]);

        $result = AppVersion::query()
            ->where('platform', $data['platform'])
            ->where('version', $data['version'])
            ->first();

        if (blank($result)) {
            return $this->respondWithEmptyData(Response::HTTP_NOT_FOUND);
        }

        return AppVersionResource::make($result);
    }
}
