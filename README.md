[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence
[c]: https://packagist.org/packages/phpgt/dom
[d]: https://dom.spec.whatwg.org
[e]: #limitations
[f]: https://html.spec.whatwg.org/multipage/dom.html
[g]: https://php.net/manual/en/book.dom.php
[h]: https://www.php.net/manual/en/book.ctype.php
[i]: https://code.mensbeam.com/MensBeam/Lit
[j]: https://github.com/whatwg/dom/issues/67
[k]: https://webidl.spec.whatwg.org
[l]: https://github.com/qt4cg/qtspecs/issues/296
[m]: https://www.w3.org/TR/xpath-30/#id-basics

# HTML DOM #

Modern DOM library written in PHP for HTML documents. This library is an attempt to implement the [WHATWG's DOM specification][d] and [WHATWG HTML DOM extensions specification][f] through a userland extension and encapsulation of PHP's built-in DOM. It exists because PHP's DOM is inaccurate, inadequate for use with any HTML, and extremely buggy. This implementation aims to fix as much as possible the inaccuracies of the PHP DOM, add in features necessary for modern HTML development, and circumvent most of the bugs.

## Requirements ##

* PHP 8.2.0 or newer with the following extensions:
  - [dom][g] extension
  - [ctype][h] extension (optional, used when parsing)
* Composer 2.0 or newer

## Changes in 2.0 ##

### DOMException classes ###

Prior to 2.0, `MensBeam\HTML\DOM\DOMException` would output a different error message depending on what code it was fed, equal to the [WebIDL spec][k]'s deprecated DOMException constants. While this is a much cleaner way of handling DOM exceptions, it's against the intention of the specification and against common PHP practices. Unfortunately, the spec has these exceptions named things like `HierarchyRequestError` which is problematic in PHP because it would be named something like `HierarchyRequestException` since there's a differentiation between an exception and an error in PHP. We have adhered to the spec here, but it's under another namespace level `MensBeam\HTML\DOM\DOMException` to make it a bit more clear that it's a `DOMException`. All of them are outlined below.

### XPath ###

XPath in the HTML spec is in flux; it has been [in discussion][j] for some time, but there has been some movement on it recently. In any case, XPath's JavaScript API is horrendously terrible to use; we now believe it was a mistake for us to replicate that in HTML-DOM, and any decision by the WHATWG on how XPath will be shoehorned onto HTML will likely maintain most of the ridiculousness of the current API for backwards-compatibility's sake. We don't need to do this. We will keep the `Document::evaluate` method, but every other XPath-related class and trait has been removed except for `XPathException`. Everything is explained below when outlining `MensBeam\HTML\DOM\Document` and in _Limitations & Differences from Specification_ below.

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
    use DocumentOrElement, NonElementParentNode, ParentNode;

    public function __construct(
        ?string $source = null,
        ?string $charset = null
    );

    public function destroy(): void;

    public function evaluate(
        string $expression,
        ?Node $contextNode = null,
        bool|callable $resolver = false,
        int $type = XPathResult::ANY_TYPE
    ): XPathResult;

    public function registerXPathFunctions(
        string|array|null $restrict = null
    ): void;

    public function registerXPathNamespaces(
        array $lookup
    ): void;

    public function serialize(
        ?Node $node = null,
        array $config = []
    ): string;

    public function serializeInner(
        ?Node $node = null,
        array $config = []
    ): string;

    public function unregisterXPathNamespaces(
        string ...$prefix
    ): void;
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
  gbk
  ```

#### MensBeam\HTML\DOM\Document::destroy ####

Destroys references associated with the instance so it may be garbage collected by PHP. Because of the way PHP's garbage collection is and the poor state of the library PHP DOM is based off of, references must be kept in userland for every created node. Therefore, this method should unfortunately be manually called whenever the document is not needed anymore.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document();
$d->destroy();
unset($d);
```

#### MensBeam\HTML\DOM\Document::evaluate ####

Selects elements based on a supplied XPath expression.

* `expression`: The XPath expression to be evaluated
* `contextNode`: The context node where the query will start at, defaults to the document

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document(<<<HTML
<!DOCTYPE html>
<html>
<body>
  <h1>Ook</h1>
  <div class="ook">ook</div>
  <div class="ook">eek</div>
  <div class="ook">ack</div>
  <div>ugh</div>
</body>
</html>
HTML);
$results = $d->evaluate('//div[@class="ook"]/text()');

echo "Found " . count($results) . " text nodes with div element parents that have an \"ook\" class:\n";
foreach ($result as $node) {
    echo $node->data . "\n";
}
```

Output:

```
Found 3 text nodes with div element parents that have an "ook" class:
ook
eek
ack
```

_NOTE_: XPath cannot select namespaced elements that don't contain prefixes, so they must be manually defined.

_NOTE_: While HTML-DOM supports non-ASCII characters in element names, XPath does not. Working around it is fairly easy. This is an edge case and unlikely to turn up when working with HTML documents, but we will document it here to be thorough:

```php
namespace MensBeam\HTML\DOM;

$d = new Document(<<<HTML
<!DOCTYPE html>
<html>
<body>
  <h1>Ook</h1>
  <poo💩>ook</poo💩>
  <div>ugh</div>
</body>
</html>
HTML);
$results = $d->evaluate('name(//pooU01F4A9)');

echo $results[0] . "\n";
```

Output:

```
poo💩
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

#### MensBeam\HTML\DOM\Document::registerXPathNamespaces ####

Register prefixes with their namespace URIs for use in XPath expressions.

* `lookup`: A lookup table with prefixes as keys and namespace URIs as values.

If `lookup` is invalid a `MensBeam\HTML\DOM\InvalidArgumentException` will be thrown.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document(<<<HTML
<!DOCTYPE html>
<html>
<body>
  <h1>Ook</h1>
  <svg role="img" viewBox="0 0 100 100"><title>:)</title><path d="M50,75c0,13.2-11.8,24-25,24S1,88.2,1,75s10.8-24,24-24c4.2,0,8.4,1.1,12,3.2l1.5,0.9V12.5C38.5,6.2,43.7,1,50,1c6.3,0,11.5,5.2,11.5,11.5v42.6l1.5-0.9c3.6-2.1,7.8-3.2,12-3.2c13.2,0,24,10.8,24,24S88.2,99,75,99S50,88.2,50,75z"></path></svg>
</body>
</html>
HTML);

$d->registerXPathNamespaces([
    'svg' => Node::SVG_NAMESPACE,
    'mathml' => Node::MATHML_NAMESPACE
]);
$result = $d->evaluate('count(//svg:svg/svg:path)');

echo "There is $result svg path element\n";
```

Output:

```
There is 1 svg path element
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

#### MensBeam\HTML\DOM\Document::unregisterXPathNamespaces ####

Unregister prefixes used in XPath expressions.

* `prefix`: The prefix to be unregistered.

##### Example #####

```php
namespace MensBeam\HTML\DOM;

$d = new Document(<<<HTML
<!DOCTYPE html>
<html>
<body>
  <h1>Ook</h1>
  <svg role="img" viewBox="0 0 100 100"><title>:)</title><path d="M50,75c0,13.2-11.8,24-25,24S1,88.2,1,75s10.8-24,24-24c4.2,0,8.4,1.1,12,3.2l1.5,0.9V12.5C38.5,6.2,43.7,1,50,1c6.3,0,11.5,5.2,11.5,11.5v42.6l1.5-0.9c3.6-2.1,7.8-3.2,12-3.2c13.2,0,24,10.8,24,24S88.2,99,75,99S50,88.2,50,75z"></path></svg>
</body>
</html>
HTML);

$d->registerXPathNamespaces([
    'svg' => Node::SVG_NAMESPACE,
    'mathml' => Node::MATHML_NAMESPACE
]);
$result = $d->evaluate('count(//svg:svg/svg:path)');
echo "There is $result svg path element\n";

$d->unregisterXPathNamespace('svg');
$result = $d->evaluate('count(//svg:svg/svg:path)');
echo "There is $result svg path element\n";
```

Output:

```
There is 1 svg path element
There is 0 svg path element
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

**WARNING**: Manipulating this node directly can result in unexpected behavior. This is available in the public API only so this library may be interfaced with other libraries which expect a \\DOMDocument object such as [MensBeam\\Lit][i].


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

The primary aim of this library is accuracy when possible and/or practical. However, due either to limitations imposed by PHP's DOM, by assumptions made by the specification that aren't applicable to a PHP library, or simply because of impracticality some changes have needed to be made. There appears to be a lot of deviations from the specification below, but this is simply an exhaustive list of details about the implementation with a few even explaining why we follow the specification instead of what browsers do.

1. Any mention of scripting or anything necessary because of scripting (such as the `ElementCreationOptions` options dictionary on `Document::createElement`) will not be implemented.
2. Due to a PHP bug which severely degrades performance with large documents and in consideration of existing PHP software and because of bizarre uncircumventable `xmlns` attribute bugs when the document is in the HTML namespace, HTML elements in HTML documents are placed in the null namespace internally rather than in the HTML namespace. However, externally they will be shown as having the HTML namespace. Even though null namespaced elements do not exist in the HTML specification one can create them using the DOM. Those will be shown as having a `null` namespace. This might change in the future because of [discussion][l] around what the default namespace for HTML elements should be in context of interoperability with XPath.
3. In the [WHATWG HTML DOM extensions specification][f] `Document` has named properties. In JavaScript one accesses them through either property notation (`document.ook`) or array notation (`document['ook']`). In PHP this is impractical because there's a differentation between the two notations. Instead, all named properties need to be accessed via array notation (`$document['ook']`).
4. The specification is written entirely with browsers in mind and aren't concerned with the DOM's being used outside of the browser. In browser there is always a document created by parsing serialized markup, and the DOM spec always assumes such. This is impossible in the way this PHP library is intended to be used. The default when creating a new `Document` is to set its content type to "application/xml". This isn't ideal when creating an HTML document entirely through the DOM, so this implementation will instead default to "text/html" unless using `XMLDocument`.
5. Again, because the specification assumes the implementation will be a browser, processing instructions are supposed to be parsed as comments. While it makes sense for a browser, this is impractical for a DOM library used outside of the browser where one may want to manipulate them; this library will instead preserve them when parsing a document but will convert them to comments when using `Element::innerHTML`.
6. Per the specification an actual HTML document cannot be created outside of the parser itself unless created via `DOMImplementation::createHTMLDocument`. Also, per the spec `DOMImplementation` cannot be instantiated via its constructor. This would require in this library's use case first creating a document then creating an HTML document via the first document's implementation. This is impractical and stupid, so in this library (like PHP DOM itself) a `DOMImplementation` can be instantiated independent of a document.
7. The specification shows `Document` as being able to be instantiated through its constructor and shows `XMLDocument` as inheriting from `Document`. In browsers `XMLDocument` cannot be instantiated through its constructor. We will follow the specification here and allow it.
8. CDATA section nodes, text nodes, and document fragments per the specification can be instantiated by their constructors independent of the `Document::createCDATASectionNode`, `Document::createTextNode`, and `Document::createDocumentFragment` methods respectively. This is not possible currently with this library and probably never will be due to the difficulty of implementing it and the awkwardness of their being different from every other node type in this respect.
9. As the DOM is presently specified, CDATA section nodes cannot be created on an HTML document. However, they can be created (and rightly so) on XML documents. The DOM, however, does not prohibit importing of CDATA section nodes into an HTML document and will be appended to the document as such. Seeing as this wouldn't be an issue in browser it has been overlooked by the specification authors. This library will allow importing of CDATA section nodes into HTML documents but will instead convert them to text nodes.
10. The DOM spec states clearly that when creating an element the localName should match the Name production; however, HTML is more restrictive in naming, only allowing an ASCII alpha character as the first character. All browsers prohibit creation of elements in HTML documents which have non-ASCII alpha characters as the first character, so we will do so here as well as it makes more sense. In addition, this library will throw a `NotSupportedError` `DOMException` when importing nodes with invalid HTML element names into HTML documents.
11. This implementation will not implement the `NodeIterator` and `TreeWalker` APIs. They are horribly conceived and impractical APIs that few people actually use because it's literally easier and faster to write recursive loops to walk through the DOM than it is to use those APIs. Walking downward through the tree has been replaced with the `ParentNode::walk` generator, and walking through adjacent children and moonwalking up the DOM tree can be accomplished through simple while or do/while loops.
12. All of the `Range` APIs will also not be implemented due to the sheer complexity of creating them in userland and how it adds undue difficulty to node manipulation in the "core" DOM. Numerous operations reference in excrutiating detail what to do with Ranges when manipulating nodes and would have to be added here to be compliant or mostly so -- slowing everything else down in the process on an already extremely front-heavy library.
13. The `DOMParser` and `XMLSerializer` APIs will not be implemented because they are ridiculous and limited in their scope. For instance, `DOMParser::parseFromString` won't set a document's character set to anything but UTF-8. This library needs to be able to print to other encodings due to the nature of how it is used. `Document::__construct` will accept optional `$source` and `$charset` arguments, and there are both `Document::load` and `Document::loadFile` methods for loading DOM from a string or a file respectively.
14. Aside from `HTMLElement`, `HTMLPreElement`, `HTMLTemplateElement`, `HTMLUnknownElement`, `MathMLElement`, and `SVGElement` none of the specific derived element classes (such as `HTMLAnchorElement` or `SVGSVGElement`) are implemented. The ones listed before are required for the element interface algorithm. The focus on this library will be on the core DOM before moving onto those -- if ever.
15. This class is meant to be used with HTML, but it will work -MOSTLY- work as needed with XML when using `XMLDocument`. Loading of XML uses PHP DOM's XML parser which does not completely conform to the XML specification. Writing an actual conforming XML parser is outside of the scope of this library. One notable feature of this library which won't work per the XML specification are unicode characters in element names. XML allows for capital letters while HTML doesn't. This implementation's workaround (because PHP's DOM doesn't support unicode at all in element names) internally coerces all non-ASCII characters to 'Uxxxxxx' which would be valid modern XML names. Something like a lookup table would be necessary for XML instead, but this isn't implemented and may not be because of complexity.
16. There will only be support for XPath 1.0 in this implementation because that is all PHP DOM's XPath supports. Writing a custom XPath parser to support XPath 3 would be a huge undertaking and is outside of the scope of this library.
17. The DOM XPath specification itself is an ill-conceived API that is entirely impractical to use, and as a result in browser land isn't used much at all. Version 1.x of this library attempted to implement much of the XPath extensions to maintain conformity with the specification, but in practice using it compared to PHP DOM's implementation was a horrible experience. Therefore, all XPath-related classes and traits have been removed from the library except for `XPathException`. Evaluation of XPath expressions is now done entirely through `Document::evaluate`.
18. `Document::evaluate` as specified (as part of `XPathEvaluatorBase`) is as ill-conceived as the rest of the DOM XPath specification, so while this library keeps it around it behaves entirely like `\DOMXPath::evaluate`. Namespaces must be manually defined using `Document::registerXPathNamespaces`. It's just a much simpler and less bullshit way to use XPath, and the PHP authors were right in deviating. As with `\DOMXPath`, `Document::evaluate` will return an integer, float, or a `NodeList` depending on what the expression would return instead of `XPathResult` as defined in the specification.
19. Unlike node names and prefixes this implementation will NOT coerce non-ASCII characters. As of this writing (December 2023) no browser supports unicode characters in XPath expression selectors, but the [XPath specification][m] states the expression should be a Unicode string and that node names used in selectors should follow the same conventions as XML. On the surface it seems it's as easy as coercing any non-ASCII characters in an expression, but internally attribute values aren't coerced. To accomplish coercion of selectors a superficial tokenization of the expression would be necessary to isolate selectors and string comparison on `name()`. We might consider doing this in the future, but it's not a high priority.