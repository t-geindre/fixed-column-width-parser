<?php

namespace Zol\Parser\FixedColumnWidth\tests\units;

use mageekguy\atoum\test;
use Zol\Parser\FixedColumnWidth\SchemaValidator as Base;


class SchemaValidator extends test
{
    public function getBaseInstance()
    {
        return new Base;
    }

    public function testValidateSchemaFailed($schema, $message)
    {
        $validator = $this->getBaseInstance();

        $this
            ->exception(function() use($validator, $schema) {
                $validator->validateSchema($schema, true);
            })
                ->isInstanceOf('Zol\Parser\FixedColumnWidth\SchemaValidationException')
                ->hasMessage($message)
            ->boolean($validator->validateSchema($schema, false))
                ->isFalse()
        ;
    }

    public function testValidateSchemaSuccess()
    {
        $schema = [
            'ignore' => [1, 2, 3],
            'header' => [1, 2, 3],
            'entry' => [1, 2 , 3],
            'header-as-field-name' => true,
            'ignore-empty-lines' => true,
            'multiple' => true,
            'separator' => [
                'field' => 10,
                'values' => ['foo'],
                'ignore' => true
            ]
        ];

        $validator = $this->getBaseInstance();

        $this
            ->boolean($validator->validateSchema($schema, true))
               ->isTrue()
        ;
    }

    public function testValidateSchemaFailedDataProvider()
    {
        return [
            [
                'schema' => ['ignore' => true],
                'message' => 'ignore option must be null or an array, boolean given'
            ],
            [
                'schema' => ['ignore' => [1, 2, 'a']],
                'message' => 'ignore option should only contains integer, string found'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => true
                            ],
                'message' => 'header definition must be null or an array, boolean given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 'a']
                            ],
                'message' => 'header defnition should only contains integer, string found'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3]
                            ],
                'message' => 'entry definition is required et must not be empty'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => true
                            ],
                'message' => 'entry defnition must be an array, boolean given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 'a']
                            ],
                'message' => 'entry defnition should only contains integer, string found'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => 'a'
                            ],
                'message' => 'header-as-field-name option must be boolean, string given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => 'a'
                            ],
                'message' => 'ignore-empty-lines option must be boolean, string given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => 'a'
                            ],
                'message' => 'multiple option must be boolean, string given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => true
                            ],
                'message' => 'serapator definition must be an array, boolean given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'foo' => 'bar'
                                ]
                            ],
                'message' => 'serapator field definition is required et must not be empty'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'field' => 'bar'
                                ]
                            ],
                'message' => 'serapator field definition must be an integer, string given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'field' => 10
                                ]
                            ],
                'message' => 'serapator field values definition is required et must not be empty'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'field' => 10,
                                    'values' => 'foo'
                                ]
                            ],
                'message' => 'serapator field values definition must be an array, string given'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'field' => 10,
                                    'values' => ['foo']
                                ]
                            ],
                'message' => 'serapator ignore line option is required et must not be empty'
            ],
            [
                'schema' => [
                                'ignore' => [1, 2, 3],
                                'header' => [1, 2, 3],
                                'entry' => [1, 2 , 3],
                                'header-as-field-name' => true,
                                'ignore-empty-lines' => true,
                                'multiple' => true,
                                'separator' => [
                                    'field' => 10,
                                    'values' => ['foo'],
                                    'ignore' => 'a'
                                ]
                            ],
                'message' => 'serapator ignore line option must be boolean, string given'
            ],
        ];
    }
}
