<?php

namespace Omlex;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testAutoloadReturnsTrueIfClassExists()
    {
        $this->assertTrue(ClassLoader::getInstance()->autoload('Omlex\OEmbed'));
    }

    public function testAutoloadReturnsFalseIfClassDoesNotExist()
    {
        $this->assertFalse(ClassLoader::getInstance()->autoload('Omlex\Invalid'));
    }
}
