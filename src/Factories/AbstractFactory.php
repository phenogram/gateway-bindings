<?php

declare(strict_types=1);

namespace Phenogram\GatewayBindings\Factories;

use Phenogram\GatewayBindings\Factory;
use Phenogram\GatewayBindings\FactoryInterface;

abstract class AbstractFactory
{
    private static FactoryInterface $factory;

    protected static function factory(): FactoryInterface
    {
        if (!isset(self::$factory)) {
            self::$factory = new Factory();
        }

        return self::$factory;
    }

    public static function setFactory(FactoryInterface $factory): void
    {
        if (isset(self::$factory)) {
            throw new \RuntimeException('Factory already set');
        }

        self::$factory = $factory;
    }
}
