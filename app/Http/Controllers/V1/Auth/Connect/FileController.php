<?php

namespace App\Http\Controllers\V1\Auth\Connect;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class FileController extends Controller
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
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'purpose' => ['required', 'string'],
            'file'    => ['required', 'file']
        ]);

        return JsonResource::make($request->asStripeFile('file', $request->input('purpose')));
    }
}
