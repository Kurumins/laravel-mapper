<?php

namespace Mapper\Lib;

use Carbon\Carbon;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;

/**
 * This object represents a half path from a table field to a class attribute.
 *
 * @package Mapper\Lib
 */
class MetaField
{
    /**
     * @var Column
     */
    private $column;

    /**
     * String prefix used for get/set methods of a simple field
     */
    const PREFIX_SET_METHODS = 'set';

    const PREFIX_GET_METHODS = 'get';

    /**
     * MetaAttribute constructor.
     * @param Column $column
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->column->getName();
    }

    /**
     * @return Column
     */
    public function getDoctrineColunm()
    {
        return $this->column;
    }

    /**
     * @param Column $column
     * @return self
     */
    public function setDoctrineColunm(Column $column): self
    {
        $this->column = $column;
        return $this;
    }

    /**
     * Creates an array with all data necessary to define this field "set" method.
     *
     * @return array
     */
    public function getSetMethodData()
    {
        $args = $this->getPhpFieldType() . ' $' . $this->getPhpAttributeName();
        if (!$this->column->getNotnull()) {
            $args .= ' = null';
        }
        return [
            'type' => '$this',
            'name' => $this->makeAMethodName(self::PREFIX_SET_METHODS),
            'args' => $args,
            'target' => $this->getFieldName(),
            'nullable' => $this->column->getNotnull()
        ];
    }

    /**
     * Creates an array with all data necessary to define this field "get" method.
     *
     * @return array
     */
    public function getGetMethodData()
    {
        $returnType = $this->getPhpFieldType();
        if (!$this->column->getNotnull()) {
            $returnType .= '|null';
        }
        return [
            'type' => $returnType,
            'name' => $this->makeAMethodName(self::PREFIX_GET_METHODS),
            'args' => null,
            'target' => $this->getFieldName(),
            'nullable' => !$this->column->getNotnull()
        ];
    }

    /**
     * Makes a name for a get/set method for this field.
     *
     * @param string $mode One of the self::PREFIX_[*]_METHODS constant names.
     * @return string
     */
    protected function makeAMethodName($mode)
    {
        return $this->getMethodModePrefix($mode) . static::formatNameToMethod($this->getFieldName());
    }

    /**
     * @param string $name
     * @return string
     */
    protected static function formatNameToMethod(string $name)
    {
        return studly_case($name);
    }

    /**
     * Gets the prefixed based the mode (set or get).
     *
     * @param string $mode One of the self::PREFIX_[*]_METHODS constant names.
     * @return string
     */
    protected function getMethodModePrefix($mode)
    {
        if ($mode === self::PREFIX_GET_METHODS) {
            /**
             * @todo turn it customizable
             */
            if ($this->getPhpFieldType() === 'bool') {
                return 'is';
            }
            return 'get';
        } else {
            return 'set';
        }
    }

    /**
     * Converts a mysql fild type name to a PHP type.
     *
     * @param string $type The mysql field type
     * @return string The equivalent PHP type
     */
    protected function transCastMysqlToPhp($type)
    {
        $type = strtolower(str_replace(['(', ')'], '', $type));
        switch ($type) {
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'float':
            case 'double':
            case 'decimal':
            case 'year':
                return 'int';
            case 'bit':
            case 'tinyint':
            case 'boolean':
                return 'bool';
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'binary':
            case 'varbinary':
            case 'tinyblob':
            case 'blob':
            case 'mediumblob':
            case 'longblob':
            case 'enum':
                return 'string';
            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
                return 'Carbon';
            default:
                return $type;
        }
    }

    /**
     * @return string The PHP type for this field
     */
    protected function getPhpFieldType()
    {
        return $this->transCastMysqlToPhp($this->column->getType());
    }

    /**
     * @return string The name of the field converted to a PHP attribute.
     */
    public function getPhpAttributeName()
    {
        return Str::camel($this->getFieldName());
    }

    /**
     * Gets the dependency class for this field (if exists)
     *
     * @return null|string A full class name
     */
    public function getClassDependencies(): ?string
    {
        if ($this->getPhpFieldType() === 'Carbon') {
            return Carbon::class;
        } else {
            return null;
        }
    }

    /**
     * Useless for this class, but it is here to make this class fully compatible with VirutalField
     *
     * @todo Remove this method without breaking the compatibility.
     * @return array|null
     */
    public function getRelationshipDefinition(): ?array
    {
        return null;
    }
}