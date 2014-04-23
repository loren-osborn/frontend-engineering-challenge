<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use LinuxDr\CitizenNetCnfcBundle\Exceptions\InvalidAccessException;
use LinuxDr\CitizenNetCnfcBundle\Entity\EntityBase;

class EntityBaseTest__MockEntity extends EntityBase
{	
	/*
	 * @var mixed $id
	 */
	private $id;
	
	/*
	 * @var mixed $foo
	 */
	private $foo;
	
	/*
	 * @var mixed $bar
	 */
	private $bar;
	
    public function __construct(array $propertiesToExpose = array())
    {
        parent::__construct($propertiesToExpose);
    }
}

class EntityBaseTest extends PHPUnit_Framework_TestCase
{
    public function testCantInstantiate()
    {
    	$classRef = new ReflectionClass('LinuxDr\\CitizenNetCnfcBundle\\Entity\\EntityBase');
    	$this->assertTrue($classRef->getMethod('__construct')->isProtected());
    }

    public function testDirectAccess()
    {
        $entity = new EntityBaseTest__MockEntity(array('foo'));
        $entity->foo = 3;
        $this->assertEquals(3, $entity->foo);
        $entity->foo = 4;
        $this->assertEquals(4, $entity->foo);
    }

    public function testInvalidDirectAccess()
    {
        $entity = new EntityBaseTest__MockEntity(array('foo', 'unknown'));
        $fail = null;
        try {
        	$entity->bar = 3;
        	$fail = 'Should have thrown exception';
        } catch (InvalidAccessException $e) {
        }
        if (!$fail) {
			try {
				$junk = $entity->bar;
				$fail = 'Should have thrown exception';
			} catch (InvalidAccessException $e) {
			}
        }
        if ($fail) {
        	$this->fail($fail);
        }
    }

    public function assertIsArrayCollectionOfSize($count, $mixed)
    {
        $this->assertInstanceOf('Doctrine\\Common\\Collections\\ArrayCollection', $mixed);
        $this->assertEquals($count, $mixed->count());
    }

    public function assertIsTestObject($obj)
    {
        $this->assertInstanceOf('LinuxDr\\CitizenNetCnfcBundle\\Tests\\Entity\\EntityBaseTest__MockEntity', $obj);
    }

    public function testArrayOfEntities()
    {
        $entity = new EntityBaseTest__MockEntity(array(
        	'id',
        	'foo' => array(
        		EntityBaseTest__MockEntity::ARRAY_OF_ENTITIES,
        		'reference' => 'foo'
        	)
        ));
        $entity->foo = array(new EntityBaseTest__MockEntity(array('foo')));
        $this->assertIsArrayCollectionOfSize(1, $entity->foo);
        $this->assertIsTestObject($entity->foo[0]);
        $this->assertNull($entity->foo[0]->foo->id);
        $entity->id = 37;
        $this->assertIsArrayCollectionOfSize(1, $entity->foo);
        $this->assertIsTestObject($entity->foo[0]);
        $this->assertEquals(37, $entity->foo[0]->foo->id);
        $entity->foo = array(new EntityBaseTest__MockEntity(array('foo')));
        $this->assertEquals(37, $entity->foo[0]->foo->id);
        $entity->id = 38;
        $this->assertIsArrayCollectionOfSize(1, $entity->foo);
        $this->assertIsTestObject($entity->foo[0]);
        $this->assertEquals(38, $entity->foo[0]->foo->id);
        
        // This is known broken:
        // $entity->foo->add(new EntityBaseTest__MockEntity(array('foo->id')));
        
        // instead do this:
        $entity->foo = array($entity->foo[0], new EntityBaseTest__MockEntity(array('foo')));
        
        
        $this->assertIsArrayCollectionOfSize(2, $entity->foo);
        $this->assertIsTestObject($entity->foo[0]);
        $this->assertIsTestObject($entity->foo[1]);
        $this->assertEquals(38, $entity->foo[0]->foo->id);
        $this->assertEquals(38, $entity->foo[1]->foo->id);
        $entity->id = 39;
        $this->assertIsArrayCollectionOfSize(2, $entity->foo);
        $this->assertIsTestObject($entity->foo[0]);
        $this->assertIsTestObject($entity->foo[1]);
        $this->assertEquals(39, $entity->foo[0]->foo->id);
        $this->assertEquals(39, $entity->foo[1]->foo->id);
    }

}
