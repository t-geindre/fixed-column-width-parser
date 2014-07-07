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
        'ignore-empty-lines' => true
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
     * @var boolean
     */
    protected $sctrictMode;

    /**
     * @var integer
     */
    protected $currentLineNumber;

    /**
     * @var array
     */
    protected $currentEntryData;

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
     * @param boolean $strictMode
     * @param string  $file
     */
    public function __construct($schema = array(), $strictMode = false, $file = null)
    {
        $this->setSchema($schema);
        $this->setStrictMode($strictMode);

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
     *     // first line is indexed by 0
     *     // optionnal, null by default
     *     'ignore' => [1, 8 , 9],
     *     // Header line, null if missing
     *     // optionnal, null by default
     *     'header' => ['field' => length],
     *     'entry' => [
     *          // Entry can be placed on multiple lines
     *          // each array represent one line
     *          ['field' => length, 'field' => length],
     *          ['field' => length]
     *     ],
     *     // Use header values as entry field names
     *     // If true, entry field names will be replaced with header values
     *     // optionnal, false by default
     *     'header-as-field-name' => false,
     *     // Ignore empty line
     *     // optionnal, true by default
     *     'ignore-empty-lines' => true
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
     * Define strict mode
     *
     * @param boolean $mode
     *
     * @return FixedColumnWidth
     */
    public function setStrictMode($mode)
    {
        $this->strictMode = $mode;

        return $this;
    }

    /**
     * Get strict mode
     *
     * @return boolean
     */
    public function getStrictMode()
    {
        return $this->strictMode;
    }

    /**
     * Parse given file (or previously setted file).
     * If strict mode is enabled, it will throw exception if fields are missing
     * or if extra fields are found
     *
     * @param boolean $strictMode
     * @param string  $file
     * @param array   $schema
     *
     * @return array
     */
    public function parse($strictMode = null, $file = null, $schema = null)
    {
        if (!is_null($sctrictMode)) {
            $this->setStrictMode($strictMode);
        }

        if (!is_null($file)) {
            $this->setFile($file);
        }

        if (!is_null($schema)) {
            $this->setSchema($schema);
        }

        $handle = fopen($this->file, 'r');

        if (!$handle) {
            throw new \RuntimeException(sprintf('Unable to read "%s" file', $this->file));
        }

        $this->headerLine = !is_array($this->schema['header']) ? -1 : 0;
        $this->currentLineNumber = 0;
        $this->currentEntryData = [];
        $this->data = [
            'header' => null,
            'entries' => []
        ];

        while (($line = fgets($handle)) !== false) {

            // Ignored lines
            if (!is_null($this->schema['ignored'])) {
                if (in_array($this->currentLineNumber, $this->schema['ignored'])) {
                    if ($this->currentLineNumber == $this->headerLine) {
                        $this->headerLine++;
                    }
                    continue;
                }
            }

            if ($this->currentLineNumber == $this->headerLine) {
                $this->data['header'] = $this->parseHeader($line);
            } else {
                if (!is_null($data = $this->parseEntry($line))) {
                    $this->data['entries'][] = $data;
                }
            }

            $this->currentLineNumber++;
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

    protected function parseHeader($line)
    {
        $header = [];
        foreach ($this->schema['header'] as $field => $length) {
            $value = $this->parseField($line, $length);
            $header[$field] = $value;
        }

        return $header;
    }

    protected function parseEntry($line)
    {
    }

    protected function parseField(&$line, $length)
    {

    }
}
