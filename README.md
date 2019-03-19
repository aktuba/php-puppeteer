# PhpPupeeteer

PhpPupeeteer based on Puppeteer.
A [Puppeteer](https://github.com/GoogleChrome/puppeteer/) bridge for PHP, supporting the entire API. Based on [Rialto](https://github.com/nesk/rialto/), a package to manage Node resources from PHP.

Here are some examples [borrowed from Puppeteer's documentation](https://github.com/GoogleChrome/puppeteer/blob/master/README.md#usage) and adapted to PHP's syntax:

**Example** - navigating to https://example.com and saving a screenshot as *example.png*:

```php
use PhpPupeeteer\PhpPupeeteer;

$phpPuppeteer = new PhpPupeeteer;
$browser = $phpPuppeteer->getBrowser();
$page = $browser->newPage();
$page->gotoWithWait('https://example.com');
$page->screenshot(['path' => 'example.png']);
$browser->close();
```

**Example** - evaluate a script in the context of the page:

```php
use Puphpeteer\Puppeteer;
use Puphpeteer\Data\Js;

$puppeteer = new PhpPupeeteer;

$browser = $phpPuppeteer->getBrowser();
$page = $browser->newPage();
$page->gotoWithWait('https://example.com');

// Get the "viewport" of the page, as reported by the page.
$dimensions = $page->evaluate(Js::createWithBody("
    return {
        width: document.documentElement.clientWidth,
        height: document.documentElement.clientHeight,
        deviceScaleFactor: window.devicePixelRatio,
    };
"));

printf('Dimensions: %s', print_r($dimensions, true));

$browser->close();
```

## Requirements and installation

This package requires PHP >= 7.1 and Node >= 8.

Install it with these two command lines:

```shell
composer require aktuba/php-puphpeteer@dev-master
npm install @nesk/puphpeteer
```

## Notable differences between PhpPupeeteer and Puppeteer

### Puppeteer's class must be instanciated

Instead of requiring Puppeteer:

```js
const puppeteer = require('puppeteer');
```

You have to instanciate the `PhpPupeeteer` class:

```php
use PhpPupeeteer\PhpPupeeteer;

$puppeteer = PhpPupeeteer;
```

This will create a new Node process controlled by PHP.

You can also pass some options to the constructor, see [Rialto's documentation](https://github.com/nesk/rialto/blob/master/docs/api.md#options). PuPHPeteer also extends these options:

```php
[
    // Logs the output of Browser's console methods (console.log, console.debug, etc...) to the PHP logger
    'log_browser_console' => false,
]
```

<details>
<summary><strong>⏱ Want to use some timeouts higher than 30 seconds in Puppeteer's API?</strong></summary> <br>

If you use some timeouts higher than 30 seconds, you will have to set a higher value for the `read_timeout` option (default: `35`):

```php
$phpPuppeteer = $phpPuppeteer->getBrowser([
	'read_timeout' => 65, // In seconds
]);

$browser->newPage()->goto($url, [
    'timeout' => 60000, // In milliseconds
]);
```
</details>

### No need to use the `await` keyword

With PhpPupeeteer, every method call or property getting/setting is synchronous.

### Some methods have been aliased

The following methods have been aliased because PHP doesn't support the `$` character in method names:

- `$` => `querySelector`
- `$$` => `querySelectorAll`
- `$x` => `querySelectorXPath`
- `$eval` => `querySelectorEval`
- `$$eval` => `querySelectorAllEval`

Use these aliases just like you would have used the original methods:

```php
$divs = $page->querySelectorAll('div');
```

### Evaluated functions must be created with `\PhpPupeeteer\Data\Js`

Functions evaluated in the context of the page must be written [with the `JsFunction` class](https://github.com/nesk/rialto/blob/master/docs/api.md#javascript-functions), the body of these functions must be written in JavaScript instead of PHP.

```php
use PhpPupeeteer\Data\Js;

$pageFunction = Js::createWithParameters(['element'])
    ->body("return element.textContent")
;
```

### Exceptions must be catched with `->tryCatch`

If an error occurs in Node, a `Node\FatalException` will be thrown and the process closed, you will have to create a new instance of `Puppeteer`.

To avoid that, you can ask Node to catch these errors by prepending your instruction with `->tryCatch`:

```php
use Nesk\Rialto\Exceptions\Node;

try {
    $page->tryCatch->goto('invalid_url');
} catch (Node\Exception $exception) {
    // Handle the exception...
}
```

Instead, a `Node\Exception` will be thrown, the Node process will stay alive and usable.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
