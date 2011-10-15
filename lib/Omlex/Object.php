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

use Omlex\Exception\NoSupportException;
use Omlex\Exception\ObjectException;

/**
 * Base class for consuming Omlex objects.
 * 
 * @author Michael H. Arieli <excelwebzone@gmail.com>
 */
abstract class Object
{
    /**
     * Valid object types
     *
     * @var array
     */
    static protected $types = array(
        'photo' => 'Photo',
        'video' => 'Video',
        'link'  => 'Link',
        'rich'  => 'Rich'
    );

    /**
     * Create an Omlex object from result
     *
     * @param object $object Raw object returned from API
     *
     * @throws ObjectException on object error
     * @return object Instance of object driver
     */
    static public function factory($object)
    {
        if (!isset($object->type)) {
            throw new ObjectException('Object has no type');
        }

        $type = (string)$object->type;
        if (!isset(self::$types[$type])) {
            throw new NoSupportException(sprint('Object type is unknown or invalid: %s', $type));
        }

        $class = '\\Omlex\\Object\\'.self::$types[$type];
        if (!class_exists($class)) {
            throw new ObjectException('Object class is invalid or not found');
        }

        $instance = new $class($object);
        return $instance;
    }

    /**
     * Instantiation is not allowed
     *
     * @return void
     */
    private function __construct()
    {
    }
}
