<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\Inner;

/**
 * Class which uses reflection to gain access to protected constructors and
 * properties. We are well aware of the negative conotations around using
 * Reflection. The specification itself is written in a manner that expects
 * C++-style friend classes which PHP lacks, so it first comes out of necessity.
 * Any other use of it comes down to speed optimizations (such as gaining access
 * to Node::innerNode).
 */
class Reflection {
    public static function createFromProtectedConstructor(string $className, ...$arguments): mixed {
        $reflector = new \ReflectionClass($className);
        $result = $reflector->newInstanceWithoutConstructor();
        $constructor = new \ReflectionMethod($result, '__construct');
        $constructor->setAccessible(true);
        $constructor->invoke($result, ...$arguments);
        return $result;
    }

    public static function getProtectedProperty(mixed $instance, string $propertyName): mixed {
        $property = new \ReflectionProperty($instance, $propertyName);
        $property->setAccessible(true);
        return $property->getValue($instance);
    }

    public static function setProtectedProperties(mixed $instance, array $properties): void {
        foreach ($properties as $propertyName => $value) {
            $property = new \ReflectionProperty($instance, $propertyName);
            $property->setAccessible(true);
            $property->setValue($instance, $value);
        }
    }
}