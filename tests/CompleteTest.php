<?php
namespace Tests;

use Carbon\Carbon;
use Mapper\Customize;
use Mapper\Workers\MapperModel;
use MapperTest\Author;
use MapperTest\Post;

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

    }

    public function testBelongsTo()
    {
        $profAuthor = new Author();
        $profAuthor->setName("Jhon Kenedy");
        $profAuthor->setType("professional");
        $profAuthor->save();

        $studentAuthor = new Author();
        $studentAuthor->setName("Barak Obama");
        $studentAuthor->setType("beginner");
        $studentAuthor->setTeacher($profAuthor);
        $studentAuthor->save();

        $profAuthor->fresh();
        $studentAuthor->fresh();

        $this->assertInstanceOf(Author::class, $profAuthor->getStudent());
        $this->assertNull($profAuthor->getTeacher());

        $this->assertInstanceOf(Author::class, $studentAuthor->getTeacher());
        $this->assertNull($studentAuthor->getStudent());
    }

    public function testHasOne()
    {
        $studentAuthor = new Author();
        $studentAuthor->setName("Barak Obama");
        $studentAuthor->setType("beginner");

        $profAuthor = new Author();
        $profAuthor->setName("Jhon Kenedy");
        $profAuthor->setType("professional");
        $profAuthor->save();

        $profAuthor->setStudent($studentAuthor);

        $profAuthor->fresh();
        $studentAuthor->fresh();

        $this->assertInstanceOf(Author::class, $profAuthor->getStudent());
        $this->assertNull($profAuthor->getTeacher());

        $this->assertInstanceOf(Author::class, $studentAuthor->getTeacher());
        $this->assertNull($studentAuthor->getStudent());
    }

    public function testHasMany()
    {
        $author = new Author();
        $author->setName("Barak Obama");
        $author->setType("beginner");
        $author->save();

        $post = new Post();
        $post->setAproved(true)->setAuthor($author)->setContent("Cool ".Carbon::now())->setTitle("Works")->save();

        $this->assertGreaterThan(0, count($author->listMyPosts()));
        $this->assertInstanceOf(Author::class, $post->getAuthor());

    }



}
