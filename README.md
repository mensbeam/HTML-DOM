[a]: https://dom.spec.whatwg.org/#htmlcollection
[b]: https://webidl.spec.whatwg.org/#idl-sequence

# HTML DOM #

Modern DOM library written in PHP for HTML documents.

## Usage ##

Coming soon

## Limitations ##

The primary aim of this library is accuracy. If the document model differs from what the specification mandates, this is probably a bug. However, we are also constrained by PHP, which imposes various limitations. These are as follows:

1. Due to PHP's DOM being designed for XML 1.0 Second Edition, element and attribute names which are illegal in XML 1.0 Second Edition are mangled as recommended by the specification.
2. Due to a PHP bug which severely degrades performance with large documents and in consideration of existing PHP software, HTML elements are placed in the null namespace rather than in the HTML namespace.
3. While `DOMDocumentType` can be extended and registered by PHP's `DOMDocument::registerNodeClass` `DOMImplementation` cannot; this means that doctypes created with `DOMImplementation::createDocumentType` can't ever be a registered class. Therefore, doctypes remain as `DOMDocumentType` in this library and retain the same limitations as ones in PHP's DOM.
4. The DOM specification mentions that [`HTMLCollection`][a] has to be kept around for backwards compatibility in browsers, but any new implementations should use [`sequence<T>`][b] instead which is essentially just a typed array object of some kind. Any methods should also return a copy of an object instead of a reference to the platform object, meaning the bane of any web developer's existence -- live lists -- shouldn't be a thing anymore either. Since this implementation is not a fully userland PHP implementation of the DOM but instead an extension of it, this implementation will use `DOMNodeList` where PHP's DOM would normally and array for anything that cannot be hacked to use `DOMNodeList` to keep things consistent.