<?php
/**
 * The base snapshot class, an extension of the base Codeception one.
 *
 * @package tad\Codeception\SnapshotAssertions
 */

namespace tad\Codeception\SnapshotAssertions;

use Codeception\Exception\ContentNotFound;
use Codeception\Snapshot;
use Codeception\Util\Debug;
use Codeception\Util\ReflectionHelper;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Class AbstractSnapshot
 * @package tad\Codeception\SnapshotAssertions
 */
class AbstractSnapshot extends Snapshot
{
    /**
     * Keeps a counter for each class, function and data-set combination.
     *
     * @var array<string,array>
     */
    protected static $counters = [];

    /**
     * A list of method names provided by the SnapshotAssertions trait.
     *
     * @var array<string>
     */
    protected static $traitMethods = [];

    /**
     * The callback that will be called on each data entry of the snapshot.
     *
     * @var callable
     */
    protected $dataVisitor;

    /**
     * Snapshot constructor.
     *
     * @param  mixed  $current  The current value.
     */
    public function __construct(protected $current = null)
    {
    }

    /**
     * Returns the absolute path to the snapshot file that has been, or will be, generated.
     *
     * @throws \ReflectionException If there's an error while building the class reflection.
     */
    public function snapshotFileName(): string
    {
        return $this->getFileName();
    }

    /**
     * Returns the path to the snapshot file that will be, or has been generated, including the file extension.
     *
     * @return string The snapshot file name, including the file extension.
     * @throws \ReflectionException If the class that called the class cannot be reflected.
     */
    protected function getFileName(): string
    {
        if (empty($this->fileName)) {
            $traitMethods = static::getTraitMethods();
            $backtrace = array_values(array_filter(
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS
                    | DEBUG_BACKTRACE_PROVIDE_OBJECT, 5),
                static function (array $backtraceEntry) use ($traitMethods): bool {
                    return !in_array(
                        $backtraceEntry['class'],
                        [Snapshot::class, static::class, self::class, SnapshotAssertions::class],
                        true
                    ) && !in_array($backtraceEntry['function'], $traitMethods, true);
                }
            ));
            $class = $backtrace[0]['class'];
            $classFrags = explode('\\', $class);
            $classBasename = array_pop($classFrags);
            $classFile = (new \ReflectionClass($class))->getFileName();

            if ($classFile === false) {
                throw new \RuntimeException('Cannot get the filename of the class ' . $class);
            }

            $classDir = dirname($classFile);
            $function = $backtrace[0]['function'];
            $dataSetFrag = '';
            if ($backtrace[0]['object'] instanceof TestCase) {
                /** @var TestCase $testCase */
                $testCase = $backtrace[0]['object'];
                $dataName = $this->getDataName($testCase);
                if ($dataName !== '') {
                    $dataSetFrag = '__'.$dataName;
                }
            }
            $fileName = sprintf(
                '%s__%s%s__%d.%s',
                $classBasename,
                $function,
                $dataSetFrag,
                $this->getCounterFor($class, $function, $dataSetFrag),
                $this->fileExtension()
            );
            $this->fileName = $classDir.'/__snapshots__/'.$fileName;
        }

