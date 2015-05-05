<?php

/**
*
*/
class texter
{

	private function __construct()
	{}

		static function send_stored() {

			include_once(ROOT.'/library/vendor/other/nexmo/NexmoMessage.php');

			$messages = text_message::find_all(array('where' => array('Sent' => 'No')));

			if (!is_array($messages)) $messages = array();

			$numSent = 0;
			$numAttempts = 0;

			$start = microtime(true);
			$time_elapsed_secs = microtime(true) - $start;

			foreach($messages as $message) {
				$message_ids = array();
				while (($numAttempts / $time_elapsed_secs) > (TEXTS_PER_SECOND)) {
					$time_elapsed_secs = microtime(true) - $start;
				}
				$message_to_send = new NexmoMessage(NEXMO_KEY, NEXMO_SECRET);
				$response = $message_to_send->sendText (message::add_international_code($message->Number->value) , TEXT_FROM, $message->Text->value);
				if ($response->messages[0]->status) {
					$message->error_code->value = $response->messages[0]->status;
					$message->error_message->value = $response->messages[0]->errortext;
				}
				else {
					foreach ($response->messages as $message_response) {
						$message_ids[] = $message_response->messageid;
					}
					$message->message_id->value = implode(", ", $message_ids);
					$message->cost->value = $response->cost;
					$message->network->value = $response->messages[0]->network;
					$message->Sent->value = "Yes";
					$numSent++;
				}
				$message->update();
				$numAttempts++;
			}
			$status_text = "Sent $numSent text messages in $time_elapsed_secs seconds.";
			echo $status_text;
			logger::texter($status_text);

		}

	static function send($to, $from, $text) {

		require ROOT . DS . 'config' . DS . 'email.php';
		$mailer = Swift_Mailer::newInstance($transport);

		$failedRecipients = array();

		if (!DEVELOPMENT_ENVIRONMENT) {
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

		if (SEND_ALL_MAIL_TO_DEVELOPER) {
			$check_message_to_send = Swift_Message::newInstance($admin_subject_prefix.$subject)
				  ->setFrom($from)
				  ->setTo(array(DEVELOPER_EMAIL_ADDRESS))
				  ->setBody($html, 'text/html')
				  ->addPart($plain, 'text/plain');

			$mailer->send($check_message_to_send, $failedRecipients);
		}

		if (!in_array($to, $failedRecipients)) {
			logger::mailer('Message sent to '.$to);
			if (DEVELOPMENT_ENVIRONMENT) logger::mailer($plain);
			return true;
		}
		else {
			logger::mailer('Error: Message could not be sent to '.$to);
			return false;
		}


	}

	static function queue($to, $from, $subject, $plain, $html = '') {

		if (DEVELOPMENT_ENVIRONMENT == TRUE) {
			$to = DEVELOPER_EMAIL_ADDRESS;
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
