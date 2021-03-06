<?php
namespace controllers;
use core\Acl;
use core\Controller;
use models\Users as UsersModel;
use models\Departments as DepartmentModel;
use core\FlashMessages;
use models\Reports as ReportsModel;
use controllers\Main as Time;
use models\Holidays;
use core\Utils;

class Reports extends Controller {

    /**
    * Render page of reports by user or all users in current department
    * @return void
    */
    public function timeoffsAction() {
        if(!Acl::checkPermission('timeoffs_reports')){
            $this->render("errorAccess.tpl");
        }
        $timeoffs = array();
        $users = array();
        $reportAllDaysArray = array();
        $name = array();
        $totalDepInfo = array();

        $timeoffsAllUsers = array();
        $user = new UsersModel();
        $dep = new DepartmentModel();
        $date = date('m-Y');
        $id = '';
        if (isset($_GET['date']) && !empty($_GET['date'])){
            $date = $queryDate = $_GET['date'];
            $date = strtotime(strrev(strrev($date).'.10'));
            $date = date('Y-m', $date);
            if (isset($_GET['user_id']) && $_GET['user_id'] != 0 ){
                $reportAllDaysArray = $this->getMonthReport($_GET['user_id'], $date);
                $userInfo = $user->getInfo($_GET['user_id']);
                $name['user'] = $userInfo['name'];
                $id = $_GET['user_id'];
            }

            if (isset($_GET['dep_id']) && $_GET['dep_id'] != 0 ){
                $totalDepInfo['statuses'] = $user->getUserStatuses();
                $depInfo = $dep->getDepById($_GET['dep_id']);
                $name['dep'] = $depInfo['name'];
                $users = $dep->getUsers($_GET['dep_id']);
                foreach ($users as $currentUser) {
                    $totalUserStats[] = array(
                        'id' => $currentUser['id'],
                        'name' => $currentUser['name'],
                        'stats' => $this->totalSumReports($this->getMonthReport($currentUser['id'], $date))
                    );
                    $totalDepInfo['totalUserStats'] = $totalUserStats;
                    $totalDepInfo['date'] = $queryDate;
                }
            }
        }
        $allUsers = $user->getRegistered();
        $allDep = $dep->getMenuDepartments();
        $statuses = $user->getUserStatuses();
        $timeoffsAttr = array('date' => $date, 'name' => $name, 'id' => $id);
        $this->render("Reports/timeoffs_list.tpl" , array('statuses' => $statuses,
            'timeoffsAttr' => $timeoffsAttr,
            'allUsers' => $allUsers,
            'allDep'=>$allDep,
            'users'=>$users,
            'reportAllDaysArray' => $reportAllDaysArray,
            'name' => $name,
            'totalDepInfo' => $totalDepInfo));
    }

    /**
    * Render page of graph exits and entrances
    * @return void
    */
    public  function officeloadAction() {
        if(!Acl::checkPermission('officeload_reports')){
            $this->render("errorAccess.tpl");
        }
        $reportModel = new ReportsModel;

        if (isset($_GET['date'])) {
            $date = $_GET['date'];
        } else {
            $date = date('Y-m-d');
        }

        $desiredDate = $reportModel->getTimesList($date);
        $outDesireDate = $reportModel->getOutTimesList($date);

        $sortedTimes = array();
        $outSortedTimes = array();
        $stringForGraph = "";
        $outStringForGraph = "";
        foreach ($desiredDate as $hour) {
            $sortedTimes[$hour['hour']] = $hour['count'];
        }
        foreach ($outDesireDate as $hour) {
            $outSortedTimes[$hour['hour']] = $hour['count'];
        }
        for ($i = 0; $i <= 23; $i++) {
            if (isset($sortedTimes[$i])) {
                $entersCount = $sortedTimes[$i];
            } else {
                $entersCount = 0;
            }
            if(isset($outSortedTimes[$i])) {
                $outsCount = $outSortedTimes[$i];
            } else {
                $outsCount = 0;
            }
                
            $stringForGraph .= "[".$i.",".$entersCount."]".",";
            $outStringForGraph .= "[".$i.",".$outsCount."]".",";
        }
        $stringForGraph = "[".substr($stringForGraph, 0, -1)."]";
        $outStringForGraph = "[".substr($outStringForGraph, 0, -1)."]";
        $this->render("Reports/officeload.tpl", array('date' => $date,
                                                      'stringForGraph' => $stringForGraph, 'outStringForGraph'=> $outStringForGraph));
    }

