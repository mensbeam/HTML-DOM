<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


/**
 * Exists for inheritance reasons. All properties & methods necessary for
 * CharacterData are in Trait/CharacterData; not declaring them twice.
 */
interface CharacterData extends Node {}
