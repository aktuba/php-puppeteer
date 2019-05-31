<?php

namespace PhpPupeeteer\Traits;

use PhpPupeeteer\Data\Js;
use PhpPupeeteer\Resources\JSHandle;

trait AliasesEvaluationMethods
{
	/**
	 * @param bool|int|float|string|array|null|JSHandle ...$args
	 * @return bool|int|float|string|array|null
	 */
	public function querySelectorEval(string $selector, Js $pageFunction, ...$args)
	{
		return $this->__call('$eval', array_merge([$selector, $pageFunction], $args));
	}

	public function querySelectorAllEval(string $selector, Js $pageFunction, ...$args)
	{
		return $this->__call('$$eval', array_merge([$selector, $pageFunction], $args));
	}
}
