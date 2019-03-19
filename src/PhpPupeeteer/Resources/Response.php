<?php declare(strict_types=1);

namespace PhpPupeeteer\Resources;

/**
 * Class Response
 * @package PhpPupeeteer\Resources
 *
 * @method Buffer buffer()
 * @method Frame|null frame()
 * @method bool fromCache()
 * @method bool fromServiceWorker()
 * @method array headers()
 * @method array json()
 * @method bool ok()
 * @method array remoteAddress()
 * @method Request request()
 * @method SecurityDetails|null securityDetails()
 * @method int status()
 * @method string statusText()
 * @method string text()
 */
class Response extends Buffer {

	public function url(): string {
		return $this->frame()->evaluate(\PhpPupeeteer\Data\Js::createWithBody("
		    return document.location.href;
		"));
	}

}
