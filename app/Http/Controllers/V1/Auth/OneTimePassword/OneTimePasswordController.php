<?php

namespace App\Http\Controllers\V1\Auth\OneTimePassword;

use Illuminate\Http\Request;
use App\Rules\ValidPhoneNumber;
use App\Support\ValidatesPhone;
use App\Http\Controllers\Controller;
use App\Support\OneTimePassword\InteractsWithOneTimePassword;

class OneTimePasswordController extends Controller
{
    use InteractsWithOneTimePassword;
    use ValidatesPhone;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        $payload = $request->validate([
            'phone_number' => ['required', new ValidPhoneNumber]
        ]);

        $this->sendOneTimePassword($this->uncleanPhoneNumber($payload['phone_number']));

        return $this->respondWithEmptyData();
    }
}
