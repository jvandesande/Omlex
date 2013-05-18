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
 * iFixit provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class iFixit extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint = null, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://www.ifixit.com/Embed',
            array(
                'http://*.ifixit.com/Guide/View/*',
            ),
            'http://www.ifixit.com',
            'iFixit'
        );
    }
}
