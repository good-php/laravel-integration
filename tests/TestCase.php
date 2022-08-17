<?php

namespace Tests;

use GoodPhp\LaravelIntegration\GoodPhpServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	/**
	 * @inheritDoc
	 */
	protected function getPackageProviders($app): array
	{
		return [
			GoodPhpServiceProvider::class,
		];
	}
}
