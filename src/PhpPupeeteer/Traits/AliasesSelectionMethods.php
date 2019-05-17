<?php

namespace PhpPupeeteer\Traits;

use PhpPupeeteer\Resources\ElementHandle;

trait AliasesSelectionMethods
{
	public function querySelector(string $selector): ?ElementHandle
	{
		return $this->__call('$', [$selector]);
	}

	public function querySelectorAll(string $selector): array
	{
		return $this->__call('$$', [$selector]);
	}

	public function querySelectorXPath(string $expression): array
	{
		return $this->__call('$x', [$expression]);
	}
}
