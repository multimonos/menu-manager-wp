<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace MenuManager\Vendor\League\Csv\Query;

use Closure;
/**
 * @phpstan-type Condition Predicate|Closure(mixed, array-key): bool
 * @phpstan-type ConditionExtended Predicate|Closure(mixed, array-key): bool|callable(mixed, array-key): bool
 */
interface PredicateCombinator extends \MenuManager\Vendor\League\Csv\Query\Predicate
{
    /**
     * Return an instance with the specified predicates
     * joined together and with the current predicate
     * using the AND Logical operator.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified changes.
     *
     * @param Condition ...$predicates
     */
    public function and(\MenuManager\Vendor\League\Csv\Query\Predicate|Closure ...$predicates) : self;
    /**
     * Return an instance with the specified predicates
     * joined together and with the current predicate
     * using the OR Logical operator.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified changes.
     *
     * @param Condition ...$predicates
     */
    public function or(\MenuManager\Vendor\League\Csv\Query\Predicate|Closure ...$predicates) : self;
    /**
     * Return an instance with the specified predicates
     * joined together and with the current predicate
     * using the NOT Logical operator.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified changes.
     *
     * @param Condition ...$predicates
     */
    public function not(\MenuManager\Vendor\League\Csv\Query\Predicate|Closure ...$predicates) : self;
    /**
     * Return an instance with the specified predicates
     * joined together and with the current predicate
     * using the XOR Logical operator.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified changes.
     *
     * @param Condition ...$predicates
     */
    public function xor(\MenuManager\Vendor\League\Csv\Query\Predicate|Closure ...$predicates) : self;
}
