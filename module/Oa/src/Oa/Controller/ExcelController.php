<?php


namespace Oa\Controller;

use Oa\Exception\ErrorException;
use Oa\Model\History;
use Oa\Model\Origin;
use Oa\Model\User;
use Oa\Model\Userinfo;
use Oa\Tools\Utils;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Oa\Model\Record;
use Oa\Tools\Common;
use Oa\Controller\BaseController;
class ExcelController extends BaseController
{
    //上传记录
    public function indexAction()
    {
        $pagenum = (int)$this->params()->fromRoute('id', 1);
        $order = 'uptime desc';
        $where = array();
        $formInfo = null;
        $time = !empty($_GET['time']) ? $_GET['time'] : null;
        if (strlen($time) > 0) {
            $w = new Where();
            $w->greaterThan('uptime', $time);
            array_push($where, $w);
            $formInfo['uptime'] = $time;
        }
        $paginator = $this->common->getHistoryTable()->getPaginator($where, $order);
        $paginator->setCurrentPageNumber($pagenum);
        $paginator->setItemCountPerPage(30);

        return new ViewModel(array(
            'infos' => $paginator,
            'formInfo' => $formInfo
        ));
    }


    public function employAction()
    {
        if (isset($_FILES['employ']) && strlen($_FILES['employ']['name']) > 0) {
            $dir = date('Y-m-d');
            if (!is_dir(APP . '/data/uploads/' . $dir)) {
                mkdir(APP . '/data/uploads/' . $dir);
            }
            $filename = $this->common->getRandom() . '-' . $_FILES['employ']['name'];

            $arr = explode('.', $_FILES['employ']['name']);
            $ext = $arr[count($arr) - 1];
            if ($ext != 'xls' && $ext != 'xlsx') {
                return $this->redirect()->toRoute('oa/default', array('controller' => 'employ', 'action' => 'index'));
            }
            $route = APP . '/data/uploads/' . $dir . '/' . $filename;
            if (move_uploaded_file($_FILES['employ']['tmp_name'], $route)) {

                $excel = \PHPExcel_IOFactory::load($route);
                $excel->setActiveSheetIndex(0);

                $infos = $excel->getActiveSheet()->toArray();
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);

                $userinfo = new Userinfo();
                $user = new User();
                //保存信息
                $conn = $this->getAdapter()->getDriver()->getConnection();
                $conn->beginTransaction();
                try {
                    $this->common->getUserinfoTable()->deleteAll('userinfo');
                    for ($i = 1; $i < $count; $i++) {
                        $userinfo->employeeId = $infos[$i][7];
                        $userinfo->attendId =$infos[$i][8];
                        $userinfo->name = $infos[$i][1];
                        $userinfo->company = "北京微播易";
                        $userinfo->area = "北京";
                        $userinfo->team = $infos[$i][2];
                        $userinfo->part1 = $infos[$i][3];
                        $userinfo->part2 = $infos[$i][4];
                        $userinfo->job = $infos[$i][5];
                        $userinfo->sex = $infos[$i][8];
                        $userinfo->identify = null;
                        $result = $this->common->getUserinfoTable()->saveAs($userinfo);
                        $where="employeeId=".$infos[$i][7];
                        $userhg=$this->common->getUserTable()->fetchAll($where)->toArray();
                        if(empty($userhg)){
                            $user->employeeId = $infos[$i][7];
                            $user->email =$infos[$i][10];;
                            $user->password =md5("123456");
                            $user->name = $infos[$i][1];
                            $user->role = 1;
                            $user->initlogin=1;
                            $result1 = $this->common->getUserTable()->saveAs($user);
                            if (!$result1) {
                                throw new ErrorException('');
                            }
                        }
                        if (!$result) {
                            throw new ErrorException('');
                        }
                    }
                    unset($infos);  //释放上传的数据
                    //保存上传历史
                    $this->saveHistory($filename);
                    $conn->commit();
                } catch (\Exception $e) {
                    $conn->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("普通员工基本信息表插入数据第" . $i . "行出错");
                }

                $excel->setActiveSheetIndex(1);
                $infos = $excel->getActiveSheet()->toArray();
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);

                $userinfo = new Userinfo();
                //保存信息
                $conn = $this->getAdapter()->getDriver()->getConnection();
                $conn->beginTransaction();
                try {
                    for ($i = 1; $i < $count; $i++) {
                        $userinfo->employeeId = $infos[$i][5];
                        $userinfo->attendId =$infos[$i][6];
                        $userinfo->name = $infos[$i][1];
                        $userinfo->company = "北京微播易";
                        $userinfo->area = "北京";
                        $userinfo->team = $infos[$i][2];
                        $userinfo->part1 = $infos[$i][3];
                        $userinfo->part2 = $infos[$i][3];
                        $userinfo->job = $infos[$i][4];
                        $userinfo->sex = $infos[$i][8];
                        $userinfo->identify = null;
                        $result = $this->common->getUserinfoTable()->saveAs($userinfo);
                        $where="employeeId=".$infos[$i][5];
                        $userhg=$this->common->getUserTable()->fetchAll($where)->toArray();
                        if(empty($userhg)){
                            $user->employeeId = $infos[$i][5];
                            $user->email =null;
                            $user->password =md5("123456");
                            $user->name = $infos[$i][1];
                            $user->role = 5;
                            $user->initlogin=1;
                            $result1 = $this->common->getUserTable()->saveAs($user);
                            if (!$result1) {
                                throw new ErrorException('');
                            }
                        }
                        if (!$result) {
                            throw new ErrorException('');
                        }
                    }
                    unset($infos);  //释放上传的数据
                    //保存上传历史
                    $this->saveHistory($filename);
                    $conn->commit();
                } catch (\Exception $e) {
                    $conn->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("其他员工基本信息表插入数据第" . $i . "行出错");
                }
                $excel->setActiveSheetIndex(2);
                $infos = $excel->getActiveSheet()->toArray();
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);

