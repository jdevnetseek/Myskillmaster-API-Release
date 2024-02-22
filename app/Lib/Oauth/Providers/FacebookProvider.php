<?php

namespace App\Lib\Oauth\Providers;

use Exception;
use App\Lib\Oauth\User;
use Illuminate\Support\Arr;
use App\Lib\Oauth\Contracts\Provider;
use App\Lib\Oauth\Exceptions\InvalidTokenException;

class FacebookProvider extends AbstractProvider implements Provider
{
    /**
     * The base Facebook Graph URL.
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.facebook.com';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected $version = 'v6.0';

    /**
     * The user fields being requested.
     *
     * @var array
     */
    protected $fields = ['name', 'email', 'gender', 'verified', 'first_name', 'last_name'];

    /**
     * The provided user access token
     *
     * @var string
     */
    protected $token;

    /**
     * The token information returned from google server
     *
     * @var array
     */
    protected $tokenInfo;

    /**
     * Facebook Client ID
     *
     * @var string
     */
    protected $clientId;

    /**
     * Facebook Client Secret
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * Set google client ids
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->clientId = config('oauth.providers.facebook.client_id');
        $this->clientSecret = config('oauth.providers.facebook.client_secret');
    }

    /**
     * {@inheritdoc}
     */
    public function userFromToken(string $accessToken): User
    {
        $this->token = $accessToken;
        $this->fetchTokenInfo();

        $user            = new User();
        $user->id        = Arr::get($this->tokenInfo, 'id');
        $user->email     = Arr::get($this->tokenInfo, 'email');
        $user->name      = Arr::get($this->tokenInfo, 'name');
        $user->firstName = Arr::get($this->tokenInfo, 'first_name');
        $user->lastName  = Arr::get($this->tokenInfo, 'last_name');
        $user->avatar    = $this->fetchAvatar($user);

        return $user;
    }

    /**
     * Fetch token information from the user access token
     *
     * @return void
     */
    private function fetchTokenInfo()
    {
        try {
            $meUrl = $this->graphUrl . '/' . $this->version . '/me?access_token=' . $this->token
                . '&fields=' . implode(',', $this->fields);

            if (!empty($this->clientSecret)) {
                $appSecretProof = hash_hmac('sha256', $this->token, $this->clientSecret);
                $meUrl .= '&appsecret_proof=' . $appSecretProof;
            }
            $response = $this->getHttpClient()->get($meUrl);

            $this->tokenInfo = json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }

    /**
     * Fetch user avatar
     * @return void
     */
    private function fetchAvatar($user)
    {
        try {
            $avatar = $this->graphUrl . '/' . $this->version . '/' . $user->id . '/picture?height=500&access_token=' . $this->token;
            return $avatar;
        } catch (Exception $e) {
            throw null;
        }
    }
}
