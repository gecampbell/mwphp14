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
    $compute = $client->computeService(
    	'cloudServersOpenStack', getenv('OS_REGION_NAME'));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {
    echo $e->getResponse();
    die("OOPS\n");
}

print "Getting an image...\n";
$centos = $compute->image('85019bd8-fb5d-4230-b3bc-63b192800f28');

print "Getting a flavor...\n";
$flavor = $compute->flavor('performance1-1');

print "Creating a server...\n";

use OpenCloud\Compute\Constants\Network;

$server = $compute->server();

try {
    $response = $server->create(array(
        'name'     => 'My lovely server',
        'image'    => $centos,
        'flavor'   => $flavor,
        'networks' => array(
            $compute->network(Network::RAX_PUBLIC),
            $compute->network(Network::RAX_PRIVATE)
        )
    ));
} catch (\Guzzle\Http\Exception\BadResponseException $e) {

    // No! Something failed. Let's find out:

    $responseBody = (string) $e->getResponse()->getBody();
    $statusCode   = $e->getResponse()->getStatusCode();
    $headers      = $e->getResponse()->getHeaderLines();

    echo sprintf("Status: %s\nBody: %s\nHeaders: %s", $statusCode, $responseBody, implode(', ', $headers));
}

use OpenCloud\Compute\Constants\ServerState;

$callback = function($server) {
    if (!empty($server->error)) {
        var_dump($server->error);
        exit;
    } else {
        echo sprintf(
            "\rWaiting on %s/%-12s %4s%%",
            $server->name(),
            $server->status(),
            isset($server->progress) ? $server->progress : 0
        );
    }
};

$server->waitFor(ServerState::ACTIVE, 600, $callback);

print "\nDONE\n";
