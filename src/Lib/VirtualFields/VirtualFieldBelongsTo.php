<?php

namespace Mapper\Lib\VirtualFiedls;


use Illuminate\Support\Str;
use Mapper\Lib\MetaTable;


class VirtualFieldBelongsTo extends VirtualFieldHasOne
{

	public static function getType(): string
	{
		return 'belongsTo';
	}

	public function getFkCol(): string
	{
		return $this->relatedFK->getForeignColumns()[0];
	}
	public function getSetMethodData()
	{
		$setData = parent::getSetMethodData();
		$arg = $this->formatNameToMethod($this->getFieldName());
		if($this->hasReferClass()) {
			$model = $this->getReferredClassName();
			$setData['args'] = $model.' $'.$arg;
		}
		$setData['name'] = $this->nameToMethod($this->getFieldName(), self::PREFIX_SET_METHODS);
		return $setData;
	}

	public function getGetMethodData()
	{
		$getData = parent::getGetMethodData();
		if($this->hasReferClass()) {
			$getData['type'] = $this->getReferredClassName();
			if($getData['nullable']) {
				$getData['type'] .= '|null';
			}
		}
		$getData['name'] = $this->nameToMethod($this->getFieldName(), self::PREFIX_GET_METHODS);
		return $getData;
	}

    protected function formatNameToMethod($fieldName)
    {
        return Str::singular(VirtualField::formatNameToMethod($fieldName));
    }

}