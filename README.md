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

## Limitations ##

The primary aim of this library is accuracy. If the document model differs from what the specification mandates, this is probably a bug. However, we are also constrained by PHP, which imposes various limitations. These are as follows:

1. Due to PHP's DOM being designed for XML 1.0 Second Edition, element and attribute names which are illegal in XML 1.0 Second Edition are mangled as recommended by the specification.
2. CDATA section nodes, text nodes, and document fragments per the specification can be created by their constructors independent of the `Document::createCDATASectionNode`, `Document::createTextNode`, and `Document::createDocumentFragment` methods respectively. This is not possible currently with this library and probably never will be due to the difficulty of implementing it and the awkwardness of their being different from every other node type in this respect.
3. This implementation will not implement the `NodeIterator` and `TreeWalker` APIs. They are horribly conceived and impractical APIs that few people actually use because it's literally easier to write recursive loops to walk through the DOM than it is to use those APIs. They have instead been replaced with the `ChildNode::moonwalk`, `ParentNode::walk`, `ChildNode::walkFollowing`, and `ChildNode::walkPreceding` generators.
4. Aside from `HTMLElement`, `HTMLTemplateElement`, `MathMLElement`, and `SVGElement` none of the specific derived element classes will yet be implemented.