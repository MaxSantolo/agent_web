<?php 
require_once("vendor/autoload.php");
include "tech/connect.php";

// Configure API key authorization: api-key
$config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('api-key', 'Bearer');

$apiInstance = new SendinBlue\Client\Api\ContactsApi (
// If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
// This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);



$email2check = $conn->query("SELECT email FROM email_check limit 10");

for($e = 0; $emails[$e] = mysqli_fetch_assoc($email2check); $e++) ;

$quante_email = count($emails);

    for($f=0;$f<=$quante_email;$f++) {

        $email = $emails[$f]["email"];

        if ($email != NULL) {
            $result = $apiInstance->getContactStats($email)->getMessagesSent();
            $quante_azioni = count($result);

        }
        for ($i = 0; $i < $quante_azioni; $i++) {

            $id_campagne = $result[$i]["campaignId"];
            $data_evento = $result[$i]["eventTime"]->format('Y-m-d');

            $conn->query("INSERT INTO campaigns_react (email, id_campagna, reazione, data) VALUES ('" . $email . "', '" . $id_campagne . "', 'sent', '" . $data_evento . "')");
        }
    }





