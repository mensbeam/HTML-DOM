<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class Comment extends CharacterData {
    public function __construct(string $data = '') {
        $this->innerNode = new \DOMComment($data);
    }
}