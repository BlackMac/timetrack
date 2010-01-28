<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initPlaceholders()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML5');
        $view->headTitle('Timetrack')->setSeparator(' - ');
        $view->headLink()->prependStylesheet('styles/global.css');
        $view->headMeta()->appendHttpEquiv("Content-Type", "text/html; charset=utf-8");
        $view->headMeta()->appendName("apple-mobile-web-app-capable", "yes");
        $view->headMeta()->appendName("apple-mobile-web-app-status-bar-style", "black");
 
    }
    
}

