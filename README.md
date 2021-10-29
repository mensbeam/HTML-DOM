[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence
[c]: https://packagist.org/packages/phpgt/dom
[d]: https://html.spec.whatwg.org
[e]: #limitations

# HTML DOM #

Modern DOM library written in PHP for HTML documents. This implementation is a userland extension of PHP's built-in DOM. It exists because PHP's DOM is inaccurate, inadequate for use with any HTML, and buggy. This implementation aims to fix as much as possible the inaccuracies of the PHP DOM, add in features necessary for modern HTML development, and circumvent most of the bugs.

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
  $d->loadHTML('<!DOCTYPE html><html><head><title>Ook</title></head><body><h1>Ook!</h1></body></html>');
  ```

- Creating a new document from existing DOM:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document(new \DOMDocument());
  ```

  or:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document();
  $d->loadDOM(new \DOMDocument());
  ```

## Differences from Specification ##

The primary aim of this library is accuracy. However, due either to limitations imposed by PHP's DOM or by assumptions made by the specification that aren't applicable to a PHP library some changes have needed to be made. These are as follows:

1. Any mention of scripting or anything necessary because of scripting (such as the `ElementCreationOptions` options dictionary on `Document::createElement`) will not be implemented.
2. The specification is written entirely with browsers in mind and aren't concerned with the DOM's being used outside of the browser. In browser there is always a document created by parsing serialized markup, and the DOM spec always assumes such. This is impossible in the way this PHP library is intended to be used. The default when creating a new `Document` is to set its content type to "application/xml". This isn't ideal when creating an HTML document entirely through the DOM, so this implementation will instead default to "text/html" unless using `XMLDocument`.
3. Per the specification an actual HTML document cannot be created outside of the parser itself unless created via `DOMImplementation::createHTMLDocument`. Also, per the spec `DOMImplementation` cannot be instantiated via its constructor. This would require in this library's use case first creating a document then creating an HTML document via its implementation. This is impractical, so in this library (like PHP DOM itself) a `DOMImplementation` can be instantiated independent of a document.
4. The specification shows `Document` as being able to be instantated through its constructor and shows `XMLDocument` as inheriting from `Document`. In browsers `XMLDocument` cannot be instantiated through its constructor. We will follow the specification here and allow it.
5. CDATA section nodes, text nodes, and document fragments per the specification can be instantiated by their constructors independent of the `Document::createCDATASectionNode`, `Document::createTextNode`, and `Document::createDocumentFragment` methods respectively. This is not possible currently with this library and probably never will be due to the difficulty of implementing it and the awkwardness of their being different from every other node type in this respect.
6. This implementation will not implement the `NodeIterator` and `TreeWalker` APIs. They are horribly conceived and impractical APIs that few people actually use because it's literally easier to write recursive loops to walk through the DOM than it is to use those APIs. They have instead been replaced with the `ParentNode::walk` generator.
7. All of the `Range` APIs will also not be implemented due to the sheer complexity of creating them in userland and how it adds undue difficulty to node manipulation in the "core" DOM. Numerous operations reference in excrutiating detail what to do with Ranges when manipulating nodes and would have to be added here to be compliant or mostly so -- slowing everything else down in the process on an already front-heavy library.
8. Aside from `HTMLElement`, `HTMLTemplateElement`, `MathMLElement`, and `SVGElement` none of the specific derived element classes (such as `HTMLAnchorElement` or `SVGSVGElement`) are implemented. The focus on this library will be on the core DOM before moving onto those. They may or may not be implemented in the future.