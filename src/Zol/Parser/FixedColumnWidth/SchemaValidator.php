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
    public function validateSchema(array $schema, $thowException = false)
    {
        if (is_null($schema)) {
            $schema = $this->schema;
        }

        $schema = array_merge(Parser::getDefaultSchema(), $schema);

        try {

            // "ignore" section
            $this->checkNullOrArray($schema, 'ignore', 'ignore option');
            if (is_array($schema['ignore'])) {
                $this->checkArrayOfInt($schema, 'ignore', 'ignore option');
            }

            // "header" section
            $this->checkNullOrArray($schema, 'header', 'header definition');
            if (is_array($schema['header'])) {
                $this->checkArrayOfInt($schema, 'header', 'header defnition');
            }

            // "entry" section
            $this->checkEmpty($schema, 'entry', 'entry definition');
            $this->checkArray($schema, 'entry', 'entry defnition');
            $this->checkArrayOfInt($schema, 'entry', 'entry defnition');

            // boolean options
            $this->checkBoolean($schema, 'header-as-field-name', 'header-as-field-name option');
            $this->checkBoolean($schema, 'ignore-empty-lines', 'ignore-empty-lines option');
            $this->checkBoolean($schema, 'multiple', 'multiple option');

            // "separator" section
            if ($schema['multiple']) {
                $this->checkEmpty($schema, 'separator', 'multiple option is true, separator definition');
                $this->checkArray($schema, 'separator', 'serapator definition');

                $this->checkEmpty($schema['separator'], 'field', 'serapator field definition');
                $this->checkInt($schema['separator'], 'field', 'serapator field definition');

                $this->checkEmpty($schema['separator'], 'values', 'serapator field values definition');
                $this->checkArray($schema['separator'], 'values', 'serapator field values definition');

                $this->checkEmpty($schema['separator'], 'ignore', 'serapator ignore line option');
                $this->checkBoolean($schema['separator'], 'ignore', 'serapator ignore line option');
            }

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
     * @param string $identifier
     */
    protected function checkNullOrArray($schema, $index, $identifier)
    {
        if (!is_array($schema[$index]) && !is_null($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '%s must be null or an array, %s given',
                $identifier,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema contains boolean value
     *
     * @param array  $schema
     * @param string $index
     * @param string $identifier
     */
    protected function checkBoolean($schema, $index, $identifier)
    {
        if (!is_bool($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '%s must be boolean, %s given',
                $identifier,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema contains string value
     *
     * @param array  $schema
     * @param string $index
     * @param string $identifier
     */
    protected function checkString($schema, $index, $identifier)
    {
        if (!is_bool($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '%s must be string, %s given',
                $identifier,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema contains an array of int
     *
     * @param array  $schema
     * @param string $index
     * @param string $identifier
     */
    protected function checkArrayOfInt($schema, $index, $identifier)
    {
        if (is_array($schema[$index])) {
            foreach ($schema[$index] as $intValue) {
                if (!is_int($intValue)) {
                    throw new SchemaValidationException(sprintf(
                        '%s should only contains integer, %s found',
                        $identifier,
                        gettype($intValue)
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
     * @param string $identifier
     */
    protected function checkEmpty($schema, $index, $identifier)
    {
        if (!isset($schema[$index]) || (empty($schema[$index]) && $schema[$index] !== false)) {
            throw new SchemaValidationException(sprintf(
                '%s is required et must not be empty',
                $identifier
            ));
        }
    }

    /**
     * Check if given schema is an array
     *
     * @param array  $schema
     * @param string $index
     * @param string $identifier
     */
    protected function checkArray($schema, $index, $identifier)
    {
        if (!is_array($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '%s must be an array, %s given',
                $identifier,
                gettype($schema[$index])
            ));
        }
    }

    /**
     * Check if given schema is an integer
     *
     * @param array  $schema
     * @param string $index
     * @param string $identifier
     */
    protected function checkInt($schema, $index, $identifier)
    {
        if (!is_int($schema[$index])) {
            throw new SchemaValidationException(sprintf(
                '%s must be an integer, %s given',
                $identifier,
                gettype($schema[$index])
            ));
        }
    }
}
