[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence
[c]: https://packagist.org/packages/phpgt/dom
[d]: https://dom.spec.whatwg.org
[e]: #limitations
[f]: https://html.spec.whatwg.org/multipage/dom.html

# HTML DOM #

Modern DOM library written in PHP for HTML documents. This library is an attempt to implement the [WHATWG's DOM specification][d] and [WHATWG HTML DOM extensions specification][f] through a userland extension and encapsulation of PHP's built-in DOM. It exists because PHP's DOM is inaccurate, inadequate for use with any HTML, and extremely buggy. This implementation aims to fix as much as possible the inaccuracies of the PHP DOM, add in features necessary for modern HTML development, and circumvent most of the bugs.

## Usage ##

Coming soon

## Examples ##

- Creating a new document:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document();
  ```

- Creating a new document from a string:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document('<!DOCTYPE html><html><head><title>Ook</title></head><body><h1>Ook!</h1></body></html>');
  ```

  or:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document();
  $d->load('<!DOCTYPE html><html><head><title>Ook</title></head><body><h1>Ook!</h1></body></html>');
  ```

## Limitations & Differences from Specification ##

The primary aim of this library is accuracy. However, due either to limitations imposed by PHP's DOM, by assumptions made by the specification that aren't applicable to a PHP library, or simply because of impracticality some changes have needed to be made. These are as follows:

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
14. This class is meant to be used with HTML, but it will -MOSTLY- as needed work with XML. Loading of XML uses PHP DOM's XML parser which does not conform to the XML specification. Writing an actual conforming XML parser is outside of the scope of this library.
15. While there is implementation of much of the XPath extensions, there will only be support for XPath 1.0 because that is all PHP DOM's XPath supports.
16. The XPath DOM specification allows for the use of the `XPathNSResolver` to automatically resolve namespaces for prefixes. To polyfill this behavior for use with PHP's XPath implementation would require writing at least partially a XPath 1.0 parser to grab any prefixes and then use `DOMXPath::registerNamespace` to associate namespaces. This might be something done at a later date. In the meantime this implementation instead exposes this ability to assocate namespaces with prefixes through the `Document::registerXPathNamespace` and `XPathEvaluator::registerXPathNamespace` methods. However, to eliminate common uses of namespace association the `xmlns` namespace is automatically associated.