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

try {
    $upd_list = $conn->query("SELECT * FROM sms_subs WHERE to_add = '1' AND SMS != ''");


    while ($upd_list_data = $upd_list->fetch_assoc()) {

        $array_user = array('email'=>$upd_list_data['email'],
                            "attributes"=> array(
                                                'NOME'=>$upd_list_data['nome'],
                                                'SURNAME'=>$upd_list_data['cognome'],
                                                'SMS'=>$upd_list_data['SMS'],
                                                'FONTE_ORIGINARIA'=>$upd_list_data['origine']
                                            ),
                            'listIds'=>array(14),
                            'updateEnabled'=>TRUE
                            );

        $crea_contatto = new \SendinBlue\Client\Model\CreateContact($array_user);
        $apiInstance->createContact($crea_contatto);

    }




} catch (Exception $e) {
    echo 'Exception when calling AccountApi->getAccount: ', $e->getMessage(), PHP_EOL;
}

