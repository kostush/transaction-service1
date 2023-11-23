<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\DTO\ReturnTypes;

use ProBillerNG\Transaction\Domain\Model\BillerInteraction;

trait TransactionRequestResponseInteractionTrait
{
    /**
     * @param array $billerInteractions The biller interactions
     * @return void
     */
    public static function sortBillerInteractions(array &$billerInteractions): void
    {
        usort(
            $billerInteractions,
            function (BillerInteraction $a, BillerInteraction $b) {
                return $a->createdAt()->format('Y-m-d H:i:s.u') < $b->createdAt()->format('Y-m-d H:i:s.u')
                    ? -1 : 1;
            }
        );
    }

    /**
     * @param array $billerInteractionCollection The biller interaction array
     * @return array
     */
    public static function getRequestInteractions(array $billerInteractionCollection): array
    {
        $requestInteractions = [];
        /** @var BillerInteraction $billerInteraction */
        foreach ($billerInteractionCollection as $billerInteraction) {
            if ($billerInteraction->type() == BillerInteraction::TYPE_REQUEST) {
                $requestInteractions[] = $billerInteraction;
            }
        }

        return $requestInteractions;
    }

    /**
     * @param array $billerInteractionCollection The biller interaction array
     * @return array
     */
    public static function getResponseInteractions(array $billerInteractionCollection): array
    {
        $responseInteractions = [];
        /** @var BillerInteraction $billerInteraction */
        foreach ($billerInteractionCollection as $billerInteraction) {
            if ($billerInteraction->type() == BillerInteraction::TYPE_RESPONSE) {
                $responseInteractions[] = $billerInteraction;
            }
        }

        return $responseInteractions;
    }
}
