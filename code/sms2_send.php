#!/usr/bin/php -q
<?php


require_once(__DIR__ . '/vendor/autoload.php');

use Telstra_Messaging\Api\AuthenticationApi;
use Telstra_Messaging\Api\MessagingApi;
use Telstra_Messaging\Api\ProvisioningApi;
use Telstra_Messaging\Model\SendSMSRequest;
use Telstra_Messaging\Model\ProvisionNumberRequest;

$recipient = $argv[1];
$message = substr($argv[2], 0, 159);

$apiInstance = new AuthenticationApi();
$client_id = getenv("CLIENT_ID");
$client_secret = getenv("CLIENT_SECRET");

if (empty($client_id) || empty($client_secret)) {
    die("Please set the CLIENT_ID & CLIENT_SECRET environment variables.\n");
}

$sms_number = NULL;

try {
    $result = $apiInstance->authToken($client_id, $client_secret, "client_credentials");
}
catch (Exception $e) {
    echo "Exception when calling AuthenticationApi->authToken:\n", $e->getResponseBody(), PHP_EOL;
}

$config = Telstra_Messaging\Configuration::getDefaultConfiguration()->setAccessToken($result->getAccessToken());

$provisionApi = new ProvisioningApi(
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $provisionApi->getSubscription();
    $sms_number = $result->getDestinationAddress();
    $active_days = $result->getActiveDays();
}
catch (Exception $e) {
    echo "Exception when calling ProvisioningApi->getSubscription:\n", $e->getResponseBody(), PHP_EOL;
}

if (!isset($sms_number) || !isset($active_days) || $active_days <= 0) {
    $body = new ProvisionNumberRequest([
        'active_days' => 365,
    ]);

    try {
        $result = $provisionApi->createSubscription($body);
    }
    catch (Exception $e) {
        echo "Exception when calling ProvisioningApi->createSubscription:\n", $e->getResponseBody(), PHP_EOL;
    }

    try {
        $result = $provisionApi->getSubscription();
        $sms_number = $result->getDestinationAddress();
        $active_days = $result->getActiveDays();
    }
    catch (Exception $e) {
        echo "Exception when calling ProvisioningApi->getSubscription:\n", $e->getResponseBody(), PHP_EOL;
    }
}

$msgApi = new MessagingApi(
    new GuzzleHttp\Client(),
    $config
);

$payload = new SendSMSRequest([
    'to' => $recipient,
    'body' => $message
]);

try {
    $result = $msgApi->sendSMS($payload);
}
catch (Exception $e) {
    echo "Exception when calling MessagingApi->SendSMS:\n", $e->getResponseBody(), PHP_EOL;
}
