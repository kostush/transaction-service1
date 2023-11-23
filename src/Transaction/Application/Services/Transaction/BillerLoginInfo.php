<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services\Transaction;

class BillerLoginInfo
{
    /**
     * @var string|null
     */
    public $userName;

    /**
     * @var string|null
     */
    public $password;

    /**
     * Member constructor.
     * @param null|string $userName User Name
     * @param null|string $password Password
     */
    public function __construct(
        ?string $userName,
        ?string $password
    ) {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function userName(): ?string
    {
        return $this->userName;
    }

    /**
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->password;
    }
}
