<?php declare(strict_types=1);

namespace PhpPupeeteer\Data;

use Nesk\Rialto\Data\JsFunction;

/**
 * Class Js
 * @package PhpPupeeteer\Data
 *
 * @method static Js createWithAsync()
 * @method static Js createWithParameters(array $parameters)
 * @method static Js createWithBody(string $jsCode)
 * @method static Js createWithScope(array $scope)
 * @method Js parameters(array $parameters)
 * @method Js body(string $jsCode)
 * @method Js scope(array $scope)
 * @method Js async(bool $async = true)
 * @method array jsonSerialize()
 */
class Js extends JsFunction {}
