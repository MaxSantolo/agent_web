<?php
require_once("vendor/autoload.php");
include "tech/connect.php";
require_once $_SERVER['DOCUMENT_ROOT']."/tech/class/PHPMailerAutoload.php";
require_once $_SERVER['DOCUMENT_ROOT']."/tech/classes/Mail.php";
require_once $_SERVER['DOCUMENT_ROOT']."/tech/classes/Log.php";
require_once $_SERVER['DOCUMENT_ROOT']."/tech/classes/PickLog.php";

$config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
$apiInstance = new SendinBlue\Client\Api\ContactsApi (

    new GuzzleHttp\Client(),
    $config
);

$mail = new Mail();
$plog = new PickLog();
$msg = "";
$errormsg = "";

try {

    $sqlSMSSubs = "SELECT * FROM sms_subs WHERE to_add = '1' AND SMS != ''";
    $upd_list = $conn->query($sqlSMSSubs);

    if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlSMSSubs . " - Errore: " . $conn->error . PHP_EOL;
    else {
        $msg = "Eseguita la Q: " . $sqlSMSSubs . " - Righe: " . $conn->affected_rows . PHP_EOL;
    }


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
    $errormsg .= "Errore di chiamata della API si SendinBlue AccountApi->createContact: " . $e->getMessage();
}

//log ed email errore

if ($errormsg == "") {

    $msg = json_encode($msg);
    Log::wLog("Contatti SMS aggiunti.");
    $url = "?app=AGENT&content={$msg}&action=SMS_CRM_SENDINBLUE";

} else {

    $errormsg = json_encode($errormsg);
    $smail = $mail->sendErrorEmail($errormsg,"AZN: SMS_SENDINBLUE");
    Log::wLog($errormsg);
    $url = "?app=AGENT&content={$errormsg}&action=SMS_CRM_SENDINBLUE";
}

header("Location: http://213.215.209.158:97/API/create_log.php".$url);