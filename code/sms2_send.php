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
} catch (Exception $e) {
    echo 'Exception when calling AuthenticationApi->authToken: ', $e->getMessage(), PHP_EOL;
}

$config = Telstra_Messaging\Configuration::getDefaultConfiguration()->setAccessToken($result->getAccessToken());

$provisionApi = new ProvisioningApi(
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $provisionApi->getSubscription();
    $sms_number = $result->getDestinationAddress();
} catch (Exception $e) {
    echo 'Exception when calling ProvisioningApi->getSubscription: ', $e->getMessage(), PHP_EOL;
}

if (!isset($sms_number)) {
    $body = new ProvisionNumberRequest([
        'active_days' => 1,
    ]);

    try {
        $result = $provisionApi->createSubscription($body);
    } catch (Exception $e) {
        echo 'Exception when calling ProvisioningApi->createSubscription: ', $e->getMessage(), PHP_EOL;
    }

    // Allow a little time for the subscription to process.
    sleep(10);

    try {
        $result = $provisionApi->getSubscription();
        print_r($result);
    } catch (Exception $e) {
        echo 'Exception when calling ProvisioningApi->getSubscription: ', $e->getMessage(), PHP_EOL;
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
    echo 'Exception when calling MessagingApi->SendSMS: ', $e->getMessage(), PHP_EOL;
}
