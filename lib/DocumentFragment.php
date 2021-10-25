<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class DocumentFragment extends Node {
    use ParentNode;

    protected ?\WeakReference $host = null;


    protected function __construct(\DOMDocumentFragment $fragment) {
        parent::__construct($fragment);
    }
}
