<?php

namespace MenuManager\Vendor\Matrix;

use MenuManager\Vendor\Matrix\Operators\Addition;
use MenuManager\Vendor\Matrix\Operators\DirectSum;
use MenuManager\Vendor\Matrix\Operators\Division;
use MenuManager\Vendor\Matrix\Operators\Multiplication;
use MenuManager\Vendor\Matrix\Operators\Subtraction;
class Operations
{
    public static function add(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('Addition operation requires at least 2 arguments');
        }
        $matrix = \array_shift($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('Addition arguments must be Matrix or array');
        }
        $result = new Addition($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
    public static function directsum(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('DirectSum operation requires at least 2 arguments');
        }
        $matrix = \array_shift($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('DirectSum arguments must be Matrix or array');
        }
        $result = new DirectSum($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
    public static function divideby(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('Division operation requires at least 2 arguments');
        }
        $matrix = \array_shift($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('Division arguments must be Matrix or array');
        }
        $result = new Division($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
    public static function divideinto(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('Division operation requires at least 2 arguments');
        }
        $matrix = \array_pop($matrixValues);
        $matrixValues = \array_reverse($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('Division arguments must be Matrix or array');
        }
        $result = new Division($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
    public static function multiply(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('Multiplication operation requires at least 2 arguments');
        }
        $matrix = \array_shift($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('Multiplication arguments must be Matrix or array');
        }
        $result = new Multiplication($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
    public static function subtract(...$matrixValues) : \MenuManager\Vendor\Matrix\Matrix
    {
        if (\count($matrixValues) < 2) {
            throw new \MenuManager\Vendor\Matrix\Exception('Subtraction operation requires at least 2 arguments');
        }
        $matrix = \array_shift($matrixValues);
        if (\is_array($matrix)) {
            $matrix = new \MenuManager\Vendor\Matrix\Matrix($matrix);
        }
        if (!$matrix instanceof \MenuManager\Vendor\Matrix\Matrix) {
            throw new \MenuManager\Vendor\Matrix\Exception('Subtraction arguments must be Matrix or array');
        }
        $result = new Subtraction($matrix);
        foreach ($matrixValues as $matrix) {
            $result->execute($matrix);
        }
        return $result->result();
    }
}
