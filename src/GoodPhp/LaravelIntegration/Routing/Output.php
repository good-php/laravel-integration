<?php

namespace GoodPhp\LaravelIntegration\Routing;

use Attribute;

/**
 * Denotes a controller method which response should be serialized into the specified return type.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Output
{
}
