# Changelog

All notable changes to `Cloudflare Laravel Request` will be documented in this file.

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
