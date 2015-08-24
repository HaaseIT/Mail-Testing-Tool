<?php

define("ATTACHMENT_DIRECTORY", 'attachments/');
define("ATTACHMENT_DIRECTORY_EMBEDDEDIMG", 'img/');
date_default_timezone_set("Europe/Berlin");

$C = array(
    'mail_to' => array(
        /*
         either one email address per row or an array containing one email address per row
         a not valid email address will be used as a headline (disabled option)
        eg:
        'mail@john.doe',
        array(
            'mail@john.doe',
            'mail@jane.doe',
        ),
         */
        'mail@john.doe',
    ),
    'mail_from_user' => 'Marcus Haase',
    'mail_from_email' => 'mail@marcus.haase.name',
    'mail_subject' => 'HTML Testmail - '.date("Y-m-d H:i:s"),
    'log_mails' => true,
    'premailer_enable' => true,
    'premailer_executable' => 'premailer',
    'premailer_generate_plaintext' => false,
);
