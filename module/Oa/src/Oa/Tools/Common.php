<?php
namespace Oa\Tools;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\ServiceManager;



class Common
{
    public $sm;
    public function __construct(ServiceManager $sm)
    {
        $this->sm = $sm;
    }

    public function getHistoryTable()
    {
        return $this->sm->get('Oa\Model\HistoryTable');
    }

    public function getOriginTable()
    {
        return $this->sm->get('Oa\Model\OriginTable');
    }

    public function getRecordTable()
    {
        return $this->sm->get('Oa\Model\RecordTable');
    }

    public function getReportTable()
    {
        return $this->sm->get('Oa\Model\ReportTable');
    }

    public function getStandardTable()
    {
        return $this->sm->get('Oa\Model\StandardTable');
    }

    public function getRuleTable()
    {
        return $this->sm->get('Oa\Model\RuleTable');
    }

    public function getUserTable()
    {
        return $this->sm->get('Oa\Model\UserTable');
    }

    public function getUserinfoTable()
    {
        return $this->sm->get('Oa\Model\UserinfoTable');
    }

    //获取随机字符

    public function getRandom($count = 3){
        $string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i=0;$i<$count;$i++) {
            $n = rand(0,61);
            $str .= substr($string,$n,1);
        }
        return $str;
    }

    public function getTypeDesc($time,$type)
    {
        $hour = explode(':',NIGHT);
         $hour = $hour[0];
        $ruleInfo = $this->getRuleTable()->fetchAll(array('type'=>$type));
        $date = date('Y-m-d',strtotime('-'.$hour.' hour',$time));

        foreach($ruleInfo as $item)
        {
            $endTimestamp= strtotime($date.' '.$item->endtime);
            $diff = strcasecmp($item->endtime , '24:00:00');
            if($type != 5  )
            {
                if($diff >= 0) {
                    $endTimestamp = strtotime('+' . $hour . ' hour', strtotime($date . ' ' . $item->endtime));
                }
            }

            if($time > strtotime($date.' '.$item->starttime) && $time <= $endTimestamp)
            {
                return $item->typeDesc;
            }
        }
        return '00';
    }


    /**
     * Returns an array of values representing a single column from the input
     * array.
     * @param array $array A multi-dimensional array from which to pull a
     *     column of values.
     * @param mixed $columnKey The column of values to return. This value may
     *     be the integer key of the column you wish to retrieve, or it may be
     *     the string key name for an associative array. It may also be NULL to
     *     return complete arrays (useful together with index_key to reindex
     *     the array).
     * @param mixed $indexKey The column to use as the index/keys for the
     *     returned array. This value may be the integer key of the column, or
     *     it may be the string key name.
     * @return array
     */

    //
    function array_column(array $array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {

            if (!is_array($subArray)) {
                
                continue;
            } elseif (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $result[$subArray[$indexKey]] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $result[$subArray[$indexKey]] = $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
    


}