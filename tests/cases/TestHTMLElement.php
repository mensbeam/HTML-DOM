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
    DOMException
};


/** @covers \MensBeam\HTML\DOM\HTMLElement */
class TestHTMLElement extends \PHPUnit\Framework\TestCase {
    public function testProperty_accessKey(): void {
        $d = new Document('<!DOCTYPE html><html><body><a id="ook" href="https://ook.com" accesskey="o"></a></body></html>');
        $ook = $d->getElementById('ook');
        $this->assertSame('o', $ook->accessKey);
        $ook->accessKey = 'e';
        $this->assertSame('e', $ook->accessKey);
    }

    public function testProperty_autocapitalize(): void {
        $d = new Document('<!DOCTYPE html><html><body><form><input type="text" autocapitalize="on"></form></body></html>');
        $ook = $d->getElementsByTagName('input')[0];
        $this->assertSame('sentences', $ook->autocapitalize);
        $ook->removeAttribute('autocapitalize');
        $this->assertSame('', $ook->autocapitalize);
        $ook->autocapitalize = 'words';
        $this->assertSame('words', $ook->autocapitalize);
        $ook->autocapitalize = 'off';
        $this->assertSame('none', $ook->autocapitalize);

        $form = $d->getElementsByTagName('form')[0];
        $form->autocapitalize = 'bullshit';
        $ook->removeAttribute('autocapitalize');
        $this->assertSame('sentences', $ook->autocapitalize);
    }

    public function testProperty_contentEditable_isContentEditable(): void {
        $d = new Document('<!DOCTYPE html><html><body><div></div></body></html>');
        $div = $d->getElementsByTagName('div')[0];
        $this->assertSame('inherit', $div->contentEditable);
        $this->assertFalse($div->isContentEditable);
        $div->contentEditable = 'true';
        $this->assertSame('true', $div->contentEditable);
        $this->assertTrue($div->isContentEditable);
        $div->contentEditable = 'false';
        $this->assertSame('false', $div->contentEditable);
        $this->assertFalse($div->isContentEditable);
        $div->contentEditable = 'inherit';
        $this->assertFalse($div->hasAttribute('contenteditable'));
        $this->assertSame('inherit', $div->contentEditable);
        $div->removeAttribute('contenteditable');
        $d->body->contentEditable = 'true';
        $this->assertSame('inherit', $div->contentEditable);
        $this->assertTrue($div->isContentEditable);

        $d->designMode = 'on';
        $this->assertTrue($div->isContentEditable);
    }

