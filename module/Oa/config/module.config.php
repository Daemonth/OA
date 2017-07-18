<?php


namespace Oa;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
//配置默认的路径
return array(
    'router' => array(
        'routes' => array(

            'oa' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Oa\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    //路由规则
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[:controller[/:action[/:id][/:time]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*', 
                               
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                   
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
           // 'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
          
        ),
    ),
    //注册控制器
    'controllers' => array(
        'invokables' => array(
            'Oa\Controller\Index' => 'Oa\Controller\IndexController',
            'Oa\Controller\Rule' =>'Oa\Controller\RuleController',
            'Oa\Controller\Origin' =>'Oa\Controller\OriginController',
             'Oa\Controller\User' =>'Oa\Controller\UserController',
             'Oa\Controller\Excel' =>'Oa\Controller\ExcelController',
             'Oa\Controller\Employ' =>'Oa\Controller\EmployController',
             'Oa\Controller\Standard' =>'Oa\Controller\StandardController',
             'Oa\Controller\Record' =>'Oa\Controller\RecordController',
             'Oa\Controller\Report' =>'Oa\Controller\ReportController',
        ),
    ),
//    'controller_plugins'=>array(
//            'invokables'=>array(
//                'TablePlugin' => 'Oa\Controller\TablePlugin',
//            ),
//    ),
//视图显示的管理
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'Oa/index/index' => __DIR__ . '/../view/Oa/index/index.phtml',
            'Oa/index/upload' => __DIR__ . '/../view/Oa/index/upload.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
           // 'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'error/index'             => __DIR__ . '/../view/error/error.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
  
);
