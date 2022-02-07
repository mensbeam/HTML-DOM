<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\Inner\{
    Document as InnerDocument,
    NodeCache,
    Reflection
};
use MensBeam\HTML\Parser;
use MensBeam\HTML\Parser\{
    Charset,
    Data,
    Config as ParserConfig,
};


class Document extends Node implements \ArrayAccess {
    use DocumentOrElement, NonElementParentNode, ParentNode, XPathEvaluatorBase;


    protected static ?NodeCache $cache = null;
    protected string $_characterSet = 'UTF-8';
    protected string $_compatMode = 'CSS1Compat';
    protected string $_contentType = 'text/html';
    protected bool $designModeEnabled = false;
    protected DOMImplementation $_implementation;
    protected string $_URL = 'about:blank';

    protected function __get_body(): ?Element {
        $documentElement = $this->_innerNode->documentElement;
        if ($documentElement === null) {
            return null;
        }

        # The body element of a document is the first of the html element's children
        # that is either a body element or a frameset element, or null if there is no
        # such element.
        $n = $documentElement->firstChild;
        if ($n !== null) {
            do {
                if ($n instanceof \DOMElement && $n->namespaceURI === null && ($n->nodeName === 'body' || $n->nodeName === 'frameset')) {
                    return $n->ownerDocument->getWrapperNode($n);
                }
            } while ($n = $n->nextSibling);
        }

        return null;
    }

    protected function __get_charset(): string {
        return $this->_characterSet;
    }

    protected function __get_characterSet(): string {
        return $this->_characterSet;
    }

    protected function __get_compatMode(): string {
        return $this->_compatMode;
    }

    protected function __get_contentType(): string {
        return $this->_contentType;
    }

    protected function __get_designMode(): string {
        # The designMode getter steps are to return "on" if this's design mode enabled
        # is true; otherwise "off".
        return ($this->designModeEnabled) ? 'on' : 'off';
    }

    protected function __set_designMode(string $value): void {
        # The designMode setter steps are:

        # 1. Let value be the given value, converted to ASCII lowercase.
        $value = strtolower($value);

        # 2. If value is "on" and this's design mode enabled is false, then:
        if ($value === 'on' && !$this->designModeEnabled) {
            # 1. Set this's design mode enabled to true.
            $this->designModeEnabled = true;

            # 2. Reset this's active range's start and end boundary points to be at the start of this.
            // Ranges aren't implemented.

            # 3. Run the focusing steps for this's document element, if non-null.
            // There's nothing to do here; there's no chance for a focusing element.
        }
        # 3. If value is "off", then set this's design mode enabled to false.
        elseif ($value === 'off') {
            $this->designModeEnabled = false;
        }
    }

    protected function __get_dir(): string {
        # The dir IDL attribute on Document objects must reflect the dir content attribute
        # of the html element, if any, limited to only known values. If there is no such
        # element, then the attribute must return the empty string and do nothing on
        # setting.

        # If a reflecting IDL attribute is a DOMString attribute whose content attribute
        # is an enumerated attribute, and the IDL attribute is limited to only known
        # values, then, on getting, the IDL attribute must return the keyword value
        # associated with the state the attribute is in, if any, or the empty string if
        # the attribute is in a state that has no associated keyword value or if the
        # attribute is not in a defined state (e.g. the attribute is missing and there
        # is no missing value default). If there are multiple keyword values for the
        # state, then return the conforming one. If there are multiple conforming
        # keyword values, then one will be designated the canonical keyword; choose that
        # one.

        $documentElement = $this->documentElement;
        return ($documentElement !== null && $documentElement->namespaceURI === Node::HTML_NAMESPACE && $documentElement->tagName === 'html') ? $documentElement->dir : '';
    }

    protected function __set_dir(string $value): void {
        # The dir IDL attribute on Document objects must reflect the dir content attribute
        # of the html element, if any, limited to only known values. If there is no such
        # element, then the attribute must return the empty string and do nothing on
        # setting.

        # On setting, the content attribute must be set to the specified new value.
        $documentElement = $this->documentElement;
        if ($documentElement !== null && $documentElement->namespaceURI === Node::HTML_NAMESPACE && $documentElement->tagName === 'html') {
            $documentElement->dir = $value;
        }
    }

