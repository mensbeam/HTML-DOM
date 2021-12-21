<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class XPathEvaluator {
    use XPathEvaluatorBase;


    public function registerXPathFunctions(Document $document, string|array|null $restrict = null): void {
        $this->xpathRegisterPhpFunctions($document, $restrict);
    }
}