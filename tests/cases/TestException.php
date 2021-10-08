<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM\TestCase;

use MensBeam\HTML\DOM\{
    DOMException,
    Exception
};
use MensBeam\HTML\Parser;


/**
 * @covers \MensBeam\HTML\DOM\DOMException
 * @covers \MensBeam\HTML\DOM\Exception
 */
class TestException extends \PHPUnit\Framework\TestCase {
    public function provideConstructorFailures(): iterable {
        return [
            [ function() {
                $d = new DOMException(2112);
            }, Exception::INVALID_CODE ],
            [ function() {
                $d = new Exception(2112);
            }, Exception::INVALID_CODE ],
            [ function() {
                throw new DOMException(DOMException::NOT_FOUND, 'FAIL');
            }, Exception::INCORRECT_PARAMETERS_FOR_MESSAGE ],
            [ function() {
                throw new Exception(Exception::UNKNOWN_ERROR, 'FAIL');
            }, Exception::INCORRECT_PARAMETERS_FOR_MESSAGE ],
        ];
    }

    /**
     * @dataProvider provideConstructorFailures
     * @covers \MensBeam\HTML\DOM\DOMException::__construct
     * @covers \MensBeam\HTML\DOM\Exception::__construct
     */
    public function testConstructorFailures(\Closure $closure, int $errorCode): void {
        $this->expectException(Exception::class);
        $this->expectExceptionCode($errorCode);
        $closure();
    }
}