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



$email2check = $conn->query("SELECT email FROM email_check where email IS NOT NULL LIMIT 200");

//for($e = 0; $emails[$e] = mysqli_fetch_assoc($email2check); $e++) ;

//$quante_email = count($emails)-1; //eliminare ultima iterazione dell'array generato da mysqli_fetch_assoc

    //for($f=0;$f<$quante_email;$f++) {

while ($emails = $email2check->fetch_assoc()) {

        $email = $emails["email"];

        //if ($email != NULL) {
        $result = $apiInstance->getContactStats($email)->getClicked();
        $quante_azioni = count($result);

        //}

        //if ($result != NULL) {

            for ($i = 0; $i < $quante_azioni; $i++) {

                //print_r($result[$i]["campaignId"]."<BR>");
                //print_r($result[$i]["eventTime"]["date"]."<BR>");
                $id_campagne = $result[$i]["campaignId"];
                // if ($result[$i]["eventTime"] != NULL) { $data_evento = $result[$i]["eventTime"]->format('Y-m-d'); } else $data_evento = '0000-00-00';

                //$conn->query("INSERT INTO campaigns_react (email, id_campagna, reazione, data) VALUES ('" . $email . "', '" . $id_campagne . "', 'Cliccata', '" . date('Y-m-d') . "')");
                print_r("INSERT INTO campaigns_react (email, id_campagna, reazione, data) VALUES ('" . $email . "', '" . $id_campagne . "', 'Cliccata', '" . date('Y-m-d') . "')<BR>");
            }
        //}
    }





