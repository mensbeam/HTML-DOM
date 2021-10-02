<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;

// Exists so Document can extend methods from its traits.
abstract class AbstractDocument extends \DOMDocument {
    use DocumentOrElement, MagicProperties, ParentNode, Walk;
}
