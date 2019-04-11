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

$sqlTruncSMSSub = "TRUNCATE TABLE sms_sub";
$conn->query($sqlTruncEmailCheck); //svuoto tabella delle mail da controllare

if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlTruncSMSSub . " - Errore: " . $conn->error . PHP_EOL;
else {
    $msg = "Eseguita la Q: " . $sqlTruncSMSSub . " - Righe: " . $conn->affected_rows . PHP_EOL;
}

try {

$result = $apiInstance->getContactsFromList(14)->getContacts();
$quanti = count($result);

    for ($i=0;$i<$quanti;++$i) {

        
        $add_mail = str_replace("'", '', $result[$i]['email']);
        $sms_optout = $result[$i]['smsBlacklisted'];
        $add_sms = $result[$i]['attributes']->SMS;
        $add_nome = $result[$i]['attributes']->NOME;
        $add_cognome = $result[$i]['attributes']->SURNAME;

        $sqlInsertSMSSub = "INSERT INTO sms_subs (email, smsbl, nome, cognome, SMS) VALUES ('".$add_mail."', '".$add_optout."', '".$add_nome."', '".$add_cognome."', '".$add_sms."')";
        $conn->query($sqlInsertSMSSub);

        if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlInsertEmailCheck . " - Errore: " . $conn->error . PHP_EOL;

    }

    $msg .= "Effettuati {$quanti} inserimenti nelle tabelle troncate";

} catch (Exception $e) {
    $errormsg .= "Errore di chiamata della API si SendinBlue AccountApi->getContactsFromList: " . $e->getMessage();
}

//log ed email errore

if ($errormsg == "") {

    Log::wLog("Elenchi di numeri di telefono rigenerati. Totale: {$quanti} inserimenti.");
    $plog->sendLog(array("app"=>"AGENT","content"=>$msg,"action"=>"SMS_CRM_SENDINBLUE"));

} else {

    $smail = $mail->sendErrorEmail($errormsg,"AZN: SMS_SENDINBLUE");
    Log::wLog($errormsg);
    $plog->sendLog(array("app"=>"AGENT","content"=>$errormsg,"action"=>"SMS_CRM_SENDINBLUE"));
}

