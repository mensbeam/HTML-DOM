---
title: Document::C14N
---

Document::C14N — **DISABLED**

## Description ##

```php
public Document::C14N ( bool $exclusive = false , bool $withComments = false , array|null $xpath = null , array|null $nsPrefixes = null ) : false
```

This function has been disabled and will always return `false`. `\DOMDocument::C14N` is an extremely slow and inefficient method to serialize DOM and never should be used. Documented to show difference from [`\DOMDocument`](https://www.php.net/manual/en/class.domdocument.php).