<?php

namespace tad\Codeception\SnapshotAssertions;

class TraitTest extends BaseTestCase
{
    use CustomAssertionTraitUsingCustomAssertionTrait;

    protected $unlinkAfter = [
        __DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files__0.snapshot.txt',
        __DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files__1.snapshot.txt',
        __DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset__one__0.snapshot.txt',
        __DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset__two__0.snapshot.txt',
        __DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset__three__0.snapshot.txt',
        __DIR__ . "/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset_when_trait_using_trait__one__0.snapshot.txt",
        __DIR__ . "/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset_when_trait_using_trait__two__0.snapshot.txt",
        __DIR__ . "/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset_when_trait_using_trait__three__0.snapshot.txt",
    ];

    /**
     * @before
     */
    public function removeSnapshots()
    {
        $this->_after();
    }

    /**
     * It should correctly name snapshot files
     *
     * @test
     */
    public function should_correctly_name_snapshot_files()
    {
        // The snapshot does not exist, and it will be created.
        $this->assertMatchesCustomSnapshot('custom');
        // The snapshot does not exist, and it will be created.
        $this->assertMatchesByAnotherCriteria('custom');

        $this->assertFileExists(__DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files__0.snapshot.txt');
        // Commented out due to a back-compat bug ...
        // $this->assertFileExists(__DIR__ . '/__snapshots__/TraitTest__should_correctly_name_snapshot_files__1.snapshot.txt');
    }

    public static function names()
    {
        return [
            'one'   => [ 'one' ],
            'two'   => [ 'two' ],
            'three' => [ 'three' ]
        ];
    }

    /**
     * It should correctly name snapshot files with dataset
     *
     * @dataProvider names
     * @test
     */
    public function should_correctly_name_snapshot_files_with_dataset($name)
    {
        // The snapshot does not exist, and it will be created.
        $this->assertMatchesCustomSnapshot('custom');

        $this->assertFileExists(
            __DIR__ . "/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset__{$name}__0.snapshot.txt"
        );
    }

    /**
     * Should correctly name snapshot files with dataset when trait using trait
     *
     * @dataProvider names
     * @test
     */
    public function should_correctly_name_snapshot_files_with_dataset_when_trait_using_trait($name)
    {
        // The snapshot does not exist, and it will be created.
        // The snapshot does not exist, and it will be created.
        $this->assertMatchesCustomSnapshot('custom');

        $this->assertFileExists(
            __DIR__ . "/__snapshots__/TraitTest__should_correctly_name_snapshot_files_with_dataset_when_trait_using_trait__{$name}__0.snapshot.txt"
        );
    }
}
