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
	
    public function __construct()
    {
    	parent::__construct(array('sourceName', 'url', 'active', 'preferedAccessTime'));
    }
}
