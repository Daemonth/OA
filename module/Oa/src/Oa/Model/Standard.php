<?php

namespace Oa\Model;

use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Oa\Model\BaseModel;
class Standard extends BaseModel
{
    public $id;
    public $standard1;
    public $standard2;
    
    
     
    
    public function getInputFilter() {
        
        if(!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
             $this->inputFilter->add(array(
                'name'=>'standard1',
                'required'=>true,
                'filters'=>array(
                  array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'加班费不能为空',
                     ),
                     ),
                    ),
                 )
                
             ));
             
              
             $this->inputFilter->add(array(
                'name'=>'standard2',
                'required'=>true,
                'filters'=>array(
                  array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'加班费不能为空',
                     ),
                     ),
                    ),
                 )
                
             ));

            
        }
        
        return $this->inputFilter;
    }
    
    
   
}