    protected function __get_doctype(): ?DocumentType {
        // PHP's DOM does this correctly already.
        $doctype = $this->_innerNode->doctype;
        return ($doctype !== null) ? $this->_innerNode->getWrapperNode($doctype) : null;
    }

    protected function __get_documentElement(): ?Element {
        // PHP's DOM does this correctly already.
        $documentElement = $this->_innerNode->documentElement;
        return ($documentElement !== null) ? $this->_innerNode->getWrapperNode($documentElement) : null;
    }

    protected function __get_documentURI(): string {
        return $this->_URL;
    }

    protected function __get_embeds(): HTMLCollection {
        # The embeds attribute must return an HTMLCollection rooted at the Document
        # node, whose filter matches only embed elements.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $this->_innerNode->xpath->query(".//embed[namespace-uri()='$namespace']"));
    }

    protected function __get_forms(): HTMLCollection {
        # The forms attribute must return an HTMLCollection rooted at the Document node,
        # whose filter matches only form elements.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $this->_innerNode->xpath->query(".//form[namespace-uri()='$namespace']"));
    }

    protected function __get_head(): ?Element {
        # The head element of a document is the first head element that is a child of
        # the html element, if there is one, or null otherwise.
        # The head attribute, on getting, must return the head element of the document
        # (a head element or null).
        $documentElement = $this->_innerNode->documentElement;
        if ($documentElement !== null) {
            $children = $documentElement->childNodes;
            foreach ($children as $child) {
                if ($child instanceof \DOMElement && $child->namespaceURI === null && $child->tagName === 'head') {
                    return $this->_innerNode->getWrapperNode($child);
                }
            }
        }

        return null;
    }

    protected function __get_images(): HTMLCollection {
        # The images attribute must return an HTMLCollection rooted at the Document
        # node, whose filter matches only img elements.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $this->_innerNode->xpath->query(".//img[namespace-uri()='$namespace']"));
    }

    protected function __get_implementation(): DOMImplementation {
        return $this->_implementation;
    }

    protected function __get_inputEncoding(): string {
        return $this->_characterSet;
    }

    protected function __get_links(): HTMLCollection {
        # The links attribute must return an HTMLCollection rooted at the Document node,
        # whose filter matches only a elements with href attributes and area elements
        # with href attributes.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $this->_innerNode->xpath->query(".//*[namespace-uri()='$namespace' and @href][name()='a' or name()='area']"));
    }

    protected function __get_plugins(): HTMLCollection {
        # The plugins attribute must return the same object as that returned by the
        # embeds attribute.
        return $this->__get_embeds();
    }

    protected function __get_scripts(): HTMLCollection {
        # The scripts attribute must return an HTMLCollection rooted at the Document node, whose filter matches only script elements.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $this->_innerNode->xpath->query(".//script[namespace-uri()='$namespace']"));
    }

    protected function __get_title(): string {
        # The title attribute must, on getting, run the following algorithm:
        # 1. If the document element is an SVG svg element, then let value be the child text
        #    content of the first SVG title element that is a child of the document element.
        $value = '';
        $documentElement = $this->_innerNode->documentElement;
        if ($documentElement === null) {
            return '';
        }

        if ($documentElement->namespaceURI === Node::SVG_NAMESPACE && $documentElement->tagName === 'svg') {
            $children = $documentElement->childNodes;
            foreach ($children as $child) {
                if ($child instanceof \DOMElement && $child->namespaceURI === Node::SVG_NAMESPACE && $child->tagName === 'title') {
                    $value = $child->textContent ?? '';
                    break;
                }
            }
        }
        # 2. Otherwise, let value be the child text content of the title element, or the
        #    empty string if the title element is null.
        else {
            # The title element of a document is the first title element in the document (in
            # tree order), if there is one, or null otherwise.
            $title = $this->_innerNode->getElementsByTagName('title');
            if ($title->length > 0) {
                $value = $title->item(0)->textContent ?? '';
            }
        }

        # 3. Strip and collapse ASCII whitespace in value.
        # 4. Return value.
        return ($value !== '') ? trim(preg_replace(Data::WHITESPACE_REGEX, ' ', $value), Data::WHITESPACE) : '';
    }

