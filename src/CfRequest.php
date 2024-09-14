<?php

namespace PDPhilip\CfRequest;

use Illuminate\Http\Request;
use PDPhilip\CfRequest\Agent\Agent;

class CfRequest extends Request
{
    protected $agent;

    protected Request $originalRequest;

    public function __construct(Request $request)
    {
        parent::__construct();
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

    public function country(): ?string
    {
        return $this->getClientCountry();
    }

    public function city(): ?string
    {
        return $this->getClientCity();
    }

    public function region(): ?string
    {
        return $this->getClientRegion();
    }

    public function continent(): ?string
    {
        return $this->getClientContinent();
    }

    public function postalCode(): ?string
    {
        return $this->getClientPostalCode();
    }

    public function lat(): ?string
    {
        return $this->getClientLat();
    }

    public function lon(): ?string
    {
        return $this->getClientLon();
    }

    public function geo(): ?array
    {
        return $this->getClientGeo();
    }

    public function timezone(): ?string
    {
        return $this->getClientTimezone();
    }

    public function isBot(): ?bool
    {
        return $this->getIsBot();
    }

    public function threatScore(): ?int
    {
        return $this->getThreatScore();
    }

    public function referer(): ?string
    {
        return $this->getReferer();
    }

    public function refererDomain(): ?string
    {
        return $this->getRefererDomain();
    }

    //----------------------------------------------------------------------
    // Device and OS
    //----------------------------------------------------------------------

    public function isMobile(): ?bool
    {
        return $this->getAgent()?->isMobile();
    }

    public function isTablet(): ?bool
    {
        return $this->getAgent()?->isTablet();
    }

    public function isDesktop(): ?bool
    {
        return $this->getAgent()?->isDesktop();
    }

    public function isTv(): ?bool
    {
        return $this->getAgent()?->isTv();
    }

    public function deviceType(): ?string
    {
        return $this->getAgent()?->deviceType();
    }

    public function deviceBrand(): ?string
    {
        return $this->getAgent()?->deviceBrand();
    }

    public function deviceModel(): ?string
    {
        return $this->getAgent()?->deviceModel();
    }

    //----------------------------------------------------------------------
    // OS
    //----------------------------------------------------------------------

    public function os(): string
    {
        return $this->getAgent()?->os();
    }

    public function osName(): ?string
    {
        return $this->getAgent()?->osName();
    }

    public function osVersion(): ?string
    {
        return $this->getAgent()?->osVersion();
    }

    public function osFamily(): ?string
    {
        return $this->getAgent()?->osFamily();
    }

    public function osData(): ?array
    {
        return $this->getAgent()?->osData();
    }

    //----------------------------------------------------------------------
    // Browser
    //----------------------------------------------------------------------

    public function browser(): ?string
    {
        return $this->getAgent()?->browser();
    }

    public function browserName(): ?string
    {
        return $this->getAgent()?->browserName();
    }

    public function browserVersion(): ?string
    {
        return $this->getAgent()?->browserVersion();
    }

    public function browserFamily(): ?string
    {
        return $this->getAgent()?->browserFamily();
    }

    public function browserData(): ?array
    {
        return $this->getAgent()?->browserData();
    }

    //----------------------------------------------------------------------

    public function detectCloudflare(): bool
    {
        if ($this->headers->has('CF-ray')) {
            return true;
        }

        return false;
    }

    //----------------------------------------------------------------------
    // Getters
    //----------------------------------------------------------------------

    public function getClientCountry(): ?string
    {
        if ($this->headers->has('X-COUNTRY')) {
            return $this->headers->get('X-COUNTRY');
        }
        if ($this->headers->has('CF-IPCountry')) {
            return $this->headers->get('CF-IPCountry');
        }

        return null;
    }

    public function getClientCity(): ?string
    {
        if ($this->headers->has('X-CITY')) {
            return $this->headers->get('X-CITY');
        }

        return null;
    }

    public function getClientRegion(): ?string
    {
        if ($this->headers->has('X-REGION')) {
            return $this->headers->get('X-REGION');
        }

        return null;
    }

    public function getClientContinent(): ?string
    {
        if ($this->headers->has('X-CONTINENT')) {
            return $this->headers->get('X-CONTINENT');
        }

        return null;
    }

    public function getClientPostalCode(): ?string
    {
        if ($this->headers->has('X-POSTAL-CODE')) {
            return $this->headers->get('X-POSTAL-CODE');
        }

        return null;
    }

    public function getClientLat(): ?string
    {
        if ($this->headers->has('X-LAT')) {
            return $this->headers->get('X-LAT');
        }

        return null;
    }

    public function getClientLon(): ?string
    {
        if ($this->headers->has('X-LON')) {
            return $this->headers->get('X-LON');
        }

        return null;
    }

    public function getClientGeo(): ?array
    {
        if ($this->getClientLat() && $this->getClientLon()) {
            return [
                'lat' => $this->getClientLat(),
                'lon' => $this->getClientLon(),
            ];
        }

        return null;
    }

    public function getClientTimezone(): ?string
    {
        if ($this->headers->has('X-TIMEZONE')) {
            return $this->headers->get('X-TIMEZONE');
        }

        return null;
    }

    public function getIsBot(): ?bool
    {
        if ($this->headers->has('X-IS-BOT')) {
            return $this->headers->get('X-IS-BOT') == 'true';
        }

        return null;
    }

    public function getThreatScore(): ?int
    {
        if ($this->headers->has('X-THREAT-SCORE')) {
            return (int) $this->headers->get('X-THREAT-SCORE');
        }

        return null;
    }

    public function getReferer(): ?string
    {
        if ($this->headers->has('X-REFERER')) {
            return $this->headers->get('X-REFERER');
        }

        return $this->headers->get('Referer');
    }

    public function getRefererDomain(): ?string
    {
        $referer = $this->getReferer();
        if ($referer) {
            $referer = parse_url($referer, PHP_URL_HOST);
        }

        return $referer;
    }

    protected function getAgent()
    {
        if (! $this->userAgent()) {
            return null;
        }
        if (! $this->agent) {
            $this->agent = new Agent($this->userAgent());
        }

        return $this->agent;
    }

    //----------------------------------------------------------------------
    // Getter overrides to prioritize CF headers
    //----------------------------------------------------------------------

    public function getClientIp(): ?string
    {
        //Prioritize CF as source of IP
        if ($this->headers->has('X-IP')) {
            return $this->headers->get('X-IP');
        }
        if ($this->headers->has('CF-Connecting-IP')) {
            return $this->headers->get('CF-Connecting-IP');
        }
        //Else default
        $ipAddresses = $this->getClientIps();

        return $ipAddresses[0];
    }

    /**
     * @inerhitDoc
     */
    public function userAgent(): ?string
    {
        if ($this->headers->has('X-AGENT')) {
            return $this->headers->get('X-AGENT');
        }

        return $this->headers->get('User-Agent');
    }
}
