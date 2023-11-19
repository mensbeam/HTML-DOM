<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


/** @property \DOMProcessingInstruction $_innerNode */
class ProcessingInstruction extends CharacterData {
    protected function __get_target(): string {
        // Need to uncoerce string if necessary.
        $target = $this->_innerNode->target;
        return (!str_contains(needle: 'U', haystack: $target)) ? $target : $this->uncoerceName($target);
    }


    protected function __construct(\DOMProcessingInstruction $pi) {
        parent::__construct($pi);
    }
}