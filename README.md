<div align="center">

# Cloudflare Laravel Request

<img src="https://cdn.snipform.io/pdphilip/cf-request/cf-request.png" alt="Cloudflare Laravel Request" />

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pdphilip/cf-request.svg?style=flat-square)](https://packagist.org/packages/pdphilip/cf-request) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pdphilip/cf-request/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pdphilip/cf-request/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pdphilip/cf-request/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pdphilip/cf-request/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) 

</div>

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

## Highlights

### Lean into Cloudflare's Security

```php
public function register(CfRequest $request)
{
    if ($request->isBot()) {
        abort(403, 'Naughty bots');
    }
    if ($request->threatScore() > 50) {
        abort(403, 'Thanks but no thanks');
    }
    $attributes = $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        //... etc
    ]);
   //... etc
}
```

### Set the timezone based on the origin

```php
date_default_timezone_set(CfRequest::timezone());
// Now carbon dates will be parsed for the user's timezone
```

### Apply logic based on the user's country

```php

public function welcome()
{
   if (CfRequest::country() === 'US') {
         return view('welcome_us');
   }
   return view('welcome');
}
```

### Apply logic based on device type

```php
public function welcome()
{
    $loadVideo = true;
    if (CfRequest::deviceType() === 'mobile') {
        $loadVideo = false;
    }
    // etc
}
```

## Requirements

- Laravel 10+
- Cloudflare as a proxy (though it will work without it and have no data on the CF-specific headers)

## Installation

Add the package via composer:

```bash
composer require pdphilip/cf-request
```

Then install with:

```bash
php artisan cf-request:install
```

## Cloudflare Setup

<details>

<summary>Option 1: Via Cloudflare API</summary>

---

### Step 1: Copy Zone ID

- Go to your Cloudflare dashboard
- Click on the domain you want to configure
- Copy the Zone ID
- Save in ENV as `CF_API_ZONE_ID`

<img src="https://cdn.snipform.io/pdphilip/cf-request/zoneId.png" alt="Cloudflare Laravel Request - zoneid" />

### Step 2: Create an API Token

- Navigate to: https://dash.cloudflare.com/profile/api-tokens
- Click on "Create Token"
- Select: Create Custom Token (Get started)

#### Token Configuration

- {Enter Token name}
- Permissions
    - Account: Account Rulesets: Edit
    - Zone: Transform Rules: Edit
- Account Resources
    - Include: All Accounts
- Zone Resources
    - Include: All Zones

<img src="https://cdn.snipform.io/pdphilip/cf-request/token-perms.png" alt="Cloudflare Laravel Request - token perms" />

- Create Token and Save in ENV as `CF_API_TOKEN`

## Run the artisan command:

```bash
php artisan cf-request:headers
```
<img src="https://cdn.snipform.io/pdphilip/cf-request/cf-request-headers.gif" alt="Cloudflare Laravel Request - artisan" />

---

</details>


<details>

<summary>Option 2: Manually on Cloudflare</summary>

---
### Navigate to "Modify Request Header"

- Go to your Cloudflare dashboard
- Click on the domain you want to configure
- Click on the "Rules -> Transform Rules" menu
- Select "Modify Request Header" tab
- Click "Create a Rule"

### Creating the rule

- Name: "Laravel Headers:
- Select "All incoming requests"
- Set the following headers:

> Set dynamic    
> X-AGENT    
> http.user_agent

> Set dynamic    
> X-IP    
> ip.src

> Set dynamic    
> X-COUNTRY    
> ip.src.country

> Set dynamic    
> X-CONTINENT    
> ip.src.continent

> Set dynamic    
> X-CITY    
> ip.src.city

> Set dynamic    
> X-POSTAL-CODE    
> ip.src.postal_code

> Set dynamic    
> X-REGION    
> ip.src.region

> Set dynamic    
> X-TIMEZONE    
> ip.src.timezone.name

> Set dynamic    
> X-LAT    
> ip.src.lat

> Set dynamic    
> X-LON    
> ip.src.lon

> Set dynamic    
> X-REFERER    
> http.referer

> Set dynamic    
> X-IS-BOT    
> cf.client.bot

> Set dynamic    
> X-THREAT-SCORE    
> cf.threat_score

---

</details>

## Usage

All the standard Laravel request methods are available, with the following additional methods:

### `CfRequest::country()`

### `CfRequest::city()`

### `CfRequest::region()`

### `CfRequest::postalCode()`

### `CfRequest::lat()`

### `CfRequest::lon()`

### `CfRequest::timezone()`

### `CfRequest::isBot()`

### `CfRequest::threatScore()`

### `CfRequest::isMobile()`

### `CfRequest::isTablet()`

### `CfRequest::isDesktop()`

### `CfRequest::isTv()`

### `CfRequest::deviceType()`

### `CfRequest::deviceBrand()`

### `CfRequest::deviceModel()`

### `CfRequest::os()`

### `CfRequest::osVersion()`

### `CfRequest::osFamily()`

### `CfRequest::browser()`

### `CfRequest::browserVersion()`

### `CfRequest::browserName()`

### `CfRequest::browserFamily()`

### `CfRequest::referer()`

### `CfRequest::refererDomain()`

You can use the `CfRequest` facade or inject the `CfRequest $request` class into your controller methods.

## Testing headers

- This package comes with a test route that will display the headers being parsed from Cloudflare.
- You can access this route by visiting `/cf-request/status` on your application.
- You can disable this in the config file or by setting the `CF_ALLOW_STATUS_VIEW` environment variable to `false`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [David Philip](https://github.com/pdphilip)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
