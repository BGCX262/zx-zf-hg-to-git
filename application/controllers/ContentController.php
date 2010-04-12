<?php
/**
* Content controller (common)
* @author Алексей Дерягин (aleksey@deryagin.ru)
* @version 6/2/2009
*/
class ContentController extends MainController
{
	protected $filesPrefix = 'content'; // partials files prefix
/*
	// moved to model 4/8/2009!
	protected $topicId = 0;
	protected $topicIdNot = 0;//todo
	protected $isDates = true; // use dates
	protected $uriController = 'news';
	protected $uriItem = 'item';
 */

	function init()
	{
		parent::init();
		#$this->view->contentItemUri = $this->Content->getItemUri(); #@deprecated since 5/7/2009 use url()!
		
		if (empty($this->Content->ItemCountPerPage)) {
			$this->Content->ItemCountPerPage = 15;
		}
	}


	/**
	*
	* @param
	* @return
	*/
	function indexAction()
	{
		$rows = $this->Content->getTopicContent();
		if ($rows)
		{
			$this->setContent($this->view->partialLoopExtended("partials/ple." . $this->filesPrefix . "_list.phtml", $rows));
		} else {
			$this->setContent($this->fe->getMsg('todo'));
		}
		if ( ($this->Content->isPaginator) && (!$this->rewriteViewVars) ) {
			$this->view->paginator = $this->view->paginationControl($this->Content->getPaginator(), 'Sliding', $this->paginatorFile);
			$this->setContent($this->view->paginator);
		}
		#echo "DEBUG:<br><textarea rows=10 cols=100>" . print_r($this->view->content, 1) . "</textarea><br>";die;
		$this->renderScript($this->viewScript);
	}

	function itemAction()
	{
		$row = $this->Content->getItem($this->id);
		$this->view->topicId = $row->topic_id;
		if ($row) {
			$this->setHeader($row->title);
			#$this->setTitle($row->title);
			$this->setTitle($this->fe->setPageTitle($row->title));
			$this->setContent($this->view->partial('partials/' . $this->filesPrefix . '_item.phtml', $row));
		} else {
			$this->setContent($this->fe->getMsg('nf'));
		}
		$this->renderScript($this->viewScript);
	}
}