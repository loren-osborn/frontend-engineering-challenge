<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use LinuxDr\CitizenNetCnfcBundle\Exceptions\InvalidAccessException;
use LinuxDr\CitizenNetCnfcBundle\Entity\EntityBase;

class EntityBaseTest__MockEntity extends EntityBase
{	
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

}
