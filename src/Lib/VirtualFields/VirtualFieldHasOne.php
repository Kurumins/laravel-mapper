<?php

namespace Mapper\Lib\VirtualFiedls;


use Illuminate\Support\Str;
use Mapper\Lib\MetaTable;


class VirtualFieldHasOne extends VirtualField
{

	public static function getType(): string
	{
		return 'hasOne';
	}

	public function getFkCol(): string
	{
		return $this->relatedFK->getLocalColumns()[0];
	}

	public function getSetMethodData()
	{
		$setData = parent::getSetMethodData();
		if($this->hasReferClass()) {
			$model = $this->getReferredClassName();
			$setData['args'] = $model.' $'.$model;
		}
		// @todo Esse nome tem que ser baseado em outra coisa, pode haver dois campos aponranto para aqui
		$setData['name'] = $this->nameToMethod($this->referredTable->getTableName(), self::PREFIX_SET_METHODS);
		return $setData;
	}

	public function getGetMethodData()
	{
		$getData = parent::getGetMethodData();
		if($this->hasReferClass()) {
			$getData['type'] = $this->getReferredClassName().'|null';
		}
		$getData['name'] = $this->nameToMethod($this->referredTable->getTableName(), self::PREFIX_GET_METHODS);
		$getData['target'] = MetaTable::TARGET_AT_RELATIONSHIP_PREFIX.$this->referredTable->getTableName();
		return $getData;

	}

	protected function formatNameToMethod($fieldName)
	{
		return Str::singular(parent::formatNameToMethod($this->getFkCol()));
	}



}