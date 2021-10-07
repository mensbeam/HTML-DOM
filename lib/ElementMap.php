<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

// This is a map of elements which need to be kept in memory; it exists because
// of the peculiar way PHP works. Derived DOM classes (such as
// HTMLTemplateElement) won't remain as such in the DOM (meaning they will
// revert to being what is registered for elements in Document) unless at least
// one reference is kept for the element somewhere in userspace. This is that
// somewhere.
class ElementMap {
    // List of documents is necessary because when Document objects are destructed
    // it's not possible to check for a document's existence without triggering a
    // fatal error. Keeping document references around fixes that.
    protected static array $documents = [];
    protected static array $elements = [];


    public static function add(Element $element): bool {
        $document = $element->ownerDocument;
        $index = self::index($document);
        if ($index === -1) {
            self::$documents[] = $document;
            self::$elements[count(self::$documents) - 1][] = $element;
            return true;
        } else {
            foreach (self::$elements[$index] as $v) {
                if ($v->isSameNode($element)) {
                    return false;
                }
            }

            self::$elements[$index][] = $element;
            return true;
        }

        return false;
    }

    public static function delete(Element $element): bool {
        $document = $element->ownerDocument;
        $index = self::index($document);
        if ($index !== -1) {
            foreach (self::$elements[$index] as $k => $v) {
                if ($v->isSameNode($element)) {
                    unset(self::$elements[$index][$k]);
                    self::$elements[$index] = array_values(self::$elements[$index]);
                    return true;
                }
            }
        }

        return false;
    }

    public static function destroy(Document $document): bool {
        $index = self::index($document);
        if ($index !== -1) {
            unset(self::$documents[$index]);
            unset(self::$elements[$index]);
            self::$documents = array_values(self::$documents);
            self::$elements = array_values(self::$elements);
            return true;
        }

        return false;
    }

    public static function getIterator(Document $document): \Traversable {
        $index = self::index($document);
        foreach (self::$elements[$index] as $v) {
            yield $v;
        }
    }

    public static function has(Element $element): bool {
        $document = $element->ownerDocument;
        $index = self::index($document);
        if ($index !== -1) {
            foreach (self::$elements[$index] as $v) {
                if ($v->isSameNode($element)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected static function index(Document $document): int {
        foreach (self::$documents as $k => $d) {
            if ($d->isSameNode($document)) {
                return $k;
            }
        }

        return -1;
    }
}
