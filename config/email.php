<?php

if (EMAIL_METHOD == 'smtp') {
// send sent using smtp
// smtp server configuration

$transport = Swift_SmtpTransport::newInstance(SMTP_HOST, SMTP_PORT)->setUsername(SMTP_USERNAME)->setPassword(SMTP_PASSWORD);

}
else if (EMAIL_METHOD == 'mail') {
// email sent using usual php mail
$transport = Swift_MailTransport::newInstance();
}

else if (EMAIL_METHOD == 'sendmail') {
// email sent using sendmail

$transport = Swift_SendmailTransport::newInstance(SENDMAIL_PATH);

}
