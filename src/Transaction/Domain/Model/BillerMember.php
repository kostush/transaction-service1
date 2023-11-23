<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

class BillerMember
{
    /** @var string|null */
    private $userName;

    /** @var string|null */
    private $password;

    /**
     * Member constructor.
     * @param string|null $userName user name
     * @param string|null $password password
     */
    private function __construct(?string $userName, ?string $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @param string $userName user name
     * @param string $password password
     * @return static
     */
    public static function create(?string $userName, ?string $password)
    {
        return new static($userName, $password);
    }

    /**
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function userName(): ?string
    {
        return $this->userName;
    }
}
