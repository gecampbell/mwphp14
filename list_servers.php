<?php
require 'vendor/autoload.php';

use OpenCloud\Rackspace;

print "Creating client...\n";
$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => getenv('OS_USERNAME'),
    'apiKey' => getenv('NOVA_API_KEY'),
    'tenantName' => getenv('OS_TENANT_NAME')
));

print "Attaching to compute...\n";
try {
    $compute = $client->computeService('cloudServersOpenStack', getenv('OS_REGION_NAME'));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {
    echo $e->getResponse();
}

$list = $compute->serverList(true);

foreach($list as $server) {
    printf("%20s %-20s %-10d %s\n",
        $server->Id(),
        $server->Name(),
        $server->updated,
        $server->Status());
}
