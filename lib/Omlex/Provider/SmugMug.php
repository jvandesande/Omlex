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
 * SmugMug provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class SmugMug extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint = null, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://api.smugmug.com/services/oembed/',
            array(
                'http://*.smugmug.com/*',
            ),
            'http://www.smugmug.com',
            'SmugMug'
        );
    }
}
