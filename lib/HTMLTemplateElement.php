<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

/** Class specifically for template elements to handle its content property. */
class HTMLTemplateElement extends Element {
    public $content = null;

    public function __construct(Document $ownerDocument, string $qualifiedName, ?string $namespace = null) {
        parent::__construct($qualifiedName, null, $namespace ?? '');

        // Elements that are created by their constructor in PHP aren't owned by any
        // document and are readonly until owned by one. Temporarily append to a
        // document fragment so the element will be owned by the supplied owner
        // document.
        $frag = $ownerDocument->createDocumentFragment();
        $frag->appendChild($this);
        $frag->removeChild($this);
        unset($frag);

        $content = $this->ownerDocument->createDocumentFragment();
        $content->host = $this;
        $this->content = $content;
    }


    public function cloneNode(bool $deep = false) {
        $copy = $this->ownerDocument->createElement('template');
        foreach ($this->attributes as $attr) {
            $copy->setAttributeNS($attr->namespaceURI, $attr->name, $attr->value);
        }

        if ($deep) {
            foreach ($this->content->childNodes as $child) {
                $copy->content->appendChild($child->cloneNode(true));
            }
        }

        return $copy;
    }
}
