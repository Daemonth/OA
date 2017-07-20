<?php


namespace Oa\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Oa\Tools\Utils;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\View\Model\ViewModel;
use Oa\Controller\BaseController;
use Oa\Model\Record;
use Oa\Model\RecordTable;
use Oa\Model\Typeinfo;
use Oa\Model\TypeinfoTable;
use Zend\Json\Json;
use Zend\Db\Sql\Predicate\NotIn;
use Oa\Model\Report;
use Oa\Model\ReportTable;


class RecordController extends BaseController
{


    //考勤记录首页
    public function indexAction()
    {

        $where = array();
        $pagenum = (int)$this->params()->fromRoute('id', 1);
        $formInfo = array();

        $in = new Where();

        //类型
        if (isset($_GET['type']) && $_GET['type'] == 3) {
            //事假及未签到
            $inOr = new Where();
            $inOr->in('updateType1', array('E', 'emp'));
            $inOr->or->in('updateType2', array('emp'));
            $in->addPredicate($inOr);
            array_push($where, $in);
            $formInfo['type'] = 3;
        } else if (isset($_GET['type']) && $_GET['type'] == 2) {
            //全部
            $formInfo['type'] = 2;
        } else {
            //迟到及早退
            $in->in('updateType1', array('B', 'C', 'D', 'F'));
            array_push($where, $in);
            $formInfo['type'] = 1;
        }
//提交时间区间
        if (isset($_GET['starttime']) && strlen($_GET['starttime']) > 0 && isset($_GET['endtime']) && strlen($_GET['endtime']) > 0) {

            $in->between('signdate', $_GET['starttime'], $_GET['endtime']);
            array_push($where, $in);
            $formInfo['starttime'] = $_GET['starttime'];
            $formInfo['endtime'] = $_GET['endtime'];
        }
        //姓名
        if (isset($_GET['name']) && strlen($_GET['name']) > 0) {
            array_push($where, array('name' => $_GET['name']));
            $formInfo['name'] = $_GET['name'];
        } elseif (isset($_GET['employeeId']) && strlen($_GET['employeeId']) > 0) {
            //员工号
            array_push($where, array('employeeId' => $_GET['employeeId']));
            $formInfo['employeeId'] = $_GET['employeeId'];
        }

        $order = array('signdate desc', 'employeeId');

        $paginator = $this->common->getRecordTable()->getPaginator($where, $order);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(8);

        $typeinfos = $this->common->getRuleTable()->fetchAll()->toArray();
        $typeinfos = $this->common->array_column($typeinfos, null, 'typeDesc');

        $user = $this->common->getUserTable()->fetchAll()->toArray();
        $user = $this->common->array_column($user, null, 'employeeId');
        $fixValue = array('em' => '无记录', 'emp' => '未打卡', '00' => '数据异常', 'sun' => '周日值守', '01' => '法定假日1', '02' => '法定假日2', 'A11' => '法定假日0.5', '01H' => '0.5法定假日加班1', '01I' => '0.5法定假日加班2');
        return new ViewModel(array(
            'infos' => $paginator,
            'typeinfos' => $typeinfos,
            'formInfo' => $formInfo,
            'user' => $user,
            'num' => $pagenum,
            'fixValue' => $fixValue
        ));
    }

    //疑问查询首页
    public function serchqAction()
    {
        $where = array();
        $where4 = array();
        $formInfo = '';
        $pagenum = (int)$this->params()->fromRoute('id', 1);
        $in = new Where();
        //姓名
        if (isset($_GET['name']) && strlen($_GET['name']) > 0) {
            array_push($where, array('name' => $_GET['name']));
            $formInfo['name'] = $_GET['name'];
        } elseif (isset($_GET['employeeId']) && strlen($_GET['employeeId']) > 0) {
            //员工号
            array_push($where, array('employeeId' => $_GET['employeeId']));
            $formInfo['employeeId'] = $_GET['employeeId'];
        }
        if (isset($_GET['starttime']) && strlen($_GET['starttime']) > 0) {
            //提交查询区间
            $firstday = date("Y-m-01", strtotime($_GET['starttime']));
            $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));

