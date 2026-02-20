<?php

use Illuminate\Http\Request;
use PDPhilip\CfRequest\CfRequest;

function fakeRequest(array $headers = [], array $server = []): CfRequest
{
    $serverParams = array_merge([
        'REQUEST_URI' => '/test',
        'REQUEST_METHOD' => 'GET',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => 443,
        'HTTPS' => 'on',
    ], $server);

    foreach ($headers as $key => $value) {
        $serverKey = 'HTTP_'.str_replace('-', '_', strtoupper($key));
        $serverParams[$serverKey] = $value;
    }

    $request = new Request(server: $serverParams);

    return new CfRequest($request);
}

// ----------------------------------------------------------------------
// Geolocation
// ----------------------------------------------------------------------

describe('geolocation from X-* headers', function () {
    it('reads country from X-COUNTRY', function () {
        $cf = fakeRequest(['X-COUNTRY' => 'US']);

        expect($cf->country())->toBe('US');
    });

    it('falls back to CF-IPCountry when X-COUNTRY missing', function () {
        $cf = fakeRequest(['CF-IPCountry' => 'DE']);

        expect($cf->country())->toBe('DE');
    });

    it('prefers X-COUNTRY over CF-IPCountry', function () {
        $cf = fakeRequest([
            'X-COUNTRY' => 'FR',
            'CF-IPCountry' => 'DE',
        ]);

        expect($cf->country())->toBe('FR');
    });

    it('returns null when no country header', function () {
        $cf = fakeRequest();

        expect($cf->country())->toBeNull();
    });

    it('reads city', function () {
        $cf = fakeRequest(['X-CITY' => 'Berlin']);

        expect($cf->city())->toBe('Berlin');
    });

    it('reads region', function () {
        $cf = fakeRequest(['X-REGION' => 'Bavaria']);

        expect($cf->region())->toBe('Bavaria');
    });

    it('reads continent', function () {
        $cf = fakeRequest(['X-CONTINENT' => 'EU']);

        expect($cf->continent())->toBe('EU');
    });

    it('reads postal code', function () {
        $cf = fakeRequest(['X-POSTAL-CODE' => '10115']);

        expect($cf->postalCode())->toBe('10115');
    });

    it('reads lat and lon', function () {
        $cf = fakeRequest(['X-LAT' => '52.52', 'X-LON' => '13.405']);

        expect($cf->lat())->toBe('52.52');
        expect($cf->lon())->toBe('13.405');
    });

    it('returns geo array when both lat and lon present', function () {
        $cf = fakeRequest(['X-LAT' => '52.52', 'X-LON' => '13.405']);

        expect($cf->geo())->toBe(['lat' => '52.52', 'lon' => '13.405']);
    });

    it('returns null geo when lat or lon missing', function () {
        $cf = fakeRequest(['X-LAT' => '52.52']);

        expect($cf->geo())->toBeNull();
    });

    it('reads timezone', function () {
        $cf = fakeRequest(['X-TIMEZONE' => 'Europe/Berlin']);

        expect($cf->timezone())->toBe('Europe/Berlin');
    });

    it('returns null for all geo when no headers', function () {
        $cf = fakeRequest();

        expect($cf->city())->toBeNull();
        expect($cf->region())->toBeNull();
        expect($cf->continent())->toBeNull();
        expect($cf->postalCode())->toBeNull();
        expect($cf->lat())->toBeNull();
        expect($cf->lon())->toBeNull();
        expect($cf->timezone())->toBeNull();
    });
});

// ----------------------------------------------------------------------
// IP Resolution
// ----------------------------------------------------------------------

describe('IP resolution priority', function () {
    it('prefers X-IP header', function () {
        $cf = fakeRequest(['X-IP' => '1.2.3.4', 'CF-Connecting-IP' => '5.6.7.8']);

        expect($cf->getClientIp())->toBe('1.2.3.4');
    });

    it('falls back to CF-Connecting-IP', function () {
        $cf = fakeRequest(['CF-Connecting-IP' => '5.6.7.8']);

        expect($cf->getClientIp())->toBe('5.6.7.8');
    });
});

