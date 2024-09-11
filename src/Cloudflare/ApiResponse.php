<?php

namespace PDPhilip\CfRequest\Cloudflare;

use Illuminate\Support\Facades\Http;

class ApiResponse
{
    public $apiBase = false;

    public $token = false;

    public bool $success = false;

    public int $code = 200;

    public mixed $result;

    public mixed $message = 'ok';

    public function __construct()
    {
        $this->token = config('cf-agent.cf.token');
        $this->apiBase = config('cf-agent.cf.api');
    }

    public function requestHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
        ];
    }

    public function endPoint($endpoint): string
    {
        return $this->apiBase.$endpoint;
    }

    public static function get($endpoint): ApiResponse
    {
        $api = new ApiResponse;
        $fetch = Http::asJson()->withHeaders($api->requestHeaders())->get($api->endPoint($endpoint));
        $api->parseResponse($fetch->getBody()->getContents());

        return $api;

    }

    public static function delete($endpoint): ApiResponse
    {
        $api = new ApiResponse;
        $fetch = Http::asJson()->withHeaders($api->requestHeaders())->delete($api->endPoint($endpoint));
        $api->parseResponse($fetch->getBody()->getContents());

        return $api;
    }

    public static function post($endpoint, $payload): ApiResponse
    {
        $api = new ApiResponse;
        $fetch = Http::asJson()->withHeaders($api->requestHeaders())->post($api->endPoint($endpoint), $payload);
        $api->parseResponse($fetch->getBody()->getContents());

        return $api;
    }

    public static function patch($endpoint, $payload): ApiResponse
    {
        $api = new ApiResponse;
        $fetch = Http::asJson()->withHeaders($api->requestHeaders())->patch($api->endPoint($endpoint), $payload);
        $api->parseResponse($fetch->getBody()->getContents());

        return $api;
    }

    public function isSuccessFul()
    {
        return $this->success;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getError()
    {
        if (! $this->success) {
            return [
                'code' => $this->code,
                'message' => $this->message,
            ];
        }

        return null;
    }

    public function parseResponse($response)
    {
        $response = (object) json_decode($response);

        $result = null;
        if (! empty($response->result)) {
            $result = $response->result;
            if (is_array($response->result)) {
                $result = collect($response->result);
            }

        }
        $this->success = $response->success ?? false;
        $this->result = $result;
        if (! empty($response->errors)) {
            $this->code = $response->errors[0]->code ?? 400;
            $this->message = $response->errors[0]->message ?? 'Unknown Error';
        }
    }

    public static function setError($code, $message)
    {
        $api = new ApiResponse;
        $api->success = false;
        $api->code = $code;
        $api->message = $message;
        $api->result = 'error';

        return $api;
    }

    public function asArray()
    {
        return [
            'success' => $this->success,
            'result' => $this->result,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
