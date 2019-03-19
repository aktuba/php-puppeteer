<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

use PhpPupeeteer\Traits\{
	AliasesSelectionMethods,
	AliasesEvaluationMethods
};

/**
 * Class ElementHandle
 * @package PhpPupeeteer\Resources
 *
 * @method ElementHandle asElement()
 * @method array|null boundingBox()
 * @method array|null boxModel()
 * @method void click(array $options = [])
 * @method Frame|null contentFrame()
 * @method void dispose()
 * @method ExecutionContext executionContext()
 * @method void focus()
 * @method array getProperties()
 * @method JSHandle getProperty(string $name)
 * @method void hover()
 * @method bool isIntersectingViewport()
 * @method array jsonValue()
 * @method void press(string $key, array $options = [])
 * @method Buffer screenshot(array $options = [])
 * @method void tap()
 * @method string toString()
 * @method void type(string $type, array $options = [])
 * @method void uploadFile(...$paths)
 */
class ElementHandle extends JSHandle
{

    use AliasesSelectionMethods, AliasesEvaluationMethods;

}
