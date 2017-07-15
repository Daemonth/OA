<?php

namespace Oa\Form;

use Zend\Form\Form;

class StandardForm extends Form
{
    public function __construct($name = null) {
        parent::__construct('standard');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name'=>'id',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'standard1',
            'type'=>'text',
        ));
        $this->add(array(
            'name'=>'standard2',
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
    


