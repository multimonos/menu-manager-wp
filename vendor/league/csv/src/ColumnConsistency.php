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
namespace MenuManager\Vendor\League\Csv;

use function count;
/**
 * Validates column consistency when inserting records into a CSV document.
 */
class ColumnConsistency
{
    /**
     * @throws InvalidArgument if the column count is less than -1
     */
    public function __construct(protected int $columns_count = -1)
    {
        $this->columns_count >= -1 || throw \MenuManager\Vendor\League\Csv\InvalidArgument::dueToInvalidColumnCount($this->columns_count, __METHOD__);
    }
    /**
     * Returns the column count.
     */
    public function getColumnCount() : int
    {
        return $this->columns_count;
    }
    /**
     * Tells whether the submitted record is valid.
     */
    public function __invoke(array $record) : bool
    {
        $count = count($record);
        if (-1 === $this->columns_count) {
            $this->columns_count = $count;
            return \true;
        }
        return $count === $this->columns_count;
    }
}
