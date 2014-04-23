<?php
namespace LinuxDr\CitizenNetCnfcBundle\Entity;

use ReflectionClass;
use LinuxDr\CitizenNetCnfcBundle\Exceptions\InvalidAccessException;
use Doctrine\Common\Collections\ArrayCollection;

abstract class EntityBase
{	
	/*
	 * @var array $propertiesToExpose
	 */
	private $propertiesToExpose;
	
	const ARRAY_OF_ENTITIES = 'ARRAY_OF_ENTITIES';
	
    protected function __construct(array $propertiesToExpose = array())
    {
        $this->propertiesToExpose = array();
        foreach ($propertiesToExpose as $key => $val) {
        	if (is_integer($key)) {
        		$this->propertiesToExpose[$val] = true;
        	} else {
        		$this->propertiesToExpose[$key] = $val;
        	}
        }
    }
	
    public function __get($name)
    {
    	return $this->getRefProperty($name)->getValue($this);
    }

    public function __set($name, $value)
    {
    	$refProperty = $this->getRefProperty($name);
    	$oldValue = $refProperty->getValue($this);
    	if ($this->isArrayOfEntities($name)) {
    		if (!is_object($oldValue)) {
    			$oldValue = self::getArrayCollection();
    		}
    		if (!is_object($value)) {
    			$value = self::getArrayCollection($value);
    		}
    		$oldArrayValue = $oldValue->toArray();
    		$arrayValue = $value->toArray();
    		$refPropName = $this->propertiesToExpose[$name]['reference'];
    		$entitiesToUnlink = array_combine(array_map('spl_object_hash', $oldArrayValue), $oldArrayValue);
    		$entitiesToLink = array_combine(array_map('spl_object_hash', $arrayValue), $arrayValue);
    		$entitiesInBoth = array_intersect_key($entitiesToUnlink, $entitiesToLink);
    		$entitiesToUnlink = array_diff_key($entitiesToUnlink, $entitiesInBoth);
    		$entitiesToLink = array_diff_key($entitiesToLink, $entitiesInBoth);
        	foreach ($entitiesToUnlink as $ent) {
        		$ent->$refPropName = null;
        	}
        	foreach ($entitiesToLink as $ent) {
        		$ent->$refPropName = $this;
        	}
    	}
    	$refProperty->setValue($this, $value);
        return $value;
    }

    private function isArrayOfEntities($name)
    {
        return 
    		is_array($this->propertiesToExpose[$name]) && 
    		($this->propertiesToExpose[$name][0] === self::ARRAY_OF_ENTITIES);
    }

    private function getRefProperty($name)
    {
    	if (!array_key_exists($name, $this->propertiesToExpose)) {
    		throw new InvalidAccessException("No access was granted to access $name of " . get_class($this));
    	}
    	$classRef = new ReflectionClass(get_class($this));
    	$propRef = $classRef->getProperty($name);
    	$propRef->setAccessible(true);
        return $propRef;
    }

    public static function getArrayCollection($arr = array())
    {
        return new ArrayCollection($arr);
    }
}
