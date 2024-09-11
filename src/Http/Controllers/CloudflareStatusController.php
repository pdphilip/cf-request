<?php

namespace PDPhilip\CfRequest\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PDPhilip\CfRequest\Facades\CfRequest;

class CloudflareStatusController
{
    public function index(Request $request)
    {

        $hasMiddleware = false;
        try {
            //@phpstan-ignore-next-line
            $request->detectCloudflare();
            $hasMiddleware = true;
        } catch (Exception $e) {

        }

        return [
            'Middleware Loaded (CfRequestMiddleware)' => $hasMiddleware,
            'Cloudflare Headers' => CfRequest::detectCloudflare(),
            'location' => [
                'CfRequest::country()' => CfRequest::country(),
                'CfRequest::timezone()' => CfRequest::timezone(),
                'CfRequest::city()' => CfRequest::city(),
                'CfRequest::region()' => CfRequest::region(),
                'CfRequest::postalCode()' => CfRequest::postalCode(),
                'CfRequest::lat()' => CfRequest::lat(),
                'CfRequest::lon()' => CfRequest::lon(),
                'CfRequest::geo()' => CfRequest::geo(),
            ],
            'device' => [
                'CfRequest::isMobile()' => CfRequest::isMobile(),
                'CfRequest::isTablet()' => CfRequest::isTablet(),
                'CfRequest::isDesktop()' => CfRequest::isDesktop(),
                'CfRequest::isTv()' => CfRequest::isTv(),
                'CfRequest::isTouch()' => CfRequest::isTouch(),
                'CfRequest::deviceType()' => CfRequest::deviceType(),
                'CfRequest::deviceBrand()' => CfRequest::deviceBrand(),
                'CfRequest::deviceModel()' => CfRequest::deviceModel(),
            ],
            'os' => [
                'CfRequest::os()' => CfRequest::os(),
                'CfRequest::osName()' => CfRequest::osName(),
                'CfRequest::osVersion()' => CfRequest::osVersion(),
                'CfRequest::osFamily()' => CfRequest::osFamily(),
                'CfRequest::osData()' => CfRequest::osData(),
            ],
            'browser' => [
                'CfRequest::browser()' => CfRequest::browser(),
                'CfRequest::browserName()' => CfRequest::browserName(),
                'CfRequest::browserVersion()' => CfRequest::browserVersion(),
                'CfRequest::browserFamily()' => CfRequest::browserFamily(),
                'CfRequest::browserData()' => CfRequest::browserData(),
            ],
            'CfRequest::ip()' => CfRequest::ip(),
            'CfRequest::isBot()' => CfRequest::isBot(),
            'CfRequest::threatScore()' => CfRequest::threatScore(),
            'CfRequest::referer()' => CfRequest::referer(),
            'CfRequest::refererDomain()' => CfRequest::refererDomain(),
        ];

    }
}
