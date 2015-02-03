<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

/**
 * Crud-Handler to delete entity on request
 *
 * @author   Cyberrebell <chainsaw75@web.de>
 */
class DeleteHandler extends AbstractCrudHandler
{
	protected $entityId;
	protected $redirect;
	protected $redirectRoute;
	
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
	 * Set the redirect that will be used if data is saved successfully
	 * 
	 * @param \Zend\Mvc\Controller\Plugin\Redirect $redirect
	 * @param string $route
	 */
	public function setSuccessRedirect(\Zend\Mvc\Controller\Plugin\Redirect $redirect, $route) {
		$this->redirect = $redirect;
		$this->redirectRoute = $route;
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
	
	protected function getRedirect() {
		return $this->redirect->toRoute($this->redirectRoute);
	}
}
