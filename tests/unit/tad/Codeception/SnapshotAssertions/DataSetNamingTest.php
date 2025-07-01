<?php

namespace tad\Codeception\SnapshotAssertions;

class DataSetNamingTest extends BaseTestCase
{
    use SnapshotAssertions;

    /**
     * @before
     */
    public function cleanUp()
    {
        $this->unlinkFiles();
    }

    public static function stringSnapshotDataProvider():array
    {
        return [
            'snake_case_dataset' => ['snake_case', 'snake_case_dataset'],
            'camelCaseDataset' => ['camelCase', 'camelCaseDataset'],
            'PascalCaseDataset' => ['PascalCase', 'PascalCaseDataset'],
            'kebab-case-dataset' => ['kebab-case', 'kebab-case-dataset'],
            'spaces between words dataset' => ['spaces_between_words', 'spaces_between_words_dataset'],
            'double  spaces  between  words  dataset' => ['double_spaces_between_words_dataset', 'double_spaces_between_words_dataset'],
        ];
    }

    /**
     * Should name snapshot files with slugs
     *
     * @test
     * @dataProvider stringSnapshotDataProvider
     */
    public function should_name_snapshot_files_with_slugs(string $testString, string $expected): void
    {
        $expectedSnapshotFileName = __DIR__ . '/__snapshots__/DataSetNamingTest__should_name_snapshot_files_with_slugs__' . $expected . '__0.snapshot.txt';
        $this->unlinkAfter[]= $expectedSnapshotFileName;

        $this->assertMatchesStringSnapshot('test');

        $this->assertFileExists($expectedSnapshotFileName);
    }
}
