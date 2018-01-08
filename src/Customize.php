<?php

namespace Mapper;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MySqlConnection;
use Mapper\Lib\MetaField;
use Mapper\Lib\MetaTable;
use Mapper\Lib\VirtualFiedls\VirtualFieldBelongsTo;
use Mapper\Lib\VirtualFiedls\VirtualFieldHasMany;
use Mapper\Lib\VirtualFiedls\VirtualFieldHasOne;
use Mapper\Workers\MapperModel;

class Customize
{
    /**
     * @var MySqlConnection
     */
    private $connection;

    private $ignoreTableList = ['migrations'];

    /**
     * @var MetaTable[]
     */
    private $metaTables = [];

    /**
     * @var array
     */
    private $classMap = [];

    /**
     * @var \Doctrine\DBAL\Schema\Table[]
     */
    private $tables = [];

    private $customRelNames = [];

    private $config = [
        'path' => './',
        'namespaces' => ['App'],
    ];

    /**
     * Customize constructor.
     *
     * @param DatabaseManager $connection
     * @param array $config
     */
    public function __construct(DatabaseManager $connection, array $config = [])
    {
        $this->connection = $connection;
        if($config)
            $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function map()
    {
        echo "\n\n======Nao esquece de dar dumpautoload ANTES ========\n\n";
		$this->mapClasses();
		$this->mapDb();
    }

    /**
     * @throws \Exception
     */
    private function mapClasses()
	{
		foreach($this->findExpectedClasses() as $class ){
			if( is_subclass_of($class, MapperModel::class ) ) {
				/** @var MapperModel $class */
				if($class::ignore()) {
					echo "\nIgnoring rejected model class $class";
                } else {
					if(isset($this->classMap[$class::getStaticTable()])) {
						throw new \Exception('Only one model can represent a table (consider using an abstract class)');
					}
					$this->classMap[$class::getStaticTable()] = $class;
                }
			}
		}
	}

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function mapDb()
	{
		$this->connection
		  ->getDoctrineConnection()
		  ->getDatabasePlatform()
		  ->registerDoctrineTypeMapping('enum', 'string');

		$tables = array_diff($this->connection->getDoctrineSchemaManager()->listTableNames(), $this->ignoreTableList);
		$this->mapTables($tables);
		$this->mapFields($tables);
	}

	private function mapTables(array $tables)
    {
        $tableComments = $this->loadTablesComment();
        foreach ($tables as $tableName) {
            if(!in_array($tableName, $this->ignoreTableList)) {
                $this->tables[$tableName] = $this->connection->getDoctrineSchemaManager()->listTableDetails($tableName);
                $metaTable = new MetaTable($tableName, $this->classMap);

                if(isset($this->classMap[$tableName])) {
					$metaTable->setFullModelName($this->classMap[$tableName]);
					$metaTable->setNamespace($this->getClassNsReplaced($this->classMap[$tableName]));
				}

                $metaTable->setComment($tableComments[$tableName]);
				$this->metaTables[$tableName] = $metaTable;
            }
        }
    }

	private function mapFields(array $tables)
    {
        foreach ($tables as $tableName) {
            $metaTable = $this->metaTables[$tableName];

			$specializedField = $this->mapConstrainedFields($tableName);

			// simple fields
			foreach ($this->tables[$tableName]->getColumns() as $col) {
				if(!in_array($col->getName(), $specializedField)) {
					$metaTable->addField(new MetaField($col));
				}
			}
        }
    }

	private function mapConstrainedFields($tableName) : array
	{
		$specializedField = [];
		$uniqueFields = $this->loadUniqueFields($tableName);
		$metaTable = $this->metaTables[$tableName];
		$this->customRelNames = [];

		foreach ($this->tables[$tableName]->getForeignKeys() as $fk) {
			foreach ($fk->getLocalColumns() as $fieldName) {
				$referencedMetaTable = $this->metaTables[$fk->getForeignTableName()];
				$referencedModelName = $this->classMap[$referencedMetaTable->getTableName()] ?? null;
				$modelName = $this->classMap[$metaTable->getTableName()] ?? null;

				$relName = $this->searchForCustomRelNames($tableName, $fieldName, $referencedMetaTable);
				if($modelName && isset
                ($this->customRelNames[$referencedMetaTable->getTableName()]) && is_null($relName)) {
					$ref = $referencedMetaTable->getTableName();
					$err = 'The table `'.$tableName.'` has more than one FK to `'.$ref.'`, the class ';
					$err .= $referencedModelName.' must define each relationship name. See '.MapperModel::class.'::relNameByField() method';
					throw new \Exception($err);
				}
				$this->customRelNames[$referencedMetaTable->getTableName()][$fieldName] = $relName;

				if($modelName) {
					$specializedField[] = $fieldName;
					$this->addLocalVirtualField($metaTable, $fieldName, $fk, in_array($fieldName, $uniqueFields));
				}
				if($referencedModelName) {
					$this->addReferVirtualField($metaTable, $fieldName, $fk, in_array($fieldName, $uniqueFields));
				}
			}
		}
		return $specializedField;
	}

    public function saveTraitFile($tableName, $defaultNSDir)
	{
		$metaTables = $this->getMetaTables();
		if(!isset($metaTables[$tableName])) {
			throw new \Exception('Unkown table: '.$tableName);
		}
		$filePath = $defaultNSDir.DIRECTORY_SEPARATOR.$metaTables[$tableName]->getRelativeFilePath();
		$this->createDirIfNotExist(pathinfo($filePath, PATHINFO_DIRNAME));
		if(file_put_contents($filePath, $metaTables[$tableName]->generateTraitCode()) !== false){
			return [
			  'trait_name' => $metaTables[$tableName]->getTraitName(),
			  'path' => $filePath
			];
		} else {
			return false;
		}
	}

	public function saveMapFile($mapPath)
	{
		$mapCode = [];
		foreach ($this->getMetaTables() as $tableName => $metaTable){
			$mapCode[] = "'".$metaTable->getTableName()."' => [\n".$this->arrayToSourceCode($metaTable->getTableMap($this->classMap), 2)."\n\t]";
		}
		$code = '<?php '."\nreturn [\n\t".implode(",\n\t", $mapCode)."\n];";
		if(file_put_contents($mapPath, $code) !== false){
			return $mapPath;
		} else {
			return false;
		}
	}

	private function arrayToSourceCode(array $arr, $level = 0)
	{
		$code = [];
		$ident = str_repeat("\t", $level);
		foreach ($arr as $key => $val )
		{
			$string = $ident."'$key' => ";
			if(is_array($val)) {
				$string .= "[\n".$this->arrayToSourceCode($val, $level+1)."\n$ident],";
			} else {
				$string .= var_export($val, true).',';
			}
			$code[] = $string;
		}
		return implode("\n", $code);
	}



//
    /**
     * @return array
     */
    protected function loadTablesComment()
    {
        $comments = [];
        foreach ($this->connection->getPdo()->query('SHOW TABLE STATUS')->fetchAll() as $tableStatus){
            $comments[$tableStatus['Name']] = $tableStatus['Comment'];
        }
        return $comments;
    }

    public function getMetaTables()
    {
        return $this->metaTables;
    }

    /**
     * Return all unique fields of a talbe
     * @todo It is not eveluating multiple field unique indexes.
     *
     * @param $tableName
     * @return array
     */
    private function loadUniqueFields($tableName)
    {
        $fields = [];
        foreach ($this->tables[$tableName]->getIndexes() as $index) {
            if($index->isUnique() && count($index->getColumns()) == 1) {
                $fields[] = $index->getColumns()[0];
            }
        }
        return $fields;
    }

    private function createDirIfNotExist($pathinfo)
    {
        if (is_dir($pathinfo)) {
            return true;
        } elseif ($this->createDirIfNotExist(dirname($pathinfo))) {
            return mkdir($pathinfo);
        } else {
            return false;
        }
    }
    private function findExpectedClasses()
    {
        /** @var \Composer\Autoload\ClassLoader $autoLoader */
        $autoLoader = require base_path('/vendor/autoload.php');
		$classes = [];
        foreach ($this->config['namespaces'] as $nsMappable => $nsRaplecement) {
            foreach ($autoLoader->getClassMap() as $fullClassName => $file) {
                if(preg_match("/^".addslashes($nsMappable)."/", $fullClassName)) {
					$classes[] = $fullClassName;
                }
            }
        }
        return $classes;
    }

	private function getClassNsReplaced(string $originalClassName)
	{
		foreach ($this->config['namespaces'] as $nsMappable => $nsRaplecement) {
			if(preg_match("/^".addslashes($nsMappable)."/", $originalClassName)) {
				$originalClassName = preg_replace("/^".addslashes($nsMappable)."/", $nsRaplecement, $originalClassName);
				break;
			}
		}
		return substr($originalClassName, 0, strrpos($originalClassName, '\\'));
	}

	/**
	 * This method search for custom relationship names. All
	 * @param string $tableName
	 * @param string $fieldName
	 * @param MetaTable $referencedMetaTable
	 * @throws \Exception
	 */
	private function searchForCustomRelNames(string  $tableName, string $fieldName, MetaTable $referencedMetaTable)
	{
		if(isset($this->classMap[$referencedMetaTable->getTableName()])) {
			/** @var MapperModel $referredModel */
			$referredModel = $this->classMap[$referencedMetaTable->getTableName()];
			$relName = $referredModel::relNameByField($tableName, $fieldName);
		} else {
			$relName = null;
		}
		return $relName;
//		$this->customRelNames[$referenciedMetaTable->getTableName()][$fieldName] = $relName;
	}


	private function addLocalVirtualField(MetaTable $metaTable, string $fieldName, ForeignKeyConstraint $fk, bool $isUnique)
	{
		$doctrineReferredTbl = $this->connection->getDoctrineSchemaManager()->listTableDetails($fk->getForeignTableName());
		$refericiedCol = $doctrineReferredTbl->getColumn($fk->getForeignColumns()[0]);
		$referencedMetaTable = $this->metaTables[$fk->getForeignTableName()];
		echo "\n\n".$metaTable->getTableName()." ($fieldName) => ".$referencedMetaTable->getTableName()." = "
            .$refericiedCol->getName();
		if($isUnique) {
			$metaField = new VirtualFieldHasOne($refericiedCol, $metaTable, $fk);
		} else {
			$metaField = new VirtualFieldHasMany($refericiedCol, $metaTable, $fk);
		}
		$metaField->setReferredClass($this->classMap[$metaTable->getTableName()], $this->customRelNames[$referencedMetaTable->getTableName()][$fieldName]);
		$referencedMetaTable->addField($metaField);
	}

	private function addReferVirtualField(MetaTable $metaTable, $fieldName, ForeignKeyConstraint $fk, bool $isUnique)
	{
		$tableName = $metaTable->getTableName();
		$localCol = $this->tables[$tableName]->getColumn($fieldName);
		$referencedMetaTable = $this->metaTables[$fk->getForeignTableName()];
		$referencedModelName = $this->classMap[$referencedMetaTable->getTableName()];
		if($isUnique) {
			$metaField = new VirtualFieldBelongsTo($localCol, $referencedMetaTable, $fk);
			$metaField->setReferredClass($referencedModelName);
			$metaTable->addField($metaField);
		}
		// @todo Belongs to many
	}

}