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

$conn->query("TRUNCATE TABLE email_check"); //svuoto tabella delle mail da controllare
$conn->query("TRUNCATE TABLE email_check_blacklisted"); //svuoto tabella dei disiscritti


try {
    $quanti = $apiInstance->getContacts()->getCount();

    $volte = ($quanti / 1000) + 1;

    for ($c=0;$c<=$volte;$c++) {

        $result = $apiInstance->getContacts(1000,$c*1000)->getContacts();

        for ($i=0;$i<1000;$i++) {

            $add_mail = $result[$i]["email"];
            $add_optout = $result[$i]["emailBlacklisted"];
            $add_date = substr($result[$i]["modifiedAt"],0,10);

            //print_r($add_optout."<BR>");

            if ((strpos($add_mail, 'mailin-sms') === false) and ($add_mail != NULL) and ($add_optout == NULL)) {

                $conn->query("INSERT INTO email_check (email, status, mod_date) VALUES ('".$add_mail."', 'optin', '".$add_date."')");

            }

            if ((strpos($add_mail, 'mailin-sms') === false) and ($add_mail != NULL) and ($add_optout != NULL))
                {  $conn->query("INSERT INTO email_check_blacklisted (email, status, optout_date) VALUES ('".$add_mail."', 'optout', '".$add_date."')");

                //print_r("INSERT INTO email_check_blacklisted (email, status, optout_date) VALUES ('".$add_mail."', 'optout', '".$add_date."')"."<BR>");
                }

        }
    }


} catch (Exception $e) {
    echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;
}

