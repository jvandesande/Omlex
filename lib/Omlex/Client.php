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
 * Client simulates a browser.
 * 
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class Client
{
    protected $ignoreErrors = true;
    protected $maxRedirects = 5;
    protected $timeout = 5;
    protected $url = null;

    /**
     * Constructor
     *
     * @param string $url     The URL to fetch from
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Send a GET request
     * 
     * @return string The contents of the response
     *
     * @throws \RuntimeException On HTTP errors
     */
    public function send()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array());
        curl_setopt($curl, CURLOPT_POSTFIELDS, null);

        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0 < $this->maxRedirects);
        curl_setopt($curl, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($curl, CURLOPT_FAILONERROR, !$this->ignoreErrors);

        $data = curl_exec($curl);
        if (false === $data) {
            $errorMsg = curl_error($curl);
            $errorNo = curl_errno($curl);

            throw new \RuntimeException($errorMsg, $errorNo);
        }

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 > $code || $code >= 300) {
            throw new \RuntimeException('Non-2xx code was returned.');
        }

        curl_close($curl);

        return self::getLastResponse($data);
    }

    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
    }

    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects;
    }

    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    static protected function getLastResponse($raw)
    {
        $parts = preg_split('/((?:\\r?\\n){2})/', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = count($parts) - 3; $i >= 0; $i -= 2) {
            if (0 === stripos($parts[$i], 'http')) {
                return implode('', array_slice($parts, $i));
            }
        }

        return $raw;
    }
}
