<?php

// Eleganced at 2026-02-20

namespace PDPhilip\CfRequest\Agent;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Agent
{
    protected DeviceDetector $deviceDetector;

    protected string $deviceType = 'unknown';

    protected array $device = [
        'brand' => 'unknown',
        'model' => 'unknown',
    ];

    protected array $os = [
        'name' => 'unknown',
        'version' => '',
        'family' => '',
    ];

    protected array $browser = [
        'type' => 'unknown',
        'name' => 'unknown',
        'version' => '',
        'engine' => 'unknown',
        'engine_version' => '',
        'family' => 'unknown',
    ];

    protected bool $isBot = false;

    protected string|false $bot = false;

    public function __construct(string $userAgent)
    {
        $clientHints = ClientHints::factory($_SERVER);
        $this->deviceDetector = new DeviceDetector($userAgent, $clientHints);
        $this->deviceDetector->parse();

        $this->deviceType = $this->parseDeviceType($this->deviceDetector->getDevice());
        $this->device = [
            'brand' => $this->deviceDetector->getBrandName(),
            'model' => $this->deviceDetector->getModel(),
        ];

        $client = $this->deviceDetector->getClient();
        if ($client) {
            $this->browser = $client;
        }

        $os = $this->deviceDetector->getOs();
        if ($os) {
            $this->os = $os;
        }

        $this->detectBot($userAgent);
    }

    // ----------------------------------------------------------------------
    // Device
    // ----------------------------------------------------------------------

    public function isMobile(): bool
    {
        return $this->deviceType === 'mobile';
    }

    public function isTablet(): bool
    {
        return $this->deviceType === 'tablet';
    }

    public function isDesktop(): bool
    {
        return $this->deviceType === 'desktop';
    }

    public function isTv(): bool
    {
        return $this->deviceType === 'tv';
    }

    public function deviceType(): string
    {
        return $this->deviceType;
    }

    public function deviceBrand(): string
    {
        return $this->device['brand'];
    }

    public function deviceModel(): string
    {
        return $this->device['model'] ?: $this->device['brand'];
    }

    // ----------------------------------------------------------------------
    // OS
    // ----------------------------------------------------------------------

    public function os(): string
    {
        return trim($this->os['name'].' '.$this->os['version']);
    }

    public function osName(): string
    {
        return $this->os['name'];
    }

    public function osVersion(): string
    {
        return (string) $this->os['version'];
    }

    public function osFamily(): string
    {
        return (string) $this->os['family'];
    }

    public function osData(): array
    {
        $os = $this->os;
        unset($os['short_name']);

        return $os;
    }

    // ----------------------------------------------------------------------
    // Browser
    // ----------------------------------------------------------------------

    public function browser(): string
    {
        return trim($this->browser['name'].' '.$this->browser['version']);
    }

    public function browserName(): string
    {
        return (string) $this->browser['name'];
    }

    public function browserVersion(): string
    {
        return (string) $this->browser['version'];
    }

    public function browserFamily(): string
    {
        return (string) $this->browser['family'];
    }

    public function browserData(): array
    {
        $browser = $this->browser;
        unset($browser['short_name']);

        return $browser;
    }

    // ----------------------------------------------------------------------
    // Bot Detection
    // ----------------------------------------------------------------------

    public function isBot(): bool
    {
        return $this->isBot;
    }

    public function bot(): string|false
    {
        return $this->bot;
    }

    // ----------------------------------------------------------------------
    // Internal
    // ----------------------------------------------------------------------

    private function detectBot(string $userAgent): void
    {
        if ($userAgent === '') {
            $this->isBot = true;
            $this->bot = 'no_user_agent';

            return;
        }

        $crawlerDetect = new CrawlerDetect;
        if ($crawlerDetect->isCrawler($userAgent)) {
            $this->isBot = true;
            $this->bot = $crawlerDetect->getMatches() ?: 'unknown';

            return;
        }

        if ($this->deviceDetector->isBot()) {
            $this->isBot = true;
            $botInfo = $this->deviceDetector->getBot();
            $this->bot = $botInfo['name'] ?? 'unknown';
        }
    }

    private function parseDeviceType(?int $device): string
    {
        return match ($device) {
            AbstractDeviceParser::DEVICE_TYPE_DESKTOP => 'desktop',
            AbstractDeviceParser::DEVICE_TYPE_SMARTPHONE,
            AbstractDeviceParser::DEVICE_TYPE_FEATURE_PHONE,
            AbstractDeviceParser::DEVICE_TYPE_PHABLET => 'mobile',
            AbstractDeviceParser::DEVICE_TYPE_TABLET => 'tablet',
            AbstractDeviceParser::DEVICE_TYPE_TV => 'tv',
            AbstractDeviceParser::DEVICE_TYPE_CONSOLE => 'console',
            AbstractDeviceParser::DEVICE_TYPE_CAR_BROWSER => 'car',
            AbstractDeviceParser::DEVICE_TYPE_SMART_DISPLAY => 'smart_display',
            AbstractDeviceParser::DEVICE_TYPE_CAMERA => 'camera',
            AbstractDeviceParser::DEVICE_TYPE_PORTABLE_MEDIA_PAYER => 'media_player',
            AbstractDeviceParser::DEVICE_TYPE_SMART_SPEAKER => 'speaker',
            AbstractDeviceParser::DEVICE_TYPE_WEARABLE => 'wearable',
            AbstractDeviceParser::DEVICE_TYPE_PERIPHERAL => 'peripheral',
            default => 'unknown',
        };
    }
}
