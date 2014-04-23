<?php
namespace LinuxDr\CitizenNetCnfcBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use DateTime;


/**
 * @ORM\Entity
 */
class PollableSource extends EntityBase
{
	/**
	 * @var integer $id	
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 * @var string $sourceName	
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $sourceName;
	
	/**
	 * @var string $url	
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $url;
	
	/**
	 * @var boolean $active	
	 * @ORM\Column(type="boolean", nullable=false)
	 */
	private $active;
	
	/**
	 * @var integer $responseTimeToLive	
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $responseTimeToLive = 86400;
	
	/**
	 * @var DateTime $preferedGmtOffsetAccessTime	
	 * @ORM\Column(type="integer", nullable=false)
	 * 57600 = midnight @ GMT-8 hours
	 */
	private $preferedGmtOffsetAccessTime = 57600;
	
	/**
	 * @var array() $responses
     * @ORM\OneToMany(targetEntity="PollableSourceResponse", mappedBy="source")
	 */
	private $responses;
	
    public function __construct()
    {
    	parent::__construct(array(
    		'id',
    		'sourceName',
    		'url',
    		'active',
    		'responseTimeToLive',
    		'preferedGmtOffsetAccessTime',
    		'responses' => array(
        		self::ARRAY_OF_ENTITIES,
        		'reference' => 'source'
        	)
    	));
    }
    
    public function getCurrentResponse($entMgr)
    {
        $query = $entMgr->createQuery(
        	"SELECT " .
        		"r " .
        	"FROM " .
        		"LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSourceResponse r " .
        		"JOIN r.source s " .
        	"WHERE " .
        		"s.id = :sourceId AND " .
        		"r.httpStatus = 200 " .
        	"ORDER BY r.timestamp DESC"
        );
        $query->setParameter('sourceId', $this->id);
        $query->setMaxResults(1);
        $responses = $query->execute();
        if (count($responses) === 0) {
        	return null;
        }
        return $responses[0];
    }
    
    public static function getSourcesDueForRefresh($entMgr, $nowTime)
    {
        return $entMgr->createQuery(
        	"SELECT " .
        		"s " .
        	"FROM " .
        		"LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSource s " .
        		"LEFT JOIN s.responses r " .
        	"WHERE " .
        		"r IS NULL"
        )->execute();
    }
}