// ----------------------------------------------------------------------
// User Agent Override
// ----------------------------------------------------------------------

describe('user agent priority', function () {
    it('prefers X-AGENT over User-Agent', function () {
        $cf = fakeRequest([
            'X-AGENT' => 'CustomAgent/1.0',
            'User-Agent' => 'Mozilla/5.0',
        ]);

        expect($cf->userAgent())->toBe('CustomAgent/1.0');
    });

    it('falls back to User-Agent', function () {
        $cf = fakeRequest(['User-Agent' => 'Mozilla/5.0']);

        expect($cf->userAgent())->toBe('Mozilla/5.0');
    });
});

// ----------------------------------------------------------------------
// Referer
// ----------------------------------------------------------------------

describe('referer parsing', function () {
    it('reads referer from X-REFERER', function () {
        $cf = fakeRequest(['X-REFERER' => 'https://example.com/page']);

        expect($cf->referer())->toBe('https://example.com/page');
    });

    it('falls back to standard Referer header', function () {
        $cf = fakeRequest(['Referer' => 'https://fallback.com/page']);

        expect($cf->referer())->toBe('https://fallback.com/page');
    });

    it('extracts referer domain', function () {
        $cf = fakeRequest(['X-REFERER' => 'https://example.com/some/path?q=1']);

        expect($cf->refererDomain())->toBe('example.com');
    });

    it('returns null domain when no referer', function () {
        $cf = fakeRequest();

        expect($cf->refererDomain())->toBeNull();
    });
});

// ----------------------------------------------------------------------
// Cloudflare Detection
// ----------------------------------------------------------------------

describe('cloudflare detection', function () {
    it('detects cloudflare when CF-ray present', function () {
        $cf = fakeRequest(['CF-ray' => 'abc123-LAX']);

        expect($cf->detectCloudflare())->toBeTrue();
    });

    it('returns false when CF-ray absent', function () {
        $cf = fakeRequest();

        expect($cf->detectCloudflare())->toBeFalse();
    });
});

// ----------------------------------------------------------------------
// Bot Score
// ----------------------------------------------------------------------

describe('bot score', function () {
    it('reads bot score from header', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '45']);

        expect($cf->botScore())->toBe(45);
    });

    it('returns null when no bot score header', function () {
        $cf = fakeRequest();

        expect($cf->botScore())->toBeNull();
    });

    it('returns no_header data when missing', function () {
        $cf = fakeRequest();

        expect($cf->botScoreData()['key'])->toBe('no_header');
        expect($cf->botScoreData()['is_bot'])->toBeNull();
    });

    it('returns not_computed for score 0', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '0']);

        expect($cf->botScoreData()['key'])->toBe('not_computed');
    });

    it('returns automated for score 1', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '1']);
        $data = $cf->botScoreData();

        expect($data['key'])->toBe('automated');
        expect($data['is_bot'])->toBeTrue();
    });

    it('returns likely_automated for low scores', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '15']);
        $data = $cf->botScoreData();

        expect($data['key'])->toBe('likely_automated');
        expect($data['is_bot'])->toBeTrue();
    });

    it('returns likely_human for mid scores', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '50']);
        $data = $cf->botScoreData();

        expect($data['key'])->toBe('likely_human');
        expect($data['is_bot'])->toBeFalse();
    });

    it('returns human for max score', function () {
        $cf = fakeRequest(['X-BOT-SCORE' => '99']);
        $data = $cf->botScoreData();

        expect($data['key'])->toBe('human');
        expect($data['is_bot'])->toBeFalse();
    });
});

// ----------------------------------------------------------------------
// Generic Header
// ----------------------------------------------------------------------

describe('generic header access', function () {
    it('reads arbitrary header', function () {
        $cf = fakeRequest(['X-CUSTOM' => 'value']);

        expect($cf->getHeader('X-CUSTOM'))->toBe('value');
    });

    it('returns null for missing header', function () {
        $cf = fakeRequest();

        expect($cf->getHeader('X-NOPE'))->toBeNull();
    });
});

