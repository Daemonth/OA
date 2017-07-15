<?php
namespace Oa\Model;

use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Oa\Model\BaseModel;


class Origin extends BaseModel
{
    public $id;
    public $printdate;
    public $attendId;
    public $name ;
    public $depart ;
    public $printway ;
    public $time ;
    public $device ;
    public $devicesuq ;
    

    public function getInputFilter() {
        
        if(!$this->inputFilter) {
            $this->inputFilter = new InputFilter();
           /* 
            $this->inputFilter->add([
                'name'=>'attendId',
                'required'=>true,
                'filters'=>[
                    ['name'=>'Int'],
                ],
            ]);
            
            $this->inputFilter->add([
                'name'=>'employId',
                'required'=>true,
                'filters'=>[
                    ['name'=>'StripTags'],
                    ['name'=>'StringTrim'],
                ],
                'validators'=>[
                    ['name'=>'StringLength',
                    'options'=>[
                        'encoding'=>'UTf-8',                       
                        'max'=>10,
                    ]
                        ]
                ]
            ]);
            */
        }
        
        return $this->inputFilter;
    }
}