                $userinfo = new Userinfo();
                //保存信息
                $conn = $this->getAdapter()->getDriver()->getConnection();
                $conn->beginTransaction();
                try {
                    for ($i = 1; $i < $count; $i++) {
                        $userinfo->employeeId = $infos[$i][1];
                        $userinfo->attendId =$infos[$i][2];
                        $userinfo->name = $infos[$i][0];
                        $userinfo->company = "北京微播易";
                        $userinfo->area = "北京";
                        $userinfo->team = "";
                        $userinfo->part1 = "";
                        $userinfo->part2 = "";
                        $userinfo->job = "";
                        $userinfo->sex = $infos[$i][3];
                        $userinfo->identify = null;
                        $result = $this->common->getUserinfoTable()->saveAs($userinfo);
                        $where="employeeId=".$infos[$i][1];
                        $userhg=$this->common->getUserTable()->fetchAll($where)->toArray();
                        if(empty($userhg)){
                            $user->employeeId = $infos[$i][1];
                            $user->email =null;
                            $user->password =md5("123456");
                            $user->name = $infos[$i][0];
                            $user->role = 3;
                            $user->initlogin=1;
                            $result1 = $this->common->getUserTable()->saveAs($user);
                            if (!$result1) {
                                throw new ErrorException('');
                            }
                        }
                        if (!$result) {

                            throw new ErrorException('');
                        }
                    }
                    unset($infos);  //释放上传的数据
                    //保存上传历史
                    $this->saveHistory($filename);
                    $conn->commit();
                } catch (\Exception $e) {
                    $conn->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("保洁员工表基本信息表插入数据第" . $i . "行出错");
                }
                $excel->setActiveSheetIndex(3);
                $infos = $excel->getActiveSheet()->toArray();
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);
                $userinfo = new Userinfo();
                //保存信息
                $conn = $this->getAdapter()->getDriver()->getConnection();
                $conn->beginTransaction();
                try {
                    for ($i = 1; $i < $count; $i++) {
                        $userinfo->employeeId = $infos[$i][7];
                        $userinfo->attendId =$i;
                        $userinfo->name = $infos[$i][1];
                        $userinfo->company = "北京微播易";
                        $userinfo->area = "北京";
                        $userinfo->team = $infos[$i][2];
                        $userinfo->part1 = $infos[$i][3];
                        $userinfo->part2 = $infos[$i][4];
                        $userinfo->job = $infos[$i][5];
                        $userinfo->sex = $infos[$i][8];
                        $userinfo->identify = null;
                        $result = $this->common->getUserinfoTable()->saveAs($userinfo);
                        $user->employeeId = $infos[$i][7];
                        $user->email =$infos[$i][10];;
                        $user->password =md5("123456");
                        $user->name = $infos[$i][1];
                        $user->role = '6';
                        $user->initlogin=1;
                        $where="employeeId=".$infos[$i][7];
                        $userhg=$this->common->getUserTable()->fetchAll($where)->toArray();
                        if(empty($userhg)){
                            $user->employeeId = $infos[$i][7];
                            $user->email =$infos[$i][10];;
                            $user->password =md5("123456");
                            $user->name = $infos[$i][1];
                            $user->role = '6';
                            $user->initlogin=1;
                            $result1 = $this->common->getUserTable()->saveAs($user);
                            if (!$result1) {
                                throw new ErrorException('');
                            }
                        }
                        if (!$result) {
                            throw new ErrorException('');
                        }
                    }
                    unset($infos);  //释放上传的数据
                    //保存上传历史
                    $this->saveHistory($filename);
                    $conn->commit();
                } catch (\Exception $e) {
                    $conn->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("未打卡员工表基本信息表插入数据第" . $i . "行出错");
                }
            }

        }

        return $this->redirect()->toRoute('oa/default', array('controller' => 'employ', 'action' => 'index'));
    }

    public function userAction()
    {
        if (isset($_FILES['user']) && strlen($_FILES['user']['name']) > 0) {
            $dir = date('Y-m-d');
            if (!is_dir(APP . '/data/uploads/' . $dir)) {
                mkdir(APP . '/data/uploads/' . $dir);
            }
            $filename = $this->common->getRandom() . '-' . $_FILES['user']['name'];
            $arr = explode('.', $_FILES['user']['name']);
            $ext = $arr[count($arr) - 1];
            if ($ext != 'xls' && $ext != 'xlsx') {
                return $this->redirect()->toRoute('oa/default', array('controller' => 'user', 'action' => 'index'));
            }
            $route = APP . '/data/uploads/' . $dir . '/' . $filename;
            if (move_uploaded_file($_FILES['user']['tmp_name'], $route)) {
                $excel = \PHPExcel_IOFactory::load($route);
                $excel->setActiveSheetIndex(0);
                $infos = $excel->getActiveSheet()->toArray();
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);

                //保存信息
                $conn = $this->getAdapter()->getDriver()->getConnection();
                $conn->beginTransaction();

                try {
                    //先删除原始信息
                    $this->common->getUserTable()->deleteAll();
                    $userData = array();
                    for ($i = 1; $i < $count; $i++) {
                        $userData['employeeId'] = $infos[$i][0];
                        $userData['email'] = $infos[$i][1];
                        $userData['password'] = md5($infos[$i][2]);
                        $userData['name'] = $infos[$i][3];
                        $userData['role'] = $infos[$i][4];
                        $userData['initlogin'] = 1;
                        $result = $this->common->getUserTable()->insert($userData);
                        if (!$result) {

                            throw new ErrorException('');
                        }
                    }
                    unset($infos);  //释放上传的数据
                    //保存上传历史
                    $this->saveHistory($filename);
                    $conn->commit();
                } catch (\Exception $e) {
                    $conn->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("登录表插入数据第" . $i . "行出错");
                }
            }

        }
        return $this->redirect()->toRoute('oa/default', array('controller' => 'user', 'action' => 'index'));
    }

    //上传原始表
    public function originAction()
    {
        set_time_limit(600);
        if (empty($_POST['date'])){
            $date=date("Y-m");
        }else{
            $date=$_POST['date'];
        }
        $firstday = date("Y-m-01", strtotime($date));
        $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
        //如果有提交上传
        if (isset($_FILES['origin']) && strlen($_FILES['origin']['name']) > 0) {
            $where = new Where();
            $where->between('time', $firstday, $lastday);
            $this->common->getOriginTable()->delete($where);
            $where1 = new Where();
            $where1->between('signdate', $firstday, $lastday);
            $this->common->getRecordTable()->delete($where1);
            $where2 = new Where();
            $where2->in('month', array($date));
            $this->common->getReportTable()->delete($where2);
            $dir = date('Y-m-d');
            //以天为单位创建目录
            if (!is_dir(APP . '/data/uploads/' . $dir)) {
                mkdir(APP . '/data/uploads/' . $dir);
            }
            $filename = $this->common->getRandom() . '-' . $_FILES['origin']['name'];
            $arr = explode('.', $_FILES['origin']['name']);
            $ext = $arr[count($arr) - 1];
            //判断文件类型
            if ($ext != 'xls' && $ext != 'xlsx') {
                return $this->redirect()->toRoute('oa/default', array('controller' => 'origin', 'action' => 'index'));
            }

            $route = APP . '/data/uploads/' . $dir . '/' . $filename;

            if (move_uploaded_file($_FILES['origin']['tmp_name'], $route)) {
                $excel = \PHPExcel_IOFactory::load($route);
                $excel->setActiveSheetIndex(0);
                $infos = $excel->getActiveSheet()->toArray();
                $count = count($infos);
                //前3行为描述文字
                for ($k = 0; $k < 3; $k++) {
                    unset($infos[$k]);
                }
                $temp = array();
                foreach ($infos as $key => $value) {
                    if (empty($value[0])) {
                        break;
                    }
                    $temp[$key] = $value;
                }
                $infos = $temp;
                $count = count($infos);
                $origin = new Origin();
                $dateArray = array();
                $conn1 = $this->getAdapter()->getDriver()->getConnection();
                $conn1->beginTransaction();
                try {
                    for ($i = 3; $i < $count + 3; $i++) {
                        $origin->printdate = $infos[$i][0];
                        $origin->attendId = $infos[$i][1];
                        $origin->name = $infos[$i][2];
                        $origin->depart = $infos[$i][3];
                        $origin->printway = $infos[$i][4];
                        $origin->time = $infos[$i][5];
                        $origin->device = $infos[$i][6];
                        $origin->devicesuq = $infos[$i][7];
                        $results = $this->common->getOriginTable()->saveAs($origin);
                        if (!$results) {
                            throw new ErrorException("");
                        }
                        $month = date('Y-m-01', strtotime($infos[$i][5]));
                        if (!in_array($month, $dateArray)) {
                            $dateArray[] = $month;
                        }
                    }
                    $conn1->commit();

                } catch (\Exception $e) {
                    $conn1->rollback();
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    $i++;
                    throw new ErrorException("第 $i 出现错误," . $e->errorMessage());
                }
                $conn2 = $this->getAdapter()->getDriver()->getConnection();
                $conn2->beginTransaction();
                //生成考勤记录

                try {
                    //产生每个月的空值记录
                    foreach ($dateArray as $key => $value) {
                        $this->emptyRecord($value);
                    }
                    $userResult = $this->common->getUserTable()->fetchAll()->toArray();
                    $userResult = $this->common->array_column($userResult, null, 'employeeId');
                    $this->generateRecord($infos, $userResult);
                    unset($infos);
                    //保存上传历史记录
                    $this->saveHistory($filename);
                    $conn2->commit();

                } catch (\Exception $e) {
                    $conn2->rollback();
                    $num = $i - 3;
                    $this->getAdapter()->query('delete from ' . $this->common->getOriginTable()->tableName . ' order by id desc limit ' . $num, 'execute');
                    if (is_file($route)) {
                        @unlink($route);
                    }
                    throw new ErrorException($e->errorMessage());
                }
            }

        }

        return $this->redirect()->toRoute('oa/default', array('controller' => 'origin', 'action' => 'index'));
    }


    //保存上传记录
    public function saveHistory($filename)
    {
        $history = new History();
        $history->filename = $filename;
        $history->uptime = date('Y-m-d H:i:s', time());
        $name = $_SESSION['Zend_Auth']->storage->name;
        $history->author = $name;
        $this->common->getHistoryTable()->saveAs($history);
    }

    public function delhistoryAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        if (!$id) {
            return $this->redirect()->toRoute('oa/default', array('controller' => 'excel', 'action' => 'index'));
        }
        $history = $this->common->getHistoryTable()->fetchOne(array('id' => $id));
        if ($history) {

            $dir = explode(' ', $history->uptime);
            $dir = $dir[0];
            $filename = $history->filename;
            //$filename = iconv('UTF-8','GB2312',$filename);
            $route = APP . '/data/uploads/' . $dir . '/' . $filename;

            if (is_file($route)) {
                @unlink($route);
                $this->common->getHistoryTable()->delete(array('id' => $id));
            }
        }
        return $this->redirect()->toRoute('oa/default', array('controller' => 'excel', 'action' => 'index'));
    }

    public function downloadAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->redirect()->toRoute('oa/default', array('controller' => 'excel', 'action' => 'index'));
        }
        $history = $this->common->getHistoryTable()->fetchOne(array('id' => $id));
        $filename = $history->filename;
        $dir = explode(' ', $history->uptime);
        $dir = $dir[0];
        $route = APP . '/data/uploads/' . $dir . '/' . $filename;

        $file = fopen($route, 'r');
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $filename . '"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        echo fread($file, filesize($route));
        fclose($file);
        die();
    }

