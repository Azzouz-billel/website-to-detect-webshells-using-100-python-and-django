<?php

namespace Symfony\Component\Intl\Tests\Globals;

use PHPUnit\Framework\TestCase;
abstract class AbstractIntlGlobalsTest extends TestCase
{
    public function errorNameProvider()
    {
        return array(array(-129, '[BOGUS UErrorCode]'), array(0, 'U_ZERO_ERROR'), array(1, 'U_ILLEGAL_ARGUMENT_ERROR'), array(9, 'U_PARSE_ERROR'), array(129, '[BOGUS UErrorCode]'));
    }
    public function testGetErrorName($errorCode, $errorName)
    {
        $this->assertSame($errorName, $this->getIntlErrorName($errorCode));
    }
    protected abstract function getIntlErrorName($errorCode);
}
