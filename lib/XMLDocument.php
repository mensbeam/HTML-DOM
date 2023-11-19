<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\Parser\Charset;


class XMLDocument extends Document {
    protected string $_contentType = 'application/xml';


    public function load(string $source = null, ?string $charset = null): void {
        if ($this->hasChildNodes()) {
            throw new NoModificationAllowedError();
        }

        $this->_innerNode->encoding = Charset::fromCharset((string)$charset) ?? 'UTF-8';
        $this->_innerNode->loadXML($source);
    }
}