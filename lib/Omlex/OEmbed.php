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

use Omlex\Provider;

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
 * $oEmbed = new Omlex\OEmbed($url, 'http://www.flickr.com/services/oembed/');
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
    protected $endpoint = null;

    /**
     * URL of object to get embed information for
     *
     * @var string
     */
    protected $url = null;

    /**
     * Providers
     *
     * @var array
     */
    protected $providers = array();

    /**
     * Constructor
     *
     * @param string $url       The URL to fetch from
     * @param string $endpoint  The API endpoint
     * @param array  $providers Additional providers
     */
    public function __construct($url = null, $endpoint = null, array $providers = array())
    {
        if ($url) {
            $this->setURL($url);
        }

        if ($endpoint && $this->validateURL($endpoint)) {
            $this->endpoint = $endpoint;
        }

        $this->providers = array(
            new Provider\Flickr(),
            new Provider\Hulu(),
            new Provider\iFixit(),
            new Provider\PollEverywhere(),
            new Provider\Qik(),
            new Provider\Revision3(),
            new Provider\SlideShare(),
            new Provider\SmugMug(),
            new Provider\Viddler(),
            new Provider\Vimeo(),
            new Provider\YouTube(),
        );

        foreach ($providers as $provider) {
            if (is_array($provider) || $provider instanceof Provider) {
                $this->addProvider($provider);
            }
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
        $this->endpoint = null;
    }

    /**
     * Add provider
     *
     * @param mix $provider The provider
     */
    public function addProvider($provider)
    {
        if ($provider instanceof Provider) {
            $this->providers[] = $provider;
        }

        if (is_array($provider)) {
            $this->providers[] = new Provider(
                $provider['endpoint'],
                $provider['schemes'],
                $provider['url'],
                $provider['name']
            );
        }
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

        if ($this->endpoint === null) {
            $this->endpoint = $this->discover($this->url);
        }

        $sign = '?';
        if ($query = parse_url($this->endpoint, PHP_URL_QUERY)) {
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
            sprintf('%s%s%s', $this->endpoint, $sign, http_build_query($parameters))
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
     * Discover an oEmbed API endpoint
     *
     * @param string $url The URL to attempt to discover Omlex for
     *
     * @return string The oEmbed API endpoint discovered
     *
     * @throws \InvalidArgumentException If not $endpoint was found
     */
    protected function discover($url)
    {
        $endpoint = null;

        // try to find a provider matching the supplied URL if no one has been supplied
        foreach ($this->providers as $provider) {
            if ($provider->match($url)) {
                $endpoint = $provider->getEndpoint();
                break;
            }
        }

        // if no provider was found, try to discover the endpoint URL
        if (!$endpoint) {
            $discover = new Discoverer();
            $endpoint = $discover->getEndpointForUrl($url);
        }

        if (!$endpoint) {
            throw new \InvalidArgumentException('No oEmbed links found.');
        }

        return $endpoint;
    }
}
