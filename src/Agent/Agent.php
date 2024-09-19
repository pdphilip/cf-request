<?php

namespace PDPhilip\CfRequest\Agent;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

class Agent
{
    protected DeviceDetector $deviceDetector;

    protected array $browser = [
        'type' => 'unknown',
        'name' => 'unknown',
        'version' => '',
        'engine' => 'unknown',
        'engine_version' => '',
        'family' => 'unknown',
    ];

    protected array $os = [
        'name' => 'unknown',
        'version' => '',
        'family' => '',
    ];

    protected array $device = [
        'brand' => 'unknown',
        'model' => 'unknown',
    ];

    protected string $deviceType = 'unknown';

    public function __construct($userAgent)
    {
        $clientHints = ClientHints::factory($_SERVER);
        $this->deviceDetector = new DeviceDetector($userAgent, $clientHints);
        $this->deviceDetector->parse();
        $this->_setBrowser();
        $this->_setOs();
        $this->_setDevice();

    }

    public function isMobile(): bool
    {
        return $this->deviceType == 'mobile';
    }

    public function isTablet(): bool
    {
        return $this->deviceType == 'tablet';
    }

    public function isDesktop(): bool
    {
        return $this->deviceType == 'desktop';

    }

    public function isTv(): bool
    {
        return $this->deviceType == 'tv';
    }

    public function deviceType(): string
    {
        return $this->deviceType;

    }

    public function os(): string
    {
        return $this->os['name'].' '.$this->os['version'];
    }

    public function osName()
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

    public function deviceBrand(): string
    {
        return $this->device['brand'];
    }

    public function deviceModel(): string
    {
        if ($this->device['model']) {
            return $this->device['model'];
        }

        return $this->device['brand'];
    }

    public function browser(): string
    {
        return $this->browser['name'].' '.$this->browser['version'];
    }

    public function browserFamily(): string
    {
        return (string) $this->browser['family'];
    }

    public function browserName(): string
    {
        return (string) $this->browser['name'];
    }

    public function browserVersion(): string
    {
        return (string) $this->browser['version'];
    }

    public function browserData(): array
    {
        $browser = $this->browser;
        unset($browser['short_name']);

        return $browser;
    }

    private function _parseDeviceType($device): string
    {
        return match ($device) {
            AbstractDeviceParser::DEVICE_TYPE_DESKTOP => 'desktop',
            AbstractDeviceParser::DEVICE_TYPE_SMARTPHONE, AbstractDeviceParser::DEVICE_TYPE_FEATURE_PHONE, AbstractDeviceParser::DEVICE_TYPE_PHABLET => 'mobile',
            AbstractDeviceParser::DEVICE_TYPE_TABLET => 'tablet',
            AbstractDeviceParser::DEVICE_TYPE_CONSOLE => 'console',
            AbstractDeviceParser::DEVICE_TYPE_TV => 'tv',
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

    private function _setBrowser(): void
    {
        $browser = $this->deviceDetector->getClient();
        if ($browser) {
            $this->browser = $browser;
        }

    }

    public function _setOs(): void
    {
        $os = $this->deviceDetector->getOs();
        if ($os) {
            $this->os = $os;
        }
    }

    public function _setDevice(): void
    {
        $device = $this->deviceDetector->getDevice();
        if ($device) {
            $this->deviceType = $this->_parseDeviceType($device);
            $this->device = [
                'brand' => $this->deviceDetector->getBrandName(),
                'model' => $this->deviceDetector->getModel(),
            ];
        }

    }
}
