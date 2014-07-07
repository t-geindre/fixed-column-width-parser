<?php

namespace Zol\Parser\FixedColumnWidth;

/**
 * FixedColumnWidth file parser
 */
class Parser
{
    /**
     * Default schema values
     */
    protected static $defaultSchema = [
        'ignore' => null,
        'header' => null,
        'header-as-field-name' => false,
        'ignore-empty-lines' => true,
        'multiple' => false
    ];

    /**
     * @var string
     */
    protected $file;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var integer
     */
    protected $currentLineNumber;

    /**
     * @var integer
     */
    protected $headerLine;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array   $schema
     * @param string  $file
     */
    public function __construct($schema = array(), $file = null)
    {
        $this->setSchema($schema);

        if (!is_null($file)) {
            $this->setFile($file);
        }
    }

    /**
     * Define file to parse
     *
     * @param string $file
     *
     * @return FixedColumnWidth
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get previously defined file to parse
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Define file schema
     * [
     *     // Ignored lines, null if none
     *     // first line is indexed by 1
     *     // optionnal, null by default
     *     'ignore' => [1, 8 , 9],
     *     // Header line, null if missing
     *     // optionnal, null by default
     *     'header' => ['field' => length],
     *     // Define entry schema
     *     // required
     *     'entry' => ['field' => length, 'field' => length],
     *     // Use header values as entry field names
     *     // If true, entry field names will be replaced with header values
     *     // optionnal, false by default
     *     'header-as-field-name' => false,
     *     // Ignore empty line
     *     // optionnal, true by default
     *     'ignore-empty-lines' => true,
     *     // Multiple in one
     *     // If true, you must define separator
     *     // optionnal, default false,
     *     'multiple' => false,
     *     // Separator, only used if multiple is true
     *     // define files separator
     *     'separator' => [
     *         'field' => length, // Separator field
     *         'values' => [ 'value', 'value'], // Field values considered as separator
     *         'ignore' => true // Ignore separation line
     *     ]
     * ]
     *
     * @param array $schema
     *
     * @return FixedColumnWidth
     */
    public function setSchema(array $schema)
    {
        $this->schema = array_merge(self::getDefaultSchema(), $schema);

        return $this;
    }

    /**
     * Get previously defined schema
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Parse given file (or previously setted file).
     *
     * @param string  $file
     * @param array   $schema
     *
     * @return array
     */
    public function parse($file = null, $schema = null)
    {
        if (!is_null($file)) {
            $this->setFile($file);
        }

        if (!is_null($schema)) {
            $this->setSchema($schema);
        }

        $handle = fopen($this->file, 'r');

        if (!isset($this->schema['entry'])) {
            throw new \RuntimeException(sprintf(
                'Unable to to parse "%s" file, "entry" configuration is missing',
                $this->file
            ));
        }

        if ($this->schema['multiple'] && empty($this->schema['separator'])) {
            throw new \RuntimeException('Multiple file parsing require separator definition');
        }

        if (!$handle) {
            throw new \RuntimeException(sprintf('Unable to read "%s" file', $this->file));
        }

        $this->headerLine = !is_array($this->schema['header']) ? -1 : 1;
        $this->currentLineNumber = 0;
        $this->data = [];

        while (($line = fgets($handle)) !== false) {

            $this->currentLineNumber++;

            // Ignored lines
            if (!is_null($this->schema['ignore'])) {
                if (in_array($this->currentLineNumber, $this->schema['ignore'])) {
                    if ($this->currentLineNumber == $this->headerLine) {
                        $this->headerLine++;
                    }
                    continue;
                }
            }

            // Empty line
            $line = trim($line);
            if (empty($line) && $this->schema['ignore-empty-lines']) {
                continue;
            }

            // Multiple
            if ($this->schema['multiple']) {
                $dataCount = count($this->data);

                if (!$dataCount) {
                    unset($data);
                    $data = [];
                    $this->data[] = &$data;
                }

                if ($this->isSeparationLine($line, $this->schema['separator'])) {
                    if ($dataCount) {
                        unset($data);
                        $data = [];
                        $this->data[] = &$data;
                        $this->headerLine = $this->currentLineNumber;

                        if (is_array($this->schema['ignore'])) {

                            $currentLineNumber = $this->currentLineNumber;

                            $this->schema['ignore'] = array_map(
                                function($lineNumber) use($currentLineNumber) {
                                    return $lineNumber + $currentLineNumber - 1;
                                },
                                $this->schema['ignore']
                            );
                        }
                    }

                    if ($this->schema['separator']['ignore']) {
                        $this->headerLine++;
                        continue;
                    }

                }

            } else {
                $data = &$this->data;
            }


            // Parse line
            if ($this->currentLineNumber == $this->headerLine) {
                $data['header'] = $this->parseLine($line, $schema['header']);
            } else {
                $data['entries'][] = $this->parseLine(
                    $line,
                    $schema['entry'],
                    $this->schema['header-as-field-name'] && isset($data['header']) ? $data['header'] : null
                );
            }
        }

        fclose($handle);

        return $this->data;
    }

    /**
     * Get default schema format
     *
     * @return array
     */
    public static function getDefaultSchema()
    {
        return self::$defaultSchema;
    }

    protected function parseLine($line, array $schema, array $fieldNames = null)
    {
        $data = [];
        foreach ($schema as $field => $length) {
            $data[$field] = $this->parseField($line, $length);
        }

        if (!is_null($fieldNames)) {
            $data = array_combine($fieldNames, $data);
        }

        return $data;
    }

    protected function parseField(&$line, $length, $remove = true)
    {
        $field = substr($line, 0, min($length, strlen($line)));
        $fieldLength = strlen($field);

        if ($remove) {
            $line = substr($line, $fieldLength);
        }

        $field = trim($field);

        return empty($field) ? null: $field;
    }

    protected function isSeparationLine($line, array $separator)
    {
        return in_array(
            $this->parseField($line, $separator['field'], false),
            $separator['values']
        );
    }
}

