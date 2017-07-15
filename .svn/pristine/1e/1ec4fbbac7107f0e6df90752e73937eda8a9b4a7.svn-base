<?php
namespace Oa\Model;

use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Oa\Model\BaseModel;


class Userinfo extends BaseModel
{
    public $id;
    public $employeeId;
    public $name;
    public $company;
    public $area;
    public $part1;
    public $part2;
    public $team;
    public $job;
    public $attendId;
    public $identify;
    public $sex;
   
    public function getInputFilter() {
        
        if(!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
           
            $this->inputFilter->add(array(
                'name'=>'employeeId',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'员工号不能为空',
                     ),
                     ),
                    ),
                   
                 )
            ));
            
             $this->inputFilter->add(array(
                'name'=>'attendId',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'考勤号不能为空',
                     ),
                     ),
                    ),
                      array('name'=>'Digits',
                     'options'=>array(
                         'message'=>array(
                             '\Zend\Validator\Digits::NOT_DIGITS'=>'考勤号只能为数字'
                         )
                     )
                      )
                 )
             ));
            $this->inputFilter->add(array(
                'name'=>'identify',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                'validators'=>array(
                    array('name'=>'NotEmpty',
                        'options'=>array(
                            'message'=>array(
                                '\Zend\Validator\NotEmpty::IS_EMPTY'=>'身份证不能为空',
                            ),
                        ),
                    ),

                )
            ));
              $this->inputFilter->add(array(
                'name'=>'name',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'姓名不能为空',
                     ),
                     ),
                    ),
                 )
              ));
              
               $this->inputFilter->add(array(
                'name'=>'name',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'姓名不能为空',
                     ),
                     ),
                    ),
                 )
               ));
              $this->inputFilter->add(array(
                'name'=>'area',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'地区不能为空',
                     ),
                     ),
                    ),
                 )
              ));
               $this->inputFilter->add(array(
                'name'=>'part1',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'部门不能为空',
                     ),
                     ),
                    ),
                 )
               ));
               $this->inputFilter->add(array(
                'name'=>'company',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                 'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'公司不能为空',
                     ),
                     ),
                    ),
                 )
               ));
               
        }
        
        return $this->inputFilter;
    }
   
}