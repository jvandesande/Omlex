<?php

namespace Omlex;

use Omlex\OEmbed;
use Omlex\Object;
use Omlex\Exception\ObjectException;

class OEmbedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Array of test objects to fetch and validate
     *
     * @var array
     */
    protected $objects = array(
        'photo' => array(
            'url' => 'http://www.flickr.com/photos/jtellolopez/2656764466/',
            'api' => 'http://www.flickr.com/services/oembed/',
            'expected' => array(
                'version'       => '1.0'
                'type]'         => 'photo'
                'author_url'    => 'http://www.flickr.com/photos/24887479@N06/',
                'cache_age'     => 3600,
                'provider_name' => 'Flickr',
                'provider_url'  => 'http://www.flickr.com/',
                'title'         => 'Torrie Wilson',
                'author_name'   => 'jtellolopez',
                'width'         => '411',
                'height'        => '500',
                'url'           => 'http://farm4.static.flickr.com/3245/2656764466_afa90677e1.jpg',
            )
        ),
        'video' => array(
            'url' => 'http://www.youtube.com/watch?v=ReSxgDpAJwk',
            'api' => 'http://lab.youtube.com/oembed/',
            'expected' => array(
                'provider_url'     => 'http://www.youtube.com/',
                'title'            => 'torrie wilson vs trish stratus',
                'html'             => '<object width="459" height="344"><param name="movie" value="http://www.youtube.com/v/ReSxgDpAJwk?version=3&feature=oembed"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/ReSxgDpAJwk?version=3&feature=oembed" type="application/x-shockwave-flash" width="459" height="344" allowscriptaccess="always" allowfullscreen="true"></embed></object>',
                'author_name'      => 'johnnyg08',
                'height'           => 344,
                'thumbnail_width'  => 480,
                'width'            => 459,
                'version'          => '1.0',
                'author_url'       => 'http://www.youtube.com/user/johnnyg08',
                'provider_name'    => 'YouTube',
                'thumbnail_url'    => 'http://i3.ytimg.com/vi/ReSxgDpAJwk/hqdefault.jpg',
                'type'             => 'video',
                'thumbnail_height' => 360,
            )
        )
    );

    /**
     * An invalid object to test
     *
     * @var array
     */
    protected $error = array(
        'url' => 'http://www.flickr.com/photos/jtellolopez/2656764466/',
        'api' => 'http://www.flickr.com/services/oembed/'
    );

    /**
     * Test fetching all of the objects
     *
     * @return void
     */
    public function testGetObjects()
    {
        foreach ($this->objects as $type => $test) {
            $object = $this->getObject($test);

            $expectedObject = '\\Omlex\\Object\\' . ucfirst($type);
            $this->assertEquals($expectedObject, get_class($object));

            foreach ($test['expected'] as $key => $val) {
                $this->assertEquals($val, $object->$key, sprintf('Unexpected %s value for object type %s', $key, $type));
            }
        }
    }

    /**
     * Test the error
     *
     * @return void
     */
    public function testError()
    {
        try {
            $object = $this->getObject($this->error);
        } catch (ObjectException $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Get the Omlex object
     *
     * @param array $test The test object to fetch
     *
     * @return object Instance of Object
     */
    protected function getObject(array $test)
    {
        $Omlex = new OEmbed($test['url'], array(
             OEmbed::OPTION_API => $test['api']
        ));

        return $Omlex->getObject();
    }
}
