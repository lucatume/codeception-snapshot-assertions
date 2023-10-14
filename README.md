# Codeception Snapshot Assertions

Leverage Codeception snapshot support to make snapshot testing in Codeception projects easier.

## Code example

```php
<?php
class WidgetTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    public function testDefaultContent(){
        $widget = new Widget() ;

        $this->assertMatchesHtmlSnapshot($widget->html());
    }
    
    public function testOutput(){
       $widget = new Widget(['title' => 'Test Widget', 'content' => 'Some test content']) ;

       $this->assertMatchesHtmlSnapshot($widget->html());
    }
}
```

## Requirements

The package is based on the snapshot support added in Codeception since version `2.5.0`, as such the library requirments are:
* PHP 5.6+
* Codeception 2.5+

## Installation

Install the package using [Composer](https://getcomposer.org/):
```bash
composer install lucatume/codeception-snapshot-assertions
``` 
Codeception is a requirement for the package to work and will be installed as a dependency if not specified in the project Composer configuration file (`composer.json`).

## What is snapshot testing?

Snapshot testing is a convenient way to test code by testing its output.  
Snapshot testing is faster than full-blown visual end-to-end testing (and not a replacement for it) but less cumbersome to write than lower lever unit testing (and, again, not a replacement for it).  
This kind of testing lends itself to be used in unit and integration testing to automate the testing of output.  
Read more about snapshot testing here: 
* [Sitepoint article about snapshot testing](https://www.sitepoint.com/snapshot-testing-viable-php/)
* [Snapshot testing package from Spatie](https://hackernoon.com/a-package-for-snapshot-testing-in-phpunit-2e4558c07fe3); and the corresponding [GitHub repository](https://github.com/spatie/phpunit-snapshot-assertions).
* [Codeception introduction of snapshot testing](https://codeception.com/09-24-2018/codeception-2.5.html)

### How is this different from what Codeception does?

* snapshots do not require writing a class dedicated to it, you can just `use` the `tad\Codeception\SnapshotAssertions\SnapshotAssertions` trait in your test case and one of the `assertMatches...` methods it provides.  
* it supports string and HTML snapshot testing too.
* the snapshots generated by the code live in a folder of the same folder, the `__snapshots__` one, that generated them.

### How is this different from what the Spatie package does?

* it leverages Codeception own snapshot implementation, hence it will **not** work on vanilla [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework")
* it lowers the library requirement from PHP 7.0 to PHP 5.6.

## Usage

The package supports the following type of assertions:
1. string snapshot assertions, to compare a string to a string snapshot with the `assertMatchesStringSnapshot` method.
2. HTML snapshot assertions, to compare an HTML fragment to an HTML fragment snapshot  with the `assertMatchesHtmlSnapshot` method.
3. JSON snapshot assertions, to compare a JSON string to a stored JSON snapshot with the `assertMatchesJsonSnapshot` method.
3. Code snapshot assertions, to compare code to a stored code snapshot with the `assertMatchesCodeSnapshot` method.

The first time an `assert...` method is called the library will generate a snapshot file in the same directory as the tests, in the `__snapshots__` folder.  
As an example if the following test case lives in the `tests/Output/WidgetTest.php` file then when the `testDefaultContent` method runs the library will generate the `tests/Output/WidgetTest__testDefaultContent__0.snapshot.html`  file; you can regenerate failing snapshots by running Codeception tests in debug mode (using the `--debug` flag of the `run` command).

### Configuration

The library integrates with Codeception configuration system to allow some configuration options to be set.
The library supports two configuration parameters:
* `version` (string) that will be prefixed to the snapshot file name; defaults to `` (an empty string).
* `refresh` (boolean) that will force the snapshot regeneration on failure automatically; defaults to `false`.

The configuration parameters can be set in the `codeception.yml` or `codeception.dist.yml` file under the `snapshot` key, as in the following example:

```yaml
# Codeception own configuration
paths:
  tests: tests
  output: tests/_output
  data: tests/_data
  support: tests/_support
  envs: tests/_envs
actor_suffix: Tester
extensions:
  enabled:
    - Codeception\Extension\RunFailed
# Snapshot configuration
snapshot:
  version: 23
  refresh: true
```

#### Version
If the `version` string is set to a non-empty value the snapshot file name will be prefixed with it.
In the following example the `version` parameter is set to `alt` and the snapshot file name will be `WidgetTest__alt__testDefaultContent__alt.snapshot.html`:

```php
<?php
use Codeception\Test\Unit;

class WidgetTest extends Unit {
  public function testDefaultContent():void {
    $widget = new Widget() ;
    $this->assertMatchesHtmlSnapshot($widget->html());
  }
}
```

If the `version` parameter is not set, or set to an empty string, the snapshot file name will not be prefixed with anything and will be `WidgetTest__testDefaultContent__0.snapshot.html`:

#### Refresh

If the `refresh` parameter is set to `true` the snapshot will be regenerated on failure automatically.
Normally Codeception snapshots are regenerated by running the tests in debug mode, using the `--debug` flag of the `run` command, and by replying `yes` to the prompt asking if the snapshots should be regenerated; or by removing the snapshot files manually.
If the `refresh` parameter is set to `true` the snapshots will be regenerated automatically on failure, with no prompt, **if the current test run is in debug mode.**

### String assertions

This kind of assertion is useful when the output of a method is a plain string.  
The snapshot produced by this kind of assertion will have the `.snapshot.txt` file extension.  
The method used to make string snapshot assertions is `tad\Codeception\SnapshotAssertions\SnapshotAssertions::assertStringSnapshot()`.  
Usage example;

```php
<?php
class ErrorMessageTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    
    public function testClassAndMethodOutput(){
        $errorMessage = new ErrorMessage(__CLASS__, 'foo') ;

        $this->assertMatchesStringSnapshot($errorMessage->message());
    }
    
    public function testClassOnlyOutput(){
        $errorMessage = new ErrorMessage(__CLASS__) ;

        $this->assertMatchesStringSnapshot($errorMessage->message());
    }
}
```

### HTML assertions

This kind of assertion is useful when the output of a method is an HTML document or HTML fragment.  
The snapshot produced by this kind of assertion will have the `.snapshot.html` file extension.  
The method used to make HTML snapshot assertions is `tad\Codeception\SnapshotAssertions\SnapshotAssertions::assertHtmlSnapshot()`.  
Usage example;

```php
<?php
class WidgetTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    
    public function testDefaultContent(){
        $widget = new Widget() ;

        $this->assertMatchesHtmlSnapshot($widget->html());
    }
    
    public function testOutput(){
       $widget = new Widget(['title' => 'Test Widget', 'content' => 'Some test content']) ;

       $this->assertMatchesHtmlSnapshot($widget->html());
    }
}
```

### JSON assertions

This kind of assertion is useful when the output of a method is a JSON string.  
The snapshot produced by this kind of assertion will have the `.snapshot.html` file extension.  
The method used to make JSON snapshot assertions is `tad\Codeception\SnapshotAssertions\SnapshotAssertions::assertJsonSnapshot()`.  
Usage example:  

```php
<?php
class ApiTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    
    public function testGoodResponse(){
        $this->givenTheUserIsAuthenticated();
        $request = new Request([
            'me' => [
                'name'   
            ]
        ]);
        
        $api = new API() ;

        $this->assertMatchesJsonSnapshot($api->handle($request));
    }

    public function testMissingAuthResponse(){
        $request = new Request([
            'me' => [
                'name'   
            ]
        ]);
        
        $api = new API() ;

        $this->assertMatchesJsonSnapshot($api->handle($request));
    }
}
```

### Code assertions

This kind of assertion is useful when the output of a method is code.  
The snapshot produced by this kind of assertion will have the `.snapshot.php` file extension by default, but you can specify an extension to use for the snapshot.  
The method used to make code snapshot assertions is `tad\Codeception\SnapshotAssertions\SnapshotAssertions::assertCodeSnapshot()`.  
Usage example;

```php
<?php
class ApiTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    
    public function testGoodCode(){
		$generator = new CodeGenerator();
		$code = $generator->produce('phpCode');
        $this->assertMatchesCodeSnapshot($code);
    }

    public function testMissingAuthResponse(){
		$generator = new CodeGenerator();
		$code = $generator->produce('jsCode');
        $this->assertMatchesCodeSnapshot($code);
    }
}
```

### Directory assertions

This kind of assertion is useful to ensure directory structure and contents do not change overtime, when the output of a code block is a directory and files in it.  
This assertion will check that the current directory, and the one captured in the snapshot, have the same files, and that each file has the same contents.  
The snapshot produced by this kind of assertion will have the `.snapshot` file extension; they are plain text files.  
The method used to make code snapshot assertions is `tad\Codeception\SnapshotAssertions\SnapshotAssertions::assertDirectorySnapshot()`.  
Usage example;

```php
<?php
class DirectorySetupTest extends Codeception\TestCase\Test
{
    use tad\Codeception\SnapshotAssertions\SnapshotAssertions;
    
    public function testGoodDirectorySetUp(){
		$generator = new DirectorySetup();
        $destination = codecept_output_dir('test');

		$generator->setUpDirectory('test', $destination );

        $this->assertMatchesDirectorySnapshot($destination);
    }

    public function testFailingDirectorySetUp(){
		$generator = new DirectorySetup();
        $destination = codecept_output_dir('failing');

		$generator->setUpDirectory('failing', $destination );

        $this->assertMatchesDirectorySnapshot($destination);
    }
}
```

## Visitor functions

To allow more fine-grained control over how the assertion on the data should be made, each Snapshot implementation supports "data visitors.".  

A data visitor is a `callable` that will receive, from the snapshot implementation, the expected data and the current data.  
Depending on the snapshot type the arguments received by the callback might differ or be more than two.

### Examples

In the following example the data visitor is used to exclude some files from a directory snapshot and to drop some hashed lines from some files:

```php
<?php

public function test_files(){
        $dataVisitor = static function ($expected, $current, $pathName) {
            if (strpos($pathName, 'fileOne')) {
                // Empty file one, like dropping it.
                return [[], []];
            }

            if (strpos($pathName, 'fileTwo')) { 
                // Remove the hash line in file two.
                $removeHashLine = static function ($line) {
                    return !preg_match('/\\/\\/\\s*\\[HASH].*$/uim', $line);
                };
                return [
                    array_filter($expected, $removeHashLine),
                    array_filter($current, $removeHashLine)
                ];
            }

            return [$expected, $current];
        };
    
        $dirToTest = codecept_output('dir-to-test');
        $snapshot =  new DirectorySnapshot($dirToTest);
        $snapshot->setDataVisitor($dataVisitor);
        $snapshot->assert();
}
```

In this example the data visitor is used to remove some hash data from a JSON object:

```php
<?php

public function test_json_object(){
        $removeHashEntry = static function ($jsonString) {
            // Remove the `hash` key from the JSON object.
            return json_encode(array_diff_key(json_decode($jsonString, true), array_flip(['hash'])));
        };
        $dataVisitor = static function ($expected, $current) use ($removeHashEntry) {
            return array_map($removeHashEntry, [$expected, $current]);
        };

        // This first snapshot will create the first HTML snapshot.
        $firstSnapshot = new JsonSnapshot(MyJsonProducingObject::data());
        $firstSnapshot->setDataVisitor($dataVisitor);
        $firstSnapshot->assert();
}
```
