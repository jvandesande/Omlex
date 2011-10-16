<?php

/*
 * This file is part of the Omlex library.
 *
 * (c) Michael H. Arieli <excelwebzone@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omlex;

/**
 * Base class for consuming objects
 *
 * <code>
 * <?php
 * 
 * // The URL that we'd like to find out more information about.
 * $url = 'http://www.flickr.com/photos/24887479@N06/2656764466/';
 *
 * // The oEmbed API URI. Not all providers support discovery yet so we're
 * // explicitly providing one here. If one is not provided OEmbed
 * // attempts to discover it. If none is found an exception is thrown.
 * $oEmbed = new Omlex\OEmbed($url, array(
 *     OEmbed::OPTION_API => 'http://www.flickr.com/services/oembed/'
 * ));
 * $object = $oEmbed->getObject();
 *
 * // All of the objects have somewhat sane __toString() methods that allow
 * // you to output them directly.
 * echo (string)$object;
 * 
 * ?>
 * </code> 
 * 
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class OEmbed
{
    /**
     * The API's URI
     *
     * If the API is known ahead of time this option can be used to explicitly
     * set it. If not present then the API is attempted to be discovered 
     * through the auto-discovery mechanism.
     *
     * @var string
     */
    protected $api = null;

    /**
     * URL of object to get embed information for
     *
     * @var object
     */
    protected $url = null;

    /**
     * Constructor
     *
     * @param string $url The URL to fetch from
     * @param string $api The API URI
     */
    public function __construct($url = null, $api = null)
    {
        if ($url) {
            $this->setURL($url);
        }

        if ($api && $this->validateURL($api)) {
            $this->api = $api;
        }

        if ($this->url && $this->api === null) {
            $this->api = $this->discover($url);
        }
    }

    /**
     * Set a URL to fetch from
     * 
     * @param string $url The URL to fetch from
     *
     * @throws \InvalidArgumentException If the URL is invalid
     */
    public function setURL($url)
    {
        if (!$this->validateURL($url)) {
            throw new \InvalidArgumentException(sprintf('The URL "%s" is invalid.', $url));
        }

        $this->url = $url;
    }

    /**
     * Validate a URL
     * 
     * @param string $url The URL
     *
     * @return Boolean True if valid, false if not
     */
    public function validateURL($url)
    {
        $info = parse_url($url);
        if (false === $info) {
            return false;
        }

        return true;
    }

    /**
     * Get the oEmbed response
     *
     * @param array $params Optional parameters for 
     *
     * @return object The oEmbed response as an object
     * 
     * @throws \RuntimeException         On HTTP errors
     * @throws \InvalidArgumentException when result is not parsable 
     */
    public function getObject(array $parameters = array())
    {
        if ($this->url === null) {
            throw new \InvalidArgumentException('Missing URL.');
        }

        if ($this->api === null) {
            $this->api = $this->discover($this->url);
        }

        $sign = '?';
        if ($query = parse_url($this->api, PHP_URL_QUERY)) {
            $sign = '&';

            parse_str($query, $parameters);
        }

        if (!isset($parameters['url'])) {
            $parameters['url'] = $this->url;
        }
        if (!isset($parameters['format'])) {
            $parameters['format'] = 'json';
        }

        $client = new Client(
            sprintf('%s%s%s', $this->api, $sign, http_build_query($parameters))
        );

        $data = $client->send();

        switch ($parameters['format']) {
            case 'json':
                $data = json_decode($data);
                if (!is_object($data)) {
                    throw new \InvalidArgumentException('Could not parse JSON response.');
                }

                break;

            case 'xml':
                libxml_use_internal_errors(true);
                $data = simplexml_load_string($data);
                if (!$data instanceof SimpleXMLElement) {
                    $errors = libxml_get_errors();
                    $error  = array_shift($errors);
                    libxml_clear_errors();
                    libxml_use_internal_errors(false);
                    throw new \InvalidArgumentException($error->message, $error->code);
                }

                break;
        }

        return Object::factory($data);
    }

    /**
     * Discover an oEmbed API 
     *
     * @param string $url The URL to attempt to discover Omlex for
     *
     * @return string The oEmbed API endpoint discovered
     *
     * @throws \InvalidArgumentException If the $url is invalid
     */
    protected function discover($url)
    {
        $client = new Client($url);

        $body = $client->send();

        // Find all <link /> tags that have a valid oembed type set. We then
        // extract the href attribute for each type.
        $regexp = '#<link([^>]*)type=[^"]*"'.
                  '(application/json|text/xml)\+oembed"([^>]*)>#i';

        $matches = $result = array();
        if (!preg_match_all($regexp, $body, $matches)) {
            throw new \InvalidArgumentException('No valid oEmbed links found on page.');
        }

        foreach ($matches[0] as $key => $link) {
            $hrefs = array();
            if (preg_match('/href=[^"]*"([^"]+)"/i', $link, $hrefs)) {
                $result[$matches[2][$key]] = $hrefs[1];
            }
        }

        return (isset($result['application/json']) ? $result['application/json'] : array_pop($result));
    }
}
