<?php
/**
* @deprecated
*/
class AuthController extends MainController
{
	protected $_noRender = false;


   function init()
    {
		parent::init();
		
		if (!$this->authAllowed) {
			$this->_redirect('/');die;
		}

		if ($this->authHTTPS)
		{
           	FrontEnd::checkHTTPS();
		}

		$this->_formRender = isset($this->conf->auth->formRender) ? false : true;

 		if (!isset($this->logoutRedirect))
		{
			$this->logoutRedirect = '/';
		}
		$this->users = new Users();
		$this->view->mode = 'insert';
    }

    function indexAction()
    {
        #$this->_redirect('/');
		$this->loginAction();
    }


	/**
	* Напоминание пароля
	*/
    function remindAction()
    {
		#if (!$this->view->identity) {$this->_redirect('auth/login');}
		if (!$this->userRegistrationAllowed) {$this->_exit();}

		#http://ac.lh//auth/remind/hash/72c12639535879b758428c2b24b8cc6b
		//--< hash processing
		$hash = $this->_getParam('hash', '');
		if ($hash)
		{
			if ($this->view->identity)
			{
				$this->_redirect('auth/logout');
			}

			$row = $this->getRow('users', '"' . $hash . '"  = MD5(CONCAT(id, password2))');
			if ($row)
			{
				$row->password = $row->password2;
				$row->password2 = '';
				$res = $row->save();
				if ($res) {
					$this->setN(FrontEnd::getMsg(array('auth', 'remindComplete')));
				}
			} else {
				$this->setN(FrontEnd::getMsg('dnf'), 'errors');
			}
			$this->_redirect('auth/login');
		}
		//-->

		$this->textRow('remind');

		$form = new Form_AuthRemind();

        if ($this->getRequest()->isPost())
		{
			$rawData = $this->getRequest()->getPost();
			if ($form->isValid($rawData))
			{
				$v = $form->getValues();

				$row = $this->getRow('users', 'username = "' . $v['username'] . '" AND email = "' . $v['email'] . '"');
				if (!$row)
				{
					$this->view->notifyerr[] = FrontEnd::getMsg(array('auth', 'userFailed'));
				} else {
					$password2 = Aux::generatePassword();
					$row->password2 = md5($password2);
					$res = $row->save();
					#d($res);
					if ($res)
					{
						$options = array(
							'to' => $v['email'],
							'subject' => 'данные доступа на сайт',
							'body' => 'Это автоматический ответ на запрос напоминания пароля для логина ' . $v['username'] . '.
Так как все пароли хранятся на сайте в зашифрованном виде, восстановить их невозможно.
Для Вас сгенерирован новый пароль ' . $password2 . ', вы можете установить его, перейдя по ссылке: ' . $this->view->host . $this->view->urlt('remind') . 'hash/' . md5($row->id . $row->password2) . '
Если Вы не хотите менять текущий пароль на новый, ничего не делайте, просто удалите это письмо.'
						);
						#d($options);
						$res = FrontEnd::mail($options);
						$this->view->notifymsg[] = FrontEnd::getMsg(array('auth', 'remindSuccess'));
						$this->view->done = true;
					}
				}
	        } else {
				#$this->setN(FrontEnd::getMsg(array('form', 'errors')), 'errors');
				$this->view->notifyerr[] = FrontEnd::getMsg(array('form', 'errors'));
			}
			#$this->_redirect($this->view->requestUri);
		}
		$this->view->form = $form;
    }


	/**
	* Регистрация
	* @return
	*/
    function registerAction()
    {
		if ($this->view->identity) {$this->_redirect('/');}

		if (!$this->userRegistrationAllowed) {return $this->_forward('login');}

		$this->textRow('register');

		$form = $this->getFormRegister();

        if ($this->getRequest()->isPost())
		{
			$rawData = $this->getRequest()->getPost();
			#d($rawData);
			if ($form->isValid($rawData))
			{
				$v = $form->getValues();

				$row = $this->getRow('users', 'email = "' . $v['email'] . '" OR LCASE(username) = "' . strtolower($v['username']) . '"');
				if ($row)
				{
					#$this->setN(FrontEnd::getMsg(array('auth', 'loginExists')), 'errors');
					$this->view->notifyerr[] = FrontEnd::getMsg(array('auth', 'loginExists'));
				} else {
					$data = $v;
					$data['password'] = md5($v['password']);
					$row = $this->users->createRow($data);
					$res = $row->save();
					if ($res) {
						#$this->setN(FrontEnd::getMsg(array('auth', 'regSuccess')));#$this->setContent('');
						$this->view->notifymsg = FrontEnd::getMsg(array('auth', 'regSuccess'));
						$this->view->done = true;
					}
				}
	        } else {
				#$this->setN(FrontEnd::getMsg(array('form', 'errors')), 'errors');
				$this->view->notifyerr[] = FrontEnd::getMsg(array('form', 'errors'));
			}
			#$this->_redirect($this->view->requestUri);
		}
		$this->view->form = $form;
    }

