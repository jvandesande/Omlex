Omlex is a lightweight PHP 5.3 library for handling oEmbed services.

```php
<?php

require_once 'Omlex/ClassLoader.php';
Omlex\ClassLoader::register();

$ombed = new Omlex\OEmbed(
    'http://www.flickr.com/photos/24887479@N06/2656764466/', // URL
    'http://www.flickr.com/services/oembed/'                 // API
);
$object = $ombed->getObject();

echo $object->__toString();
```

Omlex is tested using PHPUnit. The run the test suite, execute the following
command:

    $ phpunit test/