        return $this->fileName;
    }

    /**
     * Returns an array of the trait method names.
     *
     * @return array<string> An array of the trait method names.
     *
     * @throws \ReflectionException If a reflection cannot be done on a trait method.
     */
    protected static function getTraitMethods(): array
    {
        if (!empty(static::$traitMethods)) {
            return static::$traitMethods;
        }

        $reflection = new \ReflectionClass(SnapshotAssertions::class);
        static::$traitMethods = array_map(function (\ReflectionMethod $method): string {
            return $method->name;
        }, $reflection->getMethods());

        return static::$traitMethods;
    }

    /**
     * Returns the counter, an integer, for a class, methdo and data-set combination.
     *
     * @param  string  $class        The class to return the counter for.
     * @param  string  $function     The function/method to return the counter for.
     * @param  string  $dataSetName  The name of the current dataset, if any.
     *
     * @return int The counter, managed on a static level, for the combination.
     */
    protected function getCounterFor(string $class, string $function, string $dataSetName = '')
    {
        $function .= $dataSetName;

        if (isset(static::$counters[$class][$function])) {
            return static::$counters[$class][$function]++;
        }
        static::$counters[$class][$function] = 0;
        return 0;
    }

    /**
     * Returns the file extension, without the leading dot, of the snapshot the class will generate.
     *
     * @return string The file extension, without the leading dot, of the snapshot the class will generate.
     */
    public function fileExtension(): string
    {
        return 'snapshot';
    }

    /**
     * Sets the contents of the current snapshot.
     *
     * This method is useful to create, or overwrite, the contents of the snapshot during tests.
     *
     * @param mixed $contents The snapshot contents.
     *
     * @throws \ReflectionException
     */
    public function snapshotPutContents(string|bool $contents): void
    {
        $dataSetBackup = $this->dataSet;
        $this->dataSet = $contents;
        $this->save();
        $this->dataSet = $dataSetBackup;
    }

    /**
     * Saves the snapshot contents to the snapshot file.
     *
     * @throws \Exception If there's an issue reading or saving the snapshot.
     */
    protected function save(): void
    {
        $fileName = $this->getFileName();
        $snapshotsDir = dirname($fileName);

        if (!is_dir($snapshotsDir) && !mkdir($snapshotsDir, 0777, true) && !is_dir($snapshotsDir)) {
            throw new \RuntimeException(sprintf('Snapshots directory "%s" was not created', $snapshotsDir));
        }

        file_put_contents($fileName, $this->prepareSnapshotForDump());
    }

    /**
     * Prepares the snapshot before it's dumped into a snapshot file.
     *
     * @return mixed The prepared snapshot contents.
     */
    public function prepareSnapshotForDump(): string|bool
    {
        return $this->dataSet;
    }

    /**
     * Asserts the current contents match the contents of the snapshot.
     *
     *
     * @throws \ReflectionException If there's an issue building the snapshot file name.
     */
    public function assert(): void
    {
        // Fetch data.
        $data = $this->fetchData();

        if ($this->isEmptyData($data)) {
            throw new ContentNotFound("Fetched snapshot is empty.");
        }

        $this->load();

        if (!$this->dataSet) {
            $this->printDebug('Snapshot is empty. Updating snapshot...');
            $this->dataSet = $data;
            $this->save();
            return;
        }

        try {
            $this->assertData($data);
            $this->printDebug('Data matches snapshot');
        } catch (AssertionFailedError $exception) {
            $this->printDebug('Snapshot assertion failed');

            if (!is_bool($this->refresh)) {
                $confirm = Debug::confirm('Should we update snapshot with fresh data? (Y/n) ');
            } else {
                $confirm = $this->refresh;
            }

            if ($confirm) {
                $this->dataSet = $data;
                $this->save();
                $this->printDebug('Snapshot data updated');
                return;
            }

            $this->fail($exception->getMessage());
        }
    }

    /**
     * Should return data from current test run.
     *
     * This override of the base method will, by default, return the current data.
     *
     * @return mixed The fetched data, the current data by default.
     */
    protected function fetchData(): array|string|false
    {
        return $this->current;
    }

    /**
     * Whether the data is empty or not.
     *
     * Extending classes can override this method to implement more sofisticated checks.
     *
     * @param  mixed  $data  The data to check.
     *
     * @return bool Whether the data can be considered empty, hence invalid, or not.
     */
    protected function isEmptyData($data): bool
    {
        return !$data;
    }

    /**
     * Loads the data set from the snapshot.
     *
     *
     * @throws \ReflectionException
     */
    protected function load(): void
    {
        if (!file_exists($this->getFileName())) {
            return;
        }
        $this->dataSet = file_get_contents($this->getFileName());

        if (!$this->dataSet) {
            throw new ContentNotFound("Loaded snapshot is empty");
        }
    }

    /**
     * Copy and paste of the bae method to allow for easier debug.
     *
     * @param  string  $message  The message to print in debug.
     *
     * @return void
     */
    protected function printDebug($message)
    {
        Debug::debug($this::class.': '.$message);
    }
    /**
 * Returns the data name taking care of doing so in a way that is compatible with different PHPUnit versions.
 *
 * @param  TestCase  $testCase The current test case.
 *
 * @return string The data name if available or an empty string if not available.
 */
    protected function getDataName(TestCase $testCase): string
    {
        if (method_exists($testCase, 'dataName')) {
            return (string)$testCase->dataName();
        }

        $candidates = array_reverse(class_parents($testCase));
        $testCaseClass = $testCase::class;
        $candidates[$testCaseClass] = $testCaseClass;
        $read = '';
        foreach (array_reverse($candidates) as $class) {
            try {
                $read = (string)ReflectionHelper::readPrivateProperty($testCase, 'dataName', $class);
            } catch (\ReflectionException) {
                continue;
            }
            break;
        }

        return $read;
    }

    /**
     * Overrides the base implementation to add a pre-assertion data handler.
     *
     * @param mixed $data The data to check.
     */
    protected function assertData(mixed $data): void
    {
        if ($this->dataVisitor !== null) {
            list($data, $dataSet) = call_user_func($this->dataVisitor, $data, $this->dataSet);
            $this->dataSet = $dataSet;
        }

        parent::assertData($data);
    }

    /**
     * Sets the node visitor that will be called by the snapshot on each "node".
     *
     * @param callable $dataVisitor The data visitor that will be called on each visit of a snapshot "node".
     *                              The parameters passed to the visitor will be different for each snapshot; usually
     *                              the expected data and the current data.
     */
    public function setDataVisitor(callable $dataVisitor): void
    {
        $this->dataVisitor = $dataVisitor;
    }

    /**
     * Sets the file name the snapshot should use to store and fetch information.
     *
     * @param string $snapshotFileName The absolute path to the file the snapshot file should use.
     *                                 This value is, usually, the one produced by another snapshot `snapshotFileName()`
     *                                 method.
     */
    public function setSnapshotFileName(string $snapshotFileName): void
    {
        $this->fileName = $snapshotFileName;
    }
}
