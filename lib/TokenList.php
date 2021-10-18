<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\Framework\MagicProperties,
    MensBeam\HTML\Parser\Data;


class TokenList implements \ArrayAccess, \Countable, \Iterator {
    use MagicProperties;


    protected string $localName;
    protected \WeakReference $element;

    protected int $_length = 0;
    protected int $position = 0;
    # A DOMTokenList object has an associated token set (a set), which is initially
    # empty.
    protected array $tokenSet = [];

    private const ASCII_WHITESPACE_REGEX = '/[\t\n\x0c\r ]+/';


    protected function __get_length(): int {
        return $this->_length;
    }

    protected function __get_value(): string {
        # The value attribute must return the result of running this’s serialize steps.
        return $this->__toString();
    }

    protected function __set_value(string $value) {
        # Setting the value attribute must set an attribute value for the associated
        # element using associated attribute’s local name and the given value.
        $element = $this->element->get();
        $element->setAttribute($this->localName, $value);
        // Also update the token set and the length.
        $this->tokenSet = $this->parseOrderedSet($value);
        $this->_length = count($this->tokenSet);
    }


    public function __construct(\DOMElement $element, string $attributeLocalName) {
        # A DOMTokenList object also has an associated element and an attribute’s local
        # name.

        # When a DOMTokenList object is created, then:
        #
        # 1. Let element be associated element.
        // Using a weak reference here to prevent a circular reference.
        $this->element = \WeakReference::create($element);
        // Store the element somewhere statically because PHP's garbage collection is
        // itself garbage. This seems to contradict using a WeakReference, and it does.
        // However, it simply doesn't work otherwise because PHP does reference counting
        // for garbage collection. Attempts are made elsewhere to garbage collect.
        ElementMap::add($element);
        # 2. Let localName be associated attribute’s local name.
        $this->localName = $attributeLocalName;
        # 3. Let value be the result of getting an attribute value given element and
        # localName.
        $value = $element->getAttribute($attributeLocalName);
        # 4. Run the attribute change steps for element, localName, value, value, and
        # null.
        $this->attributeChange($attributeLocalName, $value, $value);
    }

    public function add(...$tokens): void {
        # 1. For each token in tokens:
        foreach ($tokens as $token) {
            # 1. If token is the empty string, then throw a "SyntaxError" DOMException.
            if ($token === '') {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }

            # 2. If token contains any ASCII whitespace, then throw an
            # "InvalidCharacterError" DOMException.
            if (preg_match(Data::WHITESPACE_REGEX, $token)) {
                throw new DOMException(DOMException::INVALID_CHARACTER);
            }
        }

        # 2. For each token in tokens, append token to this’s token set.
        foreach ($tokens as $token) {
            if (!in_array($token, $this->tokenSet)) {
                // The spec does not say to trim, but browsers do.
                $this->tokenSet[] = trim($token);
                $this->_length++;
            }
        }

        # 3. Run the update steps.
        $this->update();
    }

    public function contains(string $token): bool {
        return (in_array($token, $this->tokenSet));
    }

    public function count(): int {
        return $this->_length;
    }

    public function current(): ?string {
        return $this->item($this->position);
    }

    public function item(int $index): ?string {
        # The item(index) method steps are:
        # 1. If index is equal to or greater than this’s token set’s size, then return null.
        if ($index >= $this->_length) {
            return null;
        }
        # 2. Return this’s token set[index].
        return $this->tokenSet[$index];
    }

    public function key(): int {
        return $this->position;
    }

    public function next(): void {
        ++$this->position;
    }

    public function rewind(): void {
        $this->position = 0;
    }

    public function offsetExists($offset): bool {
        return isset($this->tokenSet[$offset]);
    }

    public function offsetGet($offset): string {
        return $this->item($offset);
    }

    public function offsetSet($offset, $value): void {
        // Spec says nothing about setting values on DOMTokenList outside of add();
        // browsers silently fail here.
    }

    public function offsetUnset($offset): void {
        // Spec says nothing about unsetting values on DOMTokenList outside of remove();
        // browsers silently fail here.
    }

    public function remove(...$tokens): void {
        # 1. For each token in tokens:
        foreach ($tokens as $token) {
            # 1. If token is the empty string, then throw a "SyntaxError" DOMException.
            if ($token === '') {
                throw new DOMException(DOMException::SYNTAX_ERROR);
            }

            # 2. If token contains any ASCII whitespace, then throw an
            # "InvalidCharacterError" DOMException.
            if (preg_match(Data::WHITESPACE_REGEX, $token)) {
                throw new DOMException(DOMException::INVALID_CHARACTER);
            }
        }

        # For each token in tokens, remove token from this’s token set.
        $changed = false;
        foreach ($tokens as $token) {
            if ($key = array_search($token, $this->tokenSet, true)) {
                unset($this->tokenSet[$key]);
                $this->_length--;
                $changed = true;
            }
        }

        if ($changed) {
            $this->tokenSet = array_values($this->tokenSet);
        }

        # 3. Run the update steps.
        $this->update();
    }

