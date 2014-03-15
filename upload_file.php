<?php
require 'vendor/autoload.php';

use OpenCloud\Rackspace;

print "Creating client...\n";
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => getenv('OS_USERNAME'),
    'apiKey' => getenv('NOVA_API_KEY')
));

print "Attaching to object storage...\n";
try {
    $storage = $client->objectStoreService('cloudFiles', getenv('OS_REGION_NAME'));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {
    echo $e->getResponse();
    die("OOPS\n");
}

// create a container
print "Creating a container...\n";
$container = $storage->createContainer('MidWestPHP');
if ($container === false)
    $container = $storage->getContainer('MidWestPHP');

// create an object (empty)
print "Creating an object...\n";
use OpenCloud\ObjectStore\Resource\DataObject;
$container->uploadObject('myfile', fopen('upload_file.php', 'r+'));

// list objects
print "Listing objects:\n";
$files = $container->objectList();
while ($file = $files->next()) {
	printf("* %s (%d)\n.", $file->getName(), $file->getContentLength());
}
