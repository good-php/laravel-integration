<?php

namespace Tests\Integration\Routing;

use Generator;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InjectionTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		Route::any('first', [StubController::class, 'first']);
		Route::any('second', [StubController::class, 'second']);
		Route::any('third', [StubController::class, 'third']);
	}

	/**
	 * @dataProvider injectsInputProvider
	 */
	public function testInjectsInput(string $endpoint, array $input, array $expectedResponse): void
	{
		$this->postJson($endpoint, $input)
			->assertOk()
			->assertJson($expectedResponse);
	}

	public function injectsInputProvider(): Generator
	{
		yield [
			'first',
			[
				'first_key' => 123,
				'secondKey' => '2020-01-01 00:00:00',
			],
			[
				'first'  => 123,
				'second' => '2020-01-01',
			],
		];

		yield [
			'first',
			[
				'first_key' => null,
				'secondKey' => '2020-01-01',
			],
			[
				'first'  => null,
				'second' => '2020-01-01',
			],
		];

		yield [
			'first',
			[
				'secondKey' => '2020-01-01',
			],
			[
				'first'  => null,
				'second' => '2020-01-01',
			],
		];

		yield [
			'second',
			[],
			[],
		];

		yield [
			'second',
			[
				[
					'first_key' => null,
					'secondKey' => null,
				],
			],
			[
				[
					'first'  => null,
					'second' => null,
				],
			],
		];

		yield [
			'second',
			[
				[
					'first_key' => 123,
					'secondKey' => '2020-01-01',
				],
			],
			[
				[
					'first'  => 123,
					'second' => '2020-01-01',
				],
			],
		];

		yield [
			'second',
			[
				[
				],
			],
			[
				[
					'first'  => null,
					'second' => null,
				],
			],
		];

		yield [
			'third',
			[],
			[
				'first_key' => 123,
				'secondKey' => '2022-01-01T00:00:00.000+00:00',
			],
		];
	}

	/**
	 * @dataProvider failsWithValidationProvider
	 */
	public function testFailsWithValidation(string $endpoint, array $input, array $expectedErrors): void
	{
		$this->postJson($endpoint, $input)
			->assertUnprocessable()
			->assertJsonValidationErrors($expectedErrors);
	}

	public function failsWithValidationProvider(): Generator
	{
		yield [
			'first',
			[],
			[
				'secondKey' => 'Missing value',
			],
		];

		yield [
			'first',
			[
				'first_key' => 'string',
			],
			[
				'first_key' => "Expected value of type 'int', but got 'string'",
			],
		];

		yield [
			'first',
			[
				'secondKey' => 'not a date',
			],
			[
				'secondKey' => 'Failed to parse time string (not a date) at position 0 (n): The timezone could not be found in the database',
			],
		];

		yield [
			'second',
			[
				[
					'first_key' => 'str',
					'secondKey' => [
						'item',
					],
				],
			],
			[
				'0.first_key' => "Expected value of type 'int', but got 'string'",
				'0.secondKey' => "Expected value of type 'string', but got 'array'",
			],
		];
	}
}
