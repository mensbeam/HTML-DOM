<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


class XPathNSResolver {
    protected Node $nodeResolver;


    protected function __construct(Node $nodeResolver) {
        $this->nodeResolver = $nodeResolver;
    }


    public function lookupNamespaceURI(?string $prefix): ?string {
        return $this->nodeResolver->lookupNamespaceURI($prefix);
    }
}