    /**
     * Render page for download
     * @return void
     */
    public function downloadAction(){
        $user = new UsersModel();
        $dep = new DepartmentModel();
        $reports = array();
        if(isset($_GET['date'])){
            $date=$_GET['date'];
            if(isset($_GET['user_id'])){
                $userId=$_GET['user_id'];
                $infoUser=$user->getInfo($userId);
                $reports[]= array(
                    'reports' => $this->getMonthReport($userId, $date),
                    'name' => $infoUser['name']
                );
            }
            else if(isset($_GET['dep_id'])){
                $depId = $_GET['dep_id'];
                $users = $dep->getUsers($depId);
                $depName = $dep->getDepById($depId);
                foreach($users as $currentUser)
                $reports[] = array(
                    'reports' => $this->getMonthReport($currentUser['id'], $date),
                    'id' => $currentUser['id'],
                    'name' => $currentUser['name'],
                    'depName'=>$depName['name']
                );
            }
            $utils = new Utils();
            $utils->tabletoxls($reports);
        }
    }

    /**
    * Gets total time and count timeoffs type
    * @param array $report
    * @return array
    */
    public function totalSumReports($report){
        $user = new UsersModel();
        $total = array();
        $total['time'] = 0;
        $statuses = $user->getUserStatuses();
        foreach ($statuses as $status) {
            $total[$status['id']] = 0;
        }

        foreach ($report as $currentDay) {
            $total['time'] += $currentDay['time'];
            if ( isset($total[$currentDay['timeoffType']]) ){
                $total[$currentDay['timeoffType']] ++;
            }   
        }
        $hour = floor($total['time']/3600);
        $total['time'] = $total['time'] - $hour*3600;
        $min = floor($total['time']/60);
        $total['time'] = $hour.'ч '.$min.'м';
        return $total;
    }

    /**
    * Generates a report by user_id
    * @param integer $id
    * @param string $selectedDate
    * @param integer $timeoffType
    * @return array
    */
    public function getMonthReport($id, $selectedDate, $timeoffType = 0){
        $user = new UsersModel();
        $dep = new DepartmentModel();
        $monthTime = new Time();
        $holidays = new Holidays();

        $timeoffsArray = array();
        $userMonthTimeArray = array();
        $reportAllDaysArray = array();
        $vacation = array();
        $currVacation = array();

        $firstMonthDay = strtotime($selectedDate);
        $lastMonthDay = strtotime($selectedDate) + date("t", strtotime($selectedDate))*24*60*60 ;
        $vacation = $holidays->getAllDays($selectedDate);

        $timeoffs = $user->getTimeoffsById($id, $selectedDate, $timeoffType);
        foreach ($timeoffs as $timeOff) {
            $timeoffsArray[$timeOff['date']]['name'] = $timeOff['name'];
            $timeoffsArray[$timeOff['date']]['type'] = $timeOff['id'];
        }

        foreach ($vacation as $curr) {
            $currVacation[date('Y-m-d', strtotime($curr['date']))] = $curr;
        }

        $personalId = $user->getPersonalId($id);
        if ($personalId){
            $userMonthTime = $monthTime->getMonthInfo($personalId, $selectedDate);
            if (isset($userMonthTime['days'])){
                $userMonthTime = $userMonthTime['days'];
                $workDays = array_keys($userMonthTime);
                foreach ($workDays as $workDay) {
                    $userMonthTimeArray[$workDay]['time'] = $userMonthTime[$workDay]['sum'];
                }
            }
            for ($date = $firstMonthDay; $date < $lastMonthDay; $date += 86400) {
                $currentDate = date('Y-m-d', $date);
                $oneDay = array('date'=> $currentDate,
                    'dayName' => Utils::$daysFullNames[date("N", $date)-1],
                    'timeoffName' => '',
                    'time' => 0,
                    'timeoffType'=>0,
                    'dayType' => (int)$currVacation[$currentDate]['type']);
                if(isset($timeoffsArray[$currentDate])){
                    $oneDay['timeoffName'] = $timeoffsArray[$currentDate]['name'];
                    $oneDay['dayType'] = (int)$currVacation[$currentDate]['type'];
                    $oneDay['timeoffType'] = $timeoffsArray[$currentDate]['type'];
                }

                if(isset($userMonthTimeArray[$currentDate])){
                    $oneDay['timeoffName'] = '--';
                    $oneDay['time'] = $userMonthTimeArray[$currentDate]['time'];
                }
                    $reportAllDaysArray[$currentDate] = $oneDay;
            } 
        }
    return $reportAllDaysArray;
    }
}
