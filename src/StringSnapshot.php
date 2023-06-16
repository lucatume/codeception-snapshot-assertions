<?php
/**
 * Provides string snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

/**
 * Class StringSnapshot
 *
 * @package tad\Codeception\SnapshotAssertions
 */
class StringSnapshot extends AbstractSnapshot
{
    /**
     * {@inheritDoc}
     */
    public function fileExtension(): string
    {
        return 'snapshot.txt';
    }

    /**
     * StringSnapshot constructor.
     *
     * @param null|mixed $current The current value.
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
    protected function fetchData(): string
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
    protected function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return serialize($value);
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ?
                $value->__toString()
                : json_encode($value, JSON_PRETTY_PRINT);
        }

        if (is_scalar($value) || $value === null) {
            return (string)$value;
        }

        throw new \InvalidArgumentException('The value must be scalar, null or an object.');
    }
}
