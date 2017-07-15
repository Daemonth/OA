<?php

namespace Oa\Model;
use Oa\Model\BaseModel;

use Zend\ServiceManager\ServiceManager;

class User extends BaseModel
{
    public $id;
    public $employeeId;
    public $email;
    public $password;
    public $name;
    public $role;
    public $validate;
    public $initlogin;
   

    public function getInputFilter() {
        if(!$this->inputFilter) {
            $this->inputFilter = new \Zend\InputFilter\InputFilter();

            $this->inputFilter->add(array(
                'name'=>'employeeId',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim')
                ),
                'validators'=>array(
                    array('name'=>'NotEmpty',
                     'options'=>array(
                     'message'=>array(
                         '\Zend\Validator\NotEmpty::IS_EMPTY'=>'员工号不能为空',
                     ),
                     ),
                    ),
//                    ['name'=>'Db\NoRecordExists',
//                        'options'=>['table'=>'user','field'=>'employeeId','adapter'=>$this->adapter,'message'=>['recordFound'=>'已存在该员工号']]
//                    ],
                    array('name'=>'Digits',
                     'options'=>array(
                         'message'=>array(
                             '\Zend\Validator\Digits::NOT_DIGITS'=>'员工号只能为数字'
                         )
                     )
                    )
                )

            ));

            $this->inputFilter->add(array(
                'name'=>'email',
                'required'=>true,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                'validators'=>array(
                    array('name'=>'NotEmpty',
                        'options'=>array(
                     'message'=>array('isEmpty'=>'邮箱不能为空')
                        )
                    ),
                    array('name'=>'\Zend\Validator\EmailAddress',
                        'options'=>array(
                            'message'=>array('emailAddressInvalid'=>'邮箱格式不正确')
                        )
                    )
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
                        'message'=>array('isEmpty'=>'姓名不能为空')
                        )
                    ),
                )

            ));

            $this->inputFilter->add(array(
                'name'=>'password',
                'required'=>false,
                'filters'=>array(
                    array('name'=>'StringTrim'),
                ),
                'validators'=>array(
//                    ['name'=>'NotEmpty',
//                        'options'=>[
//                            'message'=>['isEmpty'=>'密码不能为空']
//                        ]
//                    ],
                    array('name'=>'StringLength',
                        'options'=>array(
                            'min'=>6,
                             'max'=>30,
                            'message'=>array('stringLengthTooShort'=>'密码长度不能小于6','stringLengthTooLong'=>'密码长度不能超过30')
                        )
                    ),
                )

            ));


        }

        return $this->inputFilter;
       
    }
  
}

