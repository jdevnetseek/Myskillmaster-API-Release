<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MediaResource;

class UserAvatarController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->only(['store', 'destroy']);
    }

    /**
     * Download user avatar
     *
     * @param  int  $id User ID
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $wantsMediaResource = request()->has('redirect') && request('redirect') == false;

        if (filled($user->avatar) && $wantsMediaResource) {
            return new MediaResource($user->avatar);
        }

        if (blank($user->avatar) && $wantsMediaResource) {
            return response()->json(null, 404);
        }

        return redirect()->to(optional($user->avatar)->getFullUrl() ?: $user->defaultAvatar());
    }

    /**
     * Download user avatart thumbnail
     *
     * @param  int  $id User ID
     * @return \Illuminate\Http\Response
     */
    public function showThumb($id)
    {
        $user = User::findOrFail($id);

        $wantsMediaResource = request()->has('redirect') && request('redirect') == false;

        if (filled($user->avatar) && $wantsMediaResource) {
            return new MediaResource($user->avatar);
        }

        if (blank($user->avatar) && $wantsMediaResource) {
            return response()->json(null, 404);
        }

        return redirect()->to(optional($user->avatar)->getFullUrl('thumb') ?: $user->defaultAvatar());
    }

    /**
     * Store new user avatar
     *
     * @param int $id User ID
     * @param \Illuminate\Http\Request  $request
     * @return MediaResource
     */
    public function store($id, Request $request)
    {
        $data = $request->validate([
            'avatar' => 'required|image'
        ]);

        $user = User::findOrFail($id);

        // Hashing file name
        $name = md5(uniqid('AVATAR' . $user->id, true));
        $fileName = $name . '.' . $data['avatar']->extension();

        $avatar = $user->addMedia($data['avatar'])
            ->usingName($name)
            ->usingFileName($fileName)
            ->toMediaCollection('avatar');

        return new MediaResource($avatar);
    }

    /**
     * Remove user avatar
     *
     * @param int $id User ID
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->avatar->delete();
    }
}