            $formInfo['starttime'] = $_GET['starttime'];
            $date1 = date("Y-m", strtotime($_GET['starttime']));
        } else {
            $date = date('Y-m-d');
            $date1 = date("Y-m", strtotime($date));
            $firstday = date("Y-m-01", strtotime($date));
            $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        }
        $paginator = $this->common->getUserTable()->getPaginator($where);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(8);
        foreach ($paginator as $k => $v) {
            $in5 = new where();
            $in5->in('employeeId', array($v->employeeId));
            array_push($where4, $in5);
            $date = $this->common->getRecordTable()->getPaginator($where4);
            $paginator3=array();
            $paginator4=array();
            foreach ($date as $k => $d) {

                $v->date = $d;
                if ($v->date->daytype ==1) {
                    $where2 = "employeeId=" . $v->employeeId . " and signdate BETWEEN " . "'" . $firstday . "'" . " and " . "'" . $lastday . "'" . " and (updateType1='emp' or updateType1='em') and daytype!=3 and daytype!=4";
                    $paginator3 = $this->common->getRecordTable()->fetchAll($where2)->toArray();
                    $where3 = "employeeId=" . $v->employeeId . " and signdate BETWEEN " . "'" . $firstday . "'" . " and " . "'" . $lastday . "'" . " and (updateType2='emp' or updateType2='em') and daytype!=3 and daytype!=4";
                    $paginator4 = $this->common->getRecordTable()->fetchAll($where3)->toArray();
                }
                if ($v->date->daytype !==5){
                    $where = "employeeId=" . $v->employeeId . " and signdate BETWEEN " . "'" . $firstday . "'" . " and " . "'" . $lastday . "'" . " and (updateType1='C' or updateType1='D'or updateType1='F' or updateType1='00' or updateType1='L' or updateType1='M') and daytype!=3 and daytype!=4";
                    $paginator1 = $this->common->getRecordTable()->fetchAll($where)->toArray();
                    $where1 = "employeeId=" . $v->employeeId . " and signdate BETWEEN " . "'" . $firstday . "'" . " and " . "'" . $lastday . "'" . " and (updateType2='F' or updateType2='00' or updateType2='L' or updateType2='M') and daytype!=3 and daytype!=4";
                    $paginator2 = $this->common->getRecordTable()->fetchAll($where1)->toArray();
                }
                if($v->role==6){
                    $v->count1 = 0;
                    $v->count2 = 0;
                    $v->count3 = 0;
                    $v->count4 = 0;
                }else {
                    $v->count1 = count($paginator1);
                    $v->count2 = count($paginator2);
                    $v->count3 = count($paginator3);
                    $v->count4 = count($paginator4);
                }

            }
        }
        return new ViewModel(array(
            'infos' => $paginator,
            'formInfo' => $formInfo,
            'date1' => $date1,
        ));
    }


    //考勤记录首页
    public function listpageAction()
    {

        $where = array();
        $where1 = array();
        $pagenum = (int)$this->params()->fromRoute('id', 1);
        $test = $this->params()->fromRoute('id', 0);
        $date = $_GET['date'];
        $firstday = date("Y-m-01", strtotime($_GET['date']));
        $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        $formInfo = array();
        $in = new Where();
        //类型
        if (empty($_GET['type'])) {
            $formInfo['type'] = 2;

        }
        if (isset($test) && strlen($test > 0)) {
            //员工号
            $in = new Where();
            $in->in('employeeId', array($test));
            $in->between('signdate', $firstday, $lastday);
            array_push($where, $in);
            $formInfo['employeeId'] = $test;
        }


        $order = array('signdate asc', 'employeeId');

        $paginator = $this->common->getRecordTable()->getPaginator($where, $order);

        $paginator->setCurrentPageNumber($pagenum);

        $paginator->setItemCountPerPage(1000);

        $in1 = new Where();
        $in1->in('employeeId', array($test));
        $in1->in('month', array($date));
        array_push($where1, $in1);

        $paginator1 = $this->common->getReportTable()->getPaginator($where1);

        $typeinfos = $this->common->getRuleTable()->fetchAll()->toArray();

        $typeinfos = $this->common->array_column($typeinfos, null, 'typeDesc');//以类型作为数组的健

        $user = $this->common->getUserTable()->fetchAll()->toArray();//获取所有用户

        $user = $this->common->array_column($user, null, 'employeeId');//以用户的ID作为数组的键

        $Duser = $this->common->getUserTable()->fetchOne(array('employeeId' => $test));

        $fixValue = array('em' => '无记录', 'emp' => '未打卡', '00' => '数据异常', 'sun' => '周日值守', '01' => '法定假日1', '02' => '法定假日2', 'A11' => '法定假日0.5', '01H' => '0.5法定假日加班1', '01I' => '0.5法定假日加班2');

        //更新当月报表
        $firstDay = $date . '-01';//当月首日
        $lastDay = date('Y-m-d', strtotime('+1 month -1 day', strtotime($firstDay)));//当月最后一天
        $month = $date;
        //工作日
        $workdays = 0;
        //月总天数
        $days = date('t', strtotime($date));
        //获取工作日天数
        for ($i = 0; $i < $days; $i++) {
            $w = date('w', strtotime("+$i day", strtotime($date)));
            if ($w != 0 && $w != 6) {
                $workdays++;
            }
        }
        $monthWorkDays = $workdays;
        $exclude = explode(',', EXCLUDE);
        //查询出员工基本信息
        $wVip = array();
        //$vipWhere = new Where();
        //$vipWhere->notIn('employeeId',$exclude);
        $vipWhere = new NotIn();
        $vipWhere->setIdentifier('employeeId')->setValueSet($exclude);
        array_push($wVip, $vipWhere);
        $userinfos = $this->common->getUserinfoTable()->fetchAll($wVip)->toArray();
        $summary = array();
        $i = 0;
        //加班费标准
        $standard = $this->common->getStandardTable()->fetchOne();

        //晚勤人员信息
        $users = $this->common->getUserTable()->fetchAll(array('role' => 4))->toArray();
        //不用刷卡人员
        $where = array();
        $w = new Where();
        $w->in('employeeId',$exclude);
        array_push($where,$w);
        $vip = $this->common->getUserinfoTable()->fetchAll($where)->toArray();
        foreach ($vip as $key => $value) {
            //固定信息
            $summary[$i]['employeeId'] = $value['employeeId'];
            $summary[$i]['name'] = $value['name'];
            $summary[$i]['company'] = $value['company'];
            $summary[$i]['area'] = $value['area'];
            $summary[$i]['part1'] = $value['part1'];
            $summary[$i]['part2'] = $value['part2'];
            $summary[$i]['team'] = $value['team'];
            $summary[$i]['job'] = $value['job'];
            $summary[$i]['workdays'] = $workdays;
            $summary[$i]['month'] = $month;
            $summary[$i]['logicdays'] = $workdays;
            $summary[$i]['standard1'] = $standard->standard1;
            $summary[$i]['standard2'] = $standard->standard2;
            $where1 = array();
            $a = new Where();
            $a->in('employeeId',array($value['employeeId']));
            array_push($where1,$a);
            $userR = $this->common->getUserTable()->fetchAll($where1)->toArray();
            foreach ($userR as $h => $d){
                $summary[$i]['role'] = $d['role'];
            }
            $i++;
        }
        foreach ($userinfos as $key => $value) {
            //按员工号和日期查询出所有的数据
            $w = array();
            $where = new Where();
            $where->between('signdate', $firstDay, $lastDay);
//            array_push($w,$where);
//            array_push($w,["employeeId"=> $value['employeeId']]);
            $w[0] = $where;
            $w[1] = array("employeeId" => $value['employeeId']);

            //当月某员工所有记录
            $results = $this->common->getRecordTable()->fetchAll($w)->toArray();
            //$results = $records->toArray();
            if (empty($results)) {
                continue;
            }
            //赋予员工基本信息
            $summary[$i]['employeeId'] = $value['employeeId'];
            $summary[$i]['name'] = $value['name'];
            $summary[$i]['company'] = $value['company'];
            $summary[$i]['area'] = $value['area'];
            $summary[$i]['part1'] = $value['part1'];
            $summary[$i]['part2'] = $value['part2'];
            $summary[$i]['team'] = $value['team'];
            $summary[$i]['job'] = $value['job'];
            $summary[$i]['workdays'] = $workdays;
            $summary[$i]['month'] = $month;
            $where1 = array();
            $a = new Where();
            $a->in('employeeId',array($value['employeeId']));
            array_push($where1,$a);
            $userR = $this->common->getUserTable()->fetchAll($where1)->toArray();
            foreach ($userR as $h => $d){
                $summary[$i]['role'] = $d['role'];
            }
            $logicdays = 0;
            $late1 = 0;
            $late2 = 0;
            $late3 = 0;
            $leavely = 0;
            $vacation1 = 0;
            $vacation2 = 0;
            $weekendwork = 0;
            $overtime1 = 0;
            $overtime2 = 0;
            $eventdays = 0;

            $workaway = 0;
            $weekendaway = 0;
            $yeardays = 0;
            $marrydays = 0;
            $maternitydays = 0;
            $sickdays = 0;
            $funeraldays = 0;
            $absencedays = 0;

            $workdays = $monthWorkDays;
            foreach ($results as $k => $v) {

                $w = date('w', strtotime($v['signdate']));
                if (($v['daytype'] == 3 || $v['daytype'] == 4) && ($w != 0 || $w != 6)) {
                    $workdays--;
                }

                switch ($v['updateType1']) {
                    case 'A' :
                        $logicdays += 0.5;
                        break;
                    case 'B' :
                        $logicdays += 0.5;
                        $late1++;
                        break;
                    case 'C' :
                        $logicdays += 0.5;
                        $late2++;
                        break;
                    case 'D' :
                        $logicdays += 0.5;
                        $late3++;
                        break;
                    case 'E' :
                        $eventdays += 0.5;
                        break;
                    case 'J':
                        $weekendwork += 0.5;
                        break;
                    case 'O':
                        $logicdays += 0.5;
                        break;
                    case 'Q' :
                        $weekendwork += 0.5;
                        break;
                    case 'S' :
                        $logicdays += 0.5;
                        break;
                    case '01':
                        $vacation1 += 0.5;
                        break;
                    case '02':
                        $vacation2 += 0.5;
                        break;
                    case '03':
                        $workaway += 0.5;
                        break;
                    case '04':
                        $yeardays += 0.5;
                        break;
                    case '05':
                        $marrydays += 0.5;
                        break;
                    case '06':
                        $sickdays += 0.5;
                        break;
                    case '07':
                        $eventdays += 0.5;
                        break;
                    case '08':
                        $maternitydays += 0.5;
                        break;
                    case '09':
                        $absencedays += 0.5;
                        break;
                    case '10':
                        $funeraldays += 0.5;
                        break;
                    case '11':
                        $weekendaway += 0.5;
                        break;
                }
                switch ($v['updateType2']) {
                    case 'F' :
                        $leavely++;
                        $logicdays += 0.5;
                        break;
                    case 'G' :
                        $logicdays += 0.5;
                        break;
                    case 'H' :
                        $logicdays += 0.5;
                        $overtime1++;
                        break;
                    case 'I' :
                        $logicdays += 0.5;
                        $overtime2++;
                        break;
                    case 'L' :
                        if ($v['updateType1'] == 'K' || $v['updateType1'] == 'J') {
                            $weekendwork += 0.5;
                        }
                        break;
                    case 'M' :
                        $weekendwork += 0.5;
                        break;
                    case 'N' :
                        $weekendwork += 0.5;
                        break;
                    case 'P' :
                        $logicdays += 0.5;
                        break;
                    case 'R' :
                        $weekendwork += 0.5;
                        break;
                    case 'T' :
                        $logicdays += 0.5;
                        break;
                    case '01':
                        $vacation1 += 0.5;
                        break;
                    case '02':
                        $vacation2 += 0.5;
                        break;
                    case '03':
                        $workaway += 0.5;
                        break;
                    case '04':
                        $yeardays += 0.5;
                        break;
                    case '05':
                        $marrydays += 0.5;
                        break;
                    case '06':
                        $sickdays += 0.5;
                        break;
                    case '07':
                        $eventdays += 0.5;
                        break;
                    case '08':
                        $maternitydays += 0.5;
                        break;
                    case '09':
                        $absencedays += 0.5;
                        break;
                    case '10':
                        $funeraldays += 0.5;
                        break;
                    case '11':
                        $weekendaway += 0.5;
                        break;
                    case '01H':
                        $overtime1++;
                        break;
                    case '01I':
                        $overtime2++;
                        break;
                    default :
                        continue;
                }

            }

            $summary[$i]['workdays'] = $workdays;
            $summary[$i]['logicdays'] = $logicdays;
            $summary[$i]['late1'] = $late1 > 5 ? $late1 - 5 + $late2 : $late2;
            $summary[$i]['late2'] = $late3;
            $summary[$i]['leavely'] = $leavely;
            $summary[$i]['vacation1'] = $vacation1;
            $summary[$i]['vacation2'] = $vacation2;
            $summary[$i]['weekendwork'] = $weekendwork;
            $summary[$i]['standard1'] = $standard->standard1;
            $summary[$i]['standard2'] = $standard->standard2;
            $summary[$i]['overtime1'] = $overtime1;
            $summary[$i]['overtime2'] = $overtime2;
            $summary[$i]['total'] = $standard->standard1 * $overtime1 + $standard->standard2 * $overtime2;
            $summary[$i]['workaway'] = $workaway;
            $summary[$i]['weekendaway'] = $weekendaway;
            $summary[$i]['eventdays'] = $eventdays;
            $summary[$i]['yeardays'] = $yeardays;
            $summary[$i]['marrydays'] = $marrydays;
            $summary[$i]['maternitydays'] = $maternitydays;
            $summary[$i]['funeraldays'] = $funeraldays;
            $summary[$i]['sickdays'] = $sickdays;
            $summary[$i]['absencedays'] = $absencedays;
            $i++;
        }

        $report = new Report();
        $report->bonus = 0;
        $report->outdays = 0;
        foreach ($summary as $key => $value) {

            foreach ($value as $k => $v) {
                $report->$k = $v;
            }
            //晚勤人员
            foreach ($users as $key => $value) {
                if ($report->employeeId == $value['employeeId']) {
                    $report->workdays = $days;
                }
            }
            $report->outdays = $report->workaway + $report->weekendaway;
            $report->checkout = true;
            //公式计算
            $all = $report->logicdays + $report->workaway + $report->eventdays;
            if ($all == $workdays) {
                $report->checkall = true;
            } else {
                $report->checkall = false;
            }
            $mWhere = array();
            array_push($mWhere, array('month' => $report->month, 'employeeId' => $report->employeeId));
            $info = $this->common->getReportTable()->fetchAll($mWhere)->toArray();

            if (in_array($report->employeeId, $exclude)) {
                $report->workdays = $workdays;
                $report->logicdays = $workdays;
            }
            if ($info) {
                $report->id = $info[0]['id'];
            }
            $this->common->getReportTable()->saveAs($report);

        }
        return new ViewModel(array(
            'infos' => $paginator,
            'infos1' => $paginator1,
            'typeinfos' => $typeinfos,
            'formInfo' => $formInfo,
            'user' => $user,
            'Duser' => $Duser,
            'num' => $pagenum,
            'fixValue' => $fixValue,

        ));
    }


    //生成报表页面
    public function generateAction()
    {
        return new ViewModel();
    }

    //调整法定假日页面
    public function adjustpageAction()
    {
        return new ViewModel();
    }

    //对工作日期类型进行修改
    public function updateAction()
    {

        if (!$this->getRequest()->isPost()) {
            throw new \Oa\Exception\ErrorException('访问出错');
        }
        $update1Type = $_POST['update1Type'];
        $update2Type = $_POST['update2Type'];
        $desc = $_POST['desc'];
        $id = $_POST['id'];
        $record = $this->common->getRecordTable()->fetchOne(array('id' => $id));
        $record->updateType1 = $update1Type;
        $record->updateType2 = $update2Type;
        $record->description = $desc;
        try {
            $this->common->getRecordTable()->saveAs($record);
            echo 'success';
            die();
        } catch (\Exception $e) {

            $this->common->getRecordTable()->logger->info("考勤记录单条记录提交修改时出错：" . $e->getMessage());
            echo 'fail';
            die();
        }

    }

    public function updateallAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Oa\Exception\ErrorException('访问出错');
        }
        $idString = $_POST['ids'];

        $ids = explode(';', $idString);
        $val1 = explode(';', $_POST['val1']);
        $val2 = explode(';', $_POST['val2']);
        $desc = explode(':', $_POST['desc']);

        $wArray = array();
        $where = new Where();
        $where->in('id', $ids);
        $wArray[] = $where;
        $records = $this->common->getRecordTable()->fetchAll($wArray)->toArray();

        $conn = $this->getAdapter()->getDriver()->getConnection();
        $conn->beginTransaction();
        try {
            foreach ($records as $key => $value) {
                $index = array_search($value['id'], $ids);

                $data ['updateType1'] = $val1[$index];
                $data ['updateType2'] = $val2[$index];
                $data ['description'] = $desc[$index];
                $this->common->getRecordTable()->update($data, array('id' => $value['id']));
            }
            $conn->commit();
            echo 'success';
            die();
        } catch (\Exception $e) {
            $conn->rollback();
            $this->common->getRecordTable()->logger->info("考勤记录页面提交修改时出错：" . $e->getMessage());
            echo 'fail';
            die();
        }

    }

    //特殊情况进行的更新
    public function updatespAction()
    {
        $date = $_GET['starttime'];
        try {

            $data['updateType1'] = 'J';
            $where = "signdate=" . "'" . $date . "'" . " and (updateType1='B' or updateType1='C')";
            $this->common->getRecordTable()->update($data, $where);

            return $this->forward()->dispatch('OA/Controller/record', array('action' => 'serchq'));
        } catch (\Exception $e) {
            $this->common->getRecordTable()->logger->info("考勤记录页面提交修改时出错：" . $e->getMessage());
            echo 'fail';
        }

    }



    //删除操作
    public function deleteAction()
    {
        $pageNum = $this->params()->fromRoute('id', 1);
        $ids = $_POST['post'];
        $where = new Where();
        $where->in('id', $ids);
        $this->common->getRecordTable()->delete($where);
        return $this->forward()->dispatch('OA/Controller/record', array('action' => 'index'));
    }


    //调整工作日与法定假日
    public function adjustAction()
    {
        $day = $_POST['day'];
        $daytype = $_POST['type'];
        $records = $this->common->getRecordTable()->fetchAll(array('signdate' => $day))->toArray();
        if (!$records || empty($records)) {
            echo '没有该日期记录';
        }
        $userInfo = $this->common->getUserTable()->fetchAll()->toArray();
        $userInfo = $this->common->array_column($userInfo, null, 'employeeId');
        $records = $this->common->array_column($records, null, 'id');
        $recordRow = new Record();
        $conn = $this->getAdapter()->getDriver()->getConnection();
        $conn->beginTransaction();
        try {

            foreach ($records as $k => $v) {
                if (empty($userInfo[$v['employeeId']])) {
                    $this->common->getRecordTable()->logger->info("员工登录表中没有对应的员工号" . $v['employeeId']);
                    continue;
                }
                foreach ($v as $key => $value) {
                    if ($key == 'inputFilter') {
                        continue;
                    }
                    $recordRow->$key = $value;
                }

                if ($daytype == 1) {
                    $role = $userInfo[$v['employeeId']]['role'];
                    $updateType1 = $v['updateType1'];
                    $updateType2 = $v['updateType1'];
                    if ($role == 1 || $role == 2) {
                        //调整为工作日，管理员和普通员工
                        if (!empty($v['time1'])) {
                            $updateType1 = $this->common->getTypeDesc(strtotime($v['time1']), 1);
                        }
                        if (!empty($v['time2'])) {
                            $updateType2 = $this->common->getTypeDesc(strtotime($v['time2']), 1);
                        }
                    } else if ($role == 3) {
                        //保洁人员调整为工作日
                        if (!empty($v['time1'])) {
                            $updateType1 = $this->common->getTypeDesc(strtotime($v['time1']), 3);
                        }
                        if (!empty($v['time2'])) {
                            $updateType2 = $this->common->getTypeDesc(strtotime($v['time2']), 3);
                        }

                    }
                    $recordRow->daytype = 1;
                    $recordRow->time1Type = $updateType1;
                    $recordRow->time2Type = $updateType2;
                    $recordRow->updateType1 = $updateType1;
                    $recordRow->updateType2 = $updateType2;
                    $this->common->getRecordTable()->saveAs($recordRow);
                    continue;
                } else if ($daytype == 3) {
                    //调整为法定假日1
                    $recordRow->daytype = 3;
                    if (!empty($v['time1']) || !empty($v['time2'])) {
                        $updateType1 = '01';
                        $updateType2 = '01';
                        $recordRow->updateType1 = $updateType1;
                        $recordRow->updateType2 = $updateType2;
                    } else {
                        $recordRow->updateType1 = 'em';
                        $recordRow->updateType2 = 'em';
                    }
                    $this->common->getRecordTable()->saveAs($recordRow);
                    continue;
                } else if ($daytype == 4) {
                    //调整为法定假日2
                    $recordRow->daytype = 4;
                    if (!empty($v['time1']) || !empty($v['time2'])) {
                        $updateType1 = '02';
                        $updateType2 = '02';
                        $recordRow->updateType1 = $updateType1;
                        $recordRow->updateType2 = $updateType2;

                    } else {
                        $recordRow->updateType1 = 'em';
                        $recordRow->updateType2 = 'em';
                    }
                    $this->common->getRecordTable()->saveAs($recordRow);

                }
            }
            $conn->commit();
            echo 'success';
            die();
        } catch (\Exception $e) {
            $conn->rollback();
            $this->common->getRecordTable()->logger->info("调整假日时出错：" . $e->getMessage());
            echo 'fail';
            die();
        }

    }


