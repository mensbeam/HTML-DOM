<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Element as InnerElement,
    MensBeam\HTML\DOM\InnerNode\Factory;


class HTMLTemplateElement extends Element {
    protected DocumentFragment $_content;

    protected function __get_content(): DocumentFragment {
        return $this->_content;
    }

    protected function __construct(InnerElement $element) {
        parent::__construct($element);

        $this->_content = $this->ownerWrapperDocument->get()->createDocumentFragment();
        Factory::setProtectedProperty($this->_content, 'host', \WeakReference::create($this));
    }
}