<?php

namespace Avaztek\PaymentCore;

class Signer
{
    public static function canonical($method, $path, $timestamp, $nonce, $body)
    {
        return strtoupper((string) $method)."\n".(string) $path."\n".(string) $timestamp."\n".(string) $nonce."\n".hash('sha256', (string) $body);
    }

    public static function sign($secret, $method, $path, $timestamp, $nonce, $body)
    {
        return hash_hmac('sha256', self::canonical($method, $path, $timestamp, $nonce, $body), (string) $secret);
    }

    public static function verifyWebhook($secret, $timestamp, $eventId, $body, $signature)
    {
        $expected = hash_hmac('sha256', (string) $timestamp."\n".(string) $eventId."\n".hash('sha256', (string) $body), (string) $secret);

        return is_string($signature) && hash_equals($expected, strtolower($signature));
    }
}
