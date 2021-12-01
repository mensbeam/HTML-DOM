<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\Reflection;


class HTMLTemplateElement extends HTMLElement {
    protected DocumentFragment $_content;

    // Templates can contain content in both light and shadow, so its content
    // fragment must be stored here instead of in PHP's main inner document tree.
    protected function __get_content(): DocumentFragment {
        return $this->_content;
    }


    protected function __construct(\DOMElement $element) {
        parent::__construct($element);

        $this->_content = $this->ownerDocument->createDocumentFragment();
        Reflection::setProtectedProperties($this->_content, [ 'host' => \WeakReference::create($this) ]);
    }
}