<?php
/**
 * File containing EditHandler class
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
 * Crud-Handler to edit entity on request
 *
 * @category ZF2DoctrineCrudHandler
 * @package  ZF2DoctrineCrudHandler\Handler
 * @author   Cyberrebell <cyberrebell@web.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0 GPL-3.0
 * @link     https://github.com/Cyberrebell/ZF2DoctrineCrudHandler
 */
class EditHandler extends AbstractFormHandler
{
    const DEFAULT_TEMPLATE = 'zf2doctrinecrudhandler/edit.phtml';
    
    protected $entityId;
    
    /**
     * Set the id of entity to delete
     * 
     * @param int $id Entity-Id
     * 
     * @return \ZF2DoctrineCrudHandler\Handler\EditHandler
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;
    
        return $this;
    }
    
    /**
     * Generates a ViewModel which is ready to render
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewModel()
    {
        $this->viewModel = new ViewModel();
        
        $form = $this->formGenerator->getForm();
        $this->viewModel->setVariable('form', $form);
        
        RequestHandler::handleEdit(
            $this->objectManager,
            $this->entityNamespace,
            $form,
            $this->request,
            $this->entityId
        );
        
        $this->setupTemplate();
        $this->setupTitle();
        
        return $this->viewModel;
    }
}
