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
 * Revision3 provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class Revision3 extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint = null, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://revision3.com/api/oembed/',
            array(
                'http://*.revision3.com/*',
            ),
            'http://www.revision3.com',
            'Revision3'
        );
    }
}
