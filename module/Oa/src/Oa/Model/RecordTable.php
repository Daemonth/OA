<?php

namespace Oa\Model;
use Zend\Db\TableGateway\TableGateway;
use Oa\Model\Record;
use Oa\Model\BaseTable ;
class RecordTable extends BaseTable
{
    public $tableModel = 'Oa\Model\Record';
    public $tableName = 'record';

    public function saveAs(Record $obj)
    {
        return parent::save($obj); // TODO: Change the autogenerated stub
    }

    public function getPaginator($where = null,$order = null)
    {
        return parent::_getPaginator($where,$order); // TODO: Change the autogenerated stub
    }
    
}



?>