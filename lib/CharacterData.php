<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


abstract class CharacterData extends Node {
    use ChildNode, NonDocumentTypeChildNode;


    protected function __get_data(): string {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->data;
    }

    protected function __set_data(string $value): void {
        // PHP's DOM does this correctly already.
        $this->_innerNode->data = $value;
    }

    protected function __get_length(): int {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->length;
    }


    public function appendData(string $data) {
        $this->_innerNode->data .= $data;
    }

    public function deleteData(int $offset, int $count) {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->deleteData($offset, $count);
    }

    public function insertData(int $offset, string $data) {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->insertData($offset, $data);
    }

    public function replaceData(int $offset, int $count, string $data) {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->replaceData($offset, $count, $data);
    }

    public function substringData(int $offset, int $count): string {
        // PHP's DOM does this correctly already.
        return $this->_innerNode->substringData($offset, $count);
    }
}