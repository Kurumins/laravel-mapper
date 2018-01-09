<?php

namespace Mapper\Lib\VirtualFiedls;


use Illuminate\Support\Str;
use Mapper\Lib\MetaTable;
use Mapper\Workers\MapperModel;


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
		$arg = $this->getRelationshipName();
		if($this->hasReferClass()) {
			$model = $this->getReferredClassName();
			$setData['args'] = $model.' $'.$arg;
		}
		$setData['name'] = $this->makeAMethodName(self::PREFIX_SET_METHODS);
		return $setData;
	}

	public function getGetMethodData()
	{
		$getData = parent::getGetMethodData();
		if($this->hasReferClass()) {
			$getData['type'] = $this->getReferredClassName().'|null';
		}
		$getData['name'] = $this->makeAMethodName(self::PREFIX_GET_METHODS);
		return $getData;
	}

	protected static function formatNameToMethod(string $name)
	{
		return Str::singular($name);
	}

    /**
     * @return string
     * @throws \Exception
     */
    public function getRelationshipName(): string
    {
        if($this->customRelName) {
            return parent::getRelationshipName();
        } else {
            /** @var MapperModel $className */
            $className = $this->getReferredClass();
            echo "\n== : ".self::getType();
            return $className::formatRelationshipName($this->getReferredClassName(), self::getType());
        }
    }
}