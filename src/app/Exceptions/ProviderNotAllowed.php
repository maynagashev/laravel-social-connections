<?php

namespace Maynagashev\SocialConnections\app\Exceptions;

use Exception;

class ProviderNotAllowed extends Exception
{
    public static function providerNotInArray($provider, $array)
    {
        $allowed = (count($array)>0) ? implode(', ', $array) : 'list is empty, check config';
        return new static("Provider `{$provider}` not allowed. Allowed providers: {$allowed}.");
    }
}