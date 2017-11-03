<?php

namespace Maynagashev\SocialConnections\app\Exceptions;

use Exception;

class ProviderExceptions extends Exception
{
    public static function providerNotAllowed($provider, $array)
    {
        $allowed = (count($array)>0) ? implode(', ', $array) : 'list is empty, check config';
        return new static("Provider `{$provider}` not allowed. Allowed providers: {$allowed}.");
    }
    public static function providerNotFound($provider)
    {
        return new static("No such provider `{$provider}` defined in services.");
    }
}