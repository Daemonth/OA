<?php


namespace Oa\Controller;

//use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\View\Model\ViewModel;
use Oa\Controller\BaseController;

use Oa\Model\Report;
use Oa\Model\ReportTable;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ReportController extends BaseController
{


    public function indexAction()
    {


        $where = array();
        $formInfo = null;
        $pagenum = (int)$this->params()->fromRoute('id', 1);

        //提交月份
        if (isset($_GET['month']) && strlen($_GET['month'] > 0)) {
            array_push($where, array('month' => $_GET['month']));
            $formInfo['month'] = $_GET['month'];

        }
        //姓名
        if (isset($_GET['name']) && strlen($_GET['name']) > 0) {
            array_push($where, array('name' => $_GET['name']));
            $formInfo['name'] = $_GET['name'];
        }
       // if (empty($where)) {
      //      $order = 'month desc,employeeId asc';
      //  } else {
       //     $order = null;
      //  }
        $order = array('month desc','employeeId');
        $paginator = $this->common->getReportTable()->getPaginator($where,$order);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(10);
        return new ViewModel(array(
            'infos' => $paginator,
            'formInfo' => $formInfo,
        ));
    }


    public function updateAction()
    {
        $form = new \Oa\Form\ReportForm();
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index'));
        }
        $reportRow = $this->common->getReportTable()->fetchOne(array('id'=>$id));
        if (!$reportRow) {
            $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index'));
        }
        $form->bind($reportRow);

        if ($this->getRequest()->isPost()) {
            // $form->setInputFilter($reportRow->getInputFilter());
            foreach ($_POST as $key => $value) {
                if ($key != 'submit') {
                    $reportRow->$key = $value;
                }
            }
            $out = $reportRow->workaway + $reportRow->weekendaway;
            if ($out == $reportRow->outdays) {
                $reportRow->checkout = 1;
            } else {
                $reportRow->checkout = 0;
            }
            $all = $reportRow->logicdays + $reportRow->workaway + $reportRow->eventdays;
            if ($all == $reportRow->workdays) {
                $reportRow->checkall = 1;
            } else {
                $reportRow->checkall = 0;
            }

            $this->common->getReportTable()->saveAs($reportRow);
            $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index'));

        }

        return array('form' => $form);

    }

    //删除记录
    public function deleteAction()
    {
        $pagenum = (int)$this->params()->fromRoute('id', 1);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            $where = new Where();
            $where->in('id',$data['post']);

            $this->common->getReportTable()->delete($where);
            $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index', 'id' => $pagenum));
        }

        $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index', 'id' => $pagenum));
    }

    //生成汇总信息
    public function generateAction()
    {
        
        $date = $_POST['date'];
      
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
        array_push($wVip,$vipWhere);
        $userinfos = $this->common->getUserinfoTable()->fetchAll($wVip)->toArray();
        $summary = array();
        $i = 0;
        //加班费标准
        $standard = $this->common->getStandardTable()->fetchOne();

        //晚勤人员信息
        $users = $this->common->getUserTable()->fetchAll(array('role'=>4))->toArray();
        //不用刷卡人员
        $where = array();
        $w = new Where();

        $w->in('employeeId', $exclude);
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
            $w[1] = array("employeeId"=> $value['employeeId']);

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

                $w = date('w',strtotime($v['signdate']));
                if(($v['daytype'] == 3 || $v['daytype'] == 4) && ($w != 0 || $w != 6))
                {
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
                switch($v['updateType2']) {
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
                        if($v['updateType1'] == 'K' || $v['updateType1'] == 'J')
                        {
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
            array_push($mWhere,array('month' => $report->month, 'employeeId' => $report->employeeId));
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
        $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index'));
    }

    //减去法定假日
    public function decreaseAction()
    {
        if (!$this->getRequest()->isPost()) {
            return new ViewModel();
        }
        $vacationMonth = $_POST['vacationMonth'];
        $days = (int)$_POST['days'];

        $reportInfo = $this->common->getReportTable()->fetchAll(array('month'=>$vacationMonth));

        foreach ($reportInfo as $key => $value) {
            $value->workdays = $value->workdays - $days;

            $out = $value->workaway + $value->weekendaway;
            if ($value->outdays == $out) {
                $value->checkout = 1;
            } else {
                $value->checkout = 0;
            }
            $all = $value->logicdays + $value->workaway + $value->eventdays;
            if ($all == $value->workdays) {
                $value->checkall = 1;
            } else {
                $value->checkall = 0;
            }
            $this->common->getReportTable()->saveAs($value);

        }
        $this->redirect()->toRoute('oa/default', array('controller' => 'report', 'action' => 'index'));
    }

    //导出汇总表
    public function exportAction()
    {   

        
        if (!$this->getRequest()->isPost()) {
            return new ViewModel();
        }
        $date = $_POST['date'];
        $today=date("Y-m");

        if ($date>$today) {
            echo "<script>alert('你选择的时间超出当前时间');</script>";
        }else{
        //导出普通员工的报表
        $i=0;
        $where=array();
        $data=array();
        $pEmployee= $this->common->getUserTable()->fetchAll(array('role'=>1))->toArray();
        foreach ($pEmployee as $k => $v) {
        $in1 = new Where();
        $in1->in('employeeId', array($v['employeeId']));
        $in1->in('month',array($date));
        array_push($where, $in1);
        $EreportInfos = $this->common->getReportTable()->fetchAll($where)->toArray();
         
        $data[$i]=$EreportInfos;
        
         $i++;
     
        }

        //取其他员工的数据
        $j=0;
        $where1=array();
        $where2=array();
        $in3 = new Where();
        $in3->in('role', array('3','5'));
        array_push($where2, $in3);        
        $data2=array();
        $oEmployee= $this->common->getUserTable()->fetchAll($where2)->toArray();
        foreach ($oEmployee as $k => $v) {
        $in2 = new Where();
        $in2->in('employeeId', array($v['employeeId']));
        $in2->in('month',array($date));
        array_push($where1, $in2);
        $oEreportInfos = $this->common->getReportTable()->fetchAll($where1)->toArray();
        $data2[$j]=$oEreportInfos;
         $j++;
        }
 

        $excel = new \PHPExcel();

        $excel->setActiveSheetIndex(0)->setTitle('普通员工考勤表');
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A1', '月份')
            ->setCellValue('B1', '公司名称')
            ->setCellValue('C1', '地区')
            ->setCellValue('D1', '一级部门')
            ->setCellValue('E1', '二级部门')
            ->setCellValue('F1', '职组')
            ->setCellValue('G1', '职位')
            ->setCellValue('H1', '工号')
            ->setCellValue('I1', '姓名')
            ->setCellValue('J1', '工作日')
            ->setCellValue('K1', '实际出勤日')
            ->setCellValue('K1', '工作日出差')
            ->setCellValue('L1', '迟到1')
            ->setCellValue('M1', '迟到2')
            ->setCellValue('N1', '早退')
            ->setCellValue('O1', '周末值守')
            ->setCellValue('P1', '节假日加班1')
            ->setCellValue('Q1', '节假日加班2')
            ->setCellValue('R1', '周末出差')
            ->setCellValue('S1', '全勤奖')
            ->setCellValue('T1', '晚补1')
            ->setCellValue('U1', '晚9:30加班')
            ->setCellValue('V1', '晚补2')
            ->setCellValue('W1', '晚10:30加班')
            ->setCellValue('X1', '加班补助合计')
            ->setCellValue('Y1', '事假')
            ->setCellValue('Z1', '年假')
            ->setCellValue('AA1', '产假')
            ->setCellValue('AB1', '病假')
            ->setCellValue('AC1', '婚假')
            ->setCellValue('AD1', '丧假')
            ->setCellValue('AE1', '旷工')
            ->setCellValue('AF1', '出差')
            ->setCellValue('AG1', 'check')
            ->setCellValue('AH1', 'check')
            ->setCellValue('AI1', '备注1')
            ->setCellValue('AJ1', '备注2')
            ->setCellValue('AK1', '备注3');
        $i = 2;

        foreach ($data as $key => $data1) {
            foreach ($data1 as $value) {
              
            $excel->setActiveSheetIndex(0)
                ->setCellValue("A$i", $value['month'])
                ->setCellValue("B$i", $value['company'])
                ->setCellValue("C$i", $value['area'])
                ->setCellValue("D$i", $value['part1'])
                ->setCellValue("E$i", $value['part2'])
                ->setCellValue("F$i", $value['team'])
                ->setCellValue("G$i", $value['job'])
                ->setCellValue("H$i", $value['employeeId'])
                ->setCellValue("I$i", $value['name'])
                ->setCellValue("J$i", $value['workdays'])
                ->setCellValue("K$i", $value['logicdays'])
                ->setCellValue("K$i", $value['workaway'])
                ->setCellValue("L$i", $value['late1'])
                ->setCellValue("M$i", $value['late2'])
                ->setCellValue("N$i", $value['leavely'])
                ->setCellValue("O$i", $value['weekendwork'])
                ->setCellValue("P$i", $value['vacation1'])
                ->setCellValue("Q$i", $value['vacation2'])
                ->setCellValue("R$i", $value['weekendaway'])
                ->setCellValue("S$i", $value['bonus'])
                ->setCellValue("T$i", $value['standard1'])
                ->setCellValue("U$i", $value['overtime1'])
                ->setCellValue("V$i", $value['standard2'])
                ->setCellValue("W$i", $value['overtime2'])
                ->setCellValue("X$i", $value['total'])
                ->setCellValue("Y$i", $value['eventdays'])
                ->setCellValue("Z$i", $value['yeardays'])
                ->setCellValue("AA$i", $value['marrydays'])
                ->setCellValue("AB$i", $value['sickdays'])
                ->setCellValue("AC$i", $value['marrydays'])
                ->setCellValue("AD$i", $value['funeraldays'])
                ->setCellValue("AE$i", $value['absencedays'])
                ->setCellValue("AF$i", $value['outdays'])
                ->setCellValue("AG$i", $value['checkout'] == 1? 'true':'false')
                ->setCellValue("AH$i", $value['checkall'] == 0? 'true':'false')
                ->setCellValue("AI$i", $value['desc1'])
                ->setCellValue("AJ$i", $value['desc2'])
                ->setCellValue("AK$i", $value['desc3']);
            $i++;
           }
        }
        
        $excel->createSheet();
        $excel->setActiveSheetIndex(1)->setTitle('其他员工考勤表');
        $excel->setActiveSheetIndex(1)
            ->setCellValue('A1', '月份')
            ->setCellValue('B1', '公司名称')
            ->setCellValue('C1', '地区')
            ->setCellValue('D1', '一级部门')
            ->setCellValue('E1', '二级部门')
            ->setCellValue('F1', '职组')
            ->setCellValue('G1', '职位')
            ->setCellValue('H1', '工号')
            ->setCellValue('I1', '姓名')
            ->setCellValue('J1', '工作日')
            ->setCellValue('K1', '实际出勤日')
            ->setCellValue('K1', '工作日出差')
            ->setCellValue('L1', '迟到1')
            ->setCellValue('M1', '迟到2')
            ->setCellValue('N1', '早退')
            ->setCellValue('O1', '周末值守')
            ->setCellValue('P1', '节假日加班1')
            ->setCellValue('Q1', '节假日加班2')
            ->setCellValue('R1', '周末出差')
            ->setCellValue('S1', '全勤奖')
            ->setCellValue('T1', '晚补1')
            ->setCellValue('U1', '晚9:30加班')
            ->setCellValue('V1', '晚补2')
            ->setCellValue('W1', '晚10:30加班')
            ->setCellValue('X1', '加班补助合计')
            ->setCellValue('Y1', '事假')
            ->setCellValue('Z1', '年假')
            ->setCellValue('AA1', '产假')
            ->setCellValue('AB1', '病假')
            ->setCellValue('AC1', '婚假')
            ->setCellValue('AD1', '丧假')
            ->setCellValue('AE1', '旷工')
            ->setCellValue('AF1', '出差')
            ->setCellValue('AG1', 'check')
            ->setCellValue('AH1', 'check')
            ->setCellValue('AI1', '备注1')
            ->setCellValue('AJ1', '备注2')
            ->setCellValue('AK1', '备注3');
        $i = 2;

        foreach ($data2 as $key => $data3) {
            foreach ($data3 as $value) {
              
            $excel->setActiveSheetIndex(1)
                ->setCellValue("A$i", $value['month'])
                ->setCellValue("B$i", $value['company'])
                ->setCellValue("C$i", $value['area'])
                ->setCellValue("D$i", $value['part1'])
                ->setCellValue("E$i", $value['part2'])
                ->setCellValue("F$i", $value['team'])
                ->setCellValue("G$i", $value['job'])
                ->setCellValue("H$i", $value['employeeId'])
                ->setCellValue("I$i", $value['name'])
                ->setCellValue("J$i", $value['workdays'])
                ->setCellValue("K$i", $value['logicdays'])
                ->setCellValue("K$i", $value['workaway'])
                ->setCellValue("L$i", $value['late1'])
                ->setCellValue("M$i", $value['late2'])
                ->setCellValue("N$i", $value['leavely'])
                ->setCellValue("O$i", $value['weekendwork'])
                ->setCellValue("P$i", $value['vacation1'])
                ->setCellValue("Q$i", $value['vacation2'])
                ->setCellValue("R$i", $value['weekendaway'])
                ->setCellValue("S$i", $value['bonus'])
                ->setCellValue("T$i", $value['standard1'])
                ->setCellValue("U$i", $value['overtime1'])
                ->setCellValue("V$i", $value['standard2'])
                ->setCellValue("W$i", $value['overtime2'])
                ->setCellValue("X$i", $value['total'])
                ->setCellValue("Y$i", $value['eventdays'])
                ->setCellValue("Z$i", $value['yeardays'])
                ->setCellValue("AA$i", $value['marrydays'])
                ->setCellValue("AB$i", $value['sickdays'])
                ->setCellValue("AC$i", $value['marrydays'])
                ->setCellValue("AD$i", $value['funeraldays'])
                ->setCellValue("AE$i", $value['absencedays'])
                ->setCellValue("AF$i", $value['outdays'])
                ->setCellValue("AG$i", $value['checkout'] == 1? 'true':'false')
                ->setCellValue("AH$i", $value['checkall'] == 0? 'true':'false')
                ->setCellValue("AI$i", $value['desc1'])
                ->setCellValue("AJ$i", $value['desc2'])
                ->setCellValue("AK$i", $value['desc3']);
            $i++;
           }
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

}
