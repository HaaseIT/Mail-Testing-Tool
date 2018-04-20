<?php

/*
Dependencies:
PHPMailer, functions.misc.inc.php - see github.com/elliebre Repo: DiesundDas
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

//include_once('phpmailer/PHPMailerAutoload.php');
include_once('vendor/autoload.php');
//include_once('functions.misc.inc.php');

if (is_file('./config.php')) {
    require './config.php';
} else {
    require './config.dist.php';
}

// Check for available attachment images for embedding
$hDirEmbImgAttachments = opendir('./'.ATTACHMENT_DIRECTORY.ATTACHMENT_DIRECTORY_EMBEDDEDIMG);
$bEmbImgAttachmentsExist = false;
if ($hDirEmbImgAttachments) {
    while($sDirentry = readdir($hDirEmbImgAttachments)) {
        if ($sDirentry[0] == '.') continue; // no hidden files
        elseif (@is_dir('./'.ATTACHMENT_DIRECTORY.ATTACHMENT_DIRECTORY_EMBEDDEDIMG.$sDirentry)) {
            continue;
        } else {
            $TMP["file"] = @GetImageSize('./'.ATTACHMENT_DIRECTORY.ATTACHMENT_DIRECTORY_EMBEDDEDIMG.$sDirentry);
            if ($TMP["file"][2] == 1 || $TMP["file"][2] == 2 || $TMP["file"][2] == 3) {
                $aDirentry = pathinfo($sDirentry);
                $sEmbImgAttachments[$aDirentry["filename"]] = $aDirentry["basename"];
                $bEmbImgAttachmentsExist = true;
            } // endif is this a gif/jpeg/png?
        }
    } // endwhile
    unset($sDirentry);
    closedir($hDirEmbImgAttachments);
}


// Check for available attachments for embedding
$hDirEmbAttachments = opendir('./'.ATTACHMENT_DIRECTORY);
$bEmbAttachmentsExist = false;
if ($hDirEmbAttachments) {
    while($sDirentry = readdir($hDirEmbAttachments)) {
        if ($sDirentry[0] == '.') continue; // no hidden files
        elseif (@is_dir('./'.ATTACHMENT_DIRECTORY.$sDirentry)) {
            continue;
        } else {
            $aDirentry = pathinfo($sDirentry);
            $sEmbAttachments[$aDirentry["filename"]] = $aDirentry["basename"];
            $bEmbAttachmentsExist = true;
        }
    } // endwhile
    unset($sDirentry);
    closedir($hDirEmbAttachments);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="author" content="Marcus Haase, Haase IT">
    <title>Haase IT Mail Testscript</title>
</head>
<body>
<style type="text/css">
    body{
        background: #cfcfcf;
    }
    select option:disabled{
        background: #cfcfcf;
        color: #000;
    }
    #embedimage{width: 800px;}
</style>
<?php
if (isset($_POST["action"]) && $_POST["action"] == 'send') {
    $sRandomstring = \HaaseIT\Tools::generateRandomString(6);
    if (isset($_POST["recipient"]) && array_key_exists($_POST["recipient"], $C["mail_to"])) {
        $mMailto = $C["mail_to"][$_POST["recipient"]];
    } else {
        $mMailto = $C["mail_to"][0];
    }

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';

    $mail->isSendmail();

    $mail->From = $C["mail_from_email"];
    $mail->FromName = $C["mail_from_user"];
    if (is_array($mMailto)) {
        foreach ($mMailto as $sMailto) {
            $mail->addAddress($sMailto);
        }
    } else {
        $mail->addAddress($mMailto);
    }

    $mail->isHTML(true);

    if ($bEmbImgAttachmentsExist && isset($_REQUEST["EmbImg"]) && is_array($_REQUEST["EmbImg"])) {
        foreach ($_REQUEST["EmbImg"] as $sValue) {
            if (isset($sEmbImgAttachments[$sValue])) {
                $mail->AddEmbeddedImage('./'.ATTACHMENT_DIRECTORY.ATTACHMENT_DIRECTORY_EMBEDDEDIMG.basename($sEmbImgAttachments[$sValue]), $sValue);
            }
        }
    }

    if ($bEmbAttachmentsExist && isset($_REQUEST["EmbAtt"]) && is_array($_REQUEST["EmbAtt"])) {
        foreach ($_REQUEST["EmbAtt"] as $sValue) {
            if (isset($sEmbAttachments[$sValue])) {
                $mail->addAttachment('./'.ATTACHMENT_DIRECTORY.basename($sEmbAttachments[$sValue]), $sEmbAttachments[$sValue]);
            }
        }
    }

    $mail->Subject = $C["mail_subject"] . ' ' . $sRandomstring;

    $sMailcontenthtml = $_POST["mailcontent"];
    $sMailcontenttext = '';

    if (isset($C["premailer_enable"]) && $C["premailer_enable"] && isset($_POST["usepremailer"]) && $_POST["usepremailer"] == 'yes') {
        $foo = exec('echo "'.str_replace('"', '\"', $sMailcontenthtml).'" | '.$C["premailer_executable"].' ', $aMailcontenthtml);
        $sMailcontenthtml = implode("\n", $aMailcontenthtml);
        if (isset($C["premailer_generate_plaintext"]) && $C["premailer_generate_plaintext"]) {
            $foo = exec('echo "'.str_replace('"', '\"', $sMailcontenthtml).'" | premailer -r --mode txt', $aMailcontenttext);
            $sMailcontenttext = implode("\n", $aMailcontenttext);
        }
    }

    $mail->Body = $sMailcontenthtml;
    if (isset($C["premailer_generate_plaintext"]) && $C["premailer_generate_plaintext"] && $sMailcontenttext != '') $mail->AltBody = $sMailcontenttext;

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Mail versandt: <script type="text/javascript">
    <!--
    var Jetzt = new Date();
    var Tag = Jetzt.getDate();
    var Monat = Jetzt.getMonth() + 1;
    var Jahr = Jetzt.getYear();
    var Stunden = Jetzt.getHours();
    var Minuten = Jetzt.getMinutes();
    var Sekunden = Jetzt.getSeconds();
    var NachVollMinuten  = ((Minuten < 10) ? ":0" : ":");
    var NachVollSekunden  = ((Sekunden < 10) ? ":0" : ":");
    if (Jahr<2000) Jahr=Jahr+1900;
    document.write(Tag + "." + Monat + "." + Jahr + "  " + Stunden + NachVollMinuten + Minuten + NachVollSekunden + Sekunden);
    //-->
</script><br>An: ';
        if (is_array($C["mail_to"][$_POST["recipient"]])) {
            echo implode(', ', $C["mail_to"][$_POST["recipient"]]);
        } else {
            echo $C["mail_to"][$_POST["recipient"]];
        }
        echo '<br>ID: ' . $sRandomstring;
    }

    if (isset($C["log_mails"]) && $C["log_mails"]) {
        // write to file
        file_put_contents('./log/' . date("Y-m-d-H-i-s") . '-' . $sRandomstring . '.html', $_POST["mailcontent"]);
        if (isset($C["premailer_enable"]) && $C["premailer_enable"] && isset($_POST["usepremailer"]) && $_POST["usepremailer"] == 'yes') {
            file_put_contents('./log/' . date("Y-m-d-H-i-s") . '-' . $sRandomstring . '-compiled.html', $aMailcontenthtml);
        }
    }
}
?>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
    <select name="recipient">
        <?php
        foreach ($C["mail_to"] as $sKey => $mValue) {
            echo '<option value="'.$sKey.'"'.(isset($_POST["recipient"]) && $_POST["recipient"] == $sKey ? ' selected' : '').(is_array($mValue) || \filter_var($mValue, FILTER_VALIDATE_EMAIL) ? '' : ' disabled').'>';
            if (is_array($mValue)) {
                echo implode(', ', $mValue);
            } else {
                echo $mValue;
            }
            echo '</option>';
        }
        ?>
    </select><br>
    <textarea name="mailcontent" rows="40" cols="120"><?php
        $sMailcontent = \HaaseIT\Tools::getFormfield('mailcontent');
        if (isset($_POST["preservenbsp"]) && $_POST["preservenbsp"] == "yes") {
            $sMailcontent = mb_ereg_replace('&nbsp;', '&amp;nbsp;', $sMailcontent);
        }
        echo $sMailcontent;
        ?></textarea>
    <input type="hidden" name="action" value="send">
    <br>
    <input type="checkbox" name="preservenbsp" id="preservenbsp" value="yes"<?php echo (\HaaseIT\Tools::getCheckbox('preservenbsp', 'yes') ? ' checked' : '') ?>><label for="preservenbsp">Preserve &amp;nbsp;</label>
    <?php if (isset($C["premailer_enable"]) && $C["premailer_enable"]) { ?>
        <br>
        <input type="checkbox" name="usepremailer" id="usepremailer" value="yes"<?php echo (\HaaseIT\Tools::getCheckbox('usepremailer', 'yes') ? ' checked' : '') ?>><label for="usepremailer">Use Premailer</label>
        <?php
    }
    if ($bEmbImgAttachmentsExist) {
        echo '<br><label for="embedimage">Embed these images:</label><br>';
        $iSelectSize = count($sEmbImgAttachments);
        if ($iSelectSize > 5) $iSelectSize = 5;
        echo '<select name="EmbImg[]" id="embedimage" size="'.$iSelectSize.'" multiple="multiple">';
        foreach ($sEmbImgAttachments as $sKey => $sValue) {
            echo '<option value="'.$sKey.'"';
            if (isset($_REQUEST["EmbImg"]) && in_array($sKey, $_REQUEST["EmbImg"])) echo ' selected';
            echo '>'.$sValue.' - available as: ';
            echo '&lt;img src="cid:'.$sKey.'">';
            echo '</option>';
        }
        echo '</select>';
    }
    if ($bEmbAttachmentsExist) {
        echo '<br><label for="embedatt">Embed these attachments:</label><br>';
        $iSelectSize = count($sEmbAttachments);
        if ($iSelectSize > 5) $iSelectSize = 5;
        echo '<select name="EmbAtt[]" id="embedatt" size="'.$iSelectSize.'" multiple="multiple">';
        foreach ($sEmbAttachments as $sKey => $sValue) {
            echo '<option value="'.$sKey.'"';
            if (isset($_REQUEST["EmbAtt"]) && in_array($sKey, $_REQUEST["EmbAtt"])) echo ' selected';
            echo '>'.$sValue;
            echo '</option>';
        }
        echo '</select>';
    }
    ?>
    <br>
    <input type="submit" value="Send">
</form>
<br>
<?php if (isset($C["log_mails"]) && $C["log_mails"]) { ?><a href="log/" target="_blank">Log des Mailversands</a><?php } ?>
</body>
