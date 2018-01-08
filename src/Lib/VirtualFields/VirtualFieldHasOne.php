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
		$arg = $this->formatNameToMethod($this->getFkCol());
		if($this->hasReferClass()) {
			$model = $this->getReferredClassName();
			$setData['args'] = $model.' $'.$arg;
		}
		$setData['name'] = $this->nameToMethod($this->getFkCol(), self::PREFIX_SET_METHODS);
		return $setData;
	}

	public function getGetMethodData()
	{
		$getData = parent::getGetMethodData();
		if($this->hasReferClass()) {
			$getData['type'] = $this->getReferredClassName().'|null';
		}
		$getData['name'] = $this->nameToMethod($this->getFkCol(), self::PREFIX_GET_METHODS);
		return $getData;
	}

	protected function formatNameToMethod($fieldName)
	{
		return Str::singular(parent::formatNameToMethod($fieldName));
	}



}