<?php namespace tad\Codeception\SnapshotAssertions;

class SnapshotAssertionsTest extends BaseTestCase
{
    use SnapshotAssertions;

    /**
     * It should correctly name snapshot files
     *
     * @test
     */
    public function should_correctly_name_snapshot_files()
    {
        $stringSnapshot = new StringSnapshot(' foo ');
        $classFrags = explode('\\', __CLASS__);
        $class = end($classFrags);
        $methodFrags = explode('::', __METHOD__);
        $method = end($methodFrags);
        $expectedSnapshotFileName = __DIR__
            .sprintf(
                '/__snapshots__/%s__%s__%d.%s',
                $class,
                $method,
                0,
                $stringSnapshot->fileExtension()
            );

        $snapshot = $stringSnapshot->snapshotFileName();

        $this->assertEquals($expectedSnapshotFileName, $snapshot);
    }

    /**
     * It should create the snapshot when asserting and snapshot is not there
     *
     * @test
     */
    public function should_create_the_snapshot_when_asserting_and_snapshot_is_not_there()
    {
        $stringSnapshot = new StringSnapshot();
        $this->unlinkAfter[] = $stringSnapshot->snapshotFileName();
        $this->assertFileNotExists($stringSnapshot->snapshotFileName());

        $this->assertMatchesStringSnapshot('foo');

        $this->assertFileExists($stringSnapshot->snapshotFileName());
    }

    /**
     * It should allow making a string assertion
     *
     * @test
     */
    public function should_allow_making_a_string_assertion()
    {
        $stringSnapshot = new StringSnapshot();
        $this->unlinkAfter[] = $stringSnapshot->snapshotFileName();
        $stringSnapshot->snapshotPutContents('foo');

        $this->assertMatchesStringSnapshot('foo');
    }

    /**
     * It should allow making a string assertion for named data-sets
     *
     * @test
     * @dataProvider namedDataSets
     */
    public function should_allow_making_a_string_assertion_for_named_data_sets($string)
    {
        $this->assertMatchesStringSnapshot($string);
        $classFrags = explode('\\', __CLASS__);
        $class = end($classFrags);
        $methodFrags = explode('::', __METHOD__);
        $method = end($methodFrags);
        $stringSnapshot = new StringSnapshot();
        $expectedSnapshotFileName = __DIR__
            .sprintf(
                '/__snapshots__/%s__%s__%s__%d.%s',
                $class,
                $method,
                $string,
                0,
                $stringSnapshot->fileExtension()
            );

        $this->assertFileExists($expectedSnapshotFileName);
        $this->unlinkAfter[] = $expectedSnapshotFileName;
    }

    /**
     * It should allow making string assertion for not named data set
     *
     * @test
     * @dataProvider notNamedDataSet
     */
    public function should_allow_making_string_assertion_for_not_named_data_set($string)
    {
        $this->assertMatchesStringSnapshot($string);
        $classFrags = explode('\\', __CLASS__);
        $class = end($classFrags);
        $methodFrags = explode('::', __METHOD__);
        $method = end($methodFrags);
        $stringSnapshot = new StringSnapshot();
        $expectedSnapshotFileName = __DIR__
            .sprintf(
                '/__snapshots__/%s__%s__%s__%d.%s',
                $class,
                $method,
                $string,
                0,
                $stringSnapshot->fileExtension()
            );

        $this->assertFileExists($expectedSnapshotFileName);
        $this->unlinkAfter[] = $expectedSnapshotFileName;
    }

    public function namedDataSets()
    {
        return [
            'one' => ['one'],
            'two' => ['two'],
            'snake_case' => ['snake_case'],
            'camelCase' => ['camelCase'],
        ];
    }

    public function notNamedDataSet()
    {
        return [
            ['0'],
            ['1'],
            ['2']
        ];
    }

    /**
     * It should allow making a partial HTML assertion
     *
     * @test
     */
    public function should_allow_making_a_partial_html_assertion()
    {
        $html = '<main><p>Some text</p></main>';
        $htmlSnapshot = new HtmlSnapshot();
        $htmlSnapshot->snapshotPutContents($html);
        $this->unlinkAfter[] = $htmlSnapshot->snapshotFileName();
        $this->assertMatchesHtmlSnapshot($html);
    }

    /**
     * It should allow making JSON assertions
     *
     * @test
     */
    public function should_allow_making_json_assertions()
    {
        $json = json_encode([
            'one' => 'two',
            'two' => 23,
            'three' => 89,
            'zero' => 0,
        ]);
        $jsonSnapshot = new JsonSnapshot();
        $this->unlinkAfter[] = $jsonSnapshot->snapshotFileName();
        $jsonSnapshot->snapshotPutContents($json);
        $this->assertMatchesJsonSnapshot($json);
    }
}
