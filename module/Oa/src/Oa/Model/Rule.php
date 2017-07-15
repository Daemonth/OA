<?php
namespace Oa\Model;
use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Oa\Model\BaseModel;
class Rule extends BaseModel
{
    public $id;
    public $starttime;
    public $endtime;
    public $type;
    public $typeDesc;
    public $short;
    public $description;


    
    
    public function getInputFilter() {
        
        if(!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
            
            $this->inputFilter->add(array(
                'name'=>'starttime',
                'required'=>true,
                'filters'=>array(
                  array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'开始时间不能为空',
                     ),
                     ),
                    ),
                    array( 'name'=>'Date',
                        'options'=>array(
                            'format'=>'H:i:s',
                            'message'=>array(
                                'dateFalseFormat'=>'时间格式错误'
                            )
                        )
                    )
                 )
                
            ));
            
             $this->inputFilter->add(array(
                'name'=>'endtime',
                'required'=>true,
                'filters'=>array(
                  array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'结束时间不能为空',
                     ),
                     ),
                    ),
                    array( 'name'=>'Date',
                        'options'=>array(
                            'format'=>'H:i:s',
                            'message'=>array(
                                'dateFalseFormat'=>'时间格式错误'
                            )
                        )
                    )
                 )
                
             ));
            
        }        
        return $this->inputFilter;
    }
   
    
}