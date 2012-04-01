<?php

/*
 * This file is part of the Omlex library.
 *
 * (c) Michael H. Arieli <excelwebzone@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omlex\Provider;

use Omlex\Provider;

/**
 * Flickr provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class Flickr extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint = null, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://www.flickr.com/services/oembed/',
            array(
                'http://*.flickr.com/*',
            ),
            'http://www.flickr.com',
            'Flickr'
        );
    }
}
