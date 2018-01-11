<?php
namespace Tests;

use Carbon\Carbon;
use Mapper\Customize;
use Mapper\Workers\MapperModel;
use MapperTest\Author;

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

    public function testBasicMethods()
    {
        $name = "Barak Obama";
        $type = "beginner";
        $author = new Author();
        $author->setName($name);
        $author->setType($type);
        $this->assertTrue($author->save());
        $id = $author->getId();

        $retrieveAuthor = Author::find($id);

        $this->assertInstanceOf(Author::class, $retrieveAuthor);
        $this->assertEquals($id, $retrieveAuthor->getId());
        $this->assertEquals($name, $retrieveAuthor->getName());
        $this->assertEquals($type, $retrieveAuthor->getType());
        $this->assertInstanceOf(Carbon::class, $retrieveAuthor->getCreatedAt());
        $this->assertInstanceOf(Carbon::class, $retrieveAuthor->getUpdatedAt());
        $this->assertNull($retrieveAuthor->getTeacher());
//        $profAuthor = new Author();
//        $profAuthor->setName($name);
//        $profAuthor->setType("beginner");

    }

    public function testObjectExchangingMethods()
    {
        $studentAuthor = new Author();
        $studentAuthor->setName("Barak Obama");
        $studentAuthor->setType("beginner");
        $studentAuthor->save();


        $profAuthor = new Author();
        $profAuthor->setName("Jhon Kenedy");
        $profAuthor->setType("professional");
        $profAuthor->setStudent($studentAuthor);
        $this->assertTrue($profAuthor->save());
        $profId = $profAuthor->getId();


        $studentAuthor->fresh();



        $this->assertInstanceOf(Author::class, $profAuthor->getStudent());
        $this->assertNull($profAuthor->getTeacher());

        $this->assertInstanceOf(Author::class, $studentAuthor->getTeacher());
        $this->assertNull($studentAuthor->getStudent());

    }

}