// ----------------------------------------------------------------------
// Agent / Device Detection
// ----------------------------------------------------------------------

describe('device detection via user agent', function () {
    it('detects desktop browser', function () {
        $cf = fakeRequest([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        expect($cf->isDesktop())->toBeTrue();
        expect($cf->isMobile())->toBeFalse();
        expect($cf->isTablet())->toBeFalse();
        expect($cf->deviceType())->toBe('desktop');
    });

    it('detects mobile browser', function () {
        $cf = fakeRequest([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ]);

        expect($cf->isMobile())->toBeTrue();
        expect($cf->isDesktop())->toBeFalse();
        expect($cf->deviceType())->toBe('mobile');
    });

    it('parses browser info', function () {
        $cf = fakeRequest([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        expect($cf->browserName())->toBe('Chrome');
        expect($cf->browserVersion())->not->toBeEmpty();
        expect($cf->browser())->toContain('Chrome');
        expect($cf->browserFamily())->toBe('Chrome');
        expect($cf->browserData())->toBeArray();
    });

    it('parses OS info', function () {
        $cf = fakeRequest([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        expect($cf->osName())->toBe('Mac');
        expect($cf->osVersion())->not->toBeEmpty();
        expect($cf->os())->toContain('Mac');
        expect($cf->osFamily())->not->toBeEmpty();
        expect($cf->osData())->toBeArray();
    });

    it('detects bot from user agent', function () {
        $cf = fakeRequest([
            'User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);

        expect($cf->isBot())->toBeTrue();
    });

    it('returns null for all device methods when no user agent', function () {
        $cf = fakeRequest();

        expect($cf->isMobile())->toBeNull();
        expect($cf->isDesktop())->toBeNull();
        expect($cf->isTablet())->toBeNull();
        expect($cf->deviceType())->toBeNull();
        expect($cf->deviceBrand())->toBeNull();
        expect($cf->deviceModel())->toBeNull();
        expect($cf->os())->toBeNull();
        expect($cf->browser())->toBeNull();
        expect($cf->isBot())->toBeNull();
    });
});

// ----------------------------------------------------------------------
// Full Request Simulation
// ----------------------------------------------------------------------

describe('full cloudflare request', function () {
    it('handles a complete cloudflare request', function () {
        $cf = fakeRequest([
            'CF-ray' => 'abc123-LAX',
            'X-IP' => '203.0.113.42',
            'X-COUNTRY' => 'AU',
            'X-CITY' => 'Sydney',
            'X-REGION' => 'New South Wales',
            'X-CONTINENT' => 'OC',
            'X-LAT' => '-33.8688',
            'X-LON' => '151.2093',
            'X-TIMEZONE' => 'Australia/Sydney',
            'X-POSTAL-CODE' => '2000',
            'X-REFERER' => 'https://google.com/search?q=test',
            'X-BOT-SCORE' => '99',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);

        expect($cf->detectCloudflare())->toBeTrue();
        expect($cf->getClientIp())->toBe('203.0.113.42');
        expect($cf->country())->toBe('AU');
        expect($cf->city())->toBe('Sydney');
        expect($cf->region())->toBe('New South Wales');
        expect($cf->continent())->toBe('OC');
        expect($cf->lat())->toBe('-33.8688');
        expect($cf->lon())->toBe('151.2093');
        expect($cf->geo())->toBe(['lat' => '-33.8688', 'lon' => '151.2093']);
        expect($cf->timezone())->toBe('Australia/Sydney');
        expect($cf->postalCode())->toBe('2000');
        expect($cf->referer())->toBe('https://google.com/search?q=test');
        expect($cf->refererDomain())->toBe('google.com');
        expect($cf->botScore())->toBe(99);
        expect($cf->isDesktop())->toBeTrue();
        expect($cf->browserName())->toBe('Chrome');
    });
});
