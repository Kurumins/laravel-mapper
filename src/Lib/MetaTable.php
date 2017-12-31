<?php
/**
 * Created by IntelliJ IDEA.
 * User: rubens
 * Date: 2017-12-30
 * Time: 10:25 PM
 */

namespace Mapper\Lib;


use Mapper\Workers\MapperModel;

class MetaTable
{
	/**
	 * @var string Db table handled by this instance
	 */
	private $table;

	/**
	 * @var string Full name (including namespace) of the model which represents this table
	 */
	private $fullModelName;

	/**
	 * @var string Full name (including namespace) of the model which represents this table
	 */
	private $namespace;

	/**
	 * @var string Table comments from the BD
	 */
	private $comment;

	/**
	 * @var MetaField[] List of fields found on this table
	 */
	private $fields = [];

	/**
	 * Fields that should be ignored by SET methods
	 * @todo turn it customizable
	 */
	const IGNORE_SET_ATTRIBUTES = ['id', 'createdAt', 'updatedAt'];

	/**
	 * Prefix used on traits names
	 * @todo turn it customizable
	 */
	const TRAIT_NAME_PREFIX = 'Mapper';

	/**
	 * MetaClass constructor.
	 * @param string $tableName
	 */
	public function __construct(string $tableName)
	{
		$this->table = $tableName;
	}

	/**
	 * @return string
	 */
	public function getTableName(): string
	{
		return $this->table;
	}

	/**
	 * @return string
	 */
	public function getFullModelName(): ?string
	{
		return $this->fullModelName;
	}

	/**
	 * @param string $fullModelName
	 * @return MetaTable
	 */
	public function setFullModelName(string $fullModelName) : self
	{
		$this->fullModelName = $fullModelName;
		return $this;
	}

	/**
	 * @param string $namespace
	 * @return MetaTable
	 */
	public function setNamespace(string $namespace) : self
	{
		$this->namespace = $namespace;
		return $this;
	}




	/**
	 * @return string
	 */
	public function getTraitName(): ?string
	{
		$this->confirmHasAvalModel();
		$className = substr($this->getFullModelName(), strrpos($this->getFullModelName(), '\\') + 1);
		return self::TRAIT_NAME_PREFIX.$className;
	}
	
	/**
	 * @return string
	 */
	public function getTraitNamespeace(): ?string
	{
//		$this->confirmHasAvalModel();
//		return substr($this->getFullModelName(), 0, strrpos($this->getFullModelName(), '\\'));
		return $this->namespace;
	}

	/**
	 * @param string $comment
	 * @return self
	 */
	public function setComment(string $comment) : self
	{
		$this->comment = $comment;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment(): string
	{
		return $this->comment;
	}

	/**
	 * @param MetaField $metaAttribute
	 * @return $this
	 */
	public function addField(MetaField $metaAttribute) : self
	{
		$this->fields[] = $metaAttribute;
		return $this;
	}

	/**
	 * @return MetaField[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @return string Relative path for files made to this table model.
	 */
	public function getRelativeFilePath()
	{
		$this->confirmHasAvalModel();
		$namespaceDirTree = explode('\\', $this->getTraitNamespeace());
		$namespaceDirTree[] = $this->getTraitName();
		return implode(DIRECTORY_SEPARATOR, $namespaceDirTree).'.php';
	}

	/**
	 * A helper to throw the right error when someone make a mistake.
	 *
	 * @throws \Exception
	 */
	private function confirmHasAvalModel()
	{
		if(is_null($this->fullModelName)) {
			throw new \Exception('This table cannot generate code because there is no model for it');
		}
	}

	public function generateTraitCode()
	{
		$this->confirmHasAvalModel();
		$template = file_get_contents(__DIR__.'/../Templates/trait');

		$template = str_replace('{{namespace}}', $this->getTraitNamespeace(), $template);
		$template = str_replace('{{traitName}}', $this->getTraitName(), $template);
		$template = str_replace('{{properties}}', $this->getProperties(), $template);
		return $template;
	}

	/**
	 * Maps the get/Set methods for each non constrained field
	 * @return array
	 */
	private function getMethods()
	{
		$setters = [];
		$getters = [];

		foreach ($this->getFields() as $fieldName => $metaField) {
			if(!in_array($metaField->getPhpAttributeName(), self::IGNORE_SET_ATTRIBUTES)) {
				$set = $metaField->getSetMethodData();
				if($set) {
					$setters[$set['name']] = $set['target'];
				}
			}

			$get = $metaField->getGetMethodData();
			if($get) {
				$getters[$get['name']] = $get['target'];
			}
		}

		return [
		  'setters' => $setters,
		  'getters' => $getters,
		];
	}

	private function getProperties()
	{
		$properties = [];
		$longest = 0;
		foreach ($this->getFields() as $fieldName => $metaField) {
			if(!in_array($metaField->getPhpAttributeName(), self::IGNORE_SET_ATTRIBUTES)) {
				$set = $metaField->getSetMethodData();
				if($set) {
					$longest = strlen($set['type']) > $longest ? strlen($set['type']) : $longest;
					$properties[] = $set;
				}
			}
			$get = $metaField->getGetMethodData();
			if($get) {
				$longest = strlen($get['type']) > $longest ? strlen($get['type']) : $longest;
				$properties[] = $get;
			}

		}
		$phpDoc = [];
		foreach ($properties as $property){
			$phpDoc[] = " * @method ".str_pad($property['type'],$longest, ' ').' '.$property['name'].'('.$property['args'].')';
		}

		return implode("\n", $phpDoc);
	}

	/**
	 * Build a map of all fields of this table to theirs respectives set/get methods
	 * @param array $classMap
	 * @return array
	 */
	public function getTableMap(array $classMap)
	{
		$atts = $this->getMethods();
		$attributes[MapperModel::MAP_SETTERS] = $atts['setters'];
		$attributes[MapperModel::MAP_GETTERS] = $atts['getters'];

		return $attributes;
	}
}