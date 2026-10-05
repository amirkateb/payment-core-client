<?php

namespace Avaztek\PaymentCore;

use Avaztek\PaymentCore\Exceptions\PaymentCoreException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class PaymentClient
{
    protected $baseUrl;
    protected $keyId;
    protected $secret;
    protected $http;

    public function __construct(array $config = array(), ClientInterface $http = null)
    {
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->keyId = (string) ($config['key_id'] ?? '');
        $this->secret = (string) ($config['secret'] ?? '');
        if ($this->baseUrl === '' || $this->keyId === '' || $this->secret === '') {
            throw new PaymentCoreException('Payment Core base_url, key_id and secret are required.');
        }
        $this->http = $http ?: new Client(array(
            'timeout' => (float) ($config['timeout'] ?? 15),
            'connect_timeout' => (float) ($config['connect_timeout'] ?? 5),
            'http_errors' => false,
        ));
    }

    public function ping()
    {
        return $this->request('GET', '/api/payment/v1/ping');
    }

    public function createTransaction(array $payload)
    {
        return $this->request('POST', '/api/payment/v1/transactions', $payload);
    }

    public function transaction($id)
    {
        return $this->request('GET', '/api/payment/v1/transactions/'.rawurlencode((string) $id));
    }

    public function checkoutUrl($id)
    {
        return $this->baseUrl.'/pay/'.rawurlencode((string) $id);
    }

    public function verifyWebhook($timestamp, $eventId, $rawBody, $signature)
    {
        return Signer::verifyWebhook($this->secret, $timestamp, $eventId, $rawBody, $signature);
    }

    public function request($method, $path, array $payload = null)
    {
        $body = $payload === null ? '' : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            throw new PaymentCoreException('Payload could not be encoded as JSON.');
        }
        $timestamp = time();
        $nonce = $this->nonce();
        $signature = Signer::sign($this->secret, $method, $path, $timestamp, $nonce, $body);
        $options = array('headers' => array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Payment-Key' => $this->keyId,
            'X-Payment-Timestamp' => (string) $timestamp,
            'X-Payment-Nonce' => $nonce,
            'X-Payment-Signature' => $signature,
            'User-Agent' => 'Avaztek-Payment-Core-PHP/1.0',
        ));
        if ($body !== '') {
            $options['body'] = $body;
        }
        $response = $this->http->request($method, $this->baseUrl.$path, $options);
        $raw = (string) $response->getBody();
        $json = json_decode($raw, true);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $message = is_array($json) ? ($json['message'] ?? $json['error'] ?? 'Payment Core request failed.') : 'Payment Core request failed.';
            throw new PaymentCoreException((string) $message, $response->getStatusCode(), $raw);
        }

        return is_array($json) ? $json : array();
    }

    protected function nonce()
    {
        return rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }
}
