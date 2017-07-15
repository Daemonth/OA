<?php
namespace Oa\Model;

use Oa\Model\BaseModel;
class Report extends BaseModel
{
    public $id;
    public $month;
    public $company;
    public $area;
    public $part1;
    public $part2;
    public $team;
    public $job;
    public $employeeId;
    public $name;
    public $workdays=0;
    public $logicdays=0;
    public $workaway=0;
    public $late1=0;
    public $late2=0;
    public $leavely=0;
    public $vacation1=0;
    public $vacation2=0;
    public $weekendwork=0;
    public $weekendaway=0;
    public $bonus=0;
    public $standard1;
    public $overtime1=0;
    public $standard2;
    public $overtime2=0;
    public $total=0;
    public $eventdays=0;
    public $yeardays=0;
    public $maternitydays=0;

    public $marrydays=0;
    public $sickdays=0;
    public $funeraldays=0;
    public $absencedays=0;
    public $outdays=0;
    public $checkout;
    public $checkall;
    public $desc1;
    public $desc2;
    public $desc3;
    public $role;

    public function exchangeArray($data)
    {

        $properties = $this->getArrayCopy();

        foreach ($properties as $key => $value) {
            if($key != 'inputFilter') {
                $this->$key = (!empty($data[$key])) ? $data[$key] : $this->$key;
            }
        }


    }
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $this->inputFilter = new \Zend\InputFilter\InputFilter();

            $this->inputFilter->add(array(
                'name' => 'workdays',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array('name' => 'NotEmpty',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\NotEmpty::IS_EMPTY' => '工作日不能为空',
                            ),
                        ),
                    ),
                    array('name' => 'Digits',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\Digits::NOT_DIGITS' => '工作日只能为数字'
                            )
                        )
                    )
                )

            ));

            $this->inputFilter->add(array(
                'name' => 'logicdays',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array('name' => 'NotEmpty',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\NotEmpty::IS_EMPTY' => '实际工作日不能为空',
                            ),
                        ),
                    ),
                    array('name' => 'Digits',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\Digits::NOT_DIGITS' => '实际工作日只能为数字'
                            )
                        )
                    )
                )

            ));

            $this->inputFilter->add(array(
                'name' => 'workdays',
                'required' => true,
                'filters' => array(
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array('name' => 'NotEmpty',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\NotEmpty::IS_EMPTY' => '工作日不能为空',
                            ),
                        ),
                    ),
                    array('name' => 'Digits',
                        'options' => array(
                            'message' => array(
                                '\Zend\Validator\Digits::NOT_DIGITS' => '工作日只能为数字'
                            )
                        )
                    )
                )

            ));

        }
        return $this->inputFilter;
    }


}