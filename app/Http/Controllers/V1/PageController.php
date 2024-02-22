<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Settings\PageSettings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class PageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, PageSettings $pageSettings, string $pageType)
    {
        $content = data_get($pageSettings, $pageType);

        if ($request->input('raw')) {
            return JsonResource::make([
                'type'    => $pageType,
                'content' => $content
            ]);
        }

        return $content;
    }
}
