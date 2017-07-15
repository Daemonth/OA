<?php

namespace Oa\Controller;

use Zend\Form\FormInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Oa\Form\RuleForm;
use Oa\Model\Rule;
use Oa\Controller\BaseController;
class RuleController extends BaseController
{

     public function indexAction()
    {
       
        $pagenum = (int) $this->params()->fromRoute('id',1);
        $order = 'id';
        $paginator = $this->common->getRuleTable()->getPaginator(null,$order);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(30);
                return new ViewModel(array(
                    'infos'=>$paginator
                ));
    }


    public function updateAction()
    {
        $form = new RuleForm();
        $form->get('submit')->setAttribute('value','提交');

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->redirect()->toRoute('oa/default', array('controller' => 'rule', 'action' => 'index'));
        }
        $entity = $this->common->getRuleTable()->fetchOne(array('id' => $id));
        $form->bind($entity);
        $request = $this->getRequest();
        if ($request->isPost())
        {
            $form->setInputFilter($entity->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {


                $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
                unset($data['id']);
                unset($data['submit']);
                $result = $this->common->getRuleTable()->update($data,array('id'=>$id));
                if ($result) {
                    $this->redirect()->toRoute('oa/default', array('controller' => 'rule', 'action' => 'index'));
                }
            }

        }
        return array('form' => $form, 'id' => $id);

    }

    public function deleteAction()
    {
        $request = $this->getRequest();
        if($request->isPost()) {
            $data = $request->getPost();
            foreach ($data['post'] as $key => $value) {
                $this->delete((int)$value);
            }
            $this->redirect()->toRoute('oa/default',array('controller'=>'rule','action'=>'index'));
        }
        $id = (int) $this->params()->fromRoute('id',0);
        $this->delete($id);
        $this->redirect()->toRoute('oa/default',array('controller'=>'rule','action'=>'index'));
    }
    
    
}