    public function testProperty_contentEditable__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::SYNTAX_ERROR);
        $d = new Document('<!DOCTYPE html><html></html>');
        $d->documentElement->contentEditable = 'fail';
    }

    public function testProperty_dir(): void {
        $d = new Document('<!DOCTYPE html><html dir="ltr"></html>');
        $html = $d->documentElement;
        $this->assertSame('ltr', $html->dir);
        $html->dir = 'bullshit';
        $this->assertSame('', $html->dir);
    }

    public function testProperty_draggable(): void {
        $d = new Document('<!DOCTYPE html><html><body><img src="ook.html"><object data="ook"></object><div></div></body></html>');
        $img = $d->getElementsByTagName('img')[0];
        $div = $d->getElementsByTagName('div')[0];
        $object = $d->getElementsByTagName('object')[0];
        $this->assertTrue($img->draggable);
        $this->assertFalse($div->draggable);

        $div->draggable = true;
        $this->assertTrue($div->draggable);
        $div->draggable = false;
        $this->assertFalse($div->draggable);
        $img->draggable = false;
        $this->assertFalse($img->draggable);

        $this->assertFalse($object->draggable);
        $object->setAttribute('type', 'image/jpeg');
        $this->assertTrue($object->draggable);
        $object->removeAttribute('type');
        $object->setAttribute('data', 'ook.png');
        $this->assertTrue($object->draggable);
    }

    public function testProperty_enterKeyHint(): void {
        $d = new Document('<!DOCTYPE html><html></html>');
        $html = $d->documentElement;
        $this->assertSame('', $html->enterKeyHint);
        $html->enterKeyHint = 'ook';
        $this->assertSame('', $html->enterKeyHint);
        $html->enterKeyHint = 'done';
        $this->assertSame('done', $html->enterKeyHint);
    }

    public function testProperty_hidden(): void {
        $d = new Document('<!DOCTYPE html><html><body><div></div></body></html>');
        $div = $d->getElementsByTagName('div')[0];
        $this->assertFalse($div->hidden);
        $div->hidden = true;
        $this->assertTrue($div->hidden);
        $div->hidden = false;
        $d->documentElement->hidden = true;
        $this->assertTrue($div->hidden);
    }

    public function testProperty_lang(): void {
        $d = new Document('<!DOCTYPE html><html></html>');
        $html = $d->documentElement;

        $this->assertSame('', $html->lang);
        $html->lang = 'en';
        $this->assertSame('en', $html->lang);
        $html->lang = 'ook';
        $this->assertSame('ook', $html->lang);
    }

    /**
     * @covers \MensBeam\HTML\DOM\HTMLElement::__get_innerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_innerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__get_outerText
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_outerText
     *
     * @covers \MensBeam\HTML\DOM\Collection::__construct
     * @covers \MensBeam\HTML\DOM\Collection::__get_length
     * @covers \MensBeam\HTML\DOM\Collection::count
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::__get_body
     * @covers \MensBeam\HTML\DOM\Document::__get_documentElement
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\Document::createTextNode
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Element::__get_innerHTML
     * @covers \MensBeam\HTML\DOM\Element::getRenderedTextFragment
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_childNodes
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Node::__get_textContent
     * @covers \MensBeam\HTML\DOM\Node::appendChild
     * @covers \MensBeam\HTML\DOM\Node::getInnerDocument
     * @covers \MensBeam\HTML\DOM\Node::getInnerNode
     * @covers \MensBeam\HTML\DOM\Node::getRootNode
     * @covers \MensBeam\HTML\DOM\Node::postInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionBugFixes
     * @covers \MensBeam\HTML\DOM\Node::preInsertionValidity
     * @covers \MensBeam\HTML\DOM\Text::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::__get_wrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::getProtectedProperty
     */
    public function testProperty_innerText_outerText(): void {
        $d = new Document();
        $d->appendChild($d->createElement('html'));
        $d->documentElement->appendChild($d->createElement('body'));
        $body = $d->body;
        $body->appendChild($d->createTextNode('ook '));
        $s = $body->appendChild($d->createElement('span'));
        $s->appendChild($d->createTextNode('ook'));
        $body->appendChild($d->createTextNode(' eek'));
        $this->assertSame('ook <span>ook</span> eek', $body->innerHTML);

        $s->innerText = <<<TEXT
        ook\r\n
            eek ook
        TEXT;
        $this->assertSame('ook ookook    eek ook eek', $body->innerText);
        $this->assertSame('ook<br><br>ook    eek ook', $s->innerHTML);

        $s->outerText = 'ack';
        $this->assertSame('ook ack eek', $body->outerText);
        $this->assertEquals(1, $body->childNodes->length);

        $s = $body->appendChild($d->createElement('span'));
        $s->outerText = '';
        $this->assertSame('ook ack eek', $body->outerText);
    }


    public function testProperty_inputMode(): void {
        $d = new Document('<!DOCTYPE html><html></html>');
        $html = $d->documentElement;
        $this->assertSame('', $html->inputMode);
        $html->inputMode = 'ook';
        $this->assertSame('', $html->inputMode);
        $html->inputMode = 'tel';
        $this->assertSame('tel', $html->inputMode);
    }


    /**
     * @covers \MensBeam\HTML\DOM\HTMLElement::__set_outerText
     *
     * @covers \MensBeam\HTML\DOM\Document::__construct
     * @covers \MensBeam\HTML\DOM\Document::createElement
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\DOMImplementation::__construct
     * @covers \MensBeam\HTML\DOM\Element::__construct
     * @covers \MensBeam\HTML\DOM\Node::__construct
     * @covers \MensBeam\HTML\DOM\Node::__get_parentNode
     * @covers \MensBeam\HTML\DOM\Inner\Document::__construct
     * @covers \MensBeam\HTML\DOM\Inner\Document::getWrapperNode
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::get
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::has
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::key
     * @covers \MensBeam\HTML\DOM\Inner\NodeCache::set
     * @covers \MensBeam\HTML\DOM\Inner\Reflection::createFromProtectedConstructor
     */
    public function testProperty_outerText__errors(): void {
        $this->expectException(DOMException::class);
        $this->expectExceptionCode(DOMException::NO_MODIFICATION_ALLOWED);
        $d = new Document();
        $h = $d->createElement('html');
        $h->outerText = 'fail';
    }
}