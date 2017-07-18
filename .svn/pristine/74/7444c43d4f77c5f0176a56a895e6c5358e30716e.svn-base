<?php

namespace Oa\Controller;

use Oa\Controller\BaseController;
use Zend\Db\Sql\Where;
use Zend\View\Model\ViewModel;
use Oa\Form\OriginForm;
use Oa\Model\Origin;

class OriginController extends BaseController
{

    //原始信息首页
     public function indexAction()
    {
        
        $pagenum = (int) $this->params()->fromRoute('id',1);
        
        $where = array();
        $formInfo = array();
        
        $start = isset($_GET['start']) ? $_GET['start'] : null;
        $end = isset($_GET['end']) ? $_GET['end'] : null;
        $pattern = '/^\d{4}-\d{1,2}-\d{1,2}$/';
        
        if(!empty($start) && preg_match($pattern,$start) && !empty($end) && preg_match($pattern,$end) && strtotime($start)< strtotime($end)) {
            $endUpdate = date('Y-m-d',strtotime('+1 day',strtotime($end)));
            $w = new Where();
            $w->between('time',$start,$endUpdate);
            array_push($where,$w);
            $formInfo['start'] = $start;
            $formInfo['end'] = $end;
        }

        $name = isset($_GET['name']) ? $_GET['name'] : null;
        if(!empty($name) && strlen($name)>0) {
            array_push($where,array('name'=>$name));
            $formInfo['name'] = $name;
        }

        $order = array('time desc');
        $paginator = $this->common->getOriginTable()->getPaginator($where,$order);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(9);
                return new ViewModel(array(
                    'infos'=>$paginator,
                    'formInfo'=>$formInfo
                ));
    }

   //上传原始表首页
    public function uploadAction() {

        return new ViewModel();
    }

}
