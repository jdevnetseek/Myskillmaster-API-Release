<?php

namespace App\Http\Controllers\V1\Admin\Settings;

use App\Enums\Pages;
use Illuminate\Http\Request;
use App\Settings\PageSettings;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class PageSettingsController extends Controller
{
    /** @var PageSettings */
    protected $pageSettings;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PageSettings $pageSettings)
    {
        $this->middleware('auth');
        $this->pageSettings = $pageSettings;
    }

    /**
     * Handles request for listing all page types.
     *
     * @return void
     */
    public function index()
    {
        $collection = collect(Pages::getValues())
            ->map(function ($page) {
                return [
                    'type'    => $page,
                    'content' => data_get($this->pageSettings, $page),
                ];
            });

        return JsonResource::collection($collection);
    }

    /**
     * Return the contents of the page type
     *
     * @param string $pageType
     * @return void
     */
    public function show(string $pageType)
    {
        return JsonResource::make([
            'type'    => $pageType,
            'content' => data_get($this->pageSettings, $pageType),
        ]);
    }

    /**
     * Handles request for updating or setting page types.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => ['required', Rule::in(Pages::getValues())],
            'content' => ['required', 'string'],
        ]);

        data_set($this->pageSettings, $data['type'], $data['content']);
        $this->pageSettings->save();

        return response()->noContent();
    }
}
