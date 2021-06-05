<?php


namespace App\Services;


class ParameterService
{
    private $appDir;

    public function __construct(string $appDir)
    {
        $this->appDir = $appDir;
    }

    public function getAppDir(): string
    {
        return $this->appDir;
    }
}