<?php

namespace GoodPhp\LaravelIntegration;

use GoodPhp\LaravelIntegration\Routing\SerializationInjectingControllerDispatcher;
use GoodPhp\Reflection\Reflector\Reflector;
use GoodPhp\Reflection\ReflectorBuilder;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\SerializerBuilder;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\Naming\BuiltInNamingStrategy;
use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Contracts\ControllerDispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Provides good-php packages.
 */
class GoodPhpServiceProvider extends ServiceProvider
{
	/**
	 * @inheritDoc
	 */
	public function register(): void
	{
		$this->app->singleton(
			Reflector::class,
			fn (Application $app) => (new ReflectorBuilder())
				->withCache($app->bootstrapPath('cache/vendor-good-php-reflection'))
				->build()
		);

		$this->app->singleton(
			Serializer::class,
			fn (Container $container) => $container
				->make(SerializerBuilder::class)
				->namingStrategy(BuiltInNamingStrategy::SNAKE_CASE)
				->build()
		);

		$this->app->singleton(ControllerDispatcher::class, SerializationInjectingControllerDispatcher::class);
	}
}
