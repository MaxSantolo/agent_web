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

$sqlTruncEmailCheck = "TRUNCATE TABLE email_check";
$conn->query($sqlTruncEmailCheck); //svuoto tabella delle mail da controllare

if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlTruncEmailCheck . " - Errore: " . $conn->error . PHP_EOL;
else {
    $msg = "Eseguita la Q: " . $sqlTruncEmailCheck . " - Righe: " . $conn->affected_rows . PHP_EOL;
}

$sqlTruncEmailCheckBlacklisted = "TRUNCATE TABLE email_check_blacklisted";
$conn->query($sqlTruncEmailCheckBlacklisted); //svuoto tabella dei disiscritti

if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlTruncEmailCheckBlacklisted . " - Errore: " . $conn->error . PHP_EOL;
else {
    $msg .= "Eseguita la Q: " . $sqlTruncEmailCheckBlacklisted . " - Righe: " . $conn->affected_rows . PHP_EOL;
}

try {
    $quanti = $apiInstance->getContacts()->getCount();

    $volte = ($quanti / 1000) + 1;

    for ($c=0;$c<=$volte;$c++) {

        $result = $apiInstance->getContacts(1000,$c*1000)->getContacts();

        for ($i=0;$i<1000;$i++) {

            $add_mail = str_replace("'", '', $result[$i]["email"]);
            $add_optout = str_replace("'", '', $result[$i]["emailBlacklisted"]);
            $add_date = substr($result[$i]["modifiedAt"],0,10);

            if ((strpos($add_mail, 'mailin-sms') === false) and ($add_mail != NULL) and ($add_optout == NULL)) {

                $sqlInsertEmailCheck = "INSERT INTO email_check (email, status, mod_date) VALUES ('".$add_mail."', 'optin', '".$add_date."')";
                $conn->query($sqlInsertEmailCheck);

                if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlInsertEmailCheck . " - Errore: " . $conn->error . PHP_EOL;
                            }

            if ((strpos($add_mail, 'mailin-sms') === false) and ($add_mail != NULL) and ($add_optout != NULL))
                {
                    $sqlInsertEmailCheckBlacklisted = "INSERT INTO email_check_blacklisted (email, status, optout_date) VALUES ('".$add_mail."', 'optout', '".$add_date."')";
                    $conn->query($sqlInsertEmailCheckBlacklisted);

                    if ($conn->error) $errormsg = "Impossibile eseguire la query: " . $sqlInsertEmailCheckBlacklisted . " - Errore: " . $conn->error . PHP_EOL;

                }

        }
    }

    $msg .= "Effettuati {$quanti} inserimenti nelle tabelle troncate";

} catch (Exception $e) {
    $errormsg .= "Errore di chiamata della API si SendinBlue AccountApi->getAccount: " . $e->getMessage();
}

//log ed email errore

if ($errormsg == "") {

    $msg = json_encode($msg);
    Log::wLog("Elenchi di email iscritte al sito rigenerati. Totale: {$quanti} inserimenti.");
    $url = "?app=AGENT&content={$msg}&action=NEWSLETTER_CRM_SENDINBLUE";
    //$plog->sendLog(array("app"=>"AGENT","content"=>$msg,"action"=>"NEWSLETTER_CRM_SENDINBLUE"));

} else {

    $errormsg = json_encode($errormsg);
    $smail = $mail->sendErrorEmail($errormsg,"AZN: NEWSLETTER_SENDINBLUE");
    Log::wLog($errormsg);
    $url = "?app=AGENT&content={$errormsg}&action=NEWSLETTER_CRM_SENDINBLUE";
}

header("Location: http://213.215.209.158:97/API/create_log.php".$url);
