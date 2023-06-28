<?php namespace tad\Codeception\SnapshotAssertions;

use PHPUnit\Framework\AssertionFailedError;

class BaseVisitorTest extends BaseTestCase
{
    /**
     * It should allow adding a visitor
     *
     * @test
     */
    public function should_allow_adding_a_visitor(): void
    {
        $removeHashEntry = static function ($jsonString): string|bool {
            return json_encode(array_diff_key(json_decode($jsonString, true), array_flip(['hash'])));
        };
        $dataVisitor = static function ($expected, $current) use ($removeHashEntry): array {
            return array_map($removeHashEntry, [$expected, $current]);
        };

        $hash = md5(microtime());
        $newHash = md5(microtime());

        $this->assertNotEquals($hash, $newHash);

        $jsonOne = json_encode(['test_one' => 'one', 'hash' => $hash]);
        $jsonTwo = json_encode(['test_one' => 'one', 'hash' => $newHash]);


        // This first snapshot will create the first HTML snapshot.
        $firstSnapshot = new JsonSnapshot($jsonOne);
        $firstSnapshot->setDataVisitor($dataVisitor);
        $snapshotFileName = $firstSnapshot->snapshotFileName();
        $firstSnapshot->assert();
        $this->unlinkAfter[] = $snapshotFileName;

        // This second snapshot will compare new data to the existing one.
        $secondSnapshot = new JsonSnapshot($jsonTwo);
        $secondSnapshot->setDataVisitor($dataVisitor);
        $secondSnapshot->setSnapshotFileName($snapshotFileName);
        $secondSnapshot->assert();

        // Expect the test to fail when the data visitor is not used.
        $this->expectException(AssertionFailedError::class);

        $failingSnapshot = new JsonSnapshot($jsonTwo);
        $failingSnapshot->setSnapshotFileName($snapshotFileName);
        $failingSnapshot->assert();
    }
}
