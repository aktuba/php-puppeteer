<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class ExecutionContext
 * @package PhpPupeeteer\Resources
 *
 * @method Frame|null frame()
 * @method JSHandle queryObjects(JSHandle $prototypeHandle)
 * @method mixed evaluate($pageFunction, ...$args)
 * @method JSHandle evaluateHandle($pageFunction, ...$args)
 */
class ExecutionContext extends Buffer {}
