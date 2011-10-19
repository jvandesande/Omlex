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
 * SlideShare provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class SlideShare extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://www.slideshare.net/api/oembed/2',
            array(
                'http://www.slideshare.net/*/*',
            ),
            'http://www.slideshare.net',
            'SlideShare'
        );
    }
}
