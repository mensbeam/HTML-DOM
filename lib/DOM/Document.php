<?php
/** @license MIT
 * Copyright 2017 , Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details */

declare(strict_types=1);
namespace MensBeam\HTML;

class Document extends \DOMDocument {
    use C14N, EscapeString, Serialize, Walk;

    // Quirks mode constants
    public const NO_QUIRKS_MODE = 0;
    public const QUIRKS_MODE = 1;
    public const LIMITED_QUIRKS_MODE = 2;

    public $documentEncoding = null;
    public $mangledAttributes = false;
    public $mangledElements = false;
    public $quirksMode = self::NO_QUIRKS_MODE;

    public function __construct() {
        parent::__construct();

        $this->registerNodeClass('DOMComment', '\MensBeam\HTML\Comment');
        $this->registerNodeClass('DOMDocumentFragment', '\MensBeam\HTML\DocumentFragment');
        $this->registerNodeClass('DOMElement', '\MensBeam\HTML\Element');
        $this->registerNodeClass('DOMProcessingInstruction', '\MensBeam\HTML\ProcessingInstruction');
        $this->registerNodeClass('DOMText', '\MensBeam\HTML\Text');
    }

    public function appendChild($node) {
        # If node is not a DocumentFragment, DocumentType, Element, Text,
        # ProcessingInstruction, or Comment node then throw a "HierarchyRequestError"
        # DOMException.
        if (!$node instanceof DocumentFragment && !$node instanceof \DOMDocumentType && !$node instanceof Element &&!$node instanceof Text && !$node instanceof ProcessingInstruction && !$node instanceof Comment) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        $result = parent::appendChild($node);
        if ($result !== false && $result instanceof TemplateElement) {
            ElementRegistry::set($result);
        }
        return $result;
    }

    public function createAttribute($name) {
        return $this->createAttributeNS(null, $name);
    }

    public function createAttributeNS($namespaceURI, $qualifiedName) {
        // Normalize the attribute name and namespace URI per modern DOM specifications.
        if ($namespaceURI !== null) {
            $namespaceURI = trim($namespaceURI);
        }
        $qualifiedName = trim($qualifiedName);

        try {
            return parent::createAttributeNS($namespaceURI, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            $this->mangledAttributes = true;
            $qualifiedName = $this->coerceName($qualifiedName);
            return $this->createAttributeNS($namespaceURI, $qualifiedName);
        }
    }

    public function createElement($name, $value = "") {
        return $this->createElementNS(null, $name, $value);
    }

    public function createElementNS($namespaceURI, $qualifiedName, $value = "") {
        // Normalize the element name and namespace URI per modern DOM specifications.
        if ($namespaceURI !== null) {
            $namespaceURI = trim($namespaceURI);
            $namespaceURI = ($namespaceURI === Parser::HTML_NAMESPACE) ? null : $namespaceURI;
        }
        $qualifiedName = ($namespaceURI === null) ? strtolower(trim($qualifiedName)) : trim($qualifiedName);

        try {
            if ($qualifiedName !== 'template' || $namespaceURI !== null) {
                $e = parent::createElementNS($namespaceURI, $qualifiedName, $value);
            } else {
                $e = new TemplateElement($this, $qualifiedName, $value);
                // Template elements need to have a reference kept in userland
                ElementRegistry::set($e);
                $e->content = $this->createDocumentFragment();
            }

            return $e;
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            $this->mangledElements = true;
            if ($namespaceURI !== null) {
                $qualifiedName = implode(":", array_map([$this, "coerceName"], explode(":", $qualifiedName, 2)));
            } else {
                $qualifiedName = $this->coerceName($qualifiedName);
            }
            return parent::createElementNS($namespaceURI, $qualifiedName, $value);
        }
    }

    public function createEntityReference($name): bool {
        return false;
    }

    public function insertBefore($node, $child = null) {
        # If node is not a DocumentFragment, DocumentType, Element, Text,
        # ProcessingInstruction, or Comment node then throw a "HierarchyRequestError"
        # DOMException.
        if (!$node instanceof DocumentFragment && !$node instanceof \DOMDocumentType && !$node instanceof Element &&!$node instanceof Text && !$node instanceof ProcessingInstruction && !$node instanceof Comment) {
            throw new DOMException(DOMException::HIERARCHY_REQUEST_ERROR);
        }

        $result = parent::insertBefore($node, $child);
        if ($result !== false) {
            if ($result instanceof TemplateElement) {
                ElementRegistry::set($result);
            }
            if ($child instanceof TemplateElement) {
                ElementRegistry::delete($child);
            }
        }
        return $result;
    }

    public function load($filename, $options = null, ?string $encodingOrContentType = null): bool {
        $data = Parser::fetchFile($filename, $encodingOrContentType);
        if (!$data) {
            return false;
        }
        [$data, $encodingOrContentType] = $data;
        Parser::parse($data, $this, $encodingOrContentType, null, (string)$filename);
        return true;
    }

    public function loadHTML($source, $options = null, ?string $encodingOrContentType = null): bool {
        assert(is_string($source), new DOMException(DOMException::STRING_EXPECTED, 'source', gettype($source)));
        Parser::parse($source, $this, $encodingOrContentType);
        return true;
    }

    public function loadHTMLFile($filename, $options = null, ?string $encodingOrContentType = null): bool {
        return $this->load($filename, $options, $encodingOrContentType);
    }

    public function loadXML($source, $options = null): bool {
        return false;
    }

    public function removeChild($child) {
        $result = parent::removeChild($child);
        if ($result !== false && $result instanceof TemplateElement) {
            ElementRegistry::delete($child);
        }
        return $result;
    }

    public function replaceChild($node, $child) {
        $result = parent::replaceChild($node, $child);
        if ($result !== false) {
            if ($result instanceof TemplateElement) {
                ElementRegistry::set($child);
            }
            if ($child instanceof TemplateElement) {
                ElementRegistry::delete($child);
            }
        }
        return $result;
    }

    public function save($filename, $options = null) {
        return file_put_contents($filename, $this->serialize());
    }

    public function saveHTML(\DOMNode $node = null): string {
        if ($node === null) {
            $node = $this;
        } elseif (!$node->ownerDocument->isSameNode($this)) {
            throw new DOMException(DOMException::WRONG_DOCUMENT);
        }

        return $node->serialize();
    }

    public function saveHTMLFile($filename): int {
        return $this->save($filename);
    }

    public function saveXML(?\DOMNode $node = null, $options = null): bool {
        return false;
    }

    public function validate(): bool {
        return true;
    }

    public function xinclude($options = null): bool {
        return false;
    }

    public function __toString() {
        return $this->serialize();
    }
}