//免责处理
    public function mianAction()
    {
        if ($this->getRequest()->isPost()) {
            set_time_limit(600);
            $type = $_POST['type'];
            $datetime = strtotime($_POST['datetime']);
            // $type = 2;$datetime = strtotime('2016-01-06 15:00:00');
            //天
            $date = date('Y-m-d', $datetime);


            //查询用户信息，获取角色
            $userInfo = $this->common->getUserTable()->fetchAll()->toArray();
            $userInfo = $this->common->array_column($userInfo, null, 'employeeId');
            //获取下午下班时间点

            if ($type == 1) {
                $w = array();
                $where = new Where();
                $where->in('updateType1', array('B', 'C', 'D', 'E', 'K'));
                $w[] = $where;
                $w[] = array('signdate' => $date);
                $records = $this->common->getRecordTable()->fetchAll($w)->toArray();
                foreach ($records as $key => $value) {
                    if ($userInfo[$value['employeeId']]['role'] == 4 || $userInfo[$value['employeeId']]['role'] == 3) {
                        continue;
                    }
                    $updateType1 = $value['updateType1'];

                    if (!$records || empty($records)) {
                        echo '没有该日期记录';
                        die();
                    }
                    if ($value['daytype'] == 1) {  //上午免责并且是工作日

                        if (!empty($value['time1']) && strtotime($value['time1']) <= $datetime) {
                            //上午免责
                            $updateType1 = 'A';
                        }
                    } elseif ($value['daytype'] == 2) {
                        if (!empty($value['time1']) && strtotime($value['time1']) <= $datetime) {
                            //周末上午免责
                            $updateType1 = 'J';
                        }
                    }
                    $data['updateType1'] = $updateType1;

                    $this->common->getRecordTable()->update($data, array('id' => $value['id']));
                }
            } else {
                $w = array();
                $where = new Where();
                $where->in('updateType2', array('F', 'L', 'emp'));
                $w[] = $where;
                $w[] = array('signdate' => $date);

                $records = $this->common->getRecordTable()->fetchAll($w)->toArray();
                if (!$records || empty($records)) {
                    echo '没有该日期记录';
                    die();
                }
                foreach ($records as $key => $value) {
                    if ($userInfo[$value['employeeId']]['role'] == 4 || $userInfo[$value['employeeId']]['role'] == 3) {
                        continue;
                    }

                    $updateType2 = $value['updateType2'];
                    if ($value['daytype'] == 1) {
                        if ($value['updateType2'] == 'F' || $value['updateType2'] == 'emp') {
                            $updateType2 = 'G';
                        }

                    } elseif ($value['daytype'] == 2) {
                        $updateType2 = 'N';
                    }
                    $data['updateType2'] = $updateType2;
                    $this->common->getRecordTable()->update($data, array('id' => $value['id']));
                }
            }
            echo 'success';
            die();
        }


        return array();
    }

    //导出考勤记录表
    public function exportAction()
    {
        if (!$this->getRequest()->isPost()) {
            return new ViewModel();
        }
        $date = $_POST['date'];
        $today = date("Y-m-d");

        if ($date > $today) {
            echo "<script>alert('你选择的时间超出当前时间');</script>";
        } else {

            $recordInfos = $this->common->getRecordTable()->getPaginator(array('signdate' => $date));

            $excel = new \PHPExcel();
            $excel->setActiveSheetIndex(0)->setTitle('考勤记录报表');


            $excel->setActiveSheetIndex(0)
                ->setCellValue('A1', '考勤日期')
                ->setCellValue('B1', '姓名')
                ->setCellValue('C1', '签到时间')
                ->setCellValue('D1', '初始')
                ->setCellValue('E1', '更新')
                ->setCellValue('F1', '签退时间')
                ->setCellValue('G1', '初始')
                ->setCellValue('H1', '更新')
                ->setCellValue('I1', '时间')
                ->setCellValue('J1', '备注');
            $i = 2;

            foreach ($recordInfos as $key => $value) {
                $excel->setActiveSheetIndex(0)
                    ->setCellValue("A$i", $value['signdate'])
                    ->setCellValue("B$i", $value['name'])
                    ->setCellValue("C$i", $value['time1'])
                    ->setCellValue("D$i", $value['time1Type'])
                    ->setCellValue("E$i", $value['updateType1'])
                    ->setCellValue("F$i", $value['time2'])
                    ->setCellValue("G$i", $value['time2Type'])
                    ->setCellValue("H$i", $value['updateType2'])
                    ->setCellValue("I$i", $value['daytype'])
                    ->setCellValue("J$i", $value['description']);
                $i++;
            }

            //设置粗体，冻结首行
            $excel->getActiveSheet()->getStyle("A1:AK1")->getFont()->setBold(true);
            $excel->getActiveSheet()->freezePane('A2');
            $excelWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

            //$excelWriter->save('aa.xlsx');
            $filename = date('YmdHis') . '.xlsx';
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . $filename . '"');
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $excelWriter->save('php://output');
            die();
        }
    }

    //调整半天假日
    public function halfAction()
    {
        if (!$this->getRequest()->isPost()) {
            throw new \Oa\Exception\ErrorException('请以正确的方式访问');
        }
        try {
            $conn = $this->getAdapter()->getDriver()->getConnection();
            $conn->beginTransaction();
            $date = $_POST['halfDay'];
            $dayType = $_POST['dayType'];
            $role = $_POST['role'];
            //获取非保洁和非晚勤人员的员工号
            $where = array();
            $userWhere = new Where();
            $userWhere->in('role', array(1, 2));
            $where[0] = $userWhere;
            $user = $this->common->getUserTable($where)->fetchAll()->toArray();
            $user = $this->common->array_column($user, null, 'employeeId');
            $userEmployee = array_keys($user);

            if ($role == 1) {
                //三八节
                $where = array();
                $eWhere = new Where();
                $eWhere->in('employeeId', $userEmployee);
                $where[0] = $eWhere;
                $where[1] = array('sex' => '女');
                $userInfo = $this->common->getUserinfoTable()->fetchAll($where)->toArray();
                $userInfo = $this->common->array_column($userInfo, null, 'employeeId');
                $employeeIdArray = array_keys($userInfo);
                $oWhere = array();
                $w = new Where();
                $w->in('employeeId', $employeeIdArray);
                $oWhere[0] = $w;
                $oWhere[1] = array('signdate' => $date);
                $record = $this->common->getRecordTable()->fetchAll($oWhere)->toArray();
                foreach ($record as $item) {
                    if ($dayType == 'am') {   //上午放假
                        $data['updateType1'] = "A11";
                    } else {
                        //下午放假
                        if ($item['updateType2'] == 'H') {
                            $data['updateType2'] = '01H';
                        } elseif ($item['updateType2'] == 'I') {
                            $data['updateType2'] = '01I';
                        } else {
                            $data['updateType2'] = "A11";
                        }
                    }
                    $this->common->getRecordTable()->update($data, array('id' => $item['id']));
                }

            } else {
                $where = array();
                $eWhere = new Where();
                $eWhere->in('employeeId', $userEmployee);
                $where[0] = $eWhere;
                $userInfo = $this->common->getUserinfoTable()->fetchAll($where)->toArray();
                $userInfo = $this->common->array_column($userInfo, 'identify', 'employeeId');
                //计算年龄
                foreach ($userInfo as $key => $item) {
                    $identify = substr($item, 6, 8);
                    $year = substr($identify, 0, 4);
                    $month = substr($identify, 4, 2);
                    $day = substr($identify, 6, 2);
                    $now = date('Y-m-d');
                    $nowDate = explode('-', $now);
                    $yearDiff = $nowDate[0] - $year;
                    if ($nowDate[1] - $month < 0) {
                        $yearDiff--;
                    } elseif ($nowDate[1] - $month == 0) {
                        if ($nowDate[2] - $day < 0)
                            $yearDiff--;
                    }
                    $userInfo[$key] = $yearDiff;
                }
                $employeeId = array_keys($userInfo);
                $oWhere = array();
                $w = new Where();
                $w->in('employeeId', $employeeId);
                $oWhere[0] = $w;
                $oWhere[1] = array('signdate' => $date);
                $record = $this->common->getRecordTable()->fetchAll($oWhere)->toArray();
                foreach ($record as $item) {
                    $age = $userInfo[$item['employeeId']];
                    if ($age <= 28) {
                        if ($dayType == 'am') {   //上午放假
                            $data['updateType1'] = "A11";
                        } else {
                            //下午放假
                            if ($item['updateType2'] == 'H') {
                                $data['updateType2'] = '01H';
                            } elseif ($item['updateType2'] == 'I') {
                                $data['updateType2'] = '01I';
                            } else {
                                $data['updateType2'] = "A11";
                            }
                        }

                        $this->common->getRecordTable()->update($data, array('id' => $item['id']));

                    }
                }
            }
            $conn->commit();
            echo 'success';
            die();
        } catch (\Exception $e) {
            $conn->rollback();
            $this->common->getRecordTable()->logger->err($e->getMessage());
            echo '亲，出错了';
            die();
        }
    }
}
