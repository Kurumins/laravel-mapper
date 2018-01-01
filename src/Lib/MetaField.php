<?php

namespace Mapper\Lib;


use Carbon\Carbon;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;

class MetaField
{
    /**
     * @var Column
     */
    private $column;

    const METHOD_SET_MODE = 'set';

    const METHOD_GET_MODE = 'get';

    /**
     * MetaAttribute constructor.
     * @param Column $column
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function getFieldName()
    {
        return $this->column->getName();
    }

    public function getDoctrineColunm()
    {
        return $this->column;
    }

    /**
     * @param Column $column
     * @return self
     */
    public function setDoctrineColunm(Column $column):self
    {
        $this->column = $column;
        return $this;
    }


    public function getSetMethodData()
    {
        $args = $this->getPhpFieldType().' $'.$this->getPhpAttributeName();
        if(!$this->column->getNotnull()) {
            $args .= ' = null';
        }
        $name = $this->nameToMethod($this->getFieldName(), self::METHOD_SET_MODE);
        return [
            'type' => '$this',
            'name' => $name,
            'args' => $args,
            'target' => $this->getFieldName(),
            'nullable' => $this->column->getNotnull()
        ];
    }


    public function getGetMethodData()
    {
        $name = $this->nameToMethod($this->getFieldName(), self::METHOD_GET_MODE);
        $returnType = $this->getPhpFieldType();
        if(!$this->column->getNotnull()) {
            $returnType .= '|null';
        }

        return [
            'type' => $returnType,
            'name' => $name,
            'args' => null,
            'target' => $this->getFieldName(),
            'nullable' => $this->column->getNotnull()
        ];
    }

    /**
     * Translate the name of the attribute to a method name
     *
     * @param $name
     * @return string
     */
    protected function nameToMethod($name, $mode)
    {
        return $this->getMethodModePrefix($mode) . $this->formatNameToMethod($name);
    }

    protected function formatNameToMethod($fieldName)
    {
        return studly_case($fieldName);
    }

    protected function getMethodModePrefix($mode)
    {
        if($mode === self::METHOD_GET_MODE) {
			/**
			 * @todo turn it customizable
			 */
            if($this->getPhpFieldType() === 'bool') {
                return 'is';
            }
            return 'get';
        } else {
            return 'set';
        }
    }


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

    protected function getPhpFieldType()
    {
        return $this->transCastMysqlToPhp($this->column->getType());
    }

    public function getPhpAttributeName()
    {
        return Str::camel($this->getFieldName());
    }

    public function getClassDependencies() :?string
	{
		if($this->getPhpFieldType() === 'Carbon') {
			return Carbon::class;
		} else return null;
	}

    public function getRelationshipDefinition():?array
    {
        return null;
    }
}