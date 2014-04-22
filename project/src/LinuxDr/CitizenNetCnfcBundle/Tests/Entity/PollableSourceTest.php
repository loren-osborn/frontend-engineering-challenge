<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use LinuxDr\CitizenNetCnfcBundle\Entity\PollableSource;
use DateTime;
use DateTimeZone;

class PollableSourceTest extends EntityTestBase
{   
    protected function getSimpleTestValues()
    {
    	return array(
    		'sourceName' => "Fred's Data Source",
    		'url' => "http://fred.com/rest/penguins",
    		'active' => true,
    		'preferedAccessTime' =>
    			new DateTime('Jan 1, 1970 08:05:41 PM', new DateTimeZone('America/Anchorage'))
    	);
    }
    
    protected function getTestEntity(){
    	return new PollableSource();
    }

}
