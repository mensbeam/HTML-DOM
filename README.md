[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence
[c]: https://packagist.org/packages/phpgt/dom
[d]: https://html.spec.whatwg.org
[e]: #limitations

# HTML DOM #

Modern DOM library written in PHP for HTML documents. This implementation is a userland extension of PHP's built-in DOM. It exists because PHP's DOM is inaccurate, inadequate for use with HTML, and buggy. This implementation attempts to fix as much as possible the inaccuracies of the PHP DOM, add in features necessary for modern HTML development, and circumvent most of the bugs without recreating the entirety of the DOM specification in userland. There is another PHP DOM library, [`phpgt/dom`][c], which does implement more of the DOM in userland; it, however, doesn't address many of PHP DOM's bugs and is incredibly slow (see [Limitations \#5][e]).

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

  $d = new Document(new DOMDocument());
  ```

  or:

  ```php
  use MensBeam\HTML\DOM;

  $d = new Document();
  $d->loadDOM(new DOMDocument());
  ```

## Limitations ##

The primary aim of this library is accuracy. If the document model differs from what the specification mandates, this is probably a bug. However, we are also constrained by PHP, which imposes various limitations. These are as follows:

1. Due to PHP's DOM being designed for XML 1.0 Second Edition, element and attribute names which are illegal in XML 1.0 Second Edition are mangled as recommended by the specification.
2. Due to a PHP bug which severely degrades performance with large documents and in consideration of existing PHP software, HTML elements are placed in the null namespace rather than in the HTML namespace.
3. While `DOMDocumentType` can be extended and registered by PHP's `DOMDocument::registerNodeClass` `DOMImplementation` cannot; this means that doctypes created with `DOMImplementation::createDocumentType` can't ever be a registered class. Therefore, doctypes remain as `DOMDocumentType` in this library and retain the same limitations as ones in PHP's DOM.
4. The DOM specification mentions that [`HTMLCollection`][a] has to be kept around for backwards compatibility in browsers, but any new implementations should use [`sequence<T>`][b] instead which is essentially just a typed array object of some kind. Any methods should also return a copy of an object instead of a reference to the platform object, meaning the bane of any web developer's existence -- live lists -- shouldn't be in any new additions to the DOM. Since this implementation is not a fully userland PHP implementation of the DOM but instead an extension of it, this implementation will use `DOMNodeList` where the specification says to use an `HTMLCollection` and an array where the specification says to use a `sequence<T>`.
5. Aside from `HTMLTemplateElement` there are no other specific element classes such as `HTMLAnchorElement`, `HTMLDivElement`, etc. and therefore are no DOM methods and properties that are specific to those elements. Implementing them is possible, but we weighed it against its utility as each specific element slows down the DOM seemingly exponentially especially when parsing serialized HTML because each element has to be converted to the specific variety manually and recursively. For instance, when parsing the WHATWG's single page HTML specification (which is an absurdly enormous HTML document on the very edge of what we should be able to parse) in our tests it takes around 6.5 seconds; with specific element classes it instead takes *15 minutes*. [`phpgt/dom`][c] mitigates this by only converting when querying for elements, but it's still slow. We decided not to go this route.
6. This implementation will not implement the `NodeIterator` and `TreeWalker` APIs. They are horribly conceived and impractical APIs that few people actually use because it's literally easier to write recursive loops to walk through the DOM than it is to use those APIs. They have instead been replaced with the `ChildNode::moonwalk`, `ParentNode::walk`, `ChildNode::walkFollowing`, and `ChildNode::walkPreceding` generators.