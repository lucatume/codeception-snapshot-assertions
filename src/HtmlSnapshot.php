<?php
/**
 *  Provides HTML snapshot assertion methods.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Gajus\Dindent\Exception\RuntimeException;
use Gajus\Dindent\Indenter;

/**
 * Class HtmlSnapshot
 *
 * @package tad\Codeception\SnapshotAssertions
 */
class HtmlSnapshot extends StringSnapshot
{
    /**
     * An instance of the HTML indenter.
     */
    protected Indenter $indenter;

    /**
     * HtmlSnapshot constructor.
     *
     * @param null $current The current HTML fragment.
     */
    public function __construct($current = null)
    {
        parent::__construct($current);
        $this->indenter = new Indenter();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function fileExtension(): string
    {
        return 'snapshot.html';
    }

    /**
     * Asserts the current HTML fragment matches the one saved in the snapshot.
     *
     * The assertion is made indenting the current and existing HTML fragments before the comparison.
     *
     * @param string $data The data, an HTML string, to check.
     *
     *
     * @throws RuntimeException If there's an issue during the HTML string parsing.
     */
    #[\Override]
    protected function assertData($data): void
    {
        if ($this->dataVisitor !== null) {
            $visited = call_user_func($this->dataVisitor, $data, $this->dataSet);
            if (!(is_array($visited) && count($visited) === 2 && is_string($visited[0]) && is_string($visited[1]))) {
                throw new RuntimeException('The data visitor must return an array of two strings.');
            }
            [$data, $dataSet] = $visited;

            $this->dataSet = $dataSet;
        }

        if (!is_string($this->dataSet)) {
            throw new RuntimeException('The data set must be a string.');
        }

        $indent = $this->indenter->indent($this->dataSet);
        $indent1 = $this->indenter->indent($data);
        $this->assertEquals($indent, $indent1);
    }
}
