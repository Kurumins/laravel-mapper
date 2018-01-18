<?php

namespace Mapper\Lib\VirtualFiedls;

use Mapper\Lib\MetaField;
use Mapper\Lib\MetaTable;
use Doctrine\DBAL\Schema\ForeignKeyConstraint as Fk;
use Doctrine\DBAL\Schema\Column;
use Mapper\Workers\MapperModel;

/**
 * This class represent a virtual field in a table. For instance, if a table has a relationship with another one, than
 * it has a virtual field. If the table has a foreign key that creates this relationship, that "plain" field become a
 * VirtualField. On the other hand, if the table is a target of a foreign key from another table, than a new field
 * (virtual) is add to this table.
 * Example: a table user may earn three virtual fields just due its 'id' field is a target of three foreign keys. So,
 * the MetaTable object for this table, will have the 'id' MetaField and other three Virtual Field.
 *
 * @package Mapper\Lib\VirtualFiedls
 */
abstract class VirtualField extends MetaField
{
    /**
     * @var MetaTable
     */
    protected $referredTable;

    /**
     * @var string Class name if the there is a model defined to the referredTable
     */
    protected $referredClass;

    /**
     * @var string Custom relationshipt name forced by the model for some reason (e.g. multiple FKs)
     */
    protected $customRelName;

    /**
     * @var Fk
     */
    protected $relatedFK;

    /**
     * VirtualField constructor.
     *
     * @param Column $column
     * @param MetaTable $referredTable
     * @param Fk $relatedFK
     */
    public function __construct(Column $column, MetaTable $referredTable, Fk $relatedFK)
    {
        parent::__construct($column);
        $this->referredTable = $referredTable;
        $this->relatedFK = $relatedFK;
    }

    /**
     * @param string $fullClassName
     * @return VirtualField
     */
    public function setReferredClass(string $fullClassName, ?string $customRelName = null): self
    {
        $this->referredClass = $fullClassName;
        $this->customRelName = $customRelName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSetMethodData()
    {
        $setData = parent::getSetMethodData();
        $setData['target'] = MapperModel::RELATIONSHIP_PREFIX . $this->getRelationshipName();
        return $setData;
    }

    /**
     * @inheritdoc
     */
    public function getGetMethodData()
    {
        $getData = parent::getGetMethodData();
        $getData['target'] = MapperModel::RELATIONSHIP_PREFIX . $this->getRelationshipName();
        return $getData;
    }

    /**
     * @return null|string The full referred class name.
     */
    public function getReferredClass(): ?string
    {
        return $this->referredClass;
    }

    /**
     * @todo Is it necessary? Do not all VirutalField have a refferred class?
     *
     * @return bool True if this field has a referred class
     */
    public function hasReferClass(): bool
    {
        return isset($this->referredClass);
    }

    public abstract static function getType(): string;

    public abstract function getFkCol(): string;

    public function getRelationshipName(): string
    {
        if ($this->customRelName) {
            return $this->customRelName;
        } else {
            return $this->getFieldName();
        }
    }

    public function getRelationshipDefinition(): ?array
    {
        return [
            $this->getRelationshipName() => [
                'table' => $this->referredTable->getTableName(),
                'rel' => static::getType(),
                'local_col' => $this->getFieldName(),
                'foreign_col' => $this->getFkCol()
            ]
        ];
    }


    /**
     * @return string
     */
    protected function getReferredClassName()
    {
        return substr($this->referredClass, strrpos($this->referredClass, '\\') + 1);
    }

    public function getClassDependencies(): ?string
    {
        if ($this->hasReferClass()) {
            return $this->getReferredClass();
        } else {
            return null;
        }
    }

    /**
     * Translate the name of the attribute to a method name
     *
     * @param $mode
     * @return string
     * @throws \Exception
     */
    protected function makeAMethodName($mode)
    {
        $name = $this->getRelationshipName();
        return $this->getMethodModePrefix($mode) . static::formatNameToMethod($name);
    }
}