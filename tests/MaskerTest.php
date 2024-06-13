<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;
use WallaceMaxters\Masker\Masker;
use WallaceMaxters\Masker\MaskException;
use WallaceMaxters\Masker\UnmaskException;

class MaskerTest extends TestCase
{
    #[DataProvider('maskDataProvider')]
    public function testMask(?string $value, string $expected, string $mask)
    {
        $masker = new Masker();

        $this->assertEquals(
            $expected, 
            $masker->mask($value, $mask), 
            "Ao usar o valor $value é esperado que a máscara $mask retorne $expected"
        );

        $this->assertEquals(
            $expected, 
            $masker($value, $mask), 
            "Ao usar o valor $value é esperado que a máscara $mask retorne $expected"
        );
    }

    public static function maskDataProvider()
    {
        return [
            'brazil cell phone'                 => [
                'value' => '31995451199', 
                'expected' => '(31) 99545-1199', 
                'mask' => '(00) 00000-0000'
            ],
            'brazil cell phone not matching'    => [
                'value' => '31995451199', 
                'expected' => '31995451199', 
                '(00) 000000000000000000000'
            ],
            'CEP'                               => [
                'value'    => '31150150', 
                'expected' => '31.150-150', 
                'mask'     => '00.000-000'
            ],
            'with null'                         => [
                'value'    => null, 
                'expected' => '', 
                'mask'     => '(00) 0000-0000'
            ],
            'Peão da Casa'                      => [
                'value'    => 'PeãodaCasaPrópria', 
                'expected' => 'Peão da Casa', 
                'mask'     => 'AAAA AA AAAA'
            ],
            'PIS'                               => [
                'value'    => '06930078232', 
                'expected' => '069.30078.23-2', 
                'mask'     => '000.00000.00-0'
            ],

            // because SPRINTF
            'with % #1'       => [
                'value' => '500',
                'expected' => '500%',
                'mask' => '000%',
            ],

            'with % #2'       => [
                'value' => '%',
                'expected' => '[%]',
                'mask' => '[A]',
            ],


            'cpf' => [
                'value'    => '43466473675',
                'expected' => '434.664.736-75',
                'mask'     => '000.000.000-00',
            ]
        ];
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
            throw new Exception('Unexpected Exception in Test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MaskException::class, $e);
        }

        try {
            $masker('ABC', 'A-AA-00000');
            throw new Exception('Unexpected Exception in Test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MaskException::class, $e);
        }
    }

    #[DataProvider('dynamicDataProvider')]
    public function testDynamicFormat(string $expected, string $value, array $masks)
    {
        $mask = new Masker();

        $this->assertEquals(
            $expected,
            $mask->dynamic($value, $masks)
        );
    }


    /**
     * @see https://www.geradordecpf.org/
     *
     * @return void
     */
    public static function dynamicDataProvider()
    {
        $cpf_or_cnpj = ['000.000.000-00', '00.000.000/0000-00'];

        return [
            'brazilPhone' => [
                '(31) 3545-1100',
                '3135451100',
                ['(00) 0000-0000', '(00) 00000-0000']
            ],
            'brazilCellPhone' => [
                '(31) 99545-1100',
                '31995451100',
                ['(00) 0000-0000', '(00) 00000-0000']
            ],

            'CPF' => [
                '455.222.483-27',
                '45522248327',
                $cpf_or_cnpj
            ],
            'CNPJ' => [
                '68.544.172/0001-60',
                '68544172000160',
                $cpf_or_cnpj
            ]
        ];
    }


    public function testDynamicException()
    {
        $masker = new Masker(
            enableExceptions: true
        );

        try {
            $masker->dynamic('ABC', ['A-AA-00000', 'AA']);
            throw new Exception('Unexpected Exception in Test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MaskException::class, $e);
        }
    }

    public function testDynamicWithoutException()
    {
        $masker = new Masker(
            enableExceptions: false
        );

        $value = $masker->dynamic('ABC', ['A-AA-00000', 'AA-']);

        $this->assertEquals('ABC', $value);
    }


}