    public function replace(string $token, string $newToken): bool {
        # 1. If either token or newToken is the empty string, then throw a "SyntaxError"
        # DOMException.
        if ($token === '' || $newToken === '') {
            throw new DOMException(DOMException::SYNTAX_ERROR);
        }

        # 2. If either token or newToken contains any ASCII whitespace, then throw an
        # "InvalidCharacterError" DOMException.
        if (preg_match(Data::WHITESPACE_REGEX, $token) || preg_match(Data::WHITESPACE_REGEX, $newToken)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        // The spec does not say to trim, but browsers do.
        $token = trim($token);
        $newToken = trim($newToken);

        # 3. If this’s token set does not contain token, then return false.
        if (!($key = array_search($token, $this->tokenSet))) {
            return false;
        }

        # 4. Replace token in this’s token set with newToken.
        $this->tokenSet[$key] = $newToken;

        # 5. Run the update steps.
        $this->update();

        # 6. Return true.
        return true;
    }

    public function supports(string $token): bool {
        # 1. Let result be the return value of validation steps called with token.
        # 2. Return result.
        #
        # A DOMTokenList object’s validation steps for a given token are:
        #
        # 1. If the associated attribute’s local name does not define supported tokens,
        # throw a TypeError.
        # 2. Let lowercase token be a copy of token, in ASCII lowercase.
        # 3. If lowercase token is present in supported tokens, return true.
        # 4. Return false.

        // This class is presently only used for Element::classList, and it supports any
        // valid class name as a token. So, there's nothing to do here at the moment.
        // Just return true.
        return true;
    }

    public function toggle(string $token, ?bool $force = null): bool {
        # 1. If token is the empty string, then throw a "SyntaxError" DOMException.
        if ($token === '') {
            throw new DOMException(DOMException::SYNTAX_ERROR);
        }

        # 2. If token contains any ASCII whitespace, then throw an
        # "InvalidCharacterError" DOMException.
        if (preg_match(Data::WHITESPACE_REGEX, $token)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 3. If this’s token set[token] exists, then:
        if (in_array($token, $this->tokenSet)) {
            # 1. If force is either not given or is false, then remove token from this’s
            # token set, run the update steps and return false.
            if (!$force) {
                $this->remove($token);
                return false;
            }

            # 2. Return true.
            return true;
        }
        # 4. Otherwise, if force not given or is true, append token to this’s token set,
        # run the update steps, and return true.
        elseif ($force === null || $force === true) {
            $this->add($token);
            return true;
        }

        # 5. Return false.
        return false;
    }

    public function valid() {
        return array_key_exists($this->position, $this->tokenSet);
    }


    protected function attributeChange(string $localName, ?string $oldValue = null, ?string $value = null, ?string $namespace = null) {
        # A DOMTokenList object has these attribute change steps for its associated
        # element:
        #
        # 1. If localName is associated attribute’s local name, namespace is null, and
        # value is null, then empty token set.
        if ($localName === $this->localName && $namespace === null && $value === null) {
            $this->tokenSet = [];
            $this->_length = 0;
        }
        # 2. Otherwise, if localName is associated attribute’s local name, namespace is
        # null, then set token set to value, parsed.
        elseif ($localName === $this->localName && $namespace === null) {
            $this->tokenSet = $this->parseOrderedSet($value);
            $this->_length = count($this->tokenSet);
        }
    }

    protected function parseOrderedSet(string $input) {
        if ($input === '') {
            return [];
        }

        # The ordered set parser takes a string input and then runs these steps:
        #
        # 1. Let inputTokens be the result of splitting input on ASCII whitespace.
        // There isn't a Set object in php, so make sure all the tokens are unique.
        $inputTokens = array_unique(preg_split(Data::WHITESPACE_REGEX, $input));

        # 2. Let tokens be a new ordered set.
        # 3. For each token in inputTokens, append token to tokens.
        # 4. Return tokens.
        // There isn't a Set object in php, so just return the uniqued input tokens.
        return $inputTokens;
    }

    protected function update() {
        # A DOMTokenList object’s update steps are:
        #
        # 1. If the associated element does not have an associated attribute and token
        # set is empty, then return.
        // Not sure what this is about. This class is constructed with a provided
        // associated element and attribute; there is no need to do this.

        # 2. Set an attribute value for the associated element using associated
        # attribute’s local name and the result of running the ordered set serializer
        # for token set.
        $element = $this->element->get();
        $class = $element->ownerDocument->createAttribute($this->localName);
        $class->value = $this->__toString();
        $element->setAttributeNode($class);
    }


    public function __toString(): string {
        # The ordered set serializer takes a set and returns the concatenation of set
        # using U+0020 SPACE.
        return implode(' ', $this->tokenSet);
    }
}
