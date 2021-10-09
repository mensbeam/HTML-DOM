<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    Document,
    Exception,
    MagicProperties
};


/** @covers \MensBeam\HTML\DOM\MagicProperties */
class TestMagicProperties extends \PHPUnit\Framework\TestCase {
    public function provideFailures(): iterable {
        return [
            [ function() {
                $d = new Document();
                $d->omgWTFBBQ;
            }, Exception::NONEXISTENT_PROPERTY ],
            [ function() {
                $d = new Document();
                $d->omgWTFBBQ = 'ook';
            }, Exception::NONEXISTENT_PROPERTY ],
            [ function() {
                $d = new Document();
                $d->xpath = 'ook';
            }, Exception::READONLY_PROPERTY ],
            [ function() {
                $d = new Document();
                unset($d->xpath);
            }, Exception::READONLY_PROPERTY ]
        ];
    }

    /**
     * @dataProvider provideFailures
     * @covers \MensBeam\HTML\DOM\MagicProperties::__get
     * @covers \MensBeam\HTML\DOM\MagicProperties::__set
     * @covers \MensBeam\HTML\DOM\MagicProperties::__unset
     */
    public function testFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }

    /** @covers \MensBeam\HTML\DOM\MagicProperties::__isset */
    public function testIsset(): void {
        $d = new Document();
        $this->assertTrue(isset($d->body));
    }

    /** @covers \MensBeam\HTML\DOM\MagicProperties::__unset */
    public function testUnset(): void {
        // Nothing allows setting values to null yet, so make one
        $d = new class {
            use MagicProperties;
            protected ?string $_ook = 'ook';


            protected function __get_ook(): ?string {
                return $this->_ook;
            }

            protected function __set_ook(?string $value): void {
                $this->_ook = $value;
            }
        };

        unset($d->ook);
        $this->assertNull($d->ook);
    }
}