<?php

namespace Oa\Form;

use Zend\Form\Form;

class RuleForm extends Form
{
    public function __construct($name = null) {
        parent::__construct('rule');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name'=>'id',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'starttime',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'endtime',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'type',
            'type'=>'select',
            'options'=>array(
                'label'=>'工作类型',
                'value_options' =>array(
                    '1'=>'普通员工工作日',
                    '2'=>'普通员工周末',
                    '3'=>'保洁员工作日',
                    '4'=>'保洁员周末',
                    '5'=>'晚值勤人员'
                ),
            ),
        ));
        $this->add(array(
            'name'=>'short',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'description',
            'type'=>'text',
        ));
        
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
             ),
         ));
    }
}
    


