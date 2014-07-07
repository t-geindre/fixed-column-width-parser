<?php

namespace Zol\Parser\FixedColumnWidth\tests\units;

use mageekguy\atoum\test;
use Zol\Parser\FixedColumnWidth\Parser as Base;


class Parser extends test
{
    public function getBaseInstance()
    {
        return new Base;
    }

    public function testGetDefaultSchema()
    {
        $this
            ->array(Base::getDefaultSchema())
                ->isEqualTo([
                    'ignore' => null,
                    'header' => null,
                    'header-as-field-name' => false,
                    'ignore-empty-lines' => true,
                    'multiple' => false
                ])
        ;
    }

    public function testConstruct()
    {
        $schema = [
            'ignore' => null,
            'header' => null,
            'header-as-field-name' => false,
            'ignore-empty-lines' => true,
            'entry' => [ 'field' => 10 ],
            'multiple' => false
        ];
        $file = uniqid();

        $parser = new Base($schema, $file);

        $this
            ->array($parser->getSchema())
                ->isEqualTo($schema)
            ->string($parser->getFile())
                ->isEqualTo($file)
        ;
    }

    public function testGetterSetter()
    {
        $schema = [
            'ignore' => null,
            'header' => null,
            'header-as-field-name' => false,
            'ignore-empty-lines' => true,
            'entry' => [ 'field' => 10 ],
            'multiple' => false
        ];
        $file = uniqid();
        $strictMode = (bool) rand(0, 1);

        $parser = $this->getBaseInstance()
            ->setFile($file)
            ->setSchema($schema)
        ;

        $this
            ->array($parser->getSchema())
                ->isEqualTo($schema)
            ->string($parser->getFile())
                ->isEqualTo($file)
        ;
    }

    public function testParse()
    {
        $parser = $this->getBaseInstance();

        // Exceptions
        $schema = [
            'ignore' => null,
            'header' => null,
            'header-as-field-name' => false,
            'ignore-empty-lines' => true,
            'multiple' => false
        ];
        $file = __DIR__.'/../fixtures/import.dat';

        $this
            ->exception(function() use($parser, $schema, $file) {
                $parser->parse($file, $schema);
            })
                ->isInstanceOf('\RuntimeException')
                ->hasMessage(sprintf('Unable to to parse "%s" file, "entry" configuration is missing', $file))
            ->array($parser->getSchema())
                ->isEqualTo($schema)
            ->string($parser->getFile())
                ->isEqualTo($file)
        ;

        $schema = array_merge($schema, [
            'multiple' => true,
            'entry' => 10
        ]);

        $this
            ->exception(function() use($parser, $schema, $file) {
                $parser->parse($file, $schema);
            })
                ->isInstanceOf('\RuntimeException')
                ->hasMessage(sprintf('Multiple file parsing require separator definition', $file))
        ;

        // Ignore empty lines
        $schema = [
            'header' => [4, 9, 11, 10],
            'entry' => ['id' => 4, 'name' => 9, 'phone' => 11, 'street' => 10],
            'ignore-empty-lines' => false
        ];

        $this
            ->array($parser->parse($file, $schema))
                ->isEqualTo([
                    'header' => [
                        'id', 'name', 'phone', 'street'
                    ],
                    'entries' => [
                        ['id' => '----', 'name' => '---------', 'phone' => '-----------', 'street' => '----------'],
                        ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
                        ['id' => null, 'name' => null, 'phone' => null, 'street' => null],
                        ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
                    ]
                ])
        ;

        // Standard use case
        $schema = [
            'ignore' => [2],
            'header' => [4, 9, 11, 10],
            'entry' => ['id' => 4, 'name' => 9, 'phone' => 11, 'street' => 10]
        ];

        $this
            ->array($parser->parse($file, $schema))
                ->isEqualTo([
                    'header' => [
                        'id', 'name', 'phone', 'street'
                    ],
                    'entries' => [
                        ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
                        ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
                    ]
                ])
        ;

        // Header as field name
        $schema = [
            'ignore' => [2],
            'header' => [4, 9, 11, 10],
            'entry' => [4, 9, 11, 10],
            'header-as-field-name' => true
        ];

        $this
            ->array($parser->parse($file, $schema))
                ->isEqualTo([
                    'header' => [
                        'id', 'name', 'phone', 'street'
                    ],
                    'entries' => [
                        ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
                        ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
                    ]
                ])
        ;

        // Multiple
        $schema = [
            'ignore' => [2],
            'header' => [4, 9, 11, 10],
            'entry' => [4, 9, 11, 10],
            'header-as-field-name' => true,
            'multiple' => true,
            'separator' => [
                'field' => 4,
                'values' => ['id'],
                'ignore' => false
            ]
        ];

        $file = __DIR__.'/../fixtures/import-multiple.dat';

        $this
            ->array($parser->parse($file, $schema))
                ->isEqualTo([
                    [
                        'header' => ['id', 'name', 'phone', 'street'],
                        'entries' => [
                            ['id' => '123', 'name' => 'john', 'phone' => '0152458652', 'street' => '5th street'],
                            ['id' => '321', 'name' => 'isabelle', 'phone' => '0352158546', 'street' => '4th street']
                        ]
                    ],
                    [
                        'header' => ['id', 'name', 'phone_num', 'street'],
                        'entries' => [
                            ['id' => '123', 'name' => 'john', 'phone_num' => '0152458652', 'street' => '1st street'],
                            ['id' => '321', 'name' => 'isabelle', 'phone_num' => '0352158546', 'street' => '2nd street']
                        ]
                    ],

                ])
        ;
    }
}
