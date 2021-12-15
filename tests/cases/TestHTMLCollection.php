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
    Element,
    Node
};


/** @covers \MensBeam\HTML\DOM\HTMLCollection */
class TestHTMLCollection extends \PHPUnit\Framework\TestCase {
    /**
     * @covers \MensBeam\HTML\DOM\HTMLCollection::current
     * @covers \MensBeam\HTML\DOM\HTMLCollection::item
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetExists
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetGet
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::current
     * @covers \MensBeam\HTML\DOM\Collection::item
     * @covers \MensBeam\HTML\DOM\Collection::key
     * @covers \MensBeam\HTML\DOM\Collection::next
     * @covers \MensBeam\HTML\DOM\Collection::rewind
     * @covers \MensBeam\HTML\DOM\Collection::valid
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\ParentNode::__get_children
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testIteration(): void {
        $d = new Document(<<<HTML
        <!DOCTYPE html>
        <html>
         <body>
          <div id="ook">Ook</div>
          <div id="eek">Eek</div>
          <div id="ack">Ack</div>
          <div name="ook">Ook</div>
          <div name="poop💩">poop💩</div>
         </body>
        </html>
        HTML, 'UTF-8');
        $body = $d->body;

        $children = $body->children;
        foreach ($children as $key => $child) {
            $this->assertTrue($child instanceof Element);
        }
    }


    /**
     * @covers \MensBeam\HTML\DOM\HTMLCollection::namedItem
     * @covers \MensBeam\HTML\DOM\HTMLCollection::offsetGet 
     *
     * @covers \MensBeam\HTML\DOM\CharacterData::__get_data
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::load
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_firstChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::hasChildNodes
     * @covers \MensBeam\HTML\DOM\Node::postParsingTemplatesFix
     * @covers \MensBeam\HTML\DOM\ParentNode::__get_children
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_xpath
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testMethod_namedItem_offsetGet(): void {
        $d = new Document(<<<HTML
        <!DOCTYPE html>
        <html>
         <body>
          <div id="ook">Ook</div>
          <div id="eek">Eek</div>
          <div id="ack">Ack</div>
          <div name="ook">Ook</div>
          <div name="poop💩">poop💩</div>
         </body>
        </html>
        HTML, 'UTF-8');
        $body = $d->body;

        $children = $body->children;
        $this->assertSame($children[0], $children['ook']);
        $this->assertSame($children[0], $children->namedItem('ook'));
        $this->assertSame('Ook', $children['ook']->firstChild->data);
        $this->assertSame('poop💩', $children['poop💩']->firstChild->data);
        $this->assertNull($children['fail']);
        $this->assertNull($children->namedItem('fail'));
        $this->assertNull($children->namedItem(''));
    }
}