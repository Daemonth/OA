<?php
namespace Oa\Model;

use \Zend\InputFilter\InputFilterAwareInterface;
use \Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;

class BaseModel implements InputFilterAwareInterface
{
   
    
    protected $inputFilter;

    public function exchangeArray($data)
    {
        
        $properties = $this->getArrayCopy();      

        foreach ($properties as $key => $value) {
            if($key != 'inputFilter') {
                $this->$key = (!empty($data[$key])) ? $data[$key] : null;
            }
        }
        
        
    }
    
    public function getArrayCopy(){
       return get_object_vars($this);
    }
    
    public function getInputFilter() {

    }
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception('Not used');
    }
}