    protected function __set_title(string $value): void {
        # On setting, the steps corresponding to the first matching condition in the following list must be run:
        #
        # If the document element is an SVG svg element
        $documentElement = $this->_innerNode->documentElement;
        if ($documentElement === null) {
            return;
        }

        # If the document element is an SVG svg element
        if ($documentElement->namespaceURI === Node::SVG_NAMESPACE && $documentElement->tagName === 'svg') {
            # 1. If there is an SVG title element that is a child of the document element,
            # let element be the first such element.
            $element = null;
            $children = $documentElement->childNodes;
            foreach ($children as $child) {
                if ($child instanceof \DOMElement && $child->namespaceURI === Node::SVG_NAMESPACE && $child->tagName === 'title') {
                    $element = $child;
                    break;
                }
            }
            # 2. Otherwise:
            if ($element === null) {
                # 1. Let element be the result of creating an element given the document element's
                #    node document, title, and the SVG namespace.
                $element = $this->_innerNode->createElementNS(Node::SVG_NAMESPACE, 'title');

                # 2. Insert element as the first child of the document element.
                $this->_innerNode->documentElement->appendChild($element);
            }

            # 3. String replace all with the given value within element.
            // This is basically what textContent will do for us...
            $element->textContent = $value;
        }
        # If the document element is in the HTML namespace
        elseif ($this->documentElement->namespaceURI === Node::HTML_NAMESPACE) {
            # 1. If the title element is null and the head element is null, then return.

            # The title element of a document is the first title element in the document (in
            # tree order), if there is one, or null otherwise.
            $title = null;
            $element = null;
            $titles = $this->_innerNode->getElementsByTagName('title');
            if ($titles->length > 0) {
                $title = $titles->item(0);
            }

            # The head element of a document is the first head element that is a child of
            # the html element, if there is one, or null otherwise.
            $head = null;
            $children = $documentElement->childNodes;
            foreach ($children as $child) {
                if ($child instanceof \DOMElement && $child->namespaceURI === null && $child->tagName === 'head') {
                    $head = $child;
                    break;
                }
            }

            if ($title === null && $head === null) {
                return;
            }

            # 2. If the title element is non-null, let element be the title element.
            if ($title !== null) {
                $element = $title;
            }
            # 3. Otherwise:
            else {
                # 1. Let element be the result of creating an element given the document
                #   element's node document, title, and the HTML namespace.
                $element = $this->_innerNode->createElementNS(Node::SVG_NAMESPACE, 'title');

                # 2. Append element to the head element.
                $head->appendChild($element);
            }

            # 4. String replace all with the given value within element.
            // This is basically what textContent will do for us...
            if ($element !== null) {
                $element->textContent = $value;
            }
        }

        # Otherwise
        # Do nothing.
    }

    protected function __get_URL(): string {
        return $this->_URL;
    }


    public function __construct(?string $source = null, ?string $charset = null) {
        parent::__construct(new InnerDocument($this));
        $this->_implementation = new DOMImplementation($this);

        if ($source !== null) {
            $this->load($source, $charset);
        } elseif ($charset !== 'UTF-8') {
            $this->_characterSet = Charset::fromCharset((string)$charset) ?? 'UTF-8';
        }

        // This cache is used to prevent "must not be accessed before initialization"
        // errors because of PHP's garbage... garbage collection.
        if (self::$cache === null) {
            // Pcov for some reason doesn't mark this line as being covered when it clearly
            // is...
            self::$cache = new NodeCache(); //@codeCoverageIgnore
        }

        self::$cache->set($this, $this->_innerNode);
    }


    public function adoptNode(Node &$node): Node {
        # The adoptNode(node) method steps are:
        #
        # 1. If node is a document, then throw a "NotSupportedError" DOMException.
        if ($node instanceof Document) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. If node is a shadow root, then throw a "HierarchyRequestError" DOMException.
        // DEVIATION: There is no scripting in this implementation

        # 3. If node is a DocumentFragment node whose host is non-null, then return.
        // DEVIATION: One can't just return here?
        if ($node instanceof DocumentFragment) {
            $host = Reflection::getProtectedProperty($node, 'host');
            if ($host !== null || $host->get() !== null) {
                return $node;
            }
        }

        # 4. Adopt node into this.
        $newNode = $this->importNode($node, true);

        $parent = $node->parentNode;
        if ($parent !== null) {
            $parent->removeChild($node);
        }

        // Remove node from the inner document's node cache.
        Reflection::getProtectedProperty($node->innerNode->ownerDocument, 'nodeCache')->delete($node);

        # 5. Return node.
        $node = $newNode;
        return $node;
    }

