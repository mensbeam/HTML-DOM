<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait HTMLOrSVGElement {
    protected function __get_autofocus(): bool {
        # The autofocus IDL attribute must reflect the content attribute of the same
        # name.

        # If a reflecting IDL attribute is a boolean attribute, then on getting the IDL
        # attribute must return true if the content attribute is set, and false if it is
        # absent. On setting, the content attribute must be removed if the IDL attribute
        # is set to false, and must be set to the empty string if the IDL attribute is
        # set to true. (This corresponds to the rules for boolean content attributes.)

        return ($this->getAttribute('autofocus') !== null);
    }

    protected function __set_autofocus(bool $value): void {
        # The autofocus IDL attribute must reflect the content attribute of the same
        # name.

        # If a reflecting IDL attribute is a boolean attribute, then on getting the IDL
        # attribute must return true if the content attribute is set, and false if it is
        # absent. On setting, the content attribute must be removed if the IDL attribute
        # is set to false, and must be set to the empty string if the IDL attribute is
        # set to true. (This corresponds to the rules for boolean content attributes.)

        if ($value) {
            $this->setAttribute('autofocus', '');
        } else {
            $this->removeAttribute('autofocus');
        }
    }
}