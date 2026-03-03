# Changelog

All notable changes to `Cloudflare Laravel Request` will be documented in this file.

## v3.0.1 - 2026-03-03

### Fixed

- Fixed `Undefined array key` error on `browserFamily()`, `osFamily()`, and other Agent accessors when DeviceDetector returned partial data missing expected keys (e.g. `family`, `engine`). The parsed client/OS arrays now merge with defaults
  instead of replacing them, so missing keys fall back gracefully.

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v3.0.0...v3.0.1

## v3.0.0 - 2026-02-20

### Summary

Major rewrite: bot detection rebuilt with CrawlerDetect, country codes validated against ISO 3166-1, new CF verified bot category support, language/ASN headers, and idiot-proof Cloudflare API commands. All nullable return types on
device/browser/OS methods tightened to non-nullable.

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v2.0.4...v3.0.0

---

### Highlights

- New: `verifiedBotCategory()` - returns CF verified bot category (`search_engine`, `advertising`, etc.) via `X-BOT-CAT` header (all CF plans)
- New: `isVerifiedBot()` - true if CF reports a verified bot category
- New: `bot()` - returns bot name string or `false`
- New: `isTor()` - detects Tor network traffic (`T1` country code)
- New: `language()` - primary language from `X-LANG` CF header with Laravel `Accept-Language` fallback
- New: `languages()` - all accepted languages from `Accept-Language` header
- New: `botScore()` / `botScoreData()` - CF Bot Management score (Enterprise; reads `X-BOT-SCORE` if manually configured)
- New: `cf-request:status` command - grouped table showing which CF transform rule headers are configured
- Improved: `cf-request:headers` command now updates existing rules via PATCH instead of silently skipping
- Improved: `country()` validates against ISO 3166-1 Alpha-2; non-country codes (`T1`, `XX`) return `null`
- Improved: All geo methods (`city()`, `region()`, `lat()`, etc.) gate on valid country - no partial geo data
- Improved: `isBot()` rebuilt with [CrawlerDetect](https://github.com/JayBizzle/Crawler-Detect) (1,400+ patterns) as primary, DeviceDetector as fallback
- Improved: `asn()` now returns `?int` instead of `?string`
- Removed: `threatScore()` - Cloudflare deprecated this; always returned 0 since v2.0.3
- Removed: `lang()` - replaced by `language()`
- Removed: All internal public getters (`getClientCountry()`, `getIsBot()`, `getASN()`, `getLang()`, etc.)

---

### Breaking changes

**Removed methods:**

- `threatScore()` - removed entirely (was deprecated in v2.0.3)
- `lang()` - renamed to `language()`
- `getClientCountry()`, `getClientCity()`, `getClientRegion()`, `getClientContinent()`, `getClientPostalCode()`, `getClientLat()`, `getClientLon()`, `getClientGeo()`, `getClientTimezone()`, `getIsBot()`, `getASN()`, `getLang()`,
  `getReferer()`, `getRefererDomain()` - internal getters removed from public API

**Return type changes:**

- `isBot()`: `?bool` -> `bool`
- `isMobile()`, `isTablet()`, `isDesktop()`, `isTv()`: `?bool` -> `bool`
- `deviceType()`, `deviceBrand()`, `deviceModel()`: `?string` -> `string`
- `os()`, `osName()`, `osVersion()`, `osFamily()`: `?string` -> `string`
- `osData()`: `?array` -> `array`
- `browser()`, `browserName()`, `browserVersion()`, `browserFamily()`: `?string` -> `string`
- `browserData()`: `?array` -> `array`
- `asn()`: `?string` -> `?int`
- `getHeader()`: `mixed` -> `?string`

**Behavioural changes:**

- `country()` now validates against ISO 3166-1 whitelist. Non-standard codes like `T1` (Tor) and `XX` (unknown) return `null`. Use `isTor()` to detect Tor traffic specifically.
- All geo methods return `null` when `country()` is `null`

---

### Upgrade guide

1. **Composer**
   ```bash
   composer require pdphilip/cf-request:^3.0
   ```

2. **Cloudflare transform rules** - run `php artisan cf-request:headers` to add new headers:
    - `X-BOT-CAT` -> `cf.verified_bot_category`
    - `X-LANG` -> `http.request.accepted_languages[0]`
    - `X-ASN` -> `ip.src.asnum` (if not already set)
    - The command now updates existing rules automatically

3. **Code changes**
    - Replace `$request->lang()` with `$request->language()`
    - Remove any `$request->threatScore()` logic
    - If you used `$request->asn()` as a string, note it now returns `?int`
    - If you null-checked device/browser methods (`$request->isMobile() === null`), that's no longer possible - they always return a value
    - If you relied on `country()` returning `T1` for Tor, use `$request->isTor()` instead

## v2.0.4 - 2026-01-23

### What's Changed

* Update matomo/device-detector requirement from 6.4.7 to 6.4.8 by @dependabot[bot] in https://github.com/pdphilip/cf-request/pull/9
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/pdphilip/cf-request/pull/10

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v2.0.3...v2.0.4

## v2.0.3 - 2025-11-20

### Summary

This release adds Cloudflare ASN and primary language support, improves user-agent/device detection behaviour, updates package and CI dependencies, and cleans up documentation.

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v1.0.2...v2.0.3


---

### Highlights

- New: `CfRequest::asn()` — returns the Autonomous System Number (ASN) when provided via Cloudflare headers.
- New: `CfRequest::lang()` — returns the browser's primary accepted language (from Cloudflare headers).
- New: `getHeader($key)` — convenience getter to fetch arbitrary header values from the request.
- Improved: Agent detection now exposes `isBot()` and uses device-detector fallback when Cloudflare headers are not definitive.
- Improved: Browser and OS name formatting now handles missing version parts gracefully.
- Deprecated: `CfRequest::threatScore()` — Cloudflare no longer provides the mapped threat score; the method remains for backwards compatibility and now returns 0.
- Documentation updated with new header mappings, examples, and badges.

---

### Breaking / Behavioural changes

- The old Cloudflare threat score mapping is removed. If your application relied on CfRequest::threatScore() for blocking/decisions, note that:

    - CfRequest::threatScore() now returns 0 (kept only for backwards compatibility).
    - Replace threatScore-based checks with other approaches such as:
        - ASN-based allow/deny checks via CfRequest::asn().
        - Relying on Cloudflare Firewall / Bot Management features.
        - Inspect other Cloudflare headers you configure in your ruleset.


- Transform rules in Cloudflare must be updated to populate X-ASN and X-LANG if you want those values available in Laravel requests.

---

### Upgrade guide

1. Composer

    - Run: composer update pdphilip/cf-request
    - Ensure project uses PHP 8.2+ and that illuminate/contracts compatibility with ^10 | ^11 | ^12 is acceptable.

2. Cloudflare transform rules

    - Update "Modify Request Header" rules to set:

        - X-ASN -> ip.src.asnum
        - X-LANG -> http.request.accepted_languages[0]

    - Remove reliance on X-THREAT-SCORE (it is no longer used by this package).


3. Application code

    - Replace any threatScore() logic with new strategies:

        - Use $request->asn() for ASN checks (block/allow lists).
        - Use $request->lang() for locale handling.
        - Use $request->isBot() which now falls back to device detection if headers are not present.

    - Examples:

        - Before: if ($request->threatScore() > 50) { ... }
        - After: // threatScore deprecated — consider alternative logic, e.g.:
            - if (in_array($request->asn(), $blockedAsns())) { ... }
            - or rely on Cloudflare Firewall rules for threat-based blocking.

## v1.0.2 - 2024-09-19

Bug fix: Agent was failing when the browser was not detectable

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v1.0.1...v1.0.2

## v1.0.1 - 2024-09-18

- Removed Middleware option

**Full Changelog**: https://github.com/pdphilip/cf-request/compare/v1.0.0...v1.0.1

## v1.0.0 - 2024-09-18

### Initial Release

Cloudflare Laravel Request inherits the request object from Laravel and parses specific headers from Cloudflare to provide additional information about the request, including:

- `CfRequest::ip()` - Original Client IP (Before it passes through any proxies)
- `CfRequest::country()` - Origin Country
- `CfRequest::timezone()` - Origin Timezone
- `CfRequest::city()` - Origin City
- `CfRequest::region()` - Origin Region
- `CfRequest::postalCode()` - Origin Postal Code
- `CfRequest::lat()` - Origin Latitude
- `CfRequest::lon()` - Origin Longitude
- `fRequest::isBot()` - If it's a bot
- `CfRequest::threatScore()` - Threat Score from Cloudflare

The User-Agent is also parsed to provide additional information about the device, including:

- `CfRequest::deviceType()` - Device Type (mobile, tablet, desktop, tv, etc)
- `CfRequest::deviceBrand()` - Device Brand
- `CfRequest::deviceModel()` - Device Model
- `CfRequest::os()` - Device OS
- `CfRequest::osVersion()` - Device OS Version
- `CfRequest::browser()` - Device Browser
- `CfRequest::browserVersion()` - Device Browser Version

With this package, you can:

- Replace `Request $request` with `CfRequest $request` in your controller methods to access the additional methods.
- Call the `CfRequest` facade anywhere in your application to access this information.
