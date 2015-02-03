<?php

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

/**
 * Crud-Handler to display void form and save posted data
 *
 * @author   Cyberrebell <chainsaw75@web.de>
 */
class AddHandler extends AbstractFormHandler
{
	const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/add.phtml';
	
	/**
	 * Generates a ViewModel which is ready to render
	 * 
	 * @return \Zend\View\Model\ViewModel
	 */
	public function getViewModel() {
		if ($this->useCache) {
			$viewModel = $this->recacheAgent->getViewModel('add', $this->entityNamespace, '');
			if ($viewModel) {
				return $viewModel;
			}
		}
		
		$this->viewModel = new ViewModel();
		
		$form = $this->formGenerator->getForm();
		$this->viewModel->setVariable('form', $form);
		
		if (RequestHandler::handleAdd($this->objectManager, $this->entityNamespace, $form, $this->request) && $this->redirect != null) {
			return $this->getRedirect();
		}
		
		$this->setupTemplate();
		$this->setupTitle();
		
		if ($this->useCache) {
			$this->recacheAgent->storeViewModel($this->viewModel, 'add', $this->entityNamespace, '');
		}
		
		return $this->viewModel;
	}
}
