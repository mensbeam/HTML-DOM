<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\InnerNode\DocumentFragment as InnerDocumentFragment;


class DocumentFragment extends Node {
    use ParentNode;

    protected ?\WeakReference $host = null;


    protected function __construct(InnerDocumentFragment $fragment) {
        parent::__construct($fragment);
    }
}
