<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use LinuxDr\CitizenNetCnfcBundle\Entity\PollableSource;
use LinuxDr\CitizenNetCnfcBundle\Entity\PollableSourceResponse;
use DateTime;
use DateTimeZone;
use DateInterval;

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
    
    protected function getTestEntity()
    {
    	return new PollableSource();
    }
    
    public function testMostRecentResponse()
    {
    	$responsesEntity = new PollableSourceResponse();
    	$responsesData = array(
    		array(
				'timestamp' => 
					new DateTime('Mar 4, 2005 06:07:08 PM', new DateTimeZone('America/New_York')),
				'httpStatus' => 200,
				'rawResponse' => 'Stale Data'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 5, 2005 06:07:08 PM', new DateTimeZone('America/New_York')),
				'httpStatus' => 503,
				'rawResponse' => 'Stale Error'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 5, 2005 06:09:08 PM', new DateTimeZone('America/New_York')),
				'httpStatus' => 200,
				'rawResponse' => 'Current Data'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 6, 2005 06:07:08 PM', new DateTimeZone('America/New_York')),
				'httpStatus' => 503,
				'rawResponse' => 'New Error'
			)
		); 
		foreach ($responsesData as $eachData) {
			$this->populateEntity(new PollableSourceResponse(), $eachData);
		}
		$allResponses = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSourceResponse');
		$sourceObj = new PollableSource();
    	$this->populateEntity(
    		$sourceObj,
    		array(
				'sourceName' => "Fred's Data Source",
				'url' => "http://fred.com/rest/penguins",
				'active' => true,
				'preferedAccessTime' =>
					new DateTime('Jan 1, 1970 08:05:41 PM', new DateTimeZone('America/Anchorage'))
			)
		);
		$sourceObj->responses = $allResponses;
		
		$otherResponseDate = array();
		foreach ($responsesData as $eachData) {
			$laterTime = new DateTime(
				$eachData['timestamp']->format('c'),
				$eachData['timestamp']->getTimezone()
			);
			$laterTime->add(new DateInterval('P1W'));
			$newData = array(
				'timestamp' => $laterTime,
				'httpStatus' => $eachData['httpStatus'],
				'rawResponse' => 'Other ' . $eachData['rawResponse']
			);
			$otherResponseDate[] = $newData;
			$this->populateEntity(new PollableSourceResponse(), $newData);
		}
		$otherResponses = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSourceResponse', " WHERE e.rawResponse LIKE 'Other%'");
    	$otherSourceObj = new PollableSource();
    	$this->populateEntity(
    		$otherSourceObj,
    		array(
				'sourceName' => "George's Data Source",
				'url' => "http://george.com/rest/llamas",
				'active' => true,
				'preferedAccessTime' =>
					new DateTime('Jan 1, 1970 07:03:41 PM', new DateTimeZone('America/Anchorage'))
			)
		);
		$otherSourceObj->responses = $otherResponses;
		foreach ($allResponses as $response) {
        	$this->assertEquals(1, $response->source->id);
        	$this->em->persist($response);
        }
		foreach ($otherResponses as $response) {
        	$this->assertEquals(2, $response->source->id);
        	$this->em->persist($response);
        }
        $this->em->flush();
		
		$allSources = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSource');
		$this->assertEquals(2, count($allSources));
        $this->assertNotEquals($allSources[0]->sourceName, $allSources[1]->sourceName);
		$this->assertEquals("Fred's Data Source", $allSources[0]->sourceName);
		$this->validateEntity($allSources[0]->getCurrentResponse($this->em), $responsesData[2]);
		$this->assertEquals("George's Data Source", $allSources[1]->sourceName);
		$this->validateEntity($allSources[1]->getCurrentResponse($this->em), $otherResponseDate[2]);
    }

}
