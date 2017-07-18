<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Oa;

use Oa\Model\HistoryTable;
use Oa\Model\RecordTable;
use Oa\Model\ReportTable;
use Oa\Model\Standard;
use Oa\Model\StandardTable;
use Oa\Model\TypeinfoTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Oa\Model\Rule;
use Oa\Model\RuleTable;
use Oa\Model\Userinfo;
use Oa\Model\UserinfoTable;
use Oa\Model\User;
use Oa\Model\UserTable;
use Oa\Model\OriginTable;
use Oa\Model\Origin;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        //404页面处理
       /* $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,function($event) {
            $event->getViewModel()->setTemplate('error/404.phtml');
        },2);*/
        //判断是否登录，没有登录则跳转到登录页面
        session_start();
        if(empty($_SESSION['Zend_Auth'])) {
            $response = $e->getResponse();
            $eventManager->attach(MvcEvent::EVENT_ROUTE,function($event) use ($response) {
                $route = $event->getRouteMatch();
                $action = $route->getParam('action');
                $employeeAction = array('login','forget','validate','newpwd');//未登录用户可访问的页面
                if(!in_array($action,$employeeAction)) {

                    $url = $event->getRouter()->assemble(array('controller' => 'user', 'action' => 'login'), array('name' => 'oa/default'));
                    $response->getHeaders()->addHeaderLine('Location', $url);
                    $response->setStatusCode(302);
                    $response->sendHeaders();
                    $event->stopPropagation();

                }

            },-100);
//            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,function($event) {
//                $event->getViewModel()->setTemplate('error/404.phtml');
//            },2);
            return $response;
        }else {
            //通过ACL进行的权限控制
            $acl = new \Zend\Permissions\Acl\Acl();
            $acl->addRole('employee');
            $acl->addRole('admin', 'employee');
            $acl->addResource('oa');
            $acl->allow('employee', 'oa', array('index:index', 'index:auth', 'user:search', 'user:updatepwd', 'user:summary'));
            $acl->allow('admin', null);

            $response = $e->getResponse();

            $eventManager->attach(MvcEvent::EVENT_ROUTE, function ($event) use ($response, $acl) {
                $route = $event->getRouteMatch();
                $controller = $route->getParam('__CONTROLLER__');
                $action = $route->getParam('action');
                $role = $_SESSION['Zend_Auth']->storage->role;
                if ($role == 2) {
                    $r = 'admin';
                } else {
                    $r = 'employee';
                }

                if (!$acl->isAllowed($r, 'oa', $controller . ':' . $action)) {
                    $url = $event->getRouter()->assemble(array('controller' => 'index', 'action' => 'auth'), array('name' => 'oa/default'));
                    $response->getHeaders()->addHeaderLine('Location', $url);
                    $response->setStatusCode(302);
                    $response->sendHeaders();
                    $event->stopPropagation();

                }

            }, -100);
//            $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,function($event) {
//                $event->getViewModel()->setTemplate('error/404.phtml');
//            },2);
            return $response;
        }
    }
    //加载配置文件
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),

        );
    }
   //提供一个工厂，为了创建一个模型的table。当我们需要的时候就取它
    public function getServiceConfig() {
        return array(
            'factories'=>array(
                'Oa\Model\RuleTable'=>function($sm) {
                    return new RuleTable($sm);
                },

                'Oa\Model\UserTable'=>function($sm) {
                    return new UserTable($sm);
                },

                'Oa\Model\UserinfoTable'=>function($sm) {
                    return new UserinfoTable($sm);
                },

                'Oa\Model\OriginTable' => function($sm) {
                    return new OriginTable($sm);
                },

                'Oa\Model\HistoryTable' => function($sm) {
                    return new HistoryTable($sm);
                },

                'Oa\Model\StandardTable' => function($sm) {
                    return new StandardTable($sm);
                },

                'Oa\Model\RecordTable' => function($sm) {
                    return new RecordTable($sm);
                },

//                'Oa\Model\TypeinfoTable' => function($sm) {
//                    return new TypeinfoTable($sm);
//                },

                'Oa\Model\ReportTable' => function($sm) {
                    return new ReportTable($sm);
                },

            ),
        );
    }
}
