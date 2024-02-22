<?php

namespace App\Http\Controllers\V1\Auth;

use App\Models\User;
use App\Lib\Oauth\Oauth;
use Illuminate\Http\Request;
use App\Models\LinkedAccount;
use App\Enums\SocialiteProvider;
use BenSampo\Enum\Rules\EnumValue;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Lib\Oauth\User as OauthUser;

class SocialAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => 'required',
            'provider' => ['required', new EnumValue(SocialiteProvider::class)],
        ]);

        $provider = Oauth::provider($data['provider']);
        $oauthUser = $provider->userFromToken($data['token']);

        // get link account
        $linkedAccount = LinkedAccount::where('provider_id', $oauthUser->getId())
            ->where('provider', $data['provider'])->first();

        if ($linkedAccount) {
            $user = $linkedAccount->user;
        } else {
            // register the suer
            $user = $this->registerUserFromOauth($oauthUser, $data['provider']);
        }

        $token = $user->createToken($request->header('user-agent'));
        return $this->respondWithToken($token->plainTextToken, new UserResource($user));
    }

    private function registerUserFromOauth(OauthUser $oauthUser, string $provider): User
    {

        $user = User::where('email', $oauthUser->getEmail())->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'email' => $oauthUser->getEmail(),
                'full_name' => $oauthUser->getName(),
                'email_verified_at' => now(),
            ]);

            if ($oauthUser->getAvatar()) {
                $fileName = md5(uniqid('AVATAR' . $user->id, true));
                // Add avatar
                $user->addMediaFromUrl($oauthUser->getAvatar())
                    ->usingName($fileName)
                    ->toMediaCollection('avatar');
            }
        }

        $user->linkedAccounts()->create([
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
        ]);

        return $user;
    }
}
