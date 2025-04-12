<?php

namespace MenuManager\Vendor\Carbon\Doctrine;

use MenuManager\Vendor\Carbon\CarbonImmutable;
use MenuManager\Vendor\Doctrine\DBAL\Types\VarDateTimeImmutableType;
class DateTimeImmutableType extends VarDateTimeImmutableType implements \MenuManager\Vendor\Carbon\Doctrine\CarbonDoctrineType
{
    /** @use CarbonTypeConverter<CarbonImmutable> */
    use \MenuManager\Vendor\Carbon\Doctrine\CarbonTypeConverter;
    /**
     * @return class-string<CarbonImmutable>
     */
    protected function getCarbonClassName() : string
    {
        return CarbonImmutable::class;
    }
}
