<?php
/**
 * @license MIT
 * Copyright 2017 Dustin Wilson, J. King, et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;
use MensBeam\HTML\DOM\Exception;


/** @covers \MensBeam\HTML\DOM\Exception */
class TestDOMException extends \PHPUnit\Framework\TestCase {
    public function provideConstructorFailures(): iterable {
        return [
            [ function() {
                $d = new Exception(2112);
            }, Exception::INVALID_CODE ],
            [ function() {
                throw new Exception(Exception::UNKNOWN_ERROR, 'FAIL');
            }, Exception::INCORRECT_PARAMETERS_FOR_MESSAGE ]
        ];
    }

    /**
     * @dataProvider provideConstructorFailures
     * @covers \MensBeam\HTML\DOM\Exception::__construct
     */
    public function testConstructorFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}