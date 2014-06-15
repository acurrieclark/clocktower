<?php

/**
*
*/
class mailer
{

	private function __construct()
	{}

		static function send_stored() {

			// configures $transport
			require ROOT . DS . 'config' . DS . 'email.php';
			$mailer = Swift_Mailer::newInstance($transport);

			$emails_per_minute = floor(EMAILS_PER_HOUR / 60);

			$mailer->registerPlugin(new Swift_Plugins_ThrottlerPlugin(
			  $emails_per_minute , Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE
			));

			$messages = mvaccmail::find_all();

			if (!is_array($messages)) $messages = array();

			$numSent = 0;
			$failedRecipients = array();

			foreach($messages as $message) {
				$message_to_send = Swift_Message::newInstance($message->subject->value)
					  ->setFrom($message->mail_from->value)
					  ->setTo($message->mail_to->value)
					  ->setBody($message->html->value, 'text/html')
					  ->addPart($message->plain->value, 'text/plain');

				$numSent += $mailer->send($message_to_send, $failedRecipients);

				if (!in_array($message->mail_to->value, $failedRecipients)) {
					logger::mailer('Message sent to '.$message->mail_to->value);
					$message->destroy();
				}
			}

			logger::mailer("Sent $numSent messages");

		}

	static function send($to, $from, $subject, $plain, $html = '') {

		require ROOT . DS . 'config' . DS . 'email.php';
		$mailer = Swift_Mailer::newInstance($transport);

		$failedRecipients = array();

		if (DEVELOPMENT_ENVIRONMENT != TRUE) {
			$message_to_send = Swift_Message::newInstance($subject)
			  ->setFrom($from)
			  ->setTo($to)
			  ->setBody($html, 'text/html')
			  ->addPart($plain, 'text/plain');

			$mailer->send($message_to_send, $failedRecipients);
			$admin_subject_prefix = "";
		}
		else {
			$admin_subject_prefix = "DEVELOPMENT MODE - ";
		}
		

		$check_message_to_send = Swift_Message::newInstance($admin_subject_prefix.'RA Email - '.$subject)
			  ->setFrom($from)
			  ->setTo(array('acurrieclark@gmail.com', 'OR@davericks.com'))
			  ->setBody($html, 'text/html')
			  ->addPart($plain, 'text/plain');

		$mailer->send($check_message_to_send, $failedRecipients);

		if (!in_array($to, $failedRecipients)) {
			logger::mailer('Message sent to '.$to);
			return true;
		}
		else {
			logger::mailer('Error: Message could not be sent to '.$to);
			return false;
		}


	}

	static function queue($to, $from, $subject, $plain, $html = '') {

		if (DEVELOPMENT_ENVIRONMENT == TRUE) {
			$to = "acurrieclark@gmail.com";
		}

		$mail_properties['mail_to'] = $to;
		$mail_properties['mail_from'] = $from;
		$mail_properties['subject'] = $subject;


		$mail_properties['plain'] = $plain;
		$mail_properties['html'] = $html;


		logger::mailer($mail_properties['html']);

		$email = new mvaccmail($mail_properties);

 	   	if ($email->save())
 	   		return true;
 	   	else return false;

	}


}


?>
