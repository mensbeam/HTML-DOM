<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\InnerNode;


class Factory {
    public static function createFromProtectedConstructor(string $className, ...$arguments): mixed {
        $reflector = new \ReflectionClass($className);
        $result = $reflector->newInstanceWithoutConstructor();
        $constructor = new \ReflectionMethod($result, '__construct');
        $constructor->setAccessible(true);
        $constructor->invoke($result, ...$arguments);
        return $result;
    }

    public static function getProtectedProperty(mixed $instance, string $propertyName): mixed {
        $reflector = new \ReflectionClass($instance::class);
        $property = new \ReflectionProperty($instance, $propertyName);
        $property->setAccessible(true);
        return $property->getValue($instance);
    }
}