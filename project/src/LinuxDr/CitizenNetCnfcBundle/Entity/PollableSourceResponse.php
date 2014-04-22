<?php
namespace LinuxDr\CitizenNetCnfcBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use DateTime;


/**
 * @ORM\Entity
 */
class PollableSourceResponse extends EntityBase
{
	/**
	 * @var integer $id	
	 * @ORM\Column(type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;
	
	/**
	 * @var DateTime $timestamp	
	 * @ORM\Column(type="datetimetz", nullable=true)
	 */
	private $timestamp;
	
	/**
	 * @var integer $httpStatus	
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $httpStatus;
	
	/**
	 * @var string $rawResponse	
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $rawResponse;
	
    public function __construct()
    {
    	parent::__construct(array('timestamp', 'httpStatus', 'rawResponse'));
    }
}
