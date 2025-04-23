<?php

namespace MenuManager\Vendor\Matrix\Decomposition;

use MenuManager\Vendor\Matrix\Exception;
use MenuManager\Vendor\Matrix\Matrix;
class Decomposition
{
    const LU = 'LU';
    const QR = 'QR';
    /**
     * @throws Exception
     */
    public static function decomposition($type, Matrix $matrix)
    {
        switch (\strtoupper($type)) {
            case self::LU:
                return new \MenuManager\Vendor\Matrix\Decomposition\LU($matrix);
            case self::QR:
                return new \MenuManager\Vendor\Matrix\Decomposition\QR($matrix);
            default:
                throw new Exception('Invalid Decomposition');
        }
    }
}
