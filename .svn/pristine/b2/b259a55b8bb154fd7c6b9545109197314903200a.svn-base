<?php

namespace Oa\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Oa\Controller\BaseController;
use Oa\Form\StandardForm;
use Oa\Model\Standard;
use Oa\Model\StandardTable;

class StandardController extends BaseController
{


    public function indexAction()
    {
        $infos = $this->common->getStandardTable()->fetchOne();
        return new ViewModel(array('infos'=>$infos));
    }

    

    public function updateAction()
    {
        $form = new StandardForm();
        $form->get('submit')->setAttribute('value','修改');
         $id = (int) $this->params()->fromRoute('id',0);
        if(!$id){
             $this->redirect()->toRoute('oa/default',array('controller'=>'standard','action'=>'index'));
        }
        $entity = $this->common->getStandardTable()->fetchOne(array('id'=>$id));
        $form->bind($entity);
       $request = $this->getRequest();
        if($request->isPost())
        {
            $form->setInputFilter($entity->getInputFilter());
            $form->setData($request->getPost());
            if($form->isValid())
            {

                $entity->standard1 = $form->get('standard1')->getValue();
                $entity->standard2 = $form->get('standard2')->getValue();
                $result = $this->common->getStandardTable()->saveAs($entity);
                if($result) {
                    $this->redirect()->toRoute('oa/default',array('controller'=>'standard','action'=>'index'));
                }
            }
        }

        return array('form'=>$form,'id'=>$id);
    }

    public function testAction() {
        $standard = $this->table->getOne(1);
        $conn = $this->getAdapter()->getDriver()->getConnection();
        $conn->beginTransaction();
        try {
            $standard->standard1 = 30;
            $result1 = $this->table->saveAs($standard);
            if(!$result1){
                throw new \Exception('result1 error');
            }

            $standard->standard2 = 'abcdefdg';
            $result2 = $this->table->saveAs($standard);
            if(!$result2){
                throw new \Exception('result2 error');
            }
            $conn->commit();
            echo 'result1 success--result2 success';
        }catch(\Exception $e) {
            $conn->rollback();
            echo $e;
        }
        die();
    }

   
    
    
}
