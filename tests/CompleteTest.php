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
            self::$wasSettedUp = true;
            $Cust = new Customize($this->getConnection());
            $Cust->map();
            $mapPath = __DIR__ . '/sample/Map.php';
            $Cust->saveMapFile($mapPath);
            foreach ($Cust->getMetaTables() as $tableName => $metaTable) {
                if (!is_null($metaTable->getFullModelName())) {
                    $Cust->saveTraitFile($tableName, config('mapper.path'));
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
