# Changelog

All notable changes to `Cloudflare Laravel Request` will be documented in this file.

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
