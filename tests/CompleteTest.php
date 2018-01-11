<?php
namespace Tests;

use Mapper\Customize;
use Mapper\Workers\MapperModel;

class CompleteTest extends TestCase
{

    private static $wasSettedUp = false;

    /**
     * @throws \Exception
     */
    public function setup()
    {
        parent::setup();
        if (!self::$wasSettedUp) {
            $mapPath = __DIR__ . '/sample/Map.php';
            $traitPath = __DIR__ . '/sample';

            self::$wasSettedUp = true;
            $Cust = new Customize($this->getConnection(), [
                'path' => $traitPath,
                'namespaces' => [
                    'MapperTest' => 'MapperTest\Traits'
                ]
            ]);
            $Cust->map();
            $Cust->saveMapFile($mapPath);
            foreach ($Cust->getMetaTables() as $tableName => $metaTable) {
                if (!is_null($metaTable->getFullModelName())){
                    $Cust->saveTraitFile($tableName, $traitPath);
                }
            }
            MapperModel::loadMap(require($mapPath));
        }
    }

    public function testOk()
    {
        $this->assertTrue(true);
    }

}
