<?php

namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class HtmlSnapshotTest extends BaseTestCase
{

    public function should_correctly_name_snapshot_files()
    {
        $htmlSnapshot = new HtmlSnapshot('<p>test</p>');
        $classFrags = explode('\\', __CLASS__);
        $class = end($classFrags);
        $methodFrags = explode('::', __METHOD__);
        $method = end($methodFrags);
        $expectedSnapshotFileName = __DIR__ .
            sprintf(
                '/__snapshots__/%s__%s__%d.%s',
                $class,
                $method,
                0,
                $htmlSnapshot->fileExtension()
            );

        $snapshot = $htmlSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }

    /**
     * It should create snapshot if not present
     *
     * @test
     */
    public function should_create_snapshot_if_not_present()
    {
        $htmlSnapshot = new HtmlSnapshot('foo');
        $snapshot = $htmlSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $htmlSnapshot->assert();

        $this->assertFileExists($snapshot);
    }

    /**
     * It should fail when snapshots differ
     *
     * @test
     */
    public function should_fail_when_snapshots_differ()
    {
        $htmlSnapshot = new HtmlSnapshot('<p>foo</p>');
        $htmlSnapshot->snapshotPutContents('<h2>bar</h2>');
        $snapshot = $htmlSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $this->expectException(AssertionFailedError::class);

        $htmlSnapshot->assert();
    }

    /**
     * It should succeed when snapshots are equal
     *
     * @test
     */
    public function should_succeed_when_snapshots_are_equal()
    {
        $htmlSnapshot = new HtmlSnapshot('<ul><li>one</li><li>two</li></ul>');
        $htmlSnapshot->snapshotPutContents('<ul><li>one</li><li>two</li></ul>');
        $snapshot = $htmlSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $htmlSnapshot->assert();
    }

    /**
     * It should not fail when HTML spacing and newlines differ
     *
     * @test
     */
    public function should_not_fail_when_html_spacing_and_newlines_differ()
    {
        $current = <<< HTML
        <ul>
            <li>one</li>
            <li>two</li>
        </ul>
HTML;
        $htmlSnapshot = new HtmlSnapshot($current);
        $htmlSnapshot->snapshotPutContents('<ul><li>one</li><li>two</li></ul>');
        $snapshot = $htmlSnapshot->snapshotFileName();
        codecept_debug('Snapshot file: ' . $snapshot);
        $this->unlinkAfter[] = $snapshot;

        $htmlSnapshot->assert();
    }

    /**
     * It should allow comparing complete HTML documents
     *
     * @test
     */
    public function should_allow_comparing_complete_html_documents()
    {
        $htmlOne = <<<HTML
<!doctype html>
<html lang="it">
<head>
<meta charset="UTF-8">
             <title>Document One</title>
</head>
<body>
    <main>
        <p>Some text</p>
    </main>
</body>
</html>
HTML;

        $htmlTwo = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
             <title>Document Two</title>
</head>
<body>
    <main>
        <p>Some modified text</p>
    </main>
</body>
</html>
HTML;
        $htmlSnapshot = new HtmlSnapshot($htmlTwo);
        $htmlSnapshot->snapshotPutContents($htmlOne);
        $this->unlinkAfter[] = $htmlSnapshot->snapshotFileName();

        $this->expectException(AssertionFailedError::class);

        $htmlSnapshot->assert();
    }

    /**
     * It should allow adding a visitor
     *
     * @test
     */
    public function should_allow_adding_a_visitor()
    {
        $removeHashLine = static function ($line) {
            return strpos($line, 'name="hash"') === false;
        };
        $dataVisitor = static function ($expected, $current) use ($removeHashLine) {
            return [
                implode("\n", array_filter(explode("\n", $expected), $removeHashLine)),
                implode("\n", array_filter(explode("\n", $current), $removeHashLine)),
            ];
        };

        $hash = md5(microtime());
        $newHash = md5(microtime());

        $this->assertNotEquals($hash, $newHash);

        $htmlOne = <<<HTML
<!doctype html>
<html lang="it">
<head>
<meta charset="UTF-8">
             <title>Document One</title>
</head>
<body>
    <input type="hidden" name="hash" value="$hash">
    <main>
        <p>Some text</p>
    </main>
</body>
</html>
HTML;

        $htmlTwo = <<<HTML
<!doctype html>
<html lang="it">
<head>
<meta charset="UTF-8">
             <title>Document One</title>
</head>
<body>
    <input type="hidden" name="hash" value="$newHash">
    <main>
        <p>Some text</p>
    </main>
</body>
</html>
HTML;


        // This first snapshot will create the first HTML snapshot.
        $firstSnapshot = new HtmlSnapshot($htmlOne);
        $firstSnapshot->setDataVisitor($dataVisitor);
        $firstSnapshot->assert();
        $snapshotFileName = $firstSnapshot->snapshotFileName();
        $this->unlinkAfter[] = $snapshotFileName;

        // This second snapshot will compare new data to the existing one.
        $secondSnapshot = new HtmlSnapshot($htmlTwo);
        $secondSnapshot->setDataVisitor($dataVisitor);
        $secondSnapshot->setSnapshotFileName($snapshotFileName);
        $secondSnapshot->assert();

        // Expect the test to fail when the data visitor is not used.
        $this->expectException(AssertionFailedError::class);

        $failingSnapshot = new HtmlSnapshot($htmlTwo);
        $failingSnapshot->setSnapshotFileName($snapshotFileName);
        $failingSnapshot->assert();
    }
}
