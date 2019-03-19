<?php declare(strict_types=1);

namespace PhpPupeeteer;

use PhpPupeeteer\Resources\Buffer;
use Nesk\Rialto\Interfaces\ShouldHandleProcessDelegation;

class ProcessDelegate implements ShouldHandleProcessDelegation
{

	private const CLASSPATH = [
		"PhpPupeeteer\\Resources\\",
		"Nesk\\Puphpeteer\\Resources\\"
	];

	public function defaultResource(): string
	{
		return Buffer::class;
	}

	/**
	 * Return the fully qualified name of a resource based on the original class name.
	 */
	public function resourceFromOriginalClassName(string $className): ?string{
		$result = null;

		foreach (static::CLASSPATH as $path) {
			$class = $path.$className;
			if (\class_exists($class)) {
				$result = $class;
				break;
			}
		}

		return $result;
	}
}
