# Intermaterium/Kickstart

## Description

A simple PHP wrapper library around AWS lambda's runtime API. To be used inside a custom PHP 8 runtime.

## Usage

### Lambda Runtime

Composer install the library into your lambdas runtime layer or another layer dedicated to composer files.

```bash
composer require intermaterium/kickstart
```

Then create a /opt/bootstrap file in your lambdas runtime layer invoking the library.

```php
use Intermaterium\Kickstart\Locator\FileHandlerLocator;
use Intermaterium\Kickstart\RuntimeFactory;

require_once("/opt/vendor/autoload.php");

// Create our runtime from the provided environment
$runtimeFactory = new RuntimeFactory();
$runtime = $runtimeFactory->create($_ENV["AWS_LAMBDA_RUNTIME_API"]);

try {
    // Retrieve the function being invoked by lambda
    $fileHandlerLocator = new FileHandlerLocator($_ENV["LAMBDA_TASK_ROOT"]);
    $lambdaHandler = $fileHandlerLocator->get($_ENV["_HANDLER"]);
} catch (\Exception $e) {
    // If we failed to get the handler send an initialisation error and kill the lambda
    $runtime->initialisationFailure("Failed to get lambda handler, $e");
    exit(1);
}

do {
    // Infinitely loop handling events from lambda until lambda kills the runtime
    $runtime->invoke($lambdaHandler);
} while(true);
```

### Lambda functions

Kickstart supports aws lambdas in the form of either php functions like:

```php
use Intermaterium\Kickstart\Context\Context;

return function($event, Context $context): mixed {
    return "Hello " . ($event["queryStringParameters"]["name"] ?? "world");
};
```

Or callable classes:

```php
use Intermaterium\Kickstart\Context\Context;

class Handler
{
    public function __invoke($event, Context $context): mixed
    {
        return "Hello " . ($event["queryStringParameters"]["name"] ?? "world");
    }
}

return new Handler();
```
