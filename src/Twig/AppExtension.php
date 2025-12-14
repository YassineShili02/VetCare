<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private string $dateFormat;
    private string $timezone;

    public function __construct(string $dateFormat = 'd/m/Y H:i', string $timezone = 'Europe/Paris')
    {
        $this->dateFormat = $dateFormat;
        $this->timezone = $timezone;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_date', [$this, 'formatDate']),
            new TwigFilter('format_datetime', [$this, 'formatDateTime']),
            new TwigFilter('truncate', [$this, 'truncate']),
            new TwigFilter('mask_email', [$this, 'maskEmail']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_dev', [$this, 'isDev']),
            new TwigFunction('get_env', [$this, 'getEnv']),
        ];
    }

    public function formatDate(\DateTimeInterface $date): string
    {
        $date->setTimezone(new \DateTimeZone($this->timezone));
        return $date->format('d/m/Y');
    }

    public function formatDateTime(\DateTimeInterface $date): string
    {
        $date->setTimezone(new \DateTimeZone($this->timezone));
        return $date->format($this->dateFormat);
    }

    public function truncate(string $text, int $length = 100): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }

    public function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }
        
        $local = $parts[0];
        $domain = $parts[1];
        
        if (strlen($local) <= 2) {
            $maskedLocal = substr($local, 0, 1) . '***';
        } else {
            $maskedLocal = substr($local, 0, 2) . '***' . substr($local, -1);
        }
        
        return $maskedLocal . '@' . $domain;
    }

    public function isDev(): bool
    {
        return $_ENV['APP_ENV'] === 'dev';
    }

    public function getEnv(string $key): ?string
    {
        return $_ENV[$key] ?? null;
    }
}