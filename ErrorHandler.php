<?php
/**
* Zx_Error
* Свой обработчик ошибок
* @example Zx_ErrorHandler::catchException($e); (Kernel.php)
*/
class Zx_ErrorHandler
{
	/**
	* Управление ошибками
	* @param exception $exception Перехватываемое исключение
	*/
	public static function catchException(Exception $exception) {

		// Получение текста ошибки
		$message = $exception->getMessage();

		// Получение трейса ошибки как строки
		$trace = $exception->getTraceAsString();
		$s = "ERROR: " . $message . "\n" . $trace;

		$conf = Zend_Registry::get('conf');

		// Если включен режим отладки отображаем сообщение о ошибке на экран
		if($conf->debug->on)
		{
			Zend_Debug::dump($s);

		// Иначе не выводим сообщение об ошибке!
		// Здесь может происходить логирование ошибки, уведомление вебмастера и т д
		} else {

			// log
			$writer = new Zend_Log_Writer_Stream(ini_get('error_log'));
			$logger = new Zend_Log($writer);
			$logger->log($s, Zend_Log::EMERG);

			// mail
			$s .= "\n\nREQUEST_URI: " . $_SERVER['REQUEST_URI'];
			$s .= "\n\nSERVER:\n" . print_r($_SERVER, 1);

			$mail = new Zend_Mail('utf-8');
			$mail->setBodyText($s);

			if (!empty($conf->site->admin->emailSupport)) {
				$mail->setFrom($conf->site->admin->emailSupport, "Administrator (" . $conf->site->url. ")");
			} else {
				$mail->setFrom($conf->support->email, "Support (" . $conf->site->url. ")");
			}
			$mail->addTo($conf->support->email, $conf->support->title);
			$mail->setSubject("Emergency event at " . $conf->site->url);
			if (LOCATION == 'stable') {
				$res = $mail->send();
			}
		}
		#echo "<!--" . print_r(ini_get('error_log'), 1) . "-->";
		die('Sorry, system error! Please try later.');
	}
}