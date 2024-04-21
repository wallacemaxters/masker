<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Throwable;
use WallaceMaxters\Masker\Masker;
use WallaceMaxters\Masker\MaskException;
use WallaceMaxters\Masker\UnmaskException;

class MaskerTest extends TestCase
{
    public function testMask()
    {
        $masker = new Masker();

        foreach ([
            ['31995451199', '(31) 99545-1199', '(00) 00000-0000'],
            ['31995451199', '31995451199', '(00) 000000000000000000000'],
            ['31150150', '31.150-150', '00.000-000'],
            [null, '', '(00) 0000-0000']
        ] as [$value, $expected, $mask]) {

            $this->assertEquals($expected, $masker->mask($value, $mask), "Ao usar o valor $value é esperado que a máscara $mask retorne $expected");
        }
    }

    public function testUmask()
    {
        $masker = new Masker();

        $this->assertEquals(
            '31995451192', 
            $masker->unmask('(31) 99545-1192', '(00) 00000-0000')
        );

        $this->assertEquals(
            'ABC1234', 
            $masker->unmask('A-BC-1234', 'A-AA-0000')
        );


        $this->assertNull(
            $masker->unmask('ABC', 'A-AA-00000')
        );
    }

    public function testUmaskException()
    {
        $masker = new Masker(
            enableExceptions: true
        );

        $this->assertEquals(
            '31995451192', 
            $masker->unmask('(31) 99545-1192', '(00) 00000-0000')
        );

        $this->assertEquals(
            'ABC1234', 
            $masker->unmask('A-BC-1234', 'A-AA-0000')
        );

        try {
            $masker->unmask('ABC', 'A-AA-00000');
            throw new Exception('Unexpected');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnmaskException::class, $e);
        }
    }

    public function testMaskException()
    {
        $masker = new Masker(
            enableExceptions: true
        );

      
        try {
            $masker->mask('ABC', 'A-AA-00000');
            throw new Exception('Unexpected');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MaskException::class, $e);
        }
    }
}