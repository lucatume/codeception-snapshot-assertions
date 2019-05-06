<?php
/**
 *  Provides HTML snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Gajus\Dindent\Indenter;

/**
 * Class HtmlSnapshot
 * @package tad\Codeception\SnapshotAssertions
 */
class HtmlSnapshot extends StringSnapshot
{
    /**
     * An instance of the HTML indenter.
     * @var Indenter
     */
    protected $indenter;

    /**
     * HtmlSnapshot constructor.
     * @param  string  $current  The current HTML fragment.
     * @throws \Gajus\Dindent\Exception\InvalidArgumentException If the set of options used to initialize the
     *                           indenter are not correct.
     */
    public function __construct($current = null)
    {
        parent::__construct($current);
        $this->indenter = new Indenter();
    }

    /**
     * {@inheritDoc}
     */
    public function fileExtension()
    {
        return 'snapshot.html';
    }

    /**
     * Asserts the current HTML fragment matches the one saved in the snapshot.
     *
     * The assertion is made indenting the current and existing HTML fragments before the comparison.
     */
    protected function assertData($data)
    {
        static::assertEquals($this->indenter->indent($this->dataSet), $this->indenter->indent($data));
    }
}
