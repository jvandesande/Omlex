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
 * Qik provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class Qik extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint = null, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://qik.com/api/oembed.json', //or xml
            array(
                'http://qik.com/video/*',
                'http://qik.com/*',
            ),
            'http://www.qik.com',
            'Qik'
        );
    }
}
