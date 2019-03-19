<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class Request
 * @package PhpPupeeteer\Resources
 *
 * @method void abort(string $errorCode)
 * @method void continue(array $overrides = [])
 * @method array|null failure()
 * @method Frame|null frame()
 * @method array headers()
 * @method bool isNavigationRequest()
 * @method string method()
 * @method string postData()
 * @method Request[] redirectChain()
 * @method string resourceType()
 * @method void respond(array $response)
 * @method Response|null response()
 * @method string url()
 */
class Request extends Buffer {}
