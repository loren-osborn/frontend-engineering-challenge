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
    		'responseTimeToLive' => 24*3600,
    		'preferedGmtOffsetAccessTime' => 18341 /* 05:05:41 GMT */
    	);
    }
    
    protected function getTestEntity()
    {
    	return new PollableSource();
    }
    
    public function setupSampleData($nowTime = null, $sourceTimeOffset = 'P1W', $ttl = 86400)
    {
    	$responsesEntity = new PollableSourceResponse();
    	$easternTime = new DateTimeZone('America/New_York');
    	$responsesData = array(
    		array(
				'timestamp' => 
					new DateTime('Mar 4, 2005 06:07:08 PM', $easternTime),
				'httpStatus' => 200,
				'rawResponse' => 'Stale Data'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 5, 2005 06:07:08 PM', $easternTime),
				'httpStatus' => 503,
				'rawResponse' => 'Stale Error'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 5, 2005 06:09:08 PM', $easternTime),
				'httpStatus' => 200,
				'rawResponse' => 'Current Data'
			),
    		array(
				'timestamp' => 
					new DateTime('Mar 6, 2005 06:07:08 PM', $easternTime),
				'httpStatus' => 503,
				'rawResponse' => 'New Error'
			)
		); 
		$filteredResponseData = array();
		foreach ($responsesData as $eachData) {
			if (is_null($nowTime) || ($nowTime->getTimestamp() > $eachData['timestamp']->getTimestamp()) ) {
				$this->populateEntity(new PollableSourceResponse(), $eachData);
				$filteredResponseData[] = $eachData;
			}
		}
		$allResponses = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSourceResponse');
		$sourceObj = new PollableSource();
    	$this->populateEntity(
    		$sourceObj,
    		array(
				'sourceName' => "Fred's Data Source",
				'url' => "http://fred.com/rest/penguins",
				'active' => true,
				'preferedGmtOffsetAccessTime' => 18341 /* 05:05:41 GMT */
			)
		);
		$sourceObj->responses = $allResponses;
		
		$otherResponseDate = array();
		foreach ($responsesData as $eachData) {
			$laterTime = new DateTime(
				$eachData['timestamp']->format('c'),
				$eachData['timestamp']->getTimezone()
			);
			$laterTime->add(new DateInterval($sourceTimeOffset));
			if (is_null($nowTime) || ($nowTime->getTimestamp() > $laterTime->getTimestamp()) ) {
				$newData = array(
					'timestamp' => $laterTime,
					'httpStatus' => $eachData['httpStatus'],
					'rawResponse' => 'Other ' . $eachData['rawResponse']
				);
				$otherResponseDate[] = $newData;
				$this->populateEntity(new PollableSourceResponse(), $newData);
			}
		}
		$otherResponses = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSourceResponse', " WHERE e.rawResponse LIKE 'Other%'");
    	$otherSourceObj = new PollableSource();
    	$this->populateEntity(
    		$otherSourceObj,
    		array(
				'sourceName' => "George's Data Source",
				'url' => "http://george.com/rest/llamas",
				'active' => true,
				'preferedGmtOffsetAccessTime' => 14621 /* 04:03:41 GMT */
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
        
        return array(
        	'responsesData' => $filteredResponseData,
        	'otherResponseDate' => $otherResponseDate);
    }
    
    public function testMostRecentResponse()
    {
		$dataSet = $this->setupSampleData();
		$allSources = $this->getPopulatedEntities('LinuxDr\\CitizenNetCnfcBundle\\Entity\\PollableSource');
		$this->assertEquals(2, count($allSources));
        $this->assertNotEquals($allSources[0]->sourceName, $allSources[1]->sourceName);
		$this->assertEquals("Fred's Data Source", $allSources[0]->sourceName);
		$this->validateEntity($allSources[0]->getCurrentResponse($this->em), $dataSet['responsesData'][2]);
		$this->assertEquals("George's Data Source", $allSources[1]->sourceName);
		$this->validateEntity($allSources[1]->getCurrentResponse($this->em), $dataSet['otherResponseDate'][2]);
    }
    
    public function getSourcesDue()
    {
		return array(
			array('Mar 4, 2005 06:07:00 PM', 86400, array('Fred', 'George')),
			array('Mar 4, 2005 06:07:10 PM', 86400, array('George')),
			array('Mar 4, 2005 06:12:10 PM', 86400, array()),
			array('Mar 4, 2005 06:03:25 AM', 122669, array()),
			array('Mar 4, 2005 06:03:55 AM', 122669, array('George')),
			array('Mar 4, 2005 06:03:25 PM', 122689, array('Fred')),
			array('Mar 4, 2005 06:03:55 PM', 122689, array('Fred', 'George'))
		);
    }
    
    /**
      * @dataProvider getSourcesDue
      */
    public function testSourcesDueForUpdate($nowTime, $ttl, $expected)
    {
    	$nowDateTime = new DateTime($nowTime, new DateTimeZone('America/New_York'));
		$this->setupSampleData($nowDateTime, 'PT5M', $ttl);
		$results = PollableSource::getSourcesDueForRefresh($this->em, $nowDateTime);
		$this->assertEquals(count($expected), count($results));
		foreach ($expected as $sourceName) {
			$found = false;
			foreach ($results as $source) {
				if ($source->sourceName === "{$sourceName}'s Data Source") {
					$found = true;
				}
			}
			$this->assertTrue($found, "{$sourceName}'s Data Source not found");
		}
    }

}
