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

    protected function __get_name(): string {
        // Return an empty string if a space because this implementation gets around a
        // PHP DOM limitation by substituting an empty string for a space.
        $name = $this->innerNode->name;
        return ($name !== ' ') ? $this->innerNode->name : '';
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