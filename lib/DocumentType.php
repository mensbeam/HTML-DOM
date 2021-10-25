<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class DocumentType extends Node {
    use ChildNode;

    // We need to work around a PHP DOM bug where doctype nodes aren't associated
    // with a document until they're appended. _ownerDocument is set when the node
    // is created but ignored once the doctype is appended to a document
    protected ?\WeakReference $_ownerDocument = null;


    protected function __get_name(): string {
        // Return an empty string if a space is the name of the doctype. While the DOM
        // itself cannot create a doctype with an empty string as the name, the HTML
        // parser can. PHP's DOM cannot handle an empty string as the name, so a single
        // space (an invalid value) is used instead and coerced to an empty string.
        $name = $this->innerNode->name;
        return ($name !== ' ') ? $this->innerNode->name : '';
    }

    protected function __get_ownerDocument(): ?Document {
        return parent::__get_ownerDocument() ?? $this->_ownerDocument->get();
    }

    protected function __get_publicId(): string {
        return $this->innerNode->publicId;
    }

    protected function __get_systemId(): string {
        return $this->innerNode->systemId;
    }


    protected function __construct(\DOMDocumentType $doctype) {
        parent::__construct($doctype);
    }
}