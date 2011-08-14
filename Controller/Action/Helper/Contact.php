<?php
/**
* Contact controller (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 4/8/2009
*/
class Zx_Controller_Action_Helper_Contact
{
	function run(&$controller, Zend_Controller_Request_Abstract $request, $config = null)
	{
		if (!empty($config['form'])) {
			$form = new $config['form'];
		} else {
			if (!empty($config['options'])) {
				$form = new Zx_Form_Contact($config['options']);
			} else {
				$form = new Zx_Form_Contact();
			}
		}

		$conf = $controller->conf;

		// post message
		if ($request->isPost())
		{
			$formData = $request->getPost();

			if ($form->isValid($formData))
			{
				$v = $form->getValues();

				if (!empty($config['options']['skip_person'])) {$v['person'] = 'Unknown person';}
				if (!empty($config['options']['skip_email'])) {$v['email'] = 'Unknown E-mail';}
				if (!empty($config['options']['skip_phone'])) {$v['phone'] = 'Unknown phone';}

				// send e-mail
				if (empty($controller->view->error))
				{
					$msg = $conf->msg->feedback->greets . " (" . $controller->conf->site->url . ").\n";
					if (!empty($v['person'])) {$msg .= $controller->conf->msg->feedback->person . ": " . $v['person'] . "\n";}
					if (!empty($v['address'])) {$msg .= $controller->conf->msg->feedback->address . ": " . $v['address'] . "\n";}
					if (!empty($v['phone'])) {$msg .= $controller->conf->msg->feedback->phone . ": " . $v['phone'] . "\n";}
					if (!empty($v['subject'])) {$msg .= $controller->conf->msg->feedback->subject . ": " . $v['subject'] . "\n";}
					$msg .= $controller->conf->msg->feedback->txt . ":\n" . $v['txt'] . "\n--\n" . $conf->site->admin->title;

					#$conf = Zend_Registry::get('conf');

					$mail = new Zend_Mail('utf-8');
					$mail->setBodyText($msg);
					#$mail->setBodyHtml($msg);
					$mail->setFrom($v['email'], $v['person']);

					if (!empty($config['to'])) {
						$mail->addTo($config['to'], $config['to']);
					// several recepients (fx "avd@informproject.info,c0d3r@inbox.ru")
					} elseif (strpos($conf->site->admin->email, ',') !== false) {
						$a = explode(',',$conf->site->admin->email);
						foreach ($a as $v) {
							$mail->addTo(trim($v), $conf->site->admin->title);
						}
					// just one recipient
					} else {
						$mail->addTo($conf->site->admin->email, $conf->site->admin->title);
					}

					$mail->addCc($conf->support->email, $conf->support->title);
					$mail->setSubject("Feedback from " . $conf->site->url);
					if (LOCATION == 'stable') {
						$res = $mail->send();
					} else {
						$res = true;
					}

					$controller->setVar('sent', $res);
					if ($res) {
						$controller->setVar('sentMsg', $controller->conf->msg->feedback->sent);
					} else {
						$controller->setVar('sentMsg', $controller->conf->msg->feedback->fail);
					}

					// save to DB
					$feedback = new Zx_Db_Table_Feedback();
					$row = $feedback->createRow();
					$row->res = $res;
					$row->person = $v['person'];
					$row->phone = $v['phone'];
					$row->email = $v['email'];
					$row->txt = $v['txt'];
					$row->dt = date('Y-m-d H:i:s');
					$row->ua = $_SERVER['HTTP_USER_AGENT'];
					$row->ip = ip2long($_SERVER['REMOTE_ADDR']);
					if (!empty($conf->site->feedback->domain)) {
						$row->domain = $conf->site->url;
					}
					if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {$row->ip2 = ip2long($_SERVER['HTTP_X_FORWARDED_FOR']);}

					$res = $row->save();
				}
			}

		} else {
			$controller->setVar('sent', false);
		}

		$controller->setVar('form', $form);

		// moved to ContactController 4/8/2009!
		#if (!empty($config['text'])) {
		#	$controller->textRow('contact');
		#}
	}
}
