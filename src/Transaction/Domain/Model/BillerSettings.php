<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

interface BillerSettings
{
    public const ROCKETGATE = 'rocketgate';
    public const PUMAPAY    = 'pumapay';
    public const NETBILLING = 'netbilling';
    public const EPOCH      = 'epoch';
    public const CRYPTO     = 'crypto';
    public const LEGACY     = 'legacy';
    public const QYSSO      = 'qysso';

    public const ROCKETGATE_ID = '23423';
    public const NETBILLING_ID = '23424';
    public const PUMAPAY_ID    = '12345';
    public const EPOCH_ID      = '23425';
    public const LEGACY_ID     = '23426';
    public const QYSSO_ID      = '23427';

    /** TODO kept for backwards compatibility - billerId removal */
    public const MAP_BILLER_IDS_TO_NAMES = [
        self::ROCKETGATE_ID => self::ROCKETGATE,
        self::NETBILLING_ID => self::NETBILLING,
        self::PUMAPAY_ID    => self::PUMAPAY,
        self::EPOCH_ID      => self::EPOCH,
        self::LEGACY_ID     => self::LEGACY,
        self::QYSSO_ID      => self::QYSSO,
    ];

    public const ACTION_START    = 'start';
    public const ACTION_STOP     = 'stop';
    public const ACTION_UPDATE   = 'update';
    public const ACTION_CANCEL   = 'cancel';
    public const ACTION_POSTBACK = 'postback';
    public const ACTION_ABORT    = 'abort';


    /**
     * @return string
     */
    public function billerName(): string;

    /**
     * @return array
     */
    public function toArray(): array;
}
