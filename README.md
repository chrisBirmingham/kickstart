# Intermaterium/Kickstart

## Description

A simple PHP wrapper library around AWS lambda's runtime API. To be used inside a custom PHP 8 runtime.

## Usage

Composer install the library into your lambdas runtime layer or another layer dedicated to composer files.

Then create a /opt/bootstrap file in your lamdbas runtime layer invoking the library.

```php
require_once("/opt/vendor/autoload.php");

use Intermaterium\Kickstart\Locator\FileHandlerLocator;
use Intermaterium\Kickstart\RuntimeFactory;

// Retrieve the function being invoked by lamdba
$fileHandlerLocator = new FileHandlerLocator($_ENV["LAMBDA_TASK_ROOT"]);
$lambdaHandler = $fileHandlerLocator->get($_ENV["_HANDLER"]);

$runtimeFactory = new RuntimeFactory();
$runtime = $runtimeFactory->create($_ENV["AWS_LAMBDA_RUNTIME_API"]);

do {
    // Infinitately loop handling events from lamdba until lambda kills the runtime
    $runtime->invoke($lambdaHandler);
} while(true);
```
