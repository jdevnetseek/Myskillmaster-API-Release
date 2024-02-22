<?php

namespace App\Http\Controllers\V1\Admin;

use App\Enums\Platform;
use App\Enums\UpgradeType;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Resources\AppVersionResource;

class AppVersionController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collection = QueryBuilder::for(AppVersion::class)
            ->allowedFilters([
                AllowedFilter::exact('platform')
            ])
            ->allowedSorts('created_at', 'version')
            ->defaultSort('-created_at')
            ->get();

        return AppVersionResource::collection($collection);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data =  $request->validate([
            'platform'      => [
                'required',
                Rule::in(Platform::getValues())
            ],
            'version'       => [
                'required',
                Rule::unique('app_versions', 'version')->where('platform', $request->platform)
            ],
            'upgrade_guide' => [
                'required',
                Rule::in(UpgradeType::getValues())
            ],
            'title' => [
                'nullable'
            ],
            'message' => [
                'nullable'
            ],
            'store_url' => [
                'nullable'
            ]
        ]);

        $appVersion = AppVersion::create($data);

        return AppVersionResource::make($appVersion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'upgrade_guide' => [
                'required',
                Rule::in(UpgradeType::getValues())
            ],
            'title' => [
                'nullable'
            ],
            'message' => [
                'nullable'
            ],
            'store_url' => [
                'nullable'
            ]
        ]);

        $appVersion = AppVersion::query()
            ->findOrFail($id);

        $appVersion->update($data);

        return AppVersionResource::make($appVersion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        AppVersion::query()
            ->whereKey($id)
            ->delete();

        return response()->noContent();
    }
}
