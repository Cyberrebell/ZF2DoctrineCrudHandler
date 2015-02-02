<?php

namespace ZF2DoctrineCrudHandler\Handler;

use DoctrineEntityFormGenerator\FormGenerator;

/**
 * Abstract Class for Form-Crud-Handlers
 * Offers some configuration options Form-Handlers need
 *
 * @author   Cyberrebell <cyberrebell@web.de>
 */
abstract class AbstractFormHandler extends AbstractCrudHandler
{
    protected $formGenerator;
    protected $request;
    protected $redirect;
    protected $redirectRoute;
    
    /**
     * Set Request which may be POST
     * needed to handle POST's
     * 
     * @param \Zend\Http\Request $request Request of Controller
     * @return null
     */
    public function setRequest(\Zend\Http\Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Blacklist Entity-Properties for form-generation
     * 
     * @param array:string $blacklist ['password', 'registrationDate']
     * @return null
     */
    public function setPropertyBlacklist(array $blacklist)
    {
        $this->formGenerator->setPropertyBlacklist($blacklist);
    }
    
    /**
     * Whitelist Entity-Properties for form-generation
     * 
     * @param array $whitelist ['name', 'age']
     * @return null
     */
    public function setPropertyWhitelist(array $whitelist)
    {
        $this->formGenerator->setPropertyWhitelist($whitelist);
    }
    
    /**
     * Set Entity-Properties to be email inputs in form-generation
     * 
     * @param array:string $emailProperties ['admin@mail.com']
     * @return null
     */
    public function setEmailProperties(array $emailProperties)
    {
        $this->formGenerator->setEmailProperties($emailProperties);
    }
    
    /**
     * Set Entity-Properties to be password inputs in form-generation
     * 
     * @param array $passwordProperties ['password']
     * @return null
     */
    public function setPasswordProperties(array $passwordProperties)
    {
        $this->formGenerator->setPasswordProperties($passwordProperties);
    }
    
    /**
     * Set the redirect that will be used if data is saved successfully
     * 
     * @param \Zend\Mvc\Controller\Plugin\Redirect $redirect
     * @param string $route
     */
    public function setSuccessRedirect(\Zend\Mvc\Controller\Plugin\Redirect $redirect, $route)
    {
        $this->redirect = $redirect;
        $this->redirectRoute = $route;
    }
    
    protected function getRedirect()
    {
        return $this->redirect->toRoute($this->redirectRoute);
    }
    
    protected function prepare()
    {
        $this->formGenerator = new FormGenerator($this->objectManager, $this->entityNamespace, $this->storageAdapter);
    }
}
