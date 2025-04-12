<?php

namespace MenuManager\Vendor\Carbon\Doctrine;

use MenuManager\Vendor\Carbon\Carbon;
use MenuManager\Vendor\Doctrine\DBAL\Types\VarDateTimeType;
class DateTimeType extends VarDateTimeType implements \MenuManager\Vendor\Carbon\Doctrine\CarbonDoctrineType
{
    /** @use CarbonTypeConverter<Carbon> */
    use \MenuManager\Vendor\Carbon\Doctrine\CarbonTypeConverter;
}
