<?php

namespace Tests\Integration\Routing;

use DateTime;
use GoodPhp\LaravelIntegration\Routing\Input;
use GoodPhp\LaravelIntegration\Routing\Output;
use Illuminate\Http\Request;
use Tests\Stubs\Data;

class StubController
{
	/**
	 * @param Data<DateTime> $body
	 */
	public function first(
		Request $request,
		#[Input] Data $body,
	): array {
		return [
			'first'  => $body->firstKey,
			'second' => $body->second->format('Y-m-d'),
		];
	}

	/**
	 * @param array<Data<DateTime|null>> $body
	 */
	public function second(
		Request $request,
		#[Input] array $body,
	): array {
		return array_map(fn (Data $data) => [
			'first'  => $data->firstKey,
			'second' => $data->second?->format('Y-m-d'),
		], $body);
	}

	/**
	 * @return Data<DateTime>
	 */
	#[Output]
	public function third(): Data
	{
		return new Data(
			123,
			new DateTime('2022-01-01 00:00:00'),
		);
	}
}
