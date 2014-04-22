<?php
namespace LinuxDr\CitizenNetCnfcBundle\Entity;

use ReflectionClass;
use LinuxDr\CitizenNetCnfcBundle\Exceptions\InvalidAccessException;

abstract class EntityBase
{	
	/*
	 * @var array $propertiesToExpose
	 */
	private $propertiesToExpose;
	
    protected function __construct(array $propertiesToExpose = array())
    {
        $this->propertiesToExpose = $propertiesToExpose;
    }
	
    public function __get($name)
    {
    	return $this->getRefProperty($name)->getValue($this);
    }

    public function __set($name, $value)
    {
    	$this->getRefProperty($name)->setValue($this,$value);
        return $value;
    }

    private function getRefProperty($name)
    {
    	if (!in_array($name, $this->propertiesToExpose)) {
    		throw new InvalidAccessException("No access was granted to access $name");
    	}
    	$classRef = new ReflectionClass(get_class($this));
    	$propRef = $classRef->getProperty($name);
    	$propRef->setAccessible(true);
        return $propRef;
    }
}
