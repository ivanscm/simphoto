<?php


namespace App\Services;


class ParameterService
{
    private $appDir;
    private $site_name;

    public function __construct(string $appDir, string $site_name)
    {
        $this->appDir = $appDir;
        $this->site_name = $site_name;
    }

    public function getAppDir(): string
    {
        return $this->appDir;
    }

    public function getSiteName(): string
    {
        return $this->site_name;
    }
}