<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;

class ProfileAvatarController extends Controller
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
     * Store new user avatar
     *
     * @param \Illuminate\Http\Request $request
     * @return MediaResource
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'avatar' => 'required|image'
        ]);

        /** @var User */
        $user = auth()->user();

        $avatar = $user->setAvatar($data['avatar']);

        return new MediaResource($avatar);
    }
}
