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

use MenuManager\Vendor\Deprecated;
use InvalidArgumentException;
use php_user_filter;
use Throwable;
use TypeError;
use function array_map;
use function in_array;
use function is_string;
use function restore_error_handler;
use function set_error_handler;
use function str_replace;
use function strcspn;
use function stream_bucket_append;
use function stream_bucket_make_writeable;
use function stream_bucket_new;
use function stream_filter_register;
use function stream_get_filters;
use function strlen;
use function trigger_error;
use const E_USER_WARNING;
use const PSFS_ERR_FATAL;
use const PSFS_PASS_ON;
use const STREAM_FILTER_READ;
use const STREAM_FILTER_WRITE;
/**
 * A stream filter to conform the CSV field to RFC4180.
 *
 * DEPRECATION WARNING! This class will be removed in the next major point release
 *
 * @deprecated since version 9.2.0
 * @see AbstractCsv::setEscape
 *
 * @see https://tools.ietf.org/html/rfc4180#section-2
 */
class RFC4180Field extends php_user_filter
{
    #[\Deprecated(message: 'use League\\Csv\\Reader::setEscape or League\\Csv\\Writer::setEscape instead', since: 'league/csv:9.2.0')]
    public const FILTERNAME = 'convert.league.csv.rfc4180';
    /**
     * The value being search for.
     *
     * @var array<string>
     */
    protected array $search = [];
    /**
     * The replacement value that replace found $search values.
     *
     * @var array<string>
     */
    protected array $replace = [];
    /**
     * Characters that triggers enclosure with PHP fputcsv.
     */
    protected static string $force_enclosure = "\n\r\t ";
    /**
     * Static method to add the stream filter to a {@link AbstractCsv} object.
     */
    public static function addTo(\MenuManager\Vendor\League\Csv\AbstractCsv $csv, string $whitespace_replace = '') : \MenuManager\Vendor\League\Csv\AbstractCsv
    {
        self::register();
        $params = ['enclosure' => $csv->getEnclosure(), 'escape' => $csv->getEscape(), 'mode' => $csv instanceof \MenuManager\Vendor\League\Csv\Writer ? STREAM_FILTER_WRITE : STREAM_FILTER_READ];
        if ($csv instanceof \MenuManager\Vendor\League\Csv\Writer && '' !== $whitespace_replace) {
            self::addFormatterTo($csv, $whitespace_replace);
            $params['whitespace_replace'] = $whitespace_replace;
        }
        return $csv->addStreamFilter(self::FILTERNAME, $params);
    }
    /**
     * Add a formatter to the {@link Writer} object to format the record
     * field to avoid enclosure around a field with an empty space.
     */
    #[\Deprecated(message: 'use League\\Csv\\Reader::setEscape or League\\Csv\\Writer::setEscape instead', since: 'league/csv:9.2.0')]
    public static function addFormatterTo(\MenuManager\Vendor\League\Csv\Writer $csv, string $whitespace_replace) : \MenuManager\Vendor\League\Csv\Writer
    {
        if ('' == $whitespace_replace || strlen($whitespace_replace) !== strcspn($whitespace_replace, self::$force_enclosure)) {
            throw new InvalidArgumentException('The sequence contains a character that enforces enclosure or is a CSV control character or is an empty string.');
        }
        $mapper = fn($value) => is_string($value) ? str_replace(' ', $whitespace_replace, $value) : $value;
        return $csv->addFormatter(fn(array $record): array => array_map($mapper, $record));
    }
    /**
     * Static method to register the class as a stream filter.
     */
    public static function register() : void
    {
        if (!in_array(self::FILTERNAME, stream_get_filters(), \true)) {
            stream_filter_register(self::FILTERNAME, self::class);
        }
    }
    /**
     * Static method to return the stream filter filtername.
     */
    public static function getFiltername() : string
    {
        return self::FILTERNAME;
    }
    /**
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     */
    public function filter($in, $out, &$consumed, bool $closing) : int
    {
        $data = '';
        while (null !== ($bucket = stream_bucket_make_writeable($in))) {
            $data .= $bucket->data;
            $consumed += $bucket->datalen;
        }
        try {
            $data = str_replace($this->search, $this->replace, $data);
        } catch (Throwable $exception) {
            trigger_error('An error occurred while executing the stream filter `' . $this->filtername . '`: ' . $exception->getMessage(), E_USER_WARNING);
            return PSFS_ERR_FATAL;
        }
        set_error_handler(fn(int $errno, string $errstr, string $errfile, int $errline) => \true);
        stream_bucket_append($out, stream_bucket_new($this->stream, $data));
        restore_error_handler();
        return PSFS_PASS_ON;
    }
    #[\Deprecated(message: 'use League\\Csv\\Reader::setEscape or League\\Csv\\Writer::setEscape instead', since: 'league/csv:9.2.0')]
    public function onCreate() : bool
    {
        if (!\is_array($this->params)) {
            throw new TypeError('The filter parameters must be an array.');
        }
        static $mode_list = [STREAM_FILTER_READ => 1, STREAM_FILTER_WRITE => 1];
        $state = isset($this->params['enclosure'], $this->params['escape'], $this->params['mode'], $mode_list[$this->params['mode']]) && 1 === strlen($this->params['enclosure']) && 1 === strlen($this->params['escape']);
        if (\false === $state) {
            return \false;
        }
        $this->search = [$this->params['escape'] . $this->params['enclosure']];
        $this->replace = [$this->params['enclosure'] . $this->params['enclosure']];
        if (STREAM_FILTER_WRITE !== $this->params['mode']) {
            return \true;
        }
        $this->search = [$this->params['escape'] . $this->params['enclosure']];
        $this->replace = [$this->params['escape'] . $this->params['enclosure'] . $this->params['enclosure']];
        if ($this->isValidSequence($this->params)) {
            $this->search[] = $this->params['whitespace_replace'];
            $this->replace[] = ' ';
        }
        return \true;
    }
    /**
     * @codeCoverageIgnore
     * Validate params property.
     */
    protected function isValidParams(array $params) : bool
    {
        static $mode_list = [STREAM_FILTER_READ => 1, STREAM_FILTER_WRITE => 1];
        return isset($params['enclosure'], $params['escape'], $params['mode'], $mode_list[$params['mode']]) && 1 === strlen($params['enclosure']) && 1 === strlen($params['escape']);
    }
    /**
     * Is Valid White space replaced sequence.
     */
    protected function isValidSequence(array $params) : bool
    {
        return isset($params['whitespace_replace']) && strlen($params['whitespace_replace']) === strcspn($params['whitespace_replace'], self::$force_enclosure);
    }
}
