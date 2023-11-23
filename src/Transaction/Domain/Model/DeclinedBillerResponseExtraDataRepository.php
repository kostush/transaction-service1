<?php

declare(strict_types=1);

namespace ProBillerNG\Transaction\Domain\Model;

use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\DeclinedBillerResponseExtraData;
use ProBillerNG\Transaction\Domain\Model\DeclinedBillerResponse\MappingCriteria;

interface DeclinedBillerResponseExtraDataRepository
{
    /**
     * Retrieve error classification  based on given criteria
     *
     * @param MappingCriteria $mappingCriteria Criteria used to retrieve the error classification
     *
     * @return DeclinedBillerResponseExtraData|null
     */
    public function retrieve(MappingCriteria $mappingCriteria) : ?DeclinedBillerResponseExtraData;
}