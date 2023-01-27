[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence
[c]: https://packagist.org/packages/phpgt/dom
[d]: https://dom.spec.whatwg.org
[e]: #limitations
[f]: https://html.spec.whatwg.org/multipage/dom.html
[g]: https://php.net/manual/en/book.dom.php
[h]: https://www.php.net/manual/en/book.ctype.php
[i]: https://code.mensbeam.com/MensBeam/Lit

# HTML DOM #

Modern DOM library written in PHP for HTML documents. This library is an attempt to implement the [WHATWG's DOM specification][d] and [WHATWG HTML DOM extensions specification][f] through a userland extension and encapsulation of PHP's built-in DOM. It exists because PHP's DOM is inaccurate, inadequate for use with any HTML, and extremely buggy. This implementation aims to fix as much as possible the inaccuracies of the PHP DOM, add in features necessary for modern HTML development, and circumvent most of the bugs.

## Requirements ##

* PHP 8.0.2 or newer with the following extensions:
  - [dom][g] extension
  - [ctype][h] extension (optional, used when parsing)
* Composer 2.0 or newer

## Usage ##

Full documentation for most of the library shouldn't be necessary because it largely follows the specification, but because of how the library is to be used there are a few things that are glaringly different. These will be outlined below.

### MensBeam\HTML\DOM\Document ###

`MensBeam\HTML\DOM\Document` implements `\ArrayAccess`, allowing the class to access named properties via array syntax:

```php
namespace MensBeam\HTML\DOM;

$d = new Document('<!DOCTYPE html><html><body><img name="ook"><img name="eek"><img id="eek" name="ack"><embed name="eek"><object id="ook"><embed name="eek"><object name="ookeek"></object></object><iframe name="eek"></iframe><object id="eek"></object></body></html>');

echo $d['ook']::class . "\n";
echo $d['eek']->length . "\n";
```

Output:

```
MensBeam\HTML\DOM\HTMLElement
5
```

There are limitations as to what is considered a named property. Refer to the [WHATWG HTML DOM extensions specification][f] for more details as to what is allowed to be accessed this way.

```php
namespace MensBeam\HTML\DOM;

partial class Document extends Node implements \ArrayAccess {
    use DocumentOrElement, NonElementParentNode, ParentNode, XPathEvaluatorBase;

    public function __construct(
        ?string $source = null,
        ?string $charset = null
    );

    public function destroy(): void;

    public function registerXPathFunctions(
        string|array|null $restrict = null
    ): void;

    public function serialize(
        ?Node $node = null,
        array $config = []
    ): string;

    public function serializeInner(
        ?Node $node = null,
        array $config = []
    ): string;
}
```

#### MensBeam\HTML\DOM\Document::__construct ####

Creates a new `MensBeam\HTML\DOM\Document` object.

* `source`: A string representing an HTML document to be parsed.
* `charset`: Character set to be used as the encoding for the document. If a document is parsed from a string its default is 'windows-1251', otherwise 'UTF-8'.

##### Examples #####

- Creating a new document:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document();
  ```

- Creating a new document from a string:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html><head><title>Ook</title></head><body><h1>Ook!</h1></body></html>');
  ```

  or:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document();
  $d->load('<!DOCTYPE html><html><head><title>Ook</title></head><body><h1>Ook!</h1></body></html>');
  ```

- Specifying a charset:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document(null, 'GB18030');
  echo $d->characterSet;
  ```

  Output:

  ```
  gb18030
  ```

#### MensBeam\HTML\DOM\Document::destroy ####

Destroys references associated with the instance so it may be garbage collected by PHP. Because of the way PHP's garbage collection is and the poor state of the library PHP DOM is based off of, references must be kept in userland for every created document. Therefore, this method should unfortunately be manually called whenever the document is not needed anymore.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document();
$d->destroy();
unset($d);
```

#### MensBeam\HTML\DOM\Document::registerXPathFunctions ####

Register PHP functions as XPath functions. Works like `\DOMXPath::registerPhpFunctions` except that the php namespace does not need to be registered.

* `restrict`: Use this parameter to only allow certain functions to be called from XPath. This parameter can be either a string (a function name), an array of function names, or null to allow everything.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document('<!DOCTYPE html><html><body><h1>Ook</h1><p class="subtitle1">Eek?</p><p class="subtitle2">Ook?</p></body></html>');
// Register PHP functions (no restrictions)
$d->registerXPathFunctions();
// Call substr function on classes
$result = $d->evaluate('//*[php:functionString("substr", @class, 0, 8) = "subtitle"]', $d);

echo "Found " . count($result) . " nodes with classes starting with 'subtitle':\n";
foreach ($result as $node) {
    echo "$node\n";
}
```

Output:

```
Found 2 nodes with classes starting with 'subtitle':
<p class="subtitle1">Eek?</p>
<p class="subtitle2">Ook?</p>
```

#### MensBeam\HTML\DOM\Document::serialize ####

Converts a node to a string.

* `node`: A node within the document to serialize, defaults to the document itself.
* `config`: A configuration array with the possible keys and value types being:
  - `booleanAttributeValues` (`bool|null`): Whether to include the values of boolean attributes on HTML elements during serialization. Per the standard this is `true` by default
  - `foreignVoidEndTags` (`bool|null`): Whether to print the end tags of foreign void elements rather than self-closing their start tags. Per the standard this is `true` by default
  - `groupElements` (`bool|null`): Group like "block" elements and insert extra newlines between groups
  - `indentStep` (`int|null`): The number of spaces or tabs (depending on setting of indentStep) to indent at each step. This is `1` by default and has no effect unless `reformatWhitespace` is `true`
  - `indentWithSpaces` (`bool|null`): Whether to use spaces or tabs to indent. This is `true` by default and has no effect unless `reformatWhitespace` is `true`
  - `reformatWhitespace` (`bool|null`): Whether to reformat whitespace (pretty-print) or not. This is `false` by default

##### Examples #####

- Serializing a document:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html></html>');
  echo $d->serialize();
  ```

  or:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html></html>');
  echo $d;
  ```

  Output:

  ```html
  <!DOCTYPE html><html><head></head><body></body></html>
  ```

- Serializing a document (pretty printing):

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html><body><h1>Ook!</h1><p>Ook, eek? Ooooook. Ook.</body></html>');
  echo $d->serialize($d, [ 'reformatWhitespace' => true ]);
  ```

  Output:

  ```html
  <!DOCTYPE html>
  <html>
   <head></head>

   <body>
    <h1>Ook!</h1>

    <p>Ook, eek? Ooooook. Ook.</p>
   </body>
  </html>
  ```

#### MensBeam\HTML\DOM\Document::serializeInner ####

Converts a node to a string but only serializes the node's contents.

* `node`: A node within the document to serialize, defaults to the document itself.
* `config`: A configuration array with the possible keys and value types being:
  - `booleanAttributeValues` (`bool|null`): Whether to include the values of boolean attributes on HTML elements during serialization. Per the standard this is `true` by default
  - `foreignVoidEndTags` (`bool|null`): Whether to print the end tags of foreign void elements rather than self-closing their start tags. Per the standard this is `true` by default
  - `groupElements` (`bool|null`): Group like "block" elements and insert extra newlines between groups
  - `indentStep` (`int|null`): The number of spaces or tabs (depending on setting of indentStep) to indent at each step. This is `1` by default and has no effect unless `reformatWhitespace` is `true`
  - `indentWithSpaces` (`bool|null`): Whether to use spaces or tabs to indent. This is `true` by default and has no effect unless `reformatWhitespace` is `true`
  - `reformatWhitespace` (`bool|null`): Whether to reformat whitespace (pretty-print) or not. This is `false` by default

##### Examples #####

- Serializing a document (functionally identical to `Document::serialize`):

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html></html>');
  echo $d->serializeInner();
  ```

  or:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html></html>');
  echo $d;
  ```

  Output:

  ```html
  <!DOCTYPE html><html><head></head><body></body></html>
  ```

- Serializing an element's contents:

  ```php
  namespace MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html><body><h1>Ook!</h1><p>Ook, eek? Ooooook. Ook.</body></html>');
  $body = $d->body;
  echo $body->serializeInner($body, [ 'reformatWhitespace' => true ]);
  ```

  Output:

  ```html
  <h1>Ook!</h1>

  <p>Ook, eek? Ooooook. Ook.</p>
  ```

### MensBeam\HTML\DOM\Node ###

Common namespace constants are provided in `MensBeam\HTML\DOM\Node` to make using namespaces with this library not so onerous. In addition, constants are provided here to be used with `MensBeam\HTML\DOM\ParentNode::walk`. `MensBeam\HTML\DOM\Node` also implements `\Stringable` which means that any node can be simply converted to a string to serialize it.

```php
namespace MensBeam\HTML\DOM;

partial abstract class Node implements \Stringable {
    public readonly \DOMNode $innerNode;

    // Common namespace constants provided for convenience
    public const HTML_NAMESPACE = 'http://www.w3.org/1999/xhtml';
    public const MATHML_NAMESPACE = 'http://www.w3.org/1998/Math/MathML';
    public const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';
    public const XLINK_NAMESPACE = 'http://www.w3.org/1999/xlink';
    public const XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';
    public const XMLNS_NAMESPACE = 'http://www.w3.org/2000/xmlns/';

    // Used with MensBeam\HTML\DOM\ParentNode::walk
    public const WALK_ACCEPT = 0x01;
    public const WALK_REJECT = 0x02;
    public const WALK_SKIP_CHILDREN = 0x04;
    public const WALK_STOP = 0x08;


    public function getNodePath(): ?string;
}
```

#### Properties ####

*innerNode*: A readonly property that returns the encapsulated inner element.

**WARNING**: Manipulating this node directly can result in unexpected behavior. This is available in the public API only so the class may be interfaced with other libraries which expect a \\DOMDocument object such as [MensBeam\\Lit][i].


#### MensBeam\HTML\DOM\Node::getNodePath ####

Carryover from PHP's DOM. It's a useful method that returns an XPath location path for the node. Returns a string if successful or null on failure.

### MensBeam\HTML\DOM\ParentNode ###

```php
namespace MensBeam\HTML\DOM;

partial trait ParentNode {
    public function walk(
        ?\Closure $filter = null,
        bool $includeReferenceNode = false
    ): \Generator;
}
```

#### MensBeam\HTML\DOM\ParentNode::walk ####

Applies the callback filter while walking down the DOM tree and yields nodes matching the filter in a generator.

* `filter`: A callback method to filter the DOM tree. Must return only the following values:
  - `Node::WALK_ACCEPT`: Accept the node.
  - `Node::WALK_REJECT`: Reject the node.
  - `Node::WALK_SKIP_CHILDREN`: Skip the node's children.
  - `Node::WALK_STOP`: Stop the walker.
* `includeReferenceNode`: Include `$this` when walking down the tree.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document(<<<HTML
<!DOCTYPE html>
<html>
 <body>
  <div><!--Ook!-->
   <div>
    <div>
     <!--Eek!-->
    </div>
   </div>
  <!--Ack!--></div>
 </body>
</html>
HTML);

$walker = $d->walk(function($n) {
    return ($n instanceof Comment) ? Node::WALK_ACCEPT : Node::WALK_REJECT;
});

echo "The following comments were found:\n";
foreach ($walker as $node) {
    echo "$node\n";
}
```

Output:

```
The following comments were found:
<!--Ook!-->
<!--Eek!-->
<!--Ack!-->
```

### MensBeam\HTML\DOM\XPathEvaluator ###

```php
namespace MensBeam\HTML\DOM;

partial class XPathEvaluator {
    public function registerXPathFunctions(Document $document, string|array|null $restrict = null): void;
}
```

#### MensBeam\HTML\DOM\XPathEvaluator::registerXPathFunctions ####

Register PHP functions as XPath functions. Works like `\DOMXPath::registerPhpFunctions` except that the php namespace does not need to be registered.

* `document`: The document to register the functions on.
* `restrict`: Use this parameter to only allow certain functions to be called from XPath. This parameter can be either a string (a function name), an array of function names, or null to allow everything.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document('<!DOCTYPE html><html><body><h1>Ook</h1><p class="subtitle1">Eek?</p><p class="subtitle2">Ook?</p></body></html>');
$e = new XPathEvaluator();
// Register PHP functions (no restrictions)
$e->registerXPathFunctions($d);
// Call substr function on classes
$result = $e->evaluate('//*[php:functionString("substr", @class, 0, 8) = "subtitle"]', $d);

echo "Found " . count($result) . " nodes with classes starting with 'subtitle':\n";
foreach ($result as $node) {
    echo "$node\n";
}
```

Output:

```
Found 2 nodes with classes starting with 'subtitle':
<p class="subtitle1">Eek?</p>
<p class="subtitle2">Ook?</p>
```

### MensBeam\HTML\DOM\XPathResult ###

`MensBeam\HTML\DOM\XPathResult` implements `\ArrayAccess`, `\Countable`, and `\Iterator` and will allow for accessing as if it is an array when the result type is `MensBeam\HTML\DOM\XPathResult::ORDERED_NODE_ITERATOR_TYPE`, `MensBeam\HTML\DOM\XPathResult::UNORDERED_NODE_ITERATOR_TYPE`, `MensBeam\HTML\DOM\XPathResult::ORDERED_NODE_SNAPSHOT_TYPE`, or `MensBeam\HTML\DOM\XPathResult::UNORDERED_NODE_SNAPSHOT_TYPE`. This is not in the specification, but not being able to simply iterate over a result is absurd.

```php
partial class XPathResult implements \ArrayAccess, \Countable, \Iterator {}
```

### MensBeam\HTML\DOM\Inner\Document ###

This is the document object that is wrapped. There are a few things that are publicly available. This is only available in the public API so the class may be interfaced with other libraries which expect a \\DOMDocument object such as [MensBeam\Lit][i].

```php
namespace MensBeam\HTML\DOM\Inner;

partial abstract class Document extends \DOMDocument {
    public readonly \MensBeam\HTML\DOM\Node $wrapperNode;

    public function getWrapperNode(\DOMNode $node): ?\MensBeam\HTML\DOM\Node;
}
```

#### Properties ####

*wrapperNode*: A readonly property that returns the wrapper document for the document.

#### MensBeam\HTML\DOM\Inner\Document::getWrapperNode ####

Returns the wrapper node that corresponds to the provided inner node. If one does not exist it is created.

* `node`: The inner node to use to look up/create the wrapper node with.

## Limitations & Differences from Specification ##

The primary aim of this library is accuracy. However, due either to limitations imposed by PHP's DOM, by assumptions made by the specification that aren't applicable to a PHP library, or simply because of impracticality some changes have needed to be made. There appears to be a lot of deviations from the specification below, but this is simply an exhaustive list of details about the implementation with a few even explaining why we follow the specification instead of what browsers do.

1. Any mention of scripting or anything necessary because of scripting (such as the `ElementCreationOptions` options dictionary on `Document::createElement`) will not be implemented.
2. Due to a PHP bug which severely degrades performance with large documents and in consideration of existing PHP software and because of bizarre uncircumventable `xmlns` attribute bugs when the document is in the HTML namespace, HTML elements in HTML documents are placed in the null namespace internally rather than in the HTML namespace. However, externally they will be shown as having the HTML namespace. Even though null namespaced elements do not exist in the HTML specification one can create them using the DOM. However, in this implementation they will be treated as HTML namespaced elements due to the HTML namespace limitation.
3. In the [WHATWG HTML DOM extensions specification][f] `Document` has named properties. In JavaScript one accesses them through either property notation (`document.ook`) or array notation (`document['ook']`). In PHP this is impractical because there's a differentation between the two notations. Instead, all named properties need to be accessed via array notation (`$document['ook']`).
4. The specification is written entirely with browsers in mind and aren't concerned with the DOM's being used outside of the browser. In browser there is always a document created by parsing serialized markup, and the DOM spec always assumes such. This is impossible in the way this PHP library is intended to be used. The default when creating a new `Document` is to set its content type to "application/xml". This isn't ideal when creating an HTML document entirely through the DOM, so this implementation will instead default to "text/html" unless using `XMLDocument`.
5. Again, because the specification assumes the implementation will be a browser, processing instructions are supposed to be parsed as comments. While it makes sense for a browser, this is impractical for a DOM library used outside of the browser where one may want to manipulate them; this library will instead preserve them when parsing a document but will convert them to comments when using `Element::innerHTML`.
6. Per the specification an actual HTML document cannot be created outside of the parser itself unless created via `DOMImplementation::createHTMLDocument`. Also, per the spec `DOMImplementation` cannot be instantiated via its constructor. This would require in this library's use case first creating a document then creating an HTML document via the first document's implementation. This is impractical and stupid, so in this library (like PHP DOM itself) a `DOMImplementation` can be instantiated independent of a document.
7. The specification shows `Document` as being able to be instantiated through its constructor and shows `XMLDocument` as inheriting from `Document`. In browsers `XMLDocument` cannot be instantiated through its constructor. We will follow the specification here and allow it.
8. CDATA section nodes, text nodes, and document fragments per the specification can be instantiated by their constructors independent of the `Document::createCDATASectionNode`, `Document::createTextNode`, and `Document::createDocumentFragment` methods respectively. This is not possible currently with this library and probably never will be due to the difficulty of implementing it and the awkwardness of their being different from every other node type in this respect.
9. As the DOM is presently specified, CDATA section nodes cannot be created on an HTML document. However, they can be created (and rightly so) on XML documents. The DOM, however, does not prohibit importing of CDATA section nodes into an HTML document and will be appended to the document as such. This appears to be a glaring omission by the maintainers of the specification. This library will allow importing of CDATA section nodes into HTML documents but will instead convert them to text nodes.
10. This implementation will not implement the `NodeIterator` and `TreeWalker` APIs. They are horribly conceived and impractical APIs that few people actually use because it's literally easier and faster to write recursive loops to walk through the DOM than it is to use those APIs. Walking downward through the tree has been replaced with the `ParentNode::walk` generator, and walking through adjacent children and moonwalking up the DOM tree can be accomplished through simple while or do/while loops.
11. All of the `Range` APIs will also not be implemented due to the sheer complexity of creating them in userland and how it adds undue difficulty to node manipulation in the "core" DOM. Numerous operations reference in excrutiating detail what to do with Ranges when manipulating nodes and would have to be added here to be compliant or mostly so -- slowing everything else down in the process on an already extremely front-heavy library.
12. The `DOMParser` and `XMLSerializer` APIs will not be implemented because they are ridiculous and limited in their scope. For instance, `DOMParser::parseFromString` won't set a document's character set to anything but UTF-8. This library needs to be able to print to other encodings due to the nature of how it is used. `Document::__construct` will accept optional `$source` and `$charset` arguments, and there are both `Document::load` and `Document::loadFile` methods for loading DOM from a string or a file respectively.
13. Aside from `HTMLElement`, `HTMLPreElement`, `HTMLTemplateElement`, `HTMLUnknownElement`, `MathMLElement`, and `SVGElement` none of the specific derived element classes (such as `HTMLAnchorElement` or `SVGSVGElement`) are implemented. The ones listed before are required for the element interface algorithm. The focus on this library will be on the core DOM before moving onto those -- if ever.
14. This class is meant to be used with HTML, but it will work -MOSTLY- as needed work with XML. Loading of XML uses PHP DOM's XML parser which does not completely conform to the XML specification. Writing an actual conforming XML parser is outside of the scope of this library. One notable feature of this library which won't work per the XML specification are unicode characters in element names. XML allows for capital letters while HTML doesn't. This implementation's workaround (because PHP's DOM doesn't support unicode at all in element names) internally coerces all non-ascii characters to 'Uxxxx' which would be valid modern XML names. Something like a lookup table would be necessary for XML instead, but this isn't implemented and may not be because of complexity.
15. While there is implementation of much of the XPath extensions, there will only be support for XPath 1.0 because that is all PHP DOM's XPath supports.
16. This library's XPath API is -- like the rest of the library itself -- a wrapper that wraps PHP's implementation but instead works like the specification, so there is no need to manually register namespaces. Namespaces that are associated with prefixes will be looked up when evaluating the expression if a `XPathNSResolver` is specified. However, access to registering PHP functions for use within XPath isn't in the specification but is available through `Document::registerXPathFunctions` and `XPathEvaluator::registerXPathFunctions`.
17. `XPathEvaluatorBase::evaluate` has a `result` argument where one provides it with an existing result object to use. I can't find any usable documentation on what this is supposed to do, and the specifications on it are vague. So, at present it does nothing until what it needs to do can be deduced.
18. At present XPath expressions cannot select elements or attributes which use any valid non-ascii character. This is because those nodes are coerced internally to work within PHP's DOM which doesn't support those characters. This can be worked around by coercing names in XPath queries, but that can only be reliably accomplished through an XPath parser. Writing an entire XPath parser for what amounts to an edge case isn't desirable.
19. The XPath API itself is an ill-conceived API that is entirely impractical to use because doing anything with the `XPathResult` object is cumbersome and stupid. Per the specification one cannot iterate over the result even if the result type is an iterator type (why in the hell call it that, then?). One has to instead repeatedly call the `XPathResult::iterateNext()` method. This implementation will allow for treating `XPathResult` snapshot or iterator types as arrays.