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

$conn->query("TRUNCATE TABLE sms_subs");

try {

$result = $apiInstance->getContactsFromList(14)->getContacts();
$quanti = count($result);

    for ($i=0;$i<$quanti;++$i) {

        
        $add_mail = $result[$i]['email'];
        $sms_optout = $result[$i]['smsBlacklisted'];
        $add_sms = $result[$i]['attributes']->SMS;
        $add_nome = $result[$i]['attributes']->NOME;
        $add_cognome = $result[$i]['attributes']->SURNAME;

        $conn->query("INSERT INTO sms_subs (email, smsbl, nome, cognome, SMS) VALUES ('".$add_mail."', '".$add_optout."', '".$add_nome."', '".$add_cognome."', '".$add_sms."')");


    }


} catch (Exception $e) {
    echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;
}

