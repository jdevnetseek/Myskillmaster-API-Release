<?php

namespace App\Http\Controllers\V1;

use App\Exports\UserExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserExportController extends Controller
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

    public function __invoke()
    {
        return new UserExport();
    }
}
