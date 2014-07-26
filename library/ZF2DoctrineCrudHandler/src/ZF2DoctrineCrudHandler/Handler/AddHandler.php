<?php
/**
 * File containing AddHandler class
 *
 * PHP version 5
 *
 * @category  ZF2DoctrineCrudHandler
 * @package   ZF2DoctrineCrudHandler\Handler
 * @author    Cyberrebell <cyberrebell@web.de>
 * @copyright 2014 - 2014 Cyberrebell
 * @license   http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @version   GIT: <git_id>
 * @link      https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */

namespace ZF2DoctrineCrudHandler\Handler;

use Zend\View\Model\ViewModel;
use ZF2DoctrineCrudHandler\Request\RequestHandler;

/**
 * Crud-Handler to display void form and save posted data
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Handler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
class AddHandler extends AbstractFormHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/add.phtml';
    
    /**
     * Generates a ViewModel which is ready to render
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewModel()
    {
        $viewModel = $this->recacheAgent->getViewModel('add', $this->entityNamespace, '');
        if ($viewModel) {
            return $viewModel;
        }
        
        $this->viewModel = new ViewModel();
        
        $form = $this->formGenerator->getForm();
        $this->viewModel->setVariable('form', $form);
        
        RequestHandler::handleAdd($this->objectManager, $this->entityNamespace, $form, $this->request);
        
        $this->setupTemplate();
        $this->setupTitle();
        
        $this->recacheAgent->storeViewModel($this->viewModel, 'add', $this->entityNamespace, '');
        
        return $this->viewModel;
    }
}
