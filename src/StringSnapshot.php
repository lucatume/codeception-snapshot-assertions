<?php
/**
 * Provides string snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

/**
 * Class StringSnapshot
 * @package tad\Codeception\SnapshotAssertions
 */
class StringSnapshot extends AbstractSnapshot
{
    /**
     * {@inheritDoc}
     */
    public function fileExtension()
    {
        return 'snapshot.txt';
    }

    /**
     * StringSnapshot constructor.
     *
     * @param  null|mixed  $current The current value.
     */
    public function __construct($current = null)
    {
        $current = $current !== null ? $this->stringify($current) : null;
        parent::__construct($current);
    }

    /**
     * Returns the string representation of the current value.
     *
     * @return string The string representation of the current value.
     */
    protected function fetchData()
    {
        return $this->stringify($this->current);
    }

    /**
     * Converts the value to a string representation.
     *
     * @param mixed $value The value that should be converted.
     *
     * @return string The string representation of the value.
     */
    protected function stringify($value)
    {
        $stringified = $value;

        if (is_array($value)) {
            $stringified = serialize($value);
        }
        if (is_object($value)) {
            $stringified = method_exists($value, '__toString') ?
                $value->__toString()
                : json_encode($value, JSON_PRETTY_PRINT);
        }

        return (string)$stringified;
    }

    /**
     *
     *
     * @param  mixed  $data  The data to check for emptyness.
     *
     * @return bool
     * @since TBD
     */
    protected function isEmptyData($data)
    {
        return $this->stringify($data) === '';
    }
}
