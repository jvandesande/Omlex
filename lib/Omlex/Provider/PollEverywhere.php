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
 * Poll Everywhere provider.
 *
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
class PollEverywhere extends Provider
{
    /**
     * {@inheritdoc}
     */
    public function __construct($endpoint, array $schemes = array(), $url = null, $name = null)
    {
        return parent::__construct(
            'http://www.polleverywhere.com/services/oembed/',
            array(
                'http://www.polleverywhere.com/polls/*',
                'http://www.polleverywhere.com/multiple_choice_polls/*',
                'http://www.polleverywhere.com/free_text_polls/*',
            ),
            'http://www.polleverywhere.com',
            'Poll Everywhere'
        );
    }
}
