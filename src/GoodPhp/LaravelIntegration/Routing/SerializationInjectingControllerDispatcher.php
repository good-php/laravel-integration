<?php

namespace GoodPhp\LaravelIntegration\Routing;

use Exception;
use GoodPhp\Reflection\Reflector\Reflection\FunctionParameterReflection;
use GoodPhp\Reflection\Reflector\Reflection\MethodReflection;
use GoodPhp\Serialization\Serializer;
use GoodPhp\Serialization\TypeAdapter\Exception\CollectionItemMappingException;
use GoodPhp\Serialization\TypeAdapter\Exception\MultipleMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\ClassProperties\PropertyMappingException;
use GoodPhp\Serialization\TypeAdapter\Primitive\PrimitiveTypeAdapter;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use ReflectionParameter;

class SerializationInjectingControllerDispatcher extends ControllerDispatcher
{
	public function __construct(
		Container $container,
		private readonly Serializer $serializer,
		private readonly \GoodPhp\Reflection\Reflector\Reflector $reflector,
	) {
		parent::__construct($container);
	}

	/**
	 * @inheritDoc
	 */
	public function dispatch(Route $route, $controller, $method)
	{
		$result = parent::dispatch($route, $controller, $method);

		$reflection = new ReflectionMethod($controller, $method);

		if ($reflection->getAttributes(Output::class)) {
			$type = $this->reflector
				->forType($reflection->getDeclaringClass()->getName())
				->methods()
				->first(fn (MethodReflection $method) => $method->name() === $reflection->getName())
				->returnType();

			return $this->serializer
				->adapter(PrimitiveTypeAdapter::class, $type)
				->serialize($result);
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function transformDependency(ReflectionParameter $parameter, $parameters, $skippableValue)
	{
		if ($parameter->getAttributes(Input::class)) {
			$type = $this->reflector
				->forType($parameter->getDeclaringClass()->getName())
				->methods()
				->first(fn (MethodReflection $method) => $method->name() === $parameter->getDeclaringFunction()->getShortName())
				->parameters()
				->first(fn (FunctionParameterReflection $goodParameter) => $parameter->name === $goodParameter->name())
				->type();

			$data = $this->container->make(Request::class)->input();

			try {
				return $this->serializer
					->adapter(PrimitiveTypeAdapter::class, $type)
					->deserialize($data);
			} catch (PropertyMappingException|CollectionItemMappingException|MultipleMappingException $e) {
				throw ValidationException::withMessages(Arr::dot($this->extractErrors($e)));
			}
		}

		return parent::transformDependency($parameter, $parameters, $skippableValue);
	}

	private function extractErrors(Exception $e): array|string
	{
		if ($e instanceof PropertyMappingException) {
			return [
				$e->path => $this->extractErrors($e->getPrevious()),
			];
		}

		if ($e instanceof CollectionItemMappingException) {
			return [
				$e->key => $this->extractErrors($e->getPrevious()),
			];
		}

		if ($e instanceof MultipleMappingException) {
			return collect($e->exceptions)->mapWithKeys(function (PropertyMappingException|CollectionItemMappingException $e) {
				return [
					$e instanceof PropertyMappingException ? $e->path : $e->key => $this->extractErrors($e->getPrevious()),
				];
			})->all();
		}

		return $e->getMessage();
	}
}
