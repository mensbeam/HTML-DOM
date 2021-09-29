<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

/**
 * Getters and setters in PHP sucks. Instead of having getter and setter
 * function types for classes we instead have the __get and __set magic methods
 * to handle all properties. Not only are they unwieldy to use when you have
 * many properties they also become difficult to handle when inheriting where
 * traits are involved. This trait attempts to create hackish getter and setter
 * functions that can be extended by simple inheritance.
 */
trait MagicProperties {
    public function __get(string $name) {
        // If a getter method exists return it. Otherwise, trigger a property does not
        // exist fatal error.
        $methodName = $this->getMagicPropertyMethodName($name);
        if ($methodName === null) {
            throw new Exception(Exception::NONEXISTENT_PROPERTY, $name);
        }
        return call_user_func([ $this, $methodName ]);
    }

    public function __isset(string $name): bool {
        return ($this->getMagicPropertyMethodName($name) !== null);
    }

    public function __set(string $name, $value) {
        // If a setter method exists return that.
        $methodName = $this->getMagicPropertyMethodName($name, false);
        if ($methodName !== null) {
            call_user_func([ $this, $methodName ], $value);
            return;
        }

        // Otherwise, if a getter exists then trigger a readonly property fatal error.
        // Finally, if a getter doesn't exist trigger a property does not exist fatal
        // error.
        if ($this->getMagicPropertyMethodName($name) !== null) {
            throw new Exception(Exception::READONLY_PROPERTY, $name);
        } else {
            throw new Exception(Exception::NONEXISTENT_PROPERTY, $name);
        }
    }

    public function __unset(string $name) {
        $methodName = $this->getMagicPropertyMethodName($name, false);
        if ($methodName === null) {
            throw new Exception(Exception::READONLY_PROPERTY, $name);
        }

        call_user_func([ $this, $methodName ], null);
    }


    // Method_exists is case-insensitive because methods are case-insensitive in
    // PHP. Properties in PHP 8 are sensitive, so let's use reflection to check
    // against the actual name to get a case sensitive match like methods should be!
    private function getMagicPropertyMethodName(string $name, bool $get = true): ?string {
        static $protectedMethodsList = null;

        $methodName = "__" . (($get) ? 'get' : 'set') . "_{$name}";
        if (method_exists($this, $methodName)) {
            if ($protectedMethodsList === null) {
                $reflector = new \ReflectionClass($this);
                // Magic property methods are protected
                $protectedMethodsList = $reflector->getMethods(\ReflectionMethod::IS_PROTECTED);
            }

            foreach ($protectedMethodsList as $method) {
                if ($method->name === $methodName) {
                    return $methodName;
                }
            }
        }

        return null;
    }
}