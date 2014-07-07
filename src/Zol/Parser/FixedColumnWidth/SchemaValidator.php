<?php

namespace Zol\Parser\FixedColumnWidth;

/**
 * FixedColumnWidth schema validator
 */
class SchemaValidator
{
    /**
     * Validate given schema
     *
     * @param array   $schema
     * @param boolean $thowException
     *
     * @return boolean
     */
    public function validateSchema(array $schema = null, $thowException = false)
    {
        if (is_null($schema)) {
            $schema = $this->schema;
        }

        if (!is_array($schema)) {
            throw new \InvalidArgumentException(sprintf('Schema must be an array, %s given', gettype($schema)));
        }

        $schema = array_merge(Parser::getDefaultSchema(), $schema);

        try {

            // "ignore" section
            $this->checkNullOrArray($schema, 'ignore');
            $this->checkArrayOfInt($schema, 'ignore');

            // "header" section
            $this->checkNullOrArray($schema, 'header');
            if (is_array($schema['header'])) {
                $this->checkFieldLengthArray($schema['header'], 'header');
            }

            // "entry" section
            $this->checkEmpty($schema, 'entry');
            $this->checkArray($schema['entry'], 'entry');
            foreach ($schema['entry'] as $entry) {
                $this->checkArray($entry, 'entry line');
                $this->checkFieldLengthArray($entry, 'entry line');
            }

            // boolean options
            $this->checkBoolean($schema, 'header-as-field-name');
            $this->checkBoolean($schema, 'ignore-empty-lines');

        } catch (SchemaValidationException $e) {
            if ($thowException) {
                throw $e;
            }

            return false;
        }

        return true;
    }


    /**
     * Check if given schema contains an array or null
     *
     * @param array  $schema
     * @param string $index
     */
    protected function checkNullOrArray($schema, $index)
    {
        if (!is_array($schema[$index]) && !is_null($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '"%s" option must be null or an array, %s given',
                $index,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema contains boolean value
     *
     * @param array  $schema
     * @param string $index
     */
    protected function checkBoolean($schema, $index)
    {
        if (!is_bool($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '"%s" option must be boolean, %s given',
                $index,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema contains an array of int
     *
     * @param array  $schema
     * @param string $index
     */
    protected function checkArrayOfInt($schema, $index)
    {
        if (is_array($schema[$index])) {
            foreach ($schema[$index] as $lineNumber) {
                if (!is_int($lineNumber)) {
                    throw new SchemaValidationException(sprintf(
                        '"%s" array option should only contains integer, %s found',
                        $index,
                        gettype($lineNumber)
                    ));
                }
            }
        }
    }

    /**
     * Check if given schema is not empty
     *
     * @param array  $schema
     * @param string $index
     */
    protected function checkEmpty($schema, $index)
    {
        if (empty($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '"%s" option is required et must not be empty',
                $index
            ));
        }
    }

    /**
     * Check if given schema is an array
     *
     * @param array  $array
     * @param string $identifier
     */
    protected function checkArray($array, $identifier)
    {
        if (!is_array($array)) {
            throw new SchemaValidationException(sprintf(
                '%s must be an array, %s found',
                $identifier,
                gettype($array)
            ));
        }
    }

    /**
     * Check if given array contains only field definitions
     *
     * @param array  $array
     * @param string $identifier
     */
    protected function checkFieldLengthArray($array, $identifier)
    {
        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                throw new SchemaValidationException(sprintf(
                    '%s definition should only contains string keys, %s found',
                    $identifier,
                    gettype($key)
                ));
            }

            if (!is_int($value)) {
                throw new SchemaValidationException(sprintf(
                    '%s definition should only contains integer values, %s found',
                    $identifier,
                    gettype($value)
                ));
            }
        }
    }

}
