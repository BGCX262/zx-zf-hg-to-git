<?php
class Zx_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$registry = Zend_Registry::getInstance();
    	$frontController = Zend_Controller_Front::getInstance();

		// ACL list
		$acl =  new Zend_Acl();

		// ------------------------   ROLES  ------------------------------
		$guestRole = new Zend_Acl_Role('guest');
		$registredRole = new Zend_Acl_Role('registred');
		
		// ... register roles
		$acl -> addRole($guestRole)
			 -> addRole($registredRole, $guestRole);
			 
		// ------------------------   RESOURCES  ------------------------------
		// menus...
		$aclResources['interface']['start']['tab']								= new Zend_Acl_Resource('start');
		$aclResources['interface']['start']['fillTheProfile'] 					= new Zend_Acl_Resource('fillTheProfile');
		
		// ... register resources for interface
		foreach ($aclResources['interface'] as $resourceGroupName => $resourceGroup) {
			foreach ($resourceGroup as $resourceName => $resource) {
				$acl -> add($resource);
			}
		}
		// --------------------------------------------------------
    	$user = $registry['user'];
    	$lang = $registry['translate'];
    	$controllerName = $frontController->getRequest()->getControllerName();
		$actionName = $frontController->getRequest()->getActionName();
		
		// creating from current controller resource and add it to ACL
		$aclResources["controllers"][$controllerName][$actionName] 	= new Zend_Acl_Resource($controllerName.".".$actionName);
		if(!$acl -> has($aclResources["controllers"][$controllerName][$actionName])) {
			$acl -> add($aclResources["controllers"][$controllerName][$actionName]);
		}
		//$str = '$aclResources["controllers"]["'.$controllerName.'"]["'.$actionName.'"] 	= new Zend_Acl_Resource("controllers.'.$controllerName.'.'.$actionName.'");';
		// --------------  ACCESS CONTROL LIST -----------------------------
		/*
		 * Внимание: если указано allow для группы ресурсов, то автоматически применяется ко всем ресурсам группы
		 * @ - подавление ошибок, т.к. возникают Notice при применении правил к несуществующим ресурсам
		 */
		require './application/default/init/acl.php'; // NB! require_once нельзя, а то упадёт _forward
		
		// Save ACL to registry
		$registry['acl'] = $acl;
		$registry['aclResources'] = $aclResources;

		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($user->get('access_level'), 1) . "</textarea><br>";
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($aclResources['controllers'][$controllerName][$actionName], 1) . "</textarea><br>";

		if(!$acl -> isAllowed($user->get('access_level'), $aclResources['controllers'][$controllerName][$actionName])) {
		 $view = new Zend_View();
		 $view -> setScriptPath('./application/default/views/scripts/'); 
		 $view -> messageText = $lang['accessDenied'].' role '.$user->get('access_level').' to '.$controllerName.'/'.$actionName.'<br/>
		 '.$lang['please'].' <a href="/user/loginForm">'.$lang['loginBlock.enterLogin'].'</a>  '.$lang['or'].' <a href="/user/registerForm">'.$lang['loginBlock.freeRegister'].'</a>
		 ';
		 $view -> messageTitle = $lang['error'];
		 print $view -> render('accessDenied.phtml');
		 // write to log file
		 $str = '['.date("m.d.y H:i").'] Role '.$user->get('access_level').' denied to '.$controllerName.'/'.$actionName.'
';
		 $fp = fopen('./logs/error.txt', 'a');
		 fwrite($fp, $str);
		 fclose($fp);
		 exit();
		} else {
		 //print $user->get('access_level').' allowed to '.$controllerName.'/'.$actionName;
		}
    }
}