<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class Worker
 * @package PhpPupeeteer\Resources
 *
 * @method ExecutionContext executionContext()
 * @method string url()
 * @method mixed evaluate($pageFunction, ...$args)
 * @method JSHandle evaluateHandle($pageFunction, ...$args)
 */
class Worker extends Buffer {}
