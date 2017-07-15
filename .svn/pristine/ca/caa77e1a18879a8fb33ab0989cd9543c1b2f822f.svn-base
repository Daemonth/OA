<?php


namespace Oa\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\Object;
use Zend\View\Model\ViewModel;
use Oa\Controller\BaseController;
use Oa\Form\UserinfoForm;
use Oa\Model\Userinfo;
use Oa\Model\UserinfoTable;
use Zend\Form\FormInterface;
use Zend\Db\Sql\Where;
class EmployController extends BaseController
{


    public function indexAction()
    {

         $pagenum = (int) $this->params()->fromRoute('id',1);
         $name = empty($_GET['name'])? null:$_GET['name'];
         $employeeId = empty($_GET['employeeId'])?null:$_GET['employeeId'];
        $w=array();
        if(strlen($name)>0) {
            $where = array('name'=>$name);
            array_push($w,$where);
        }elseif(strlen($employeeId)>0) {
            $where = array('employeeId'=>$employeeId);
            array_push($w,$where);
        }else {
            $where = null;
        }


        $paginator = $this->common->getUserinfoTable()->getPaginator($w);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(8);
        foreach($paginator as $item)
        {
            if(!$item->identify){
                continue;
            }

            $identify = substr($item->identify,6,8);
            $year = substr($identify,0,4);
            $month = substr($identify,4,2);
            $day = substr($identify,6,2);
            $now = date('Y-m-d');
            $nowDate = explode('-',$now);
            $yearDiff = $nowDate[0]-$year;
            if($nowDate[1] - $month < 0)
            {
                $yearDiff--;
            }elseif($nowDate[1] - $month == 0)
            {
                if($nowDate[2] - $day < 0)
                    $yearDiff--;
            }
            $item->identify = $yearDiff;


        }

                return new ViewModel(array(
                    'infos'=>$paginator
                    
                ));
    }
   //上传员工信息表
    public function uploadAction(){
        return new ViewModel();
    }
    public function addAction()
    {
        $form = new UserinfoForm();
        $form->get('submit')->setAttribute('value','添加');
        if($this->getRequest()->isPost()) {
            $en = new Userinfo();
            $form->setInputFilter($en->getInputFilter());
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()) {
                $en->exchangeArray($form->getData(),FormInterface::VALUES_AS_ARRAY);

                if($this->common->getUserinfoTable()->saveAs($en)) {
                    return  $this->redirect()->toRoute('oa/default',array('controller'=>'employ','action'=>'index'));
                }
            }
        }
        return array('form'=>$form);
    }

    public function updateAction()
    {
       $id = (int) $this->params()->fromRoute('id',0);
        $form = new UserinfoForm();
        $form->get('submit')->setAttribute('value', '修改');
        if($id) {
            $entity = $this->common->getUserinfoTable()->fetchOne(array('id'=>$id));

            $form->bind($entity);
            $request = $this->getRequest();
            
            if($request->isPost()) {
               
                $form->setInputFilter($entity->getInputFilter());
                $form->setData($request->getPost());
                if($form->isValid()) {
                    $entity->exchangeArray($form->getData(FormInterface::VALUES_AS_ARRAY));

                    try {
                        $this->common->getUserinfoTable()->saveAs($entity);
                    }catch(\Exception $e){
                        return $this->redirect()->toUrl('/employ/update/'.$id.'?errorMessage=*对不起您输入的信息有误，请重新输入！');
                    }
                        $this->redirect()->toRoute('oa/default',array('controller'=>'employ','action'=>'index'));

                    
                }
            }
            
            return array('form'=>$form,'id'=>$id);
        }

        $this->redirect()->toRoute('oa/default',array('controller'=>'employ','action'=>'index'));
    }

    public function deleteAction()
    {
        $pagenum = (int)$this->params()->fromRoute('id', 0);
        $request = $this->getRequest();
        if($request->isPost()) {
            $ids = $_POST['post'];
            $where = new Where();
            $where->in('id',$ids);
            $this->common->getUserinfoTable()->delete($where);

        }
        return $this->redirect()->toRoute('oa/default',array('controller'=>'employ','action'=>'index','id'=>$pagenum));

    }

    //检查是否已经存在员工号
    public function existemployeeAction()
    {
        if ($this->getRequest()->isPost()) {
            $type = $_POST['type'];
            $employeeId = $_POST['employeeId'];
            if ($type == 1) {
                $id = $_POST['id'];
                $w = array();
                $where = new Where();
                $where->equalTo('employeeId', $employeeId);
                $where->notEqualTo('id', $id);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);

                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }elseif($type ==2) {
                $w = array();
                $where = new Where();
                $where->equalTo('employeeId', $employeeId);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);
                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }
        }
    }
    //检查是否已经存在考勤号
    public function existattendidAction()
    {
        if ($this->getRequest()->isPost()) {
            $type = $_POST['type'];
            $attendId = $_POST['attendId'];
            if ($type == 1) {
                $id = $_POST['id'];
                $w = array();
                $where = new Where();
                $where->equalTo('attendId', $attendId);
                $where->notEqualTo('id', $id);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);

                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }elseif($type ==2) {
                $w = array();
                $where = new Where();
                $where->equalTo('attendId', $attendId);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);
                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }
        }
    }
    //检查是否已经存在考勤号
    public function existidentifyAction()
    {
        if ($this->getRequest()->isPost()) {
            $type = $_POST['type'];
            $identify = $_POST['identify'];
            if ($type == 1) {
                $id = $_POST['id'];
                $w = array();
                $where = new Where();
                $where->equalTo('identify', $identify);
                $where->notEqualTo('id', $id);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);

                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }elseif($type ==2) {
                $w = array();
                $where = new Where();
                $where->equalTo('identify', $identify);
                $w[0] = $where;
                $row = $this->common->getUserinfoTable()->fetchOne($w);
                if ($row) {
                    echo 'fail';
                    die();
                }
                echo 'ok';
                die();
            }
        }
    }


}
