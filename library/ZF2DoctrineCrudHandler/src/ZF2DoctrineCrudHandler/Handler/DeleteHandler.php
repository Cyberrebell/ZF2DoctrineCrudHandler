<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

/**
 * Crud-Handler to delete entity on request
 *
 * @author   Cyberrebell <chainsaw75@web.de>
 */
class DeleteHandler extends AbstractFormHandler
{
	protected $entityId;
	
	/**
	 * Set the id of entity to delete
	 * 
	 * @param int $id Entity-Id
	 * @return null
	 */
	public function setEntityId($id) {
		$this->entityId = $id;
	}
	
	/**
	 * Generates a ViewModel which is ready to render
	 *
	 * @return \Zend\View\Model\ViewModel
	 */
	public function getViewModel() {
		RequestHandler::handleDelete($this->objectManager, $this->entityNamespace, $this->entityId, $this->entityFilter);
		if ($this->redirect != null) {
			return $this->getRedirect();
		}
	}
}
