<?php

namespace PDPhilip\CfRequest\Http\Controllers;

use PDPhilip\CfRequest\CfRequest as Request;
use PDPhilip\CfRequest\Facades\CfRequest;

class CloudflareStatusController
{
    public function index()
    {
        $allowStatusView = config('cf-request.allowStatusView');
        if (! $allowStatusView) {
            abort(403, 'Unauthorized');
        }

        return [
            'Cloudflare Headers Present' => CfRequest::detectCloudflare(),
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
            'CfRequest::botScore()' => CfRequest::botScore(),
            'CfRequest::botScoreData()' => CfRequest::botScoreData(),
            'CfRequest::referer()' => CfRequest::referer(),
            'CfRequest::refererDomain()' => CfRequest::refererDomain(),
        ];

    }

    public function indexAsRequest(Request $request)
    {
        $allowStatusView = config('cf-request.allowStatusView');
        if (! $allowStatusView) {
            abort(403, 'Unauthorized');
        }

        return [
            'Cloudflare Headers Present' => $request->detectCloudflare(),
            'location' => [
                '$request->country()' => $request->country(),
                '$request->timezone()' => $request->timezone(),
                '$request->city()' => $request->city(),
                '$request->region()' => $request->region(),
                '$request->postalCode()' => $request->postalCode(),
                '$request->lat()' => $request->lat(),
                '$request->lon()' => $request->lon(),
                '$request->geo()' => $request->geo(),
            ],
            'device' => [
                '$request->isMobile()' => $request->isMobile(),
                '$request->isTablet()' => $request->isTablet(),
                '$request->isDesktop()' => $request->isDesktop(),
                '$request->isTv()' => $request->isTv(),
                '$request->deviceType()' => $request->deviceType(),
                '$request->deviceBrand()' => $request->deviceBrand(),
                '$request->deviceModel()' => $request->deviceModel(),
            ],
            'os' => [
                '$request->os()' => $request->os(),
                '$request->osName()' => $request->osName(),
                '$request->osVersion()' => $request->osVersion(),
                '$request->osFamily()' => $request->osFamily(),
                '$request->osData()' => $request->osData(),
            ],
            'browser' => [
                '$request->browser()' => $request->browser(),
                '$request->browserName()' => $request->browserName(),
                '$request->browserVersion()' => $request->browserVersion(),
                '$request->browserFamily()' => $request->browserFamily(),
                '$request->browserData()' => $request->browserData(),
            ],
            '$request->ip()' => $request->ip(),
            '$request->isBot()' => $request->isBot(),
            '$request->botScore()' => $request->botScore(),
            '$request->botScoreData()' => $request->botScoreData(),
            '$request->referer()' => $request->referer(),
            '$request->refererDomain()' => $request->refererDomain(),
        ];

    }
}
