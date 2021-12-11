<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    XMLDocument
};


/** @covers \MensBeam\HTML\DOM\CharacterData */
class TestCharacterData extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::appendData
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_length
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_appendData(): void {
        $d = new Document();
        $t = $d->createTextNode('ookeek');
        $this->assertEquals(6, $t->length);
        $t->appendData('💩');
        $this->assertEquals(7, $t->length);

        $d = new XMLDocument();
        $t = $d->createCDATASection('ookeek');
        $this->assertEquals(6, $t->length);
        $t->appendData('💩');
        $this->assertEquals(7, $t->length);
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::deleteData
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\CharacterData::__set_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_deleteData(): void {
        $d = new Document();
        $t = $d->createTextNode('ook eek');
        $t->deleteData(3, 1);
        $this->assertSame('ookeek', $t->data);
        $t->data = 'ook💩eek';
        $t->deleteData(3, 1);
        $this->assertSame('ookeek', $t->data);
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::insertData
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_insertData(): void {
        $d = new Document();
        $t = $d->createTextNode('ookeek');
        $t->insertData(3, '💩');
        $this->assertSame('ook💩eek', $t->data);
        $t->insertData(3, '💩');
        $this->assertSame('ook💩💩eek', $t->data);
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::replaceData
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_replaceData(): void {
        $d = new Document();
        $t = $d->createTextNode('ook💩💩eek');
        $t->replaceData(3, 2, ' ');
        $this->assertSame('ook eek', $t->data);
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::substringData
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_substringData(): void {
        $d = new Document();
        $t = $d->createTextNode('ook💩💩eek');
        $this->assertSame('💩💩', $t->substringData(3, 2));
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\CharacterData::__set_data
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testProperty_data(): void {
        $d = new Document();
        $t = $d->createTextNode('ook');
        // Getting is thoroughly tested, setting isn't.
        $t->data = 'eek';
        $this->assertSame('eek', $t->data);

        $d = new XMLDocument();
        $t = $d->createCDATASection('ook');
        $t->data = 'eek';
        $this->assertSame('eek', $t->data);
    }


    /**
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_length
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\CharacterData::__set_data
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createCDATASection
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testProperty_length(): void {
        $d = new Document();
        $t = $d->createTextNode('ookeek');
        $this->assertEquals(6, $t->length);
        $t->data .= '💩';
        $this->assertEquals(7, $t->length);

        $d = new XMLDocument();
        $t = $d->createCDATASection('ookeek');
        $this->assertEquals(6, $t->length);
        $t->data .= '💩';
        $this->assertEquals(7, $t->length);
    }
}