    public function createAttribute(string $localName): Attr {
        $innerNode = $this->_innerNode;

        # The createAttribute(localName) method steps are:
        #
        # 1. If localName does not match the Name production in XML, then throw an
        #    "InvalidCharacterError" DOMException.
        if (preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $localName) !== 1) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is an HTML document, then set localName to localName in ASCII
        #    lowercase.
        if (!$this instanceof XMLDocument) {
            $localName = strtolower($localName);
        }

        // Before we do the next step we need to work around a PHP DOM bug. PHP DOM
        // cannot create attribute nodes if there's no document element. So, create the
        // attribute node in a separate document which does have a document element and
        // then import
        $target = $innerNode;
        $documentElement = $this->documentElement;
        if ($documentElement === null) {
            $target = new \DOMDocument();
            $target->appendChild($target->createElement('html'));
        }

        # 3. Return a new attribute whose local name is localName and node document is
        # this.
        // We need to do a couple more things here. PHP's XML-based DOM doesn't allow
        // some characters. We have to coerce them sometimes.
        try {
            $attr = $target->createAttributeNS(null, $localName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            $attr = $target->createAttributeNS(null, $this->coerceName($localName));
        }

        if ($documentElement === null) {
            $attr = $this->cloneInnerNode($attr, $innerNode);
        }

        return $this->_innerNode->getWrapperNode($attr);
    }

    public function createAttributeNS(?string $namespace, string $qualifiedName): Attr {
        # The createAttributeNS(namespace, qualifiedName) method steps are:
        #
        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix !== null) ? "$prefix:$localName" : $localName;

        // Before we do the next step we need to work around a PHP DOM bug. PHP DOM
        // cannot create attribute nodes if there's no document element. So, create the
        // attribute node in a separate document which does have a document element and
        // then import
        $target = $this->_innerNode;
        $documentElement = $this->documentElement;
        if ($documentElement === null) {
            $target = new \DOMDocument();
            $target->appendChild($target->createElement('html'));
        }

        # 2. Return a new attribute whose namespace is namespace, namespace prefix is
        #    prefix, local name is localName, and node document is this.
        // We need to do a couple more things here. PHP's XML-based DOM doesn't allow
        // some characters. We have to coerce them sometimes.
        try {
            $attr = $target->createAttributeNS($namespace, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            //   uppercase hexadecimal digits of the character's code point
            if ($prefix !== null) {
                $qualifiedName = $this->coerceName($prefix) . ':' . $this->coerceName($localName);
            } else {
                $qualifiedName = $this->coerceName($localName);
            }

            $attr = $target->createAttributeNS($namespace, $qualifiedName);
        }

        if ($documentElement === null) {
            $attr = $this->cloneInnerNode($attr, $this->_innerNode);
        }

        return $this->_innerNode->getWrapperNode($attr);
    }

