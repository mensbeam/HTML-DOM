<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

// This is a set of elements which need to be kept in memory; it exists because
// of the peculiar way PHP works. Derived DOM classes (such as
// HTMLTemplateElement) won't remain as such in the DOM (meaning they will
// revert to being what is registered for elements in Document) unless at least
// one reference is kept for the element somewhere in userspace. This is that
// somewhere.
class ElementSet {
    protected static $_storage = [];


    public static function add(Element $element) {
        if (!self::has($element)) {
            self::$_storage[] = $element;
            return true;
        }

        return false;
    }

    public static function delete(Element $element) {
        foreach (self::$_storage as $k => $v) {
            if ($v->isSameNode($element)) {
                unset(self::$_storage[$k]);
                self::$_storage = array_values(self::$_storage);
                return true;
            }
        }

        return false;
    }

    public static function destroy(Document $document) {
        $changed = false;
        foreach (self::$_storage as $k => $v) {
            if ($v->ownerDocument->isSameNode($document)) {
                unset(self::$_storage[$k]);
                $changed = true;
            }
        }

        if ($changed) {
            self::$_storage = array_values(self::$_storage);
            return true;
        }

        return false;
    }

    public static function getIterator(): \Traversable {
        foreach (self::$_storage as $v) {
            yield $v;
        }
    }

    public static function has(Element $element) {
        foreach (self::$_storage as $v) {
            if ($v->isSameNode($element)) {
                return true;
            }
        }

        return false;
    }
}
