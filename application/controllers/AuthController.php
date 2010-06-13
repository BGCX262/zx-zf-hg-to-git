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
		$this->view->errors = array();
		$this->view->mode = 'insert';
    }

    function indexAction()
    {
        #$this->_redirect('/');
		$this->loginAction();
    }


	/**
	* Регистрация
	* @return
	*/
    function registerAction()
    {
		if ($this->view->identity) {$this->_redirect('/');}

		if (!$this->userRegistrationAllowed) {
			return $this->forward('login');
		}

		$error = false;
		$this->textRow('register');

		$form = $this->getFormRegister();

        if ($this->_request->isPost()) {

			if ($form->isValid($_POST)) {

				$values = $form->getValues();

				$filter = new Zend_Filter_StripTags();
				$username = $filter->filter($this->_request->getPost('username'));
				$password = $filter->filter($this->_request->getPost('password'));
				$pw = $filter->filter($this->_request->getPost('pw'));

/* 				if (empty($username)) {
					$this->setVar('errors', 'Не указано имя пользователя.');
					$error = true;
				}
 */
				$row = $this->getRow('users', "username = '" . $username . "'");
				if ($row) {
					$this->setVar('errors', 'Такой пользователь уже зарегистрирован на сайте.');
					$error = true;
				}

				if (!$error) {
					$row = $this->users->createRow();
					$row->username = $username;
					$row->password = md5($password);
					$res = $row->save();
					#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($res, 1) . "</textarea><br>";die;
					if ($res) {
						$this->setContent('Новый пользователь успешно зарегистрирован.');
						$this->setVar('done', true);
					}
				}
			}
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
			$formData = $this->_request->getPost();
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

				#$authAdapter->setIdentity($this->_request->getPost('username'))->setCredential($this->_request->getPost('password'))->setCredentialTreatment('md5(?) AND active = 1');

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
}
