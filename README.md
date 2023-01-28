# Intermaterium/Kickstart

## Description

A simple PHP wrapper library around AWS lambda's runtime API. To be used inside a custom PHP 8 runtime.

## Usage

Composer install the library into your lambdas runtime layer or another layer dedicated to composer files.

Then create a /opt/bootstrap file in your lambdas runtime layer invoking the library.

```php
use Intermaterium\Kickstart\Locator\FileHandlerLocator;
use Intermaterium\Kickstart\RuntimeFactory;

require_once("/opt/vendor/autoload.php");

// Create our runtime from the provided environment
$runtimeFactory = new RuntimeFactory();
$runtime = $runtimeFactory->create($_ENV["AWS_LAMBDA_RUNTIME_API"]);

try {
    // Retrieve the function being invoked by lamdba
    $fileHandlerLocator = new FileHandlerLocator($_ENV["LAMBDA_TASK_ROOT"]);
    $lambdaHandler = $fileHandlerLocator->get($_ENV["_HANDLER"]);
} catch (\Exception $e) {
    // If we failed to get the handler send an initialisation error and kill the lambda
    $runtime->initialisationFailure("Failed to get lambda handler, $e");
    exit(1);
}

do {
    // Infinitately loop handling events from lamdba until lambda kills the runtime
    $runtime->invoke($lambdaHandler);
} while(true);
```
