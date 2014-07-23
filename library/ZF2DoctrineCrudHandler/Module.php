<?php
namespace ZF2DoctrineCrudHandler;

class Module
{
    public function getConfig()
    {
        return [
            'view_manager' => [
                'template_path_stack' => [
                    __DIR__ . '/view'
                ]
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ]
            ]
        ];
    }
}
