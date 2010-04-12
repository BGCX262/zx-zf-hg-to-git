<?php
class Zx_Cache_Frontend_Page extends Zend_Cache_Frontend_Page
{
    public function start($id = false, $doNotDie = false)
    {
		if ($id && Zend_Registry::isRegistered('remove_cache')) {
			$res = $this->remove($id);
		}
		$res = parent::start($id, true);
		if ($res)
		{
			l($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' (' . (microtime(1) - T0) . ' s.)', 'CACHE');
			die;
		} else {
			#l($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' (' . (microtime(1) - T0) . ' s.)', 'NOCACHE');
			return false;
		}
	}

	public function setTags ($tags)
	{
		$this->_activeOptions['tags'] = $tags;
	}
}