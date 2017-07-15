<?php

namespace Oa\Form;

use Zend\Form\Form;

class UserinfoForm extends Form
{
    public function __construct($name = null) {
        parent::__construct('userinfo');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name'=>'id',
            'type'=>'hidden',
        ));
        $this->add(array(
            'name'=>'employeeId',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'name',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'company',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'area',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'part1',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'attendId',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'part2',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'team',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'job',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'identify',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'sex',
            'type'=>'Zend\Form\Element\Select',
            'options'=>array(
            'value_options'=>array(
                '男'=>'男',
                '女'=>'女'
            ))
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
    


