<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait ToString {
    public function __toString(): string {
        $frag = $this->ownerDocument->createDocumentFragment();
        $frag->appendChild($this->cloneNode(true));
        return $this->ownerDocument->saveHTML($frag);
    }
}