//插入空记录
    public function emptyRecord($date)
    {
        //查询出除免责人员以外的所有用户信息
        $excludeEmployee = explode(',', EXCLUDE);
        $w = array();
        // $where = new Where();
        // $where->notIn('employeeId',$excludeEmployee);
        $notIn = new NotIn();
        $notIn->setIdentifier('employeeId')->setValueSet($excludeEmployee);
        array_push($w, $notIn);
        $userInfo = $this->common->getUserinfoTable()->fetchAll($w)->toArray();

        //上月最后一天
        $start = date('Y-m-01', strtotime($date));

        //当月总天数
        $days = date('t', strtotime($date));
        $emptyRecord = new Record();

        foreach ($userInfo as $key => $value) {

            $oWhere = array();
            array_push($oWhere, array('employeeId' => $value['employeeId'], 'signdate' => $date));
            $record = $this->common->getRecordTable()->fetchAll($oWhere)->toArray();
            if (!$record) {
                //生成一个月的空记录
                for ($i = 0; $i < $days; $i++) {
                    $emptyRecord->employeeId = $value['employeeId'];
                    $signdate = date('Y-m-d', strtotime("+$i day", strtotime($start)));
                    $emptyRecord->signdate = $signdate;
                    $emptyRecord->time1Type = 'em';
                    $emptyRecord->time2Type = 'em';
                    $emptyRecord->updateType1 = 'em';
                    $emptyRecord->updateType2 = 'em';
                    $daytype = date('w', strtotime($signdate));
                    if ($daytype == 0 || $daytype == 6) {
                        $emptyRecord->daytype = 2;
                    } else {
                        $emptyRecord->daytype = 1;
                    }
                    $emptyRecord->description = '';
                    $emptyRecord->name = $value['name'];
                    try {
                        $this->common->getRecordTable()->saveAs($emptyRecord);
                    } catch (\Exception $e) {
                        throw new ErrorException('员工号' . $value['employeeId'] . '生成考勤记录出错');
                    }

                }

            }
        }
    }
    //生成考勤记录
    public function generateRecord($infos, $userResult)
    {
        $w = array();
        //$where = new Where();
        $exclude = explode(',', EXCLUDE);
        $notIn = new NotIn();
        //$where->notIn('employeeId',$exclude);
        $notIn->setIdentifier('employeeId')->setValueSet($exclude);
        array_push($w, $notIn);
        $userinfoResult = $this->common->getUserinfoTable()->fetchAll($w)->toArray();
        $userinfoAttendId = $this->common->array_column($userinfoResult, null, 'attendId');

        foreach ($infos as $key => $value) {
            if (!isset($userinfoAttendId[$value[1]])) {
                throw new \Oa\Exception\ErrorException('考勤编号' . $value[1] . '找不到对应的员工号,请查看员工基本信息');
            }
            $employeeId = $userinfoAttendId[$value[1]]['employeeId'];
            $role = $userResult[$employeeId]['role'];
            //考勤时间转为时间戳进行比较
            $timestamp = strtotime($value[5]);
            //确定工作日还是周末
            $w = date('w', $timestamp);
            if ($w == 6) {
                $dayType = 2;//周六
            }elseif($w == 0){
                $dayType = 5;//周日
            }else{
                $dayType = 1;//工作日
            }
            if (($role == 1 || $role == 2 || $role == 5 || $role == 6) && $dayType == 1) {
                //普通员工管理员实习生工作日
                $typeDesc = $this->common->getTypeDesc(strtotime($value[5]), 1);
            } elseif (($role == 1 || $role == 2 || $role == 5 || $role == 6) && $dayType == 2) {
                //普通员工管理员周六
                $typeDesc = $this->common->getTypeDesc(strtotime($value[5]), 2);
            } elseif ($role == 3 && $dayType == 1) {
                //保洁工作日
                $typeDesc = $this->common->getTypeDesc(strtotime($value[5]), 3);
            } elseif ($role == 3 && $dayType == 2) {
                //保洁周末
                $typeDesc = $this->common->getTypeDesc(strtotime($value[5]), 4);
            } elseif ($role == 4) {
                //晚勤
                $typeDesc = $this->common->getTypeDesc(strtotime($value[5]), 5);
            } else {
                $typeDesc = '00';
            }
            $hour = explode(':', NIGHT);
            $hour = $hour[0];
            if ($role == 4) {
                //如果晚勤在上午的数据属于前一天上班
                $date = date('Y-m-d', $timestamp);
                if ($timestamp < strtotime($date . ' 12:0输入日期：￼
0:00')) {
                    $date = date('Y-m-d', strtotime('-1 day', $timestamp));
                }
            } else {
                $date = date('Y-m-d', strtotime("-" . $hour . ' hour', $timestamp));
            }
            $initDate = $date;
            $record = $this->common->getRecordTable()->fetchOne(array('employeeId' => $employeeId, 'signdate' => $date));
            if (!$record) {
                continue;
            }
            if ($role == 4) {

                if ($timestamp > strtotime('+ 12 hour', strtotime($initDate)) && $timestamp <= strtotime('+18 hour', strtotime($initDate))) {
                    //签到
                    if (empty($record->time1) || strtotime($record->time1) > $timestamp) {
                        //如果开始没有记录或者有记录但已有记录大于新值则进行更新
                        $record->time1 = $value[5];
                        $record->time1Type = $typeDesc;
                        $record->updateType1 = $typeDesc;
                    }

                } else {
                    //签退
                    if (empty($record->time2) || strtotime($record->time1) < $timestamp) {
                        //如果开始没有记录或者有记录但已有记录小于新值则进行更新
                        $record->time2 = $value[5];
                        $record->time2Type = $typeDesc;
                        $record->updateType2 = $typeDesc;
                    }
                }
            } else {
                //其它角色
                if ($timestamp > strtotime("+" . $hour . " hour", strtotime($initDate)) && $timestamp <= strtotime('+13 hour 30 minute', strtotime($initDate))) {
                    //签到
                    if (empty($record->time1) || strtotime($record->time1) > $timestamp) {
                        //如果开始没有记录或者有记录但已有记录大于新值则进行更新
                        $record->time1 = $value[5];
                        $record->time1Type = $typeDesc;
                        $record->updateType1 = $typeDesc;
                    }
                } else {
                    //签退
                    if (empty($record->time2) || strtotime($record->time1) < $timestamp) {
                        //如果开始没有记录或者有记录但已有记录小于新值则进行更新
                        $record->time2 = $value[5];
                        $record->time2Type = $typeDesc;
                        $record->updateType2 = $typeDesc;
                    }
                }
            }
            if ($w != 0) {
                if (empty($record->time1) && !empty($record->time2)) {
                    $record->time1Type = 'emp';
                    $record->updateType1 = 'emp';
                } elseif (!empty($record->time1) && empty($record->time2)) {
                    $record->time2Type = 'emp';
                    $record->updateType2 = 'emp';
                }
            } else {
                $record->updateType1 = 'sun';
                $record->updateType2 = 'sun';
            }
            $record->dayType = $dayType;

            try {
                $this->common->getRecordTable()->saveAs($record);
            } catch (\Exception $e) {
                throw new ErrorException('员工号' . $record->employeeId . '保存记录过程出错');
            }
        }

    }


}
