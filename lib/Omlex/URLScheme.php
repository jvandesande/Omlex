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
 * URL scheme class.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
 class URLScheme
 {
     const WILDCARD_CHARACTER = '*';

    /**
     * The scheme
     *
     * @var string
     */
    protected $scheme = null;

    /**
     * The generated pattern from the scheme
     *
     * @var string
     */
    protected $pattern = null;

    /**
     * Constructor
     *
     * @param string $scheme The URL scheme
     *
     * @throws \InvalidArgumentException If the scheme is empty
     */
    public function __construct($scheme)
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('The scheme cannot be empty.');
        }

        $this->scheme = $scheme;
    }

    /**
     * Require a sane __toString for all objects
     *
     * @return string
     */
    public function __toString()
    {
        return $this->scheme;
    }

    /**
     * Check whether the given URL match the scheme
     *
     * @param string $url The URL to check against
     *
     * @return Boolean True if match, false if not
     */
    public function match($url)
    {
        if (!$this->pattern) {
            $this->pattern = self::buildPatternFromScheme($this);
        }

        return (bool) preg_match($this->pattern, $url);
    }

    /**
     * Builds pattern from scheme
     *
     * @return string
     */
    static protected function buildPatternFromScheme(URLScheme $scheme)
    {
		// generate a unique random string
		$uniq = md5(mt_rand());

		// replace the wildcard sub-domain if exists
		$scheme = str_replace(
			'://'.self::WILDCARD_CHARACTER.'.',
			'://'.$uniq,
			$scheme->__tostring()
		);

		// replace the wildcards
		$scheme = str_replace(
			self::WILDCARD_CHARACTER,
			$uniq,
			$scheme
		);

		// set the pattern wrap
		$wrap = '|';

		// quote the pattern
		$pattern = preg_quote($scheme, $wrap);

		// replace the unique string by the character class
		$pattern = str_replace($uniq, '.*', $pattern);

		return $wrap.$pattern.$wrap.'iu';
    }
 }
 