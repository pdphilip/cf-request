<?php

// Eleganced at 2026-02-20

namespace PDPhilip\CfRequest;

use Illuminate\Http\Request;
use PDPhilip\CfRequest\Agent\Agent;
use PDPhilip\CfRequest\Geo\Countries;

class CfRequest extends Request
{
    protected ?Agent $agent = null;

    protected Request $originalRequest;

    public function __construct(?Request $request = null)
    {
        parent::__construct();

        $request ??= app('request');
        $this->originalRequest = $request;

        $this->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent()
        );

        $this->languages = $request->languages;
        $this->charsets = $request->charsets;
        $this->encodings = $request->encodings;
        $this->acceptableContentTypes = $request->acceptableContentTypes;
        $this->pathInfo = $request->pathInfo;
        $this->requestUri = $request->requestUri;
        $this->baseUrl = $request->baseUrl;
        $this->method = $request->method;
        $this->format = $request->format;
        $this->session = $request->session;
        $this->locale = $request->locale;
        $this->defaultLocale = $request->defaultLocale;
        $this->json = $request->json;
        $this->convertedFiles = $request->convertedFiles;
        $this->userResolver = $request->userResolver;
        $this->routeResolver = $request->routeResolver;
    }

    // ----------------------------------------------------------------------
    // Geolocation
    // ----------------------------------------------------------------------

    public function country(): ?string
    {
        $code = $this->headers->get('X-COUNTRY') ?? $this->headers->get('CF-IPCountry');

        return Countries::isValid($code) ? $code : null;
    }

    public function city(): ?string
    {
        return $this->country() ? $this->headers->get('X-CITY') : null;
    }

    public function region(): ?string
    {
        return $this->country() ? $this->headers->get('X-REGION') : null;
    }

    public function continent(): ?string
    {
        return $this->country() ? $this->headers->get('X-CONTINENT') : null;
    }

    public function postalCode(): ?string
    {
        return $this->country() ? $this->headers->get('X-POSTAL-CODE') : null;
    }

    public function lat(): ?string
    {
        return $this->country() ? $this->headers->get('X-LAT') : null;
    }

    public function lon(): ?string
    {
        return $this->country() ? $this->headers->get('X-LON') : null;
    }

    public function geo(): ?array
    {
        $lat = $this->lat();
        $lon = $this->lon();

        if ($lat && $lon) {
            return ['lat' => $lat, 'lon' => $lon];
        }

        return null;
    }

    public function timezone(): ?string
    {
        return $this->country() ? $this->headers->get('X-TIMEZONE') : null;
    }

    public function isTor(): bool
    {
        $code = $this->headers->get('X-COUNTRY') ?? $this->headers->get('CF-IPCountry');

        return $code === 'T1';
    }

    // ----------------------------------------------------------------------
    // Bot Detection
    // ----------------------------------------------------------------------

    public function isBot(): bool
    {
        return $this->getAgent()->isBot();
    }

    public function bot(): string|false
    {
        return $this->getAgent()->bot();
    }

    public function verifiedBotCategory(): ?string
    {
        return $this->headers->get('X-BOT-CAT');
    }

    public function isVerifiedBot(): bool
    {
        return $this->verifiedBotCategory() !== null;
    }

    public function botScore(): ?int
    {
        $score = $this->headers->get('X-BOT-SCORE');

        return $score !== null ? (int) $score : null;
    }

    public function botScoreData(): array
    {
        $score = $this->botScore();

        if ($score === null) {
            return ['score' => null, 'is_bot' => null, 'key' => 'no_header', 'value' => 'CF header not found'];
        }
        if ($score === 0) {
            return ['score' => 0, 'is_bot' => null, 'key' => 'not_computed', 'value' => 'CF did not compute a score (Bot score is a pro feature on CF)'];
        }
        if ($score === 1) {
            return ['score' => 1, 'is_bot' => true, 'key' => 'automated', 'value' => 'Automated Bot'];
        }
        if ($score <= 29) {
            return ['score' => $score, 'is_bot' => true, 'key' => 'likely_automated', 'value' => 'Likely Automated Bot (Range 2 to 29)'];
        }
        if ($score === 99) {
            return ['score' => 99, 'is_bot' => false, 'key' => 'human', 'value' => 'Human (Max score)'];
        }

        return ['score' => $score, 'is_bot' => false, 'key' => 'likely_human', 'value' => 'Likely Human (Range 30 to 99)'];
    }

    // ----------------------------------------------------------------------
    // Referer
    // ----------------------------------------------------------------------

    public function referer(): ?string
    {
        return $this->headers->get('X-REFERER') ?? $this->headers->get('Referer');
    }

    public function refererDomain(): ?string
    {
        $referer = $this->referer();

        return $referer ? (parse_url($referer, PHP_URL_HOST) ?: null) : null;
    }

    // ----------------------------------------------------------------------
    // Device
    // ----------------------------------------------------------------------

    public function isMobile(): bool
    {
        return $this->getAgent()->isMobile();
    }

    public function isTablet(): bool
    {
        return $this->getAgent()->isTablet();
    }

    public function isDesktop(): bool
    {
        return $this->getAgent()->isDesktop();
    }

    public function isTv(): bool
    {
        return $this->getAgent()->isTv();
    }

    public function deviceType(): string
    {
        return $this->getAgent()->deviceType();
    }

    public function deviceBrand(): string
    {
        return $this->getAgent()->deviceBrand();
    }

    public function deviceModel(): string
    {
        return $this->getAgent()->deviceModel();
    }

    // ----------------------------------------------------------------------
    // OS
    // ----------------------------------------------------------------------

    public function os(): string
    {
        return $this->getAgent()->os();
    }

    public function osName(): string
    {
        return $this->getAgent()->osName();
    }

    public function osVersion(): string
    {
        return $this->getAgent()->osVersion();
    }

    public function osFamily(): string
    {
        return $this->getAgent()->osFamily();
    }

    public function osData(): array
    {
        return $this->getAgent()->osData();
    }

    // ----------------------------------------------------------------------
    // Browser
    // ----------------------------------------------------------------------

    public function browser(): string
    {
        return $this->getAgent()->browser();
    }

    public function browserName(): string
    {
        return $this->getAgent()->browserName();
    }

    public function browserVersion(): string
    {
        return $this->getAgent()->browserVersion();
    }

    public function browserFamily(): string
    {
        return $this->getAgent()->browserFamily();
    }

    public function browserData(): array
    {
        return $this->getAgent()->browserData();
    }

    // ----------------------------------------------------------------------
    // Cloudflare
    // ----------------------------------------------------------------------

    public function detectCloudflare(): bool
    {
        return $this->headers->has('CF-ray');
    }

    public function getHeader(string $key): ?string
    {
        return $this->headers->get($key);
    }

    // ----------------------------------------------------------------------
    // Request Overrides — prioritize CF headers over standard HTTP
    // ----------------------------------------------------------------------

    /** {@inheritDoc} */
    public function getClientIp(): ?string
    {
        return $this->headers->get('X-IP')
            ?? $this->headers->get('CF-Connecting-IP')
            ?? $this->getClientIps()[0];
    }

    /** {@inheritDoc} */
    public function userAgent(): ?string
    {
        return $this->headers->get('X-AGENT') ?? $this->headers->get('User-Agent');
    }

    // Trusted proxy delegates — must use originalRequest for trusted values

    public function getBaseUrl(): string
    {
        return $this->originalRequest->getBaseUrl();
    }

    public function getClientIps(): array
    {
        return $this->originalRequest->getClientIps();
    }

    public function getPort(): int|string|null
    {
        return $this->originalRequest->getPort();
    }

    public function isSecure(): bool
    {
        return $this->originalRequest->isSecure();
    }

    public function getHost(): string
    {
        return $this->originalRequest->getHost();
    }

    public function isFromTrustedProxy(): bool
    {
        return $this->originalRequest->isFromTrustedProxy();
    }

    // ----------------------------------------------------------------------
    // Internal
    // ----------------------------------------------------------------------

    protected function getAgent(): Agent
    {
        if (! $this->agent) {
            $this->agent = new Agent($this->userAgent() ?? '');
        }

        return $this->agent;
    }
}
