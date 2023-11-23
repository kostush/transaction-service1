<?php
declare(strict_types=1);

namespace ProBillerNG\Transaction\Application\Services;

use ProBillerNG\Transaction\Domain\Model\Transaction;

trait BillerResponseAttributeExtractorTrait
{
    /**
     * @param Transaction $transaction Biller Response
     * @param string      $field       Attribute Name In the Biller Response.
     *
     * @return mixed|null
     */
    protected function getAttribute(Transaction $transaction, string $field)
    {
        $strBillerResponse = $transaction->with3D() ?
            $transaction->responsePayloadThreeDsTwo() :
            $transaction->responsePayload();

        $attributeValue = null;
        if ($strBillerResponse !== null) {
            $billerResponse = json_decode($strBillerResponse);

            if (!empty($billerResponse) && isset($billerResponse->{$field})) {
                $attributeValue = $billerResponse->{$field};
            }
        }

        return $attributeValue;
    }
}