	/**
	* Авторизация
	* Использует Zend_Form!
	* @param
	* @return
	*/
    function loginAction()
    {
		if ($this->view->identity) {$this->_redirect('/');}

		$error = false;
		$this->textRow('login');

        $form = $this->getFormLogin();

        if ($this->getRequest()->isPost())
		{
			$formData = $this->getRequest()->getPost();
            if ($form->isValid($formData))
			{
				$username = $form->getValue('username');
				$password = $form->getValue('password');
				#d($username);
				#d($password);
			} else {
				$form->populate($formData);
				$error = true;
			}

 			if (empty($username)) {
				$this->setVar('errors', $form->msg('loginEmpty') . '.');
				$error = true;
			}

			if (empty($password)) {
				$this->setVar('errors', $form->msg('passwordEmpty') . '.');
				$error = true;
			}

            if (!$error)
			{
				$db = Zend_Registry::get('db');// setup Zend_Auth adapter for a database table

				#$authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password', "MD5(?) AND flag_status = '1'");
 				$authAdapter = new Zend_Auth_Adapter_DbTable($db);
				$authAdapter->setTableName($this->users->info(Zend_Db_Table::NAME));
				$authAdapter->setIdentityColumn($this->users->getIdentityColumn());
				$authAdapter->setCredentialColumn($this->users->getCredentialColumn());
				#$authAdapter->setCondition('flag_status = 1');
				$authAdapter->setCredentialTreatment('MD5(?) AND flag_status = 1');

                // Set the input credential values to authenticate against
                $authAdapter->setIdentity($username);
                $authAdapter->setCredential($password);#$authAdapter->setCredential(md5($password));

				#$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'), 'users', 'username', 'password', 'MD5(?)');
				#authAdapter->setIdentity($username)->setCredential($password);

				#$authAdapter->setIdentity($this->getRequest()->getPost('username'))->setCredential($this->getRequest()->getPost('password'))->setCredentialTreatment('md5(?) AND active = 1');

                // do the authentication
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                if ($result->isValid())
				{
                    $row = $authAdapter->getResultRowObject(null, 'password');// success : store database row to auth's storage system (not the password though!)
                    $auth->getStorage()->write($row);

					// add some security trace
					if (isset($this->conf->auth->lastData) && isset($row->id))
					{
						$data = array();
						$this->users->_addLast($data);#d($data);
						$where = $this->users->getAdapter()->quoteInto('id = ?', $row->id);
						$res = $this->users->update($data, $where);
						if ($res) {
							l($data, __METHOD__ . ' addLast update fail, data=', Zend_Log::ALERT);
						}

					}

					if (!empty($this->loginRedirect))
					{
						return $this->_redirect($this->loginRedirect);
					} else {
						$referer = $form->getValue('referer');
						if (!empty($referer)) {
							return $this->_redirect($referer);
						} else {
							$this->setContent($form->msg('loginSuccess'));
							$this->setVar('done', true);
						}
					}

                } else {
					$error = true;
					#$this->setVar('errors', $form->msg('loginFailed'));
                }
            }

			if ($error) {
				$this->setVar('errors', $form->msg('loginFailed'));
			}

		} else {
			if (!$this->loginActionDisplayForm) {
				$this->setVar('errors', $form->msg('loginEmpty') . '.');
			}
		}

		#d($this->view->errors);
		// dont show 2 forms (eg RPN.2)
       	if ( $this->loginActionDisplayForm ) {
           	$this->setVar('form', $form);
		}
    }

    function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
		if (!empty($this->logoutRedirect)) {
			$this->_redirect($this->logoutRedirect);
		} else {
			$this->_redirect('http://' . $this->view->pathParts['host']);
		}
    }


    function profileAction()
    {
		$this->view->mode = 'update';
        $this->renderScript('auth/register.phtml');
    }


    /**
     * Register form wrapper
     */
    public function getFormRegister()
    {
        $form = new Form_AuthRegister();
        return $form;
    }


    /**
     * Login form wrapper
     */
    public function getFormLogin()
    {
        $form = new Form_AuthLogin();
        return $form;
    }

	/**
	 * _redirect() wrapper
	 */
	private function _exit()
	{
        $this->_redirect('/');
    }

}
