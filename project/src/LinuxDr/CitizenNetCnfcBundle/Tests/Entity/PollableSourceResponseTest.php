<?php
namespace LinuxDr\CitizenNetCnfcBundle\Tests\Entity;

use LinuxDr\CitizenNetCnfcBundle\Entity\PollableSourceResponse;
use DateTime;
use DateTimeZone;

class PollableSourceResponseTest extends EntityTestBase
{   
    protected function getSimpleTestValues()
    {
    	return array(
    		'timestamp' => 
    			new DateTime('Mar 4, 2005 06:07:08 PM', new DateTimeZone('America/New_York')),
    		'httpStatus' => 200,
    		'rawResponse' => '{
        "php": ">=5.3.3",
        "symfony/symfony": "~2.4",
        "doctrine/orm": "~2.2,>=2.2.3",
        "doctrine/doctrine-bundle": "~1.2",
        "twig/extensions": "~1.0",
        "symfony/assetic-bundle": "~2.3",
        "symfony/swiftmailer-bundle": "~2.3",
        "symfony/monolog-bundle": "~2.4",
        "sensio/distribution-bundle": "~2.3",
        "sensio/framework-extra-bundle": "~3.0",
        "sensio/generator-bundle": "~2.3",
        "incenteev/composer-parameter-handler": "~2.0",
        "phpunit/phpunit": "4.2.*@dev",
        "phpunit/phpunit-mock-objects": "2.1.*@dev",
        "sebastian/comparator": "1.0.*@dev",
        "cbsi/doctrine2-nestedset": "dev-master",
        "doctrine/migrations": "dev-master",
        "doctrine/doctrine-migrations-bundle": "dev-master",
        "doctrine/doctrine-fixtures-bundle": "2.2.*",
        "innova/angular-js-bundle": "dev-master",
        "fsc/hateoas-bundle": "0.5.*@dev"
    }'
    	);
    }
    
    protected function getTestEntity(){
    	return new PollableSourceResponse();
    }

}
