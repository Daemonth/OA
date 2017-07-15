<?php

namespace Oa\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Oa\Tools\Common;
class BaseController extends AbstractActionController
{

    public $common;
    public $adapter;
    public function setEventManager(\Zend\EventManager\EventManagerInterface $events) {
        parent::setEventManager($events);
        $this->common = new Common($this->getServiceLocator());
    }

    public function getAdapter() {
        if(!$this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }

    

}
