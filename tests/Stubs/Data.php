<?php

namespace Tests\Stubs;

use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\SerializedName;

/**
 * @template T
 */
class Data
{
	/**
	 * @param T $second
	 */
	public function __construct(
		public readonly ?int $firstKey,
		#[SerializedName('secondKey')]
		public readonly mixed $second,
	) {
	}
}
