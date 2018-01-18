<?php

namespace Mapper\Workers;


use Carbon\Carbon;
use Mapper\BadMappingException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * Class BaseModel
 * @package App\Model
 * @property int $id
 * @method static $this find($id, $columns = ['*'])
 * @method static $this findOrFail($id, $columns = ['*'])
 * @method static $this first($columns = ['*'])
 * @method static $this|$this[] get($columns = ['*'])
 * @method static $this|$this[] where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static $this|$this[] orWhere($column, $operator = null, $value = null)
 * @method static $this|$this[] with($relations)
 * @method static $this getQuery()
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
abstract class MapperModel extends Model
{
    const MAP_RELATIONSHIP = 'relationships';
    const MAP_SETTERS = 'setters';
    const MAP_GETTERS = 'getters';
    const MAP_MODEL = 'model';

    /**
     * This prefix identify which virtual fields on class map are targetting at
     * relationships. Otherwise, they would be considered simple fields.
     */
    const RELATIONSHIP_PREFIX = 'rel:';
    private static $map = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function loadMap(array $map)
    {
        self::$map = $map;
    }

    /**
     * @param $table
     * @throws BadMappingException
     * @return string
     */
    private static function getModelFor($table)
    {
        if (!isset(self::$map[$table])) {
            throw new BadMappingException('There is no map for: ' . $table);
        }
        $tableMap = self::$map[$table];
        if (!isset($tableMap[self::MAP_MODEL]) || is_null($tableMap[self::MAP_MODEL])) {
            throw new BadMappingException('We cannot determine the model for  the table: ' . $table);
        }
        return $tableMap[self::MAP_MODEL];
    }

    /**
     * @return mixed
     * @throws BadMappingException
     */
    protected static function getMyMap()
    {
        $mapIndex = static::getStaticTable();
        if (!isset(self::$map[$mapIndex])) {
            throw new BadMappingException('Class not mapped on Mapper: ' . $mapIndex);
        }
        return self::$map[$mapIndex];
    }

    private function getClassSetters()
    {
        return self::getMyMap()[self::MAP_SETTERS];
    }

    private function getClassGetters()
    {
        return self::getMyMap()[self::MAP_GETTERS];
    }

    private function getClassRelationships()
    {
        return self::getMyMap()[self::MAP_RELATIONSHIP];
    }

    //region Methods to improve development speed

    /**
     * It handle the methods "magic" to identify get/set
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $parameters)
    {

        if (isset($this->getClassSetters()[$method])) {
            $target = $this->getClassSetters()[$method];
            $attName = self::extractRelName($target) ?? $target;
            $this->{$attName} = $parameters[0];
            return $this;
        } elseif (isset($this->getClassGetters()[$method])) {
            $target = $this->getClassGetters()[$method];
            $attName = self::extractRelName($target) ?? $target;
            return $this->{$attName};
        } elseif (isset($this->getClassRelationships()[$method])) {
            $data = $this->getClassRelationships()[$method];
            switch ($data['rel']) {
                case 'belongsTo':
                    return $this->{$data['rel']}(self::getModelFor($data['table']), $data['local_col'],
                        $data['foreign_col']);
                    break;
                case 'hasOne':
                    return $this->{$data['rel']}(self::getModelFor($data['table']), $data['foreign_col'],
                        $data['local_col']);
                    break;
                case 'hasMany':
                    return $this->{$data['rel']}(self::getModelFor($data['table']), $data['foreign_col'],
                        $data['local_col']);
                    break;
                case 'belongsToMany':
                    return $this->{$data['rel']}(self::getModelFor($data['table']), $data['pivot'], $data['local_col'],
                        $data['foreign_col']);
                    break;
                default:
                    throw new \Exception('Invalid relationship setted: ' . $data['rel']);
            }
        } else {
            return parent::__call($method, $parameters);
        }
    }

    private static function extractRelName(string $name)
    {
        echo "\n\nTIRANDO $name";
        $name = preg_replace('/^' . self::RELATIONSHIP_PREFIX . '/', '', $name, -1, $count);
        if ($count === 1) {

            echo " = FICA " . $name;
            return $name;
        } else {
            echo " = nao mudsa";
            return null;
        }
    }

    public function __get($key)
    {
        if (isset($this->getClassRelationships()[$key])) {
            $data = $this->getClassRelationships()[$key];
            switch ($data['rel']) {
                case 'belongsTo':
                case 'hasOne':
                    return $this->{$key}()->first();
                    break;
                case 'hasMany':
                case 'belongsToMany':
                    return $this->{$key}()->get();
                    break;
                default:
                    throw new \Exception('Invalid att getted: ' . $data['rel']);
            }

        }
        return parent::__get($key); // TODO: Change the autogenerated stub
    }

    public function __set($key, $value)
    {
        if (isset($this->getClassRelationships()[$key])) {
            $data = $this->getClassRelationships()[$key];
            switch ($data['rel']) {
                case 'hasOne':
                case 'hasMany':
                    echo "$key -> has one ";
                    return $this->{$key}()->save($value);
                    break;
                case 'belongsTo':
                    return $this->{$key}()->associate($value);
                    break;
                case 'belongsToMany':
                    return $this->{$key}()->attach($value->getID());
                    break;
                default:
                    throw new \Exception('Invalid att setted: ' . $data['rel']);
            }
        }
        parent::__set($key, $value); // TODO: Change the autogenerated stub
    }

    //endregion

    /**
     *
     * It is useful when a child class do not have to be mapped because its mom is the real model.
     * Use get_called_class() to identify if a child or a mather has been called.
     *
     * self::class === get_called_class() on mother class ignore the mom
     * self::class !== get_called_class() on mother class ignore the children
     *
     * @return bool
     */
    public static function ignore()
    {
        return false;
    }


    public static function getStaticTable()
    {
        $reflectionClass = new ReflectionClass(static::class);
        if (isset($reflectionClass->getDefaultProperties()['table'])) {
            return $reflectionClass->getDefaultProperties()['table'];
        } else {
            return str_replace('\\', '', Str::snake(Str::plural(class_basename(static::class))));
        }
    }

    /**
     * @todo Bug fix: this logic only works from the perspective of a belongsTo because the $fieldName is a
     * foreign key, such as user_id. But it does not works in hasOne/hasMany because the
     * $fieldName may be a ID, or other value without reference.
     *
     * @param string $fieldName
     * @return string
     * @throws \Exception
     */
    public static function formatRelationshipName(string $relName, string $relType)
    {
        switch ($relType) {
            case 'hasOne':
            case 'hasMany':
                return $relName;
                break;
            case 'belongsTo':
            case 'belongsToMany':
                $pattern = config('mapper.fk_field_pattern');
                if (!preg_match($pattern, $relName, $matche)) {
                    throw new \Exception('Foreign field ' . $relName . '.`' . static::getStaticTable() . '` does not match with your 
                default pattern: ' . $pattern);
                } else {
                    return Str::studly($matche['field'] ?? $matche[0]);
                }
            default:
                throw new \Exception('unkown relationship type: ' . $relType);
        }
    }

    public static function relNameByField($tableName, $fieldName)
    {
        return null;
    }
}