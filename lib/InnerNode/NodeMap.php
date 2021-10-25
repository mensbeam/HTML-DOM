<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\InnerNode;
use MensBeam\HTML\DOM\Node as WrapperNode;


class NodeMap {
    protected $wrapperArray = [];
    protected $innerArray = [];


    public function delete(\DOMNode|WrapperNode $node): bool {
        $key = $this->key($node);
        if ($key === false) {
            return false;
        }

        unset($this->wrapperArray[$key]);
        unset($this->innerArray[$key]);
        $this->wrapperArray = array_values($this->wrapperArray);
        $this->innerArray = array_values($this->innerArray);

        return true;
    }

    public function get(\DOMNode|WrapperNode $node): \DOMNode|WrapperNode|null {
        $key = $this->key($node);
        if ($key === false) {
            return null;
        }

        return ($node instanceof WrapperNode) ? $this->innerArray[$key] : $this->wrapperArray[$key];
    }

    public function has(\DOMNode|WrapperNode $node): bool {
        return ($this->key($node) !== false);
    }

    public function set(WrapperNode $wrapper, \DOMNode $inner): bool {
        if (!$this->has($wrapper)) {
            $this->wrapperArray[] = $wrapper;
            $this->innerArray[] = $inner;
            return true;
        }

        return false;
    }


    protected function key(\DOMNode|WrapperNode $node): int|false {
        return array_search($node, ($node instanceof WrapperNode) ? $this->wrapperArray : $this->innerArray, true);
    }
}
