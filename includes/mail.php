<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

/**
 * Отправляет электронное письмо
 *
 * @param string|array $recipient получатель/получатели
 * @param string $subject тема письма
 * @param string $body содержание письма
 * @param string $from адрес отправителя
 * @return boolean
 */
function send_mail($recipient, $subject, $body, $from = '')
{
	Custom::inc('plugins/class.phpmailer.php');

	$mail = new PHPMailer();

	if (defined('SMTP_MAIL') && SMTP_MAIL && SMTP_HOST && SMTP_LOGIN && SMTP_PASSWORD)
	{
		$mail->isSMTP(); // telling the class to use SMTP
		$mail->Host       = SMTP_HOST;  // SMTP server
		$mail->SMTPDebug  = MOD_DEVELOPER ? 1 : 0; // enables SMTP debug information (for testing)
										           // 1 = errors and messages
										           // 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		if (SMTP_PORT)
		{
			$mail->Port   = SMTP_PORT;          // set the SMTP port for the GMAIL server
		}
		$mail->Username   = SMTP_LOGIN;    // SMTP account username
		$mail->Password   = SMTP_PASSWORD; // SMTP account password
	}
	
	$mail->setFrom($from ? $from : EMAIL_CONFIG, TITLE);
	$mail->Subject = $subject;
	$mail->msgHTML($body);

	if (is_array($recipient))
	{
		foreach ($recipient as $to)
		{
			$mail->addAddress($to);
		}
	}
	elseif (strpos($recipient, ',') !== false)
	{
		$recipients = explode(',', $recipient);
		foreach ($recipients as $r)
		{
			$mail->addAddress(trim($r));
		}
	}
	else
	{
		$mail->addAddress($recipient);
	}

	// TO_DO: Don't mix up these modes; ssl on port 587 or tls on port 465 will not work.
	// TO_DO: PHPMailer 5.2.10 introduced opportunistic TLS - if it sees that the server is advertising TLS encryption (after you have connected to the server), it enables encryption automatically, even if you have not set SMTPSecure. This might cause issues if the server is advertising TLS with an invalid certificate, but you can turn it off with $mail->SMTPAutoTLS = false;.
	// TO_DO: Failing that, you can allow insecure connections via the SMTPOptions property introduced in PHPMailer 5.2.10 (it's possible to do this by subclassing the SMTP class in earlier versions), though this is not recommended as it defeats much of the point of using a secure transport at all:
	//$mail->SMTPOptions = array(
	//	'ssl' => array(
	//		'verify_peer' => false,
	//		'verify_peer_name' => false,
	//		'allow_self_signed' => true
	//	)
	//);

	$msg_error = false;
	ob_start();
	try {
		$mailssend = $mail->send();
	} catch (Exception $e) {
		$msg_error = $e->getMessage();
	}
	$trace_output = ob_get_contents();
	ob_end_clean();

	if(false !== $msg_error && MOD_DEVELOPER && (! MOD_DEVELOPER_ADMIN || ! empty($_COOKIE['dev'])))
	{
		echo '<br><br>'.'Message could not be sent.'
			.'<br>'.'<b>Mailer Error:</b>'.'<pre>'. $mail->ErrorInfo .'</pre>'
			.'<br>'.'<b>Mailer Log:</b>'.'<br>'.$trace_output.'<br><br>';
	}
	
	return $mailssend;
}
