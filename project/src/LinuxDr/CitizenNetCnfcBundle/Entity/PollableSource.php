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
	 * @var DateTime $preferedAccessTime	
	 * @ORM\Column(type="datetimetz", nullable=true)
	 */
	private $preferedAccessTime;
	
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
    		'preferedAccessTime',
    		'responses' => array(
        		self::ARRAY_OF_ENTITIES,
        		'reference' => 'source'
        	)
    	));
    }
    
    public function getCurrentResponse($em)
    {
        $query = $em->createQuery(
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
}
