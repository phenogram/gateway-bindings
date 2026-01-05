<?php

namespace Phenogram\GatewayBindings\Factories;

use Faker\Generator;
use Phenogram\GatewayBindings\Factory;
use Phenogram\GatewayBindings\FactoryInterface;

abstract class AbstractFactory
{
	private static Generator $faker;
	private static FactoryInterface $factory;


	protected static function fake(): Generator
	{
		if (!isset(static::$faker)) {
		    static::$faker = \Faker\Factory::create();
		}
		return static::$faker;
	}


	protected static function factory(): FactoryInterface
	{
		if (!isset(static::$factory)) {
		    static::$factory = new Factory();
		}
		return static::$factory;
	}


	public static function setFactory(FactoryInterface $factory): void
	{
		if (isset(static::$factory)) {
		    throw new \RuntimeException('Factory already set');
		}

		static::$factory = $factory;
	}
}
