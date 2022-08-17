<?php

namespace GoodPhp\LaravelIntegration\Routing;

use Attribute;

/**
 * Denotes a controller parameter which the request body and query parameters should be deserialized into.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Input
{
}
