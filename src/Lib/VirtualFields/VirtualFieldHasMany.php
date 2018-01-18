<?php

namespace Mapper\Lib\VirtualFiedls;


use Illuminate\Support\Str;

class VirtualFieldHasMany extends VirtualFieldHasOne
{

    public static function getType(): string
    {
        return 'hasMany';
    }

    protected function getMethodModePrefix($mode)
    {
        if ($mode === self::PREFIX_GET_METHODS) {
            return 'list';
        } else {
            return 'add';
        }
    }

    public function getGetMethodData()
    {
        $getData = parent::getGetMethodData();
        if ($this->hasReferClass()) {
            $getData['type'] = $this->getReferredClassName() . '[]';
        }
        return $getData;

    }

    /**
     * Translate the name of the attribute to a method name
     *
     * @param $name
     * @return string
     * @throws \Exception
     */
    protected function makeAMethodName($mode)
    {
        $name = $this->getRelationshipName();
        if ($mode === self::PREFIX_GET_METHODS) {
            $name = Str::plural($name);
        }
        return $this->getMethodModePrefix($mode) . $name;
    }
}