    public function createCDATASection(string $data): CDATASection {
        # The createCDATASection(data) method steps are:
        #
        # 1. If this is an HTML document, then throw a "NotSupportedError" DOMException.
        if (!$this instanceof XMLDocument) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. If data contains the string "]]>", then throw an "InvalidCharacterError"
        #    DOMException.
        if (str_contains(needle: ']]>', haystack: $data)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 3. Return a new CDATASection node with its data set to data and node document
        #    set to this.
        return $this->_innerNode->getWrapperNode($this->_innerNode->createCDATASection($data));
    }

    public function createComment(string $data): Comment {
        return $this->_innerNode->getWrapperNode($this->_innerNode->createComment($data));
    }

    public function createDocumentFragment(): DocumentFragment {
        return $this->_innerNode->getWrapperNode($this->_innerNode->createDocumentFragment());
    }

    public function createElement(string $localName): Element {
        # The createElement(localName, options) method steps are:
        // DEVIATION: The options parameter is at present only used for custom elements.
        // There is no scripting in this implementation.

        # 1. If localName does not match the Name production, then throw an
        #    "InvalidCharacterError" DOMException.
        if (!preg_match(InnerDocument::NAME_PRODUCTION_REGEX, $localName)) {
            throw new DOMException(DOMException::INVALID_CHARACTER);
        }

        # 2. If this is an HTML document, then set localName to localName in ASCII
        #    lowercase.
        if (!$this instanceof XMLElement) {
            $localName = strtolower($localName);
        }

        # 3. Let is be null.
        # 4. If options is a dictionary and options["is"] exists, then set is to it.
        // DEVIATION: There's no scripting in this implementation
        # 5. Let namespace be the HTML namespace, if this is an HTML document or this’s
        #    content type is "application/xhtml+xml"; otherwise null.
        // PHP's DOM has numerous bugs when setting the HTML namespace. Externally,
        // everything will show as HTML namespace, but internally will be null.
        # 6. Return the result of creating an element given this, localName, namespace,
        #    null, is, and with the synchronous custom elements flag set.

        try {
            $element = $this->_innerNode->createElementNS(null, $localName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            $element = $this->_innerNode->createElementNS(null, $this->coerceName($localName));
        }

        return $this->_innerNode->getWrapperNode($element);
    }

    public function createElementNS(?string $namespace, string $qualifiedName): Element {
        # The internal createElementNS steps, given document, namespace, qualifiedName,
        # and options, are as follows:
        // DEVIATION: The options parameter is at present only used for custom elements.
        // There is no scripting in this implementation.

        # 1. Let namespace, prefix, and localName be the result of passing namespace and
        #    qualifiedName to validate and extract.
        [ 'namespace' => $namespace, 'prefix' => $prefix, 'localName' => $localName ] = $this->validateAndExtract($qualifiedName, $namespace);
        $qualifiedName = ($prefix) ? "$prefix:$localName" : $localName;

        # 2. Let is be null.
        # 3. If options is a dictionary and options["is"] exists, then set is to it.
        # 4. Return the result of creating an element given document, localName, namespace,
        #    prefix, is, and with the synchronous custom elements flag set.
        // DEVIATION: There is no scripting in this implementation.

        try {
            $element = $this->_innerNode->createElementNS($namespace, $qualifiedName);
        } catch (\DOMException $e) {
            // The element name is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            $qualifiedName = $this->coerceName($prefix) . ':' . $this->coerceName($localName);
            $element = $this->_innerNode->createElementNS($namespace, $qualifiedName);
        }

        return $this->_innerNode->getWrapperNode($element);
    }

    public function createProcessingInstruction(string $target, string $data): ProcessingInstruction {
        try {
            $instruction = $this->_innerNode->createProcessingInstruction($target, $data);
        } catch (\DOMException $e) {
            // The target is invalid for XML
            // Replace any offending characters with "UHHHHHH" where H are the
            // uppercase hexadecimal digits of the character's code point
            $instruction = $this->_innerNode->createProcessingInstruction($this->coerceName($target), $data);
        }

        return $this->_innerNode->getWrapperNode($instruction);
    }

    public function createTextNode(string $data): Text {
        return $this->_innerNode->getWrapperNode($this->_innerNode->createTextNode($data));
    }

    public function destroy(): void {
        self::$cache->delete($this);
        self::$cache->delete($this->_innerNode);
    }

    public function getElementsByName(string $elementName): NodeList {
        # The getElementsByName(elementName) method steps are to return a live NodeList
        # containing all the HTML elements in that document that have a name attribute
        # whose value is identical to the elementName argument, in tree order. When the
        # method is invoked on a Document object again with the same argument, the user
        # agent may return the same as the object returned by the earlier call. In other
        # cases, a new NodeList object must be returned.
        // Because of how namespaces are handled internally they're null when a HTML document.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        // NodeLists cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\NodeList', $this->_innerNode, $this->_innerNode->xpath->query(".//*[namespace-uri()='$namespace' and @name='$elementName']"));
    }

    public function importNode(Node|\DOMNode $node, bool $deep = false): Node {
        # The importNode(node, deep) method steps are:
        #
        # 1. If node is a document or shadow root, then throw a "NotSupportedError"
        #    DOMException.
        // Because this can import from PHP's DOM we must check for more stuff.
        if ($node instanceof Document || $node instanceof \DOMDocument || ($node instanceof \DOMNode && $node->ownerDocument::class !== 'DOMDocument') || $node instanceof \DOMEntityReference) {
            throw new DOMException(DOMException::NOT_SUPPORTED);
        }

        # 2. Return a clone of node, with this and the clone children flag set if deep
        #    is true.
        return ($node instanceof \DOMNode) ? $this->_innerNode->getWrapperNode($this->cloneInnerNode($node, $this->_innerNode, $deep)) : $this->cloneWrapperNode($node, $this, $deep);
    }

    public function load(string $source = null, ?string $charset = null): void {
        if ($this->hasChildNodes()) {
            throw new DOMException(DOMException::NO_MODIFICATION_ALLOWED);
        }

        $config = new ParserConfig();
        // Preserve processing instructions when parsing. This violates the parsing
        // specification, but since this library is not a browser it should be able to
        // read and print processing instructions.
        $config->processingInstructions = true;

        if ($charset !== null) {
            $config->encodingFallback = Charset::fromCharset($charset);
        }

        $source = Parser::parseInto($source, $this->_innerNode, null, $config);
        $this->_characterSet = $source->encoding;
        $this->_compatMode = ($source->quirksMode === Parser::NO_QUIRKS_MODE || $source->quirksMode === Parser::LIMITED_QUIRKS_MODE) ? 'CSS1Compat' : 'BackCompat';

        $this->postParsingTemplatesFix($this->_innerNode);
    }

    public function loadFile(string $filename, ?string $charset = null): void {
        $f = @fopen($filename, 'r');
        if (!$f) {
            throw new DOMException(DOMException::FILE_NOT_FOUND);
        }

        $data = stream_get_contents($f);
        $charset = Charset::fromCharset((string)$charset) ?? Charset::fromTransport((string)$charset);
        $meta = stream_get_meta_data($f);
        $wrapperType = $meta['wrapper_type'];
        if (!$charset && $wrapperType === 'http') {
            // Try to find a Content-Type header field
            foreach ($meta['wrapper_data'] as $h) {
                $h = explode(':', $h, 2);
                if (count($h) === 2 && preg_match("/^\s*Content-Type\s*$/i", $h[0])) {
                    // Try to get an encoding from it
                    $charset = Charset::fromTransport($h[1]);
                    break;
                }
            }
        }

        if ($wrapperType === 'plainfile') {
            $filename = realpath($filename);
            $this->_URL = "file://$filename";
        } else {
            $this->_URL = $filename;
        }

        $this->load($data, $charset);
    }

    public function offsetExists(mixed $offset): bool {
        // There is no equivalent to this in the way it is typically implemented in
        // JavaScript, so to keep things the way PHP developers expect (that ArrayAccess
        // implementations work with isset) this will check to see if a valid named
        // element exists.

        // Because PHP is dumb and won't let us implement ArrayAccess with a more
        // specific type than its interface...
        if (!is_string($offset)) {
            trigger_error('Type error; ' . __CLASS__ . ' keys may only be strings', \E_USER_ERROR);
        }

        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        return ($this->_innerNode->xpath->query(".//*[(name()='form' or name()='iframe' or name()='img') and namespace-uri()='$namespace' and @name='$offset'] | .//img[namespace-uri()='$namespace' and @id='$offset' and @name and not(@name='')] | .//embed[namespace-uri()='$namespace' and @name='$offset' and not(ancestor::object[namespace-uri()='$namespace']) and not(descendant::*[(name()='embed' or name()='object') and namespace-uri()='$namespace'])] | .//object[namespace-uri()='$namespace' and @id='$offset' and not(ancestor::object[namespace-uri()='$namespace']) and not(descendant::*[(name()='embed' or name()='object') and namespace-uri()='$namespace'])]")->length > 0);
    }

    public function offsetGet(mixed $offset): Element|HTMLCollection|null {
        // Because PHP is dumb and won't let us implement ArrayAccess with a more
        // specific type than its interface...
        if (!is_string($offset)) {
            trigger_error('Type error; ' . __CLASS__ . ' keys may only be strings', \E_USER_ERROR);
        }

        // In JavaScript this part of the Document interface is implemented as
        // properties. This is impractical in PHP because the said properties can
        // contain characters which aren't valid PHP properties. So, this will be
        // implemented as an ArrayAccess implementation instead.

        # The Document interface supports named properties. The supported property names
        # of a Document object document at any moment consist of the following, in tree
        # order according to the element that contributed them, ignoring later
        # duplicates, and with values from id attributes coming before values from name
        # attributes when the same element contributes both:

        # • the value of the name content attribute for all exposed embed, form, iframe,
        #   img, and exposed object elements that have a non-empty name content attribute
        #   and are in a document tree with document as their root;
        # • the value of the id content attribute for all img elements that have both a
        #   non-empty id content attribute and a non-empty name content attribute, and are
        #   in a document tree with document as their root.

        # To determine the value of a named property name for a Document, the user agent
        # must return the value obtained using the following steps:
        #
        # 1. Let elements be the list of named elements with the name name that are in a
        #    document tree with the Document as their root.

        # Named elements with the name name, for the purposes of the above algorithm,
        # are those that are either:
        # • Exposed embed, form, iframe, img, or exposed object elements that have a name
        #   content attribute whose value is name, or
        # • Exposed object elements that have an id content attribute whose value is name,
        #   or
        # • img elements that have an id content attribute whose value is name, and that
        #   have a non-empty name content attribute present also.

        # An embed or object element is said to be exposed if it has no exposed object
        # ancestor, and, for object elements, is additionally either not showing its
        # fallback content or has no object or embed descendants.
        $namespace = (!$this instanceof XMLDocument) ? '' : Node::HTML_NAMESPACE;
        $elements = $this->_innerNode->xpath->query(".//*[(name()='form' or name()='iframe' or name()='img') and namespace-uri()='$namespace' and @name='$offset'] | .//img[namespace-uri()='$namespace' and @id='$offset' and @name and not(@name='')] | .//embed[namespace-uri()='$namespace' and @name='$offset' and not(ancestor::object[namespace-uri()='$namespace']) and not(descendant::*[(name()='embed' or name()='object') and namespace-uri()='$namespace'])] | .//object[namespace-uri()='$namespace' and @id='$offset' and not(ancestor::object[namespace-uri()='$namespace']) and not(descendant::*[(name()='embed' or name()='object') and namespace-uri()='$namespace'])]");

        # NOTE: There will be at least one such element, by definition.
        // This algorithm seems to expect user agents to keep up with a list of named
        // elements as elements are manipulated... I think? It is very vague on this
        // subject. There will of course be an instance where there's no such element --
        // if there's no valid named element in the document. Browsers return undefined
        // if there's no matching name, so let's return null here as PHP does not have
        // undefined.
        if ($elements->length === 0) {
            return null;
        }

        # 2. If elements has only one element, and that element is an iframe element, and
        #    that iframe element's nested browsing context is not null, then return the
        #    WindowProxy object of the element's nested browsing context.
        // No. This is stupid.

        # 3. Otherwise, if elements has only one element, return that element.
        if ($elements->length === 1) {
            return $this->_innerNode->getWrapperNode($elements->item(0));
        }

        # 4. Otherwise return an HTMLCollection rooted at the Document node, whose
        #    filter matches only named elements with the name name.
        // HTMLCollections cannot be created from their constructors normally.
        return Reflection::createFromProtectedConstructor(__NAMESPACE__ . '\\HTMLCollection', $this->_innerNode, $elements);
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        // The specification is vague as to what to do here. Browsers silently fail, so
        // that's what we're going to do.
    }

    public function offsetUnset(mixed $offset): void {
        // The specification is vague as to what to do here. Browsers silently fail, so
        // that's what we're going to do.
    }

    public function registerXPathFunctions(string|array|null $restrict = null): void {
        $this->xpathRegisterPhpFunctions($this, $restrict);
    }

    public function serialize(?Node $node = null, array $config = []): string {
        $node = $node ?? $this;
        if ($node !== $this && $node->ownerDocument !== $this) {
            throw new DOMException(DOMException::WRONG_DOCUMENT);
        }

        return Serializer::serialize($node->innerNode, $config);
    }


    public function __toString(): string {
        return $this->serialize();
    }
}