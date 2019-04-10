<?php 
require_once("vendor/autoload.php");
include "tech/connect.php";

// Configure API key authorization: api-key
$config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKeyPrefix('api-key', 'Bearer');

$apiInstance = new SendinBlue\Client\Api\EmailCampaignsApi(
// If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
// This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);


try {
    $result = $apiInstance->getEmailCampaigns(null,"sent")->getCampaigns();
    $quante = $apiInstance->getEmailCampaigns(null,"sent")->getCount();


    for ($i=0;$i<$quante;$i++) {

        $id = $result[$i]["id"];
        $title = $result[$i]["subject"];
        $name = $result[$i]["name"];
        $htmlcontent = $result[$i]["htmlContent"];
        $scheduledat = substr($result[$i]["scheduledAt"],0,10);
        $url = "https://my.sendinblue.com/camp/report/id/".$result[$i]["id"];

        $esiste = $conn->query("SELECT id FROM campaigns where id = '".$id."'")->num_rows;

        if ($esiste == 0) {

            $conn->query("INSERT INTO campaigns (id , name, title, scheduledat, url, htmlcontent, type) VALUES ('".$id."' , '".$name."' , '".$title."' , '".$scheduledat."' , '". mysqli_real_escape_string($conn,$url)."', '". mysqli_real_escape_string($conn,$htmlcontent)."', 'email')");

            if ($id != NULL) { echo "aggiunta la campagna con id= ".$id;}

        }

    }

    //nella tabella si trova una campagna con ID = 0. L'inserimento di tale riga della tabella è legata al design del database di Sendinblue a cui non abbiamo accesso diretto.

} catch (Exception $e) {
    echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;
}


