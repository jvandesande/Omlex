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
     * HTTP timeout in seconds
     * 
     * All HTTP requests made will respect this timeout. 
     * This can be passed to {@link OEmbed::setOption()} or to
     * the options parameter in {@link OEmbed::__construct()}.
     * 
     * @var string
     */
    const OPTION_TIMEOUT = 'http_timeout';

    /**
     * HTTP User-Agent 
     *
     * All HTTP requests made will be sent with the string
     * set by this option.
     *
     * @var string
     */
    const OPTION_USER_AGENT = 'http_user_agent';

    /**
     * The API's URI
     *
     * If the API is known ahead of time this option can be used to explicitly
     * set it. If not present then the API is attempted to be discovered 
     * through the auto-discovery mechanism.
     *
     * @var string
     */
    const OPTION_API = 'oembed_api';

    /**
     * Options for Omlex requests
     *
     * @var array
     */
    protected $options = array(
        self::OPTION_TIMEOUT    => 3,
        self::OPTION_API        => null,
        self::OPTION_USER_AGENT => 'OEmbed @package-version@'
    );

    /**
     * URL of object to get embed information for
     *
     * @var object
     */
    protected $url = null;

    /**
     * Constructor
     *
     * @param string $url     The URL to fetch from
     * @param array  $options A list of options
     *
     * @throws \InvalidArgumentException If the URL is invalid
     */
    public function __construct($url, array $options = array())
    {
        $info = parse_url($url);
        if (false === $info) {
            throw new \InvalidArgumentException(sprintf('The URL "%s" is invalid.', $url));
        }

        $this->url = $url;

        if (count($options)) {
            foreach ($options as $key => $val) {
                $this->setOption($key, $val);
            }
        }

        if ($this->options[self::OPTION_API] === null) {
            $this->options[self::OPTION_API] = $this->discover($url);
        } 
    }

    /**
     * Set an option for the request
     * 
     * @param mixed $option The option name
     * @param mixed $value  The option value
     *
     * @throws \InvalidArgumentException If the option is invalid
     */
    public function setOption($option, $value)
    {
        switch ($option) {
            case self::OPTION_API:
            case self::OPTION_TIMEOUT:
                break;

            default:
                throw new \InvalidArgumentException(sprintf('The option "%s" is invalid.', $option));
        }

        $this->options[$option] = $value;
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
        $sign = '?';

        if ($query = parse_url($this->options[self::OPTION_API], PHP_URL_QUERY)) {
            $sign = '&';

            parse_str($query, $parameters);
        }

        if (!isset($parameters['url'])) {
            $parameters['url'] = $this->url;
        }
        if (!isset($parameters['format'])) {
            $parameters['format'] = 'json';
        }

        $result = $this->sendRequest(
            sprintf('%s%s%s', $this->options[self::OPTION_API], $sign, http_build_query($parameters))
        );

        switch ($parameters['format']) {
            case 'json':
                $result = json_decode($result);
                if (!is_object($result)) {
                    throw new \InvalidArgumentException('Could not parse JSON response.');
                }

                break;

            case 'xml':
                libxml_use_internal_errors(true);
                $result = simplexml_load_string($result);
                if (!$result instanceof SimpleXMLElement) {
                    $errors = libxml_get_errors();
                    $error  = array_shift($errors);
                    libxml_clear_errors();
                    libxml_use_internal_errors(false);
                    throw new \InvalidArgumentException($error->message, $error->code);
                }

                break;
        }

        return Object::factory($result);
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
        $body = $this->sendRequest($url);

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

    /**
     * Send a GET request to the provider
     * 
     * @param mixed $url The URL to send the request to
     * @return object The oEmbed response as an object
     *
     * @return string The contents of the response
     *
     * @throws \RuntimeException On HTTP errors
     */
    private function sendRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->options[self::OPTION_TIMEOUT]);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->options[self::OPTION_USER_AGENT]);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \RuntimeException(curl_error($ch), curl_errno($ch));
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            throw new \RuntimeException('Non-200 code returned.');
        }

        curl_close($ch);

        return $result;
    }
}
