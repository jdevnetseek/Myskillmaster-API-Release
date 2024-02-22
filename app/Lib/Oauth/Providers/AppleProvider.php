<?php

namespace App\Lib\Oauth\Providers;

use Lcobucci\JWT\Parser;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Exception;
use App\Lib\Oauth\User;
use App\Lib\Oauth\Exceptions\InvalidTokenException;
use App\Lib\Oauth\Contracts\Provider;

class AppleProvider extends AbstractProvider implements Provider
{

    /**
     * The Google issuer name.
     *
     * @var string
     */
    protected $issuer = 'appleid.apple.com';

    /**
     * List of possible client ID that the user token has generated
     *
     * @var array
     */
    protected $clientIds = [];

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
     * Set google client ids
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->clientIds = config('oauth.providers.apple.client_ids');
    }

    /**
     * {@inheritDoc}
     */
    public function userFromToken($accessToken): User
    {
        $this->token = $accessToken;

        $this->fetchTokenInfo();
        $this->validateToken();

        $user            = new User();
        $user->id        = Arr::get($this->tokenInfo, 'sub');
        $user->email     = Arr::get($this->tokenInfo, 'email');
        $user->name      = Arr::get($this->tokenInfo, 'name');
        $user->firstName = Arr::get($this->tokenInfo, 'given_name');
        $user->lastName  = Arr::get($this->tokenInfo, 'family_name');
        $user->avatar    = Arr::get($this->tokenInfo, 'picture');

        return $user;
    }

    /**
     * Check if the token is valid
     *
     * @param string $idToken
     * @return boolean
     */
    private function validateToken(): bool
    {
        // Check the token issuer and token audience
        $validIss = Str::contains($this->tokenInfo['iss'], $this->issuer);
        $validAud = in_array($this->tokenInfo['aud'], $this->clientIds);

        if ($validIss && $validAud) {
            return true;
        }

        throw new InvalidTokenException('The token provided is invalid.');
    }

    /**
     * Fetch token information from the user access token
     *
     * @return void
     */
    private function fetchTokenInfo()
    {
        try {
            $parser = new Parser();
            $token = $parser->parse($this->token);

            $this->tokenInfo = [
                'sub'         => $token->getClaim('sub'),
                'iss'         => $token->getClaim('iss'),
                'aud'         => $token->getClaim('aud'),
                'email'       => $token->hasClaim('email') ? $token->getClaim('email') : null,
                'name'        => $token->hasClaim('name') ? $token->getClaim('name') : null,
                'given_name'  => $token->hasClaim('given_name') ? $token->getClaim('given_name') : null,
                'family_name' => $token->hasClaim('family_name') ? $token->getClaim('family_name') : null,
                'picture'     => $token->hasClaim('picture') ? $token->getClaim('picture') : null,
            ];
        } catch (Exception $e) {
            throw new InvalidTokenException($e->getMessage());
        }
    }
}
