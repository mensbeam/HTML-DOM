<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\Element as InnerElement;


class Element extends Node {
    protected function __construct(InnerElement $element) {
        parent::__construct($element);
    }
}