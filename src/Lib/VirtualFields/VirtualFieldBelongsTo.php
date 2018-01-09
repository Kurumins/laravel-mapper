<?php

namespace Mapper\Lib\VirtualFiedls;


use Illuminate\Support\Str;
use Mapper\Lib\MetaTable;
use Mapper\Workers\MapperModel;


class VirtualFieldBelongsTo extends VirtualField
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
			$getData['type'] = $this->getReferredClassName();
			if($getData['nullable']) {
				$getData['type'] .= '|null';
			}
		}
		$getData['name'] = $this->makeAMethodName(self::PREFIX_GET_METHODS);
		return $getData;
	}

    protected static function formatNameToMethod(string $name)
    {
        return Str::singular(VirtualField::formatNameToMethod($name));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getRelationshipName(): string
    {
        if($this->customRelName) {
            return $this->customRelName;
        } else {
            /** @var MapperModel $className */
            $className = $this->getReferredClass();
            return $className::formatRelationshipName($this->getFieldName(), self::getType());
        }
    }
}