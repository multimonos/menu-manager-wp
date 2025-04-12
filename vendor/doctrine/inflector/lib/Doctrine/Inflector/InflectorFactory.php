<?php

declare (strict_types=1);
namespace MenuManager\Vendor\Doctrine\Inflector;

use MenuManager\Vendor\Doctrine\Inflector\Rules\English;
use MenuManager\Vendor\Doctrine\Inflector\Rules\French;
use MenuManager\Vendor\Doctrine\Inflector\Rules\NorwegianBokmal;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Portuguese;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Spanish;
use MenuManager\Vendor\Doctrine\Inflector\Rules\Turkish;
use InvalidArgumentException;
use function sprintf;
final class InflectorFactory
{
    public static function create() : \MenuManager\Vendor\Doctrine\Inflector\LanguageInflectorFactory
    {
        return self::createForLanguage(\MenuManager\Vendor\Doctrine\Inflector\Language::ENGLISH);
    }
    public static function createForLanguage(string $language) : \MenuManager\Vendor\Doctrine\Inflector\LanguageInflectorFactory
    {
        switch ($language) {
            case \MenuManager\Vendor\Doctrine\Inflector\Language::ENGLISH:
                return new English\InflectorFactory();
            case \MenuManager\Vendor\Doctrine\Inflector\Language::FRENCH:
                return new French\InflectorFactory();
            case \MenuManager\Vendor\Doctrine\Inflector\Language::NORWEGIAN_BOKMAL:
                return new NorwegianBokmal\InflectorFactory();
            case \MenuManager\Vendor\Doctrine\Inflector\Language::PORTUGUESE:
                return new Portuguese\InflectorFactory();
            case \MenuManager\Vendor\Doctrine\Inflector\Language::SPANISH:
                return new Spanish\InflectorFactory();
            case \MenuManager\Vendor\Doctrine\Inflector\Language::TURKISH:
                return new Turkish\InflectorFactory();
            default:
                throw new InvalidArgumentException(sprintf('Language "%s" is not supported.', $language));
        }
    }
}
