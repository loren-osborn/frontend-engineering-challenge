<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use PHPUnit_Framework_TestCase;
use LinuxDr\CitizenNetCnfcBundle\Entity\PollableSource;
use Doctrine\ORM\Tools\SchemaTool;
use DirectoryIterator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use ReflectionClass;
use Doctrine\ORM\Tools\ToolsException as OrmToolsException;

abstract class EntityTestBase extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager $em
     */
    private $em;
    
    public static function getBootedKernel()
    {
    	static::$kernel = static::createKernel();
		static::$kernel->boot();
    	return static::$kernel;
    }
    
    private function getAllPhpClassesWithin($path, $namespacePrefix) {
		$it = new DirectoryIterator($path);
		$result = array();
		foreach ($it as $key => $child) {
			if ($child->isDot()) {
				continue;
			}
			if ($child->isDir()) {
				$result = array_merge(
					$result, 
					$this->getAllPhpClassesWithin(
						$child->getPathname(), 
						$namespacePrefix . $child->getBasename() . '\\'
					)
				);
			} elseif (preg_match('/\\.php$/', $child->getBasename())) {
				$result[] = $namespacePrefix . preg_replace('/\\.php$/', '', $child->getBasename());
			}
		}
		return $result;
	}
    
    private function getClassMetas($entityPath, $entityNamespace)
    {
    	// inspired by http://www.zendcasts.com/unit-testing-doctrine-2-entities/2011/02/
        $metas = array();
        $classList = $this->getAllPhpClassesWithin($entityPath, $entityNamespace);
        foreach ($classList as $className) {
        	$classRef = new ReflectionClass($className);
        	if (!$classRef->isAbstract()) {
        		$metas[] = $this->em->getClassMetaData($className);
        	}
        }
        return $metas;
    }
    
    public function createSchemaFromEntityClassFiles()
    {
		$tool = new SchemaTool($this->em);
		$entityMetas = $this->getClassMetas(
			$_SERVER['KERNEL_DIR'] . '../src/LinuxDr/CitizenNetCnfcBundle/Entity',
			'LinuxDr\\CitizenNetCnfcBundle\\Entity\\'
		);
		$tool->createSchema($entityMetas);
    }
    
    private function dropSchema()
    {
    	$params = self::getBootedKernel()->getContainer()->get('doctrine')
    		->getConnection()->getParams();
    	if (file_exists($params['path'])) {
    		unlink($params['path']);
    	}
    }
    
    public function setUp()
    {
        $this->em = self::getBootedKernel()->getContainer()->get('doctrine')->getManager();
    	$this->dropSchema();
        $this->createSchemaFromEntityClassFiles();
        
        parent::setUp();
    }
    
    public function tearDown()
    {
        $this->dropSchema();
        parent::tearDown();
    }
    
    protected function populateEntity($entity, $testValues)
    {
    	$classRef = new ReflectionClass(get_class($entity));
        
        foreach ($testValues as $propName => $val) {
        	$this->assertTrue($classRef->hasProperty($propName), "Property $propName must be defined on " . get_class($entity));
        	$this->assertFalse($classRef->getProperty($propName)->isPublic(), "Property $propName on " . get_class($entity) . " must not be public");
        	$entity->$propName = $val;
        }
        $this->em->persist($entity);
        $this->em->flush();
        
    }
    
    protected function getPopulatedEntities($entity, $dqlSuffix = '')
    {
        $dql = 'SELECT e FROM ' . get_class($entity) . ' e' . $dqlSuffix;
    	$classRef = new ReflectionClass(get_class($entity));
        
        return $this->em->createQuery($dql)->execute();
    }
    
    protected function validateEntity($entity, $testValues)
    {
        foreach ($testValues as $propName => $val) {
        	$this->assertEquals($val, $entity->$propName);
        }
        
    }
    
    public function testSimpleProperties($testValues = null)
    {
        $entity = $this->getTestEntity();
        if (is_null($testValues)) {
        	$testValues = $this->getSimpleTestValues();
        }
        $dql = 'SELECT e FROM ' . get_class($entity) . ' e';
        
        $origEntities = $this->em->createQuery($dql)->execute();
        $this->assertEquals(0, count($origEntities));
        
        $this->populateEntity($entity, $testValues);
        $newEntities = $this->getPopulatedEntities($entity);
        $this->assertEquals(1, count($newEntities));
        $this->validateEntity( $newEntities[0], $testValues);
    }
    
    abstract protected function getSimpleTestValues();
    abstract protected function getTestEntity();

}
