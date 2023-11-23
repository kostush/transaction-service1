<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

/**
 * Receive authorization token from Azure Active Directory
 */
interface RequestToken
{
    /**
     * @param string $clientSecret Client Secret
     * @param string $resource     Resource
     * @return string
     */
    public function getToken(string $clientSecret, string $resource): ?string;
}
