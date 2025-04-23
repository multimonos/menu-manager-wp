<?php

namespace MenuManager\Vendor\Matrix\Operators;

use MenuManager\Vendor\Matrix\Div0Exception;
use MenuManager\Vendor\Matrix\Exception;
use MenuManager\Vendor\Matrix\Matrix;
use MenuManager\Vendor\Matrix\Functions;
class Division extends \MenuManager\Vendor\Matrix\Operators\Multiplication
{
    /**
     * Execute the division
     *
     * @param mixed $value The matrix or numeric value to divide the current base value by
     * @throws Exception If the provided argument is not appropriate for the operation
     * @return $this The operation object, allowing multiple divisions to be chained
     **/
    public function execute($value, string $type = 'division') : \MenuManager\Vendor\Matrix\Operators\Operator
    {
        if (\is_array($value)) {
            $value = new Matrix($value);
        }
        if (\is_object($value) && $value instanceof Matrix) {
            $value = Functions::inverse($value, $type);
            return $this->multiplyMatrix($value, $type);
        } elseif (\is_numeric($value)) {
            return $this->multiplyScalar(1 / $value, $type);
        }
        throw new Exception('Invalid argument for division');
    }
}
