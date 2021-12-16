<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM;
use MensBeam\HTML\DOM\{
    Document,
    DocumentFragment,
    Node
};


/** @covers \MensBeam\HTML\DOM\Serializer */
class TestSerializer extends \PHPUnit\Framework\TestCase {
    public function testMethod_isPreformattedContent(): void {
        $d = new Document('<pre><code></code></pre>');
        $this->assertSame(<<<HTML
        <html>
         <head></head>

         <body>
          <pre><code></code></pre>
         </body>
        </html>
        HTML, $d->serialize(null, [ 'reformatWhitespace' => true ]));

        $frag = $d->createDocumentFragment();
        $p = $frag->appendChild($d->createElement('pre'));
        $t = $p->appendChild($d->createElement('template'));
        $t->content->appendChild($d->createTextNode('ook'));
        $t->content->appendChild($d->createElement('br'));

        $this->assertSame('<pre><template>ook<br></template></pre>', $d->serialize($frag, [ 'reformatWhitespace' => true ]));

        $div = $t->content->appendChild($d->createElement('div'));
        $div->appendChild($d->createTextNode('ook'));

        $this->assertSame('<pre><template>ook<br><div>ook</div></template></pre>', $d->serialize($frag, [ 'reformatWhitespace' => true ]));
    }


    public function testMethod_treatAsBlockWithTemplates(): void {
        $d = new Document('<span>ook</span><template><div>ook</div></template>');

        $this->assertSame(<<<HTML
        <body>
         <span>ook</span>

         <template>
          <div>ook</div>
         </template>
        </body>
        HTML, $d->serialize($d->body, [ 'reformatWhitespace' => true ]));
    }


    public function provideMethod_treatForeignRootAsBlock(): iterable {
        return [
            [
                function() {
                    $d = new Document(<<<HTML
                    <!DOCTYPE html>
                    <html>
                     <body>
                      <span><template><svg><g><rect id="eek--a" width="5" height="5"/></g></svg><div>ook</div></template></span>
                     </body>
                    </html>
                    HTML, 'UTF-8');

                    return $d->serialize($d->getElementsByTagName('template')[0]->content->firstChild->firstChild, [ 'reformatWhitespace' => true ]);
                },

                <<<HTML
                <g>
                 <rect id="eek--a" width="5" height="5"></rect>
                </g>
                HTML
            ],

            [
                function() {
                    $d = new Document(<<<HTML
                    <!DOCTYPE html>
                    <html>
                     <body>
                      <svg role="img" viewBox="0 0 26 26"><title>Ook</title>
                      <rect id="eek--a" width="5" height="5"/></svg>
                     </body>
                    </html>
                    HTML, 'UTF-8');

                    return $d->serialize($d->body, [ 'reformatWhitespace' => true ]);
                },

                <<<HTML
                <body><svg role="img" viewBox="0 0 26 26"><title>Ook</title> <rect id="eek--a" width="5" height="5"></rect></svg></body>
                HTML
            ],

            [
                function() {
                    $d = new Document(<<<HTML
                    <!DOCTYPE html>
                    <html>
                     <body>
                      <svg><g><g><rect id="eek--a" width="5" height="5"/></g></g></svg>
                      <div></div>
                     </body>
                    </html>
                    HTML, 'UTF-8');

                    $svg = $d->getElementsByTagNameNS(Node::SVG_NAMESPACE, 'svg')[0];
                    $g = $svg->firstChild->firstChild;

                    return $d->serialize($g, [ 'reformatWhitespace' => true ]);
                },

                <<<HTML
                <g>
                 <rect id="eek--a" width="5" height="5"></rect>
                </g>
                HTML
            ],
        ];
    }

    /** @dataProvider provideMethod_treatForeignRootAsBlock */
    public function testMethod_treatForeignRootAsBlock(\Closure $closure, string $expected): void {
        $this->assertSame($expected, $closure());
    }
}