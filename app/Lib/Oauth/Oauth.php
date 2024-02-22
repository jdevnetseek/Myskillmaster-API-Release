<?php

namespace App\Lib\Oauth;

use InvalidArgumentException;
use Illuminate\Support\Str;
use App\Lib\Oauth\Providers\GoogleProvider;
use App\Lib\Oauth\Providers\FacebookProvider;
use App\Lib\Oauth\Contracts\Provider;
use App\Lib\Oauth\Providers\AppleProvider;

class Oauth
{
    public static function provider(string $provider): Provider
    {
        return (new self())->getProvider($provider);
    }

    public function getProvider(string $provider)
    {
        $method = 'create' . Str::studly($provider) . 'Provider';

        if (method_exists($this, $method)) {
            return static::$method();
        }

        throw new InvalidArgumentException("Provider [$provider] not supported.");
    }

    public function createGoogleProvider(): GoogleProvider
    {
        return new GoogleProvider();
    }

    public function createFacebookProvider(): FacebookProvider
    {
        return new FacebookProvider();
    }

    public function createAppleProvider(): AppleProvider
    {
        return new AppleProvider();
    }
}
