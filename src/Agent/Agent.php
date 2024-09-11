<?php

namespace PDPhilip\CfRequest\Agent;

use foroco\BrowserDetection;

class Agent
{
    protected array $browserData;

    public function __construct($userAgent)
    {
        $browser = new BrowserDetection;
        $this->browserData = $browser->getAll($userAgent);
    }

    public function isMobile(): bool
    {
        return $this->browserData['device_type'] == 'mobile';
    }

    public function isTablet(): bool
    {
        return $this->browserData['device_type'] == 'tablet';
    }

    public function isDesktop(): bool
    {
        return $this->browserData['device_type'] == 'desktop';
    }

    public function deviceType(): string
    {
        return (string) $this->browserData['device_type'];
    }

    public function osFamily(): string
    {
        return (string) $this->browserData['os_family'];
    }

    public function os(): string
    {
        return (string) $this->browserData['os_title'];
    }

    public function osName(): string
    {
        return (string) $this->browserData['os_name'];
    }

    public function osVersion(): string
    {
        return (string) $this->browserData['os_version'];
    }

    public function browser(): string
    {
        return (string) $this->browserData['browser_title'];
    }

    public function browserName(): string
    {
        return (string) $this->browserData['browser_name'];
    }

    public function browserVersion(): string
    {
        return (string) $this->browserData['browser_version'];
    }
}
