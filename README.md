<div align="center">

# Cloudflare Laravel Request

<img src="https://cdn.snipform.io/pdphilip/cf-request/cf-request.png" alt="Cloudflare Laravel Request" />

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pdphilip/cf-request.svg?style=flat-square)](https://packagist.org/packages/pdphilip/cf-request)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pdphilip/cf-request/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pdphilip/cf-request/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](http://img.shields.io/packagist/dt/pdphilip/cf-request.svg)](https://packagist.org/packages/pdphilip/cf-request)

</div>

A drop-in replacement for Laravel's `Request` that extracts Cloudflare metadata - geolocation, device info, bot detection - from transform rule headers. Use it as a facade or inject it like a normal request.

```php
public function register(CfRequest $request)
{
    if ($request->isBot()) {
        abort(403);
    }

    $country  = $request->country();   // 'US'
    $timezone = $request->timezone();  // 'America/New_York'
    $device   = $request->deviceType(); // 'mobile'
    $browser  = $request->browserName(); // 'Chrome'
}
```

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12
- Cloudflare as a proxy (works without it, CF-specific methods return null)

## Installation

```bash
composer require pdphilip/cf-request
php artisan cf-request:install
```

## Cloudflare Setup

The package reads custom headers that Cloudflare injects via transform rules. You need to configure these rules on your Cloudflare zone.

<details>
<summary>Option 1: Via Cloudflare API (recommended)</summary>

---

### Step 1: Get your Zone ID

- Cloudflare dashboard > select your domain
- Copy the **Zone ID** from the sidebar
- Save as `CF_API_ZONE_ID` in your `.env`

<img src="https://cdn.snipform.io/pdphilip/cf-request/zoneId.png" alt="Zone ID location" />

### Step 2: Create an API Token

- Go to https://dash.cloudflare.com/profile/api-tokens
- Create Custom Token with these permissions:
    - Account > Account Rulesets: **Edit**
    - Zone > Transform Rules: **Edit**
    - Account Resources: All Accounts
    - Zone Resources: All Zones

<img src="https://cdn.snipform.io/pdphilip/cf-request/token-perms.png" alt="Token permissions" />

- Save the token as `CF_API_TOKEN` in your `.env`

### Step 3: Run the command

```bash
php artisan cf-request:headers
```

This creates a "Laravel Headers" transform rule on your zone with all required headers.

### Step 4: Verify

```bash
php artisan cf-request:status
```

Shows a grouped table of every expected header and whether it's configured on Cloudflare.

---

</details>

<details>
<summary>Option 2: Manual setup on Cloudflare dashboard</summary>

---

### Navigate to Transform Rules

- Cloudflare dashboard > select your domain
- Rules > Transform Rules > Modify Request Header
- Create a Rule

### Rule configuration

- **Name:** Laravel Headers
- **When:** All incoming requests
- **Then:** Set the following headers:

| Type | Header | Expression |
|------|--------|------------|
| Set dynamic | `X-IP` | `ip.src` |
| Set dynamic | `X-ASN` | `ip.src.asnum` |
| Set dynamic | `X-AGENT` | `http.user_agent` |
| Set dynamic | `X-COUNTRY` | `ip.src.country` |
| Set dynamic | `X-CITY` | `ip.src.city` |
| Set dynamic | `X-REGION` | `ip.src.region` |
| Set dynamic | `X-CONTINENT` | `ip.src.continent` |
| Set dynamic | `X-POSTAL-CODE` | `ip.src.postal_code` |
| Set dynamic | `X-LAT` | `ip.src.lat` |
| Set dynamic | `X-LON` | `ip.src.lon` |
| Set dynamic | `X-TIMEZONE` | `ip.src.timezone.name` |
| Set dynamic | `X-REFERER` | `http.referer` |
| Set dynamic | `X-LANG` | `http.request.accepted_languages[0]` |
| Set dynamic | `X-BOT-CAT` | `cf.verified_bot_category` |

---

</details>

## Usage

Use the `CfRequest` facade or inject `CfRequest $request` in place of Laravel's `Request`.

### Geolocation

```php
$request->country();    // 'AU' (ISO 3166-1 Alpha-2, validated)
$request->city();       // 'Sydney'
$request->region();     // 'New South Wales'
$request->continent();  // 'OC'
$request->postalCode(); // '2000'
$request->lat();        // '-33.8688'
$request->lon();        // '151.2093'
$request->geo();        // ['lat' => '-33.8688', 'lon' => '151.2093'] or null
$request->timezone();   // 'Australia/Sydney'
$request->isTor();      // true if traffic is from the Tor network
```

Country codes are validated against the ISO 3166-1 standard. Non-country values like `T1` (Tor) and `XX` (unknown) return `null` from `country()`, which also nulls all dependent geo fields. Use `isTor()` to detect Tor traffic specifically.

### Bot Detection

```php
$request->isBot();      // true/false (CrawlerDetect + DeviceDetector)
$request->bot();        // 'Googlebot' or 'no_user_agent' or false

// CF verified bot category (all plans)
$request->verifiedBotCategory(); // 'search_engine', 'advertising', etc.
$request->isVerifiedBot();       // true/false

// CF bot score (Enterprise only)
$request->botScore();     // 0-99 integer or null
$request->botScoreData(); // ['score' => 99, 'is_bot' => false, 'key' => 'human', 'value' => '...']
```

Bot detection works without Cloudflare. The package uses [CrawlerDetect](https://github.com/JayBizzle/Crawler-Detect) (1,400+ bot patterns) as the primary check, with [DeviceDetector](https://github.com/matomo-org/device-detector) as a fallback. Requests with no User-Agent are flagged as bots.

### Device

```php
$request->deviceType();  // 'desktop', 'mobile', 'tablet', 'tv'
$request->isMobile();    // true/false
$request->isTablet();    // true/false
$request->isDesktop();   // true/false
$request->isTv();        // true/false
$request->deviceBrand(); // 'Apple'
$request->deviceModel(); // 'iPhone'
```

### Browser

```php
$request->browser();        // 'Chrome 120.0'
$request->browserName();    // 'Chrome'
$request->browserVersion(); // '120.0'
$request->browserFamily();  // 'Chrome'
$request->browserData();    // full parsed array
```

### OS

```php
$request->os();        // 'Mac 10.15'
$request->osName();    // 'Mac'
$request->osVersion(); // '10.15'
$request->osFamily();  // 'Mac'
$request->osData();    // full parsed array
```

### Language

```php
$request->language();  // 'en_US' (from X-LANG header, falls back to Accept-Language)
$request->languages(); // ['en_US', 'en', 'de'] (from Accept-Language)
```

### Request Overrides

```php
$request->getClientIp(); // prioritizes X-IP > CF-Connecting-IP > standard
$request->asn();         // 13335 (Autonomous System Number)
$request->userAgent();   // prioritizes X-AGENT > User-Agent
$request->referer();     // prioritizes X-REFERER > Referer
$request->refererDomain(); // 'google.com' (parsed from referer)
```

### Cloudflare

```php
$request->detectCloudflare(); // true if CF-ray header is present
$request->getHeader('X-CUSTOM'); // read any header
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `cf-request:install` | Publish config and register service provider |
| `cf-request:headers` | Create transform rule headers on Cloudflare via the API |
| `cf-request:status` | Check which headers are configured on Cloudflare |

## Debug Route

Visit `/cf-request/status` in your browser to see all parsed headers as JSON. Disable with `CF_ALLOW_STATUS_VIEW=false` in your `.env`.

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Credits

- [David Philip](https://github.com/pdphilip)

## License

The MIT License (MIT). See [License File](LICENSE.md) for details.
