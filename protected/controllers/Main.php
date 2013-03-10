<?php
namespace controllers;

use core\Controller;
use models\Users as UsersModel;
use core\Registry;
use core\DateTime;

class Main extends Controller
{
    const IN_OFFICE = 2;
    const OUT_OFFICE = 1;

    public function indexAction() {
        $userInfo = Registry::getValue('user');
        $userId = $userInfo['id'];
        $date = $this->getDate();

        $dayInfo = array();
        $weekInfo = $this->getWeekInfo($userId, $date);
        $monthInfo = $this->getMonthInfo($userId, $date);

        if (isset($weekInfo['days'][$date])) {
            $dayInfo = $weekInfo['days'][$date];
        }



        $this->render("Main/index.tpl", array(
            'currentDate' => date('Y-m-d', time()),
            'date' => $date,
            'weekDays' => DateTime::getWeekDays($date),
            'day' => $dayInfo,
            'week' => $weekInfo,
            'month' => $monthInfo,
        ));
    }

    private function getWeekInfo($userId, $date) {
        $ut = strtotime($date);
        $uWeekFirstDay = DateTime::getWeekFirstDay($ut);
        $uOffsetDay = $uWeekFirstDay + 7 * 24 * 60 * 60;

        $usersModel = new UsersModel();
        $weekActions = $usersModel->getActions($userId, $uWeekFirstDay, $uOffsetDay);
        $weekPeriods = $this->formPeriods($weekActions, date('Y-m-d', $uOffsetDay));
        return $weekPeriods;
    }

    private function getMonthInfo($userId, $date) {
        $uTime = strtotime($date);
        $uMonthFirstDay = strtotime('first day of this month', $uTime);
        $uOffsetDay = strtotime('last day of this month', $uTime) + 24 * 60 * 60;

        $usersModel = new UsersModel();
        $monthActions = $usersModel->getActions($userId, $uMonthFirstDay, date('Y-m-d', $uOffsetDay));
        $monthPeriods = $this->formPeriods($monthActions, $uOffsetDay);

        return $monthPeriods;
    }

    function formPeriods(array $actions, $offsetDate = null) {
        $daysPeriods = array();
        $daysPeriods['total_sum'] = 0;
        $setTimer = false;
        $currentDate = date('Y-m-d', time());
        $previousDate = date('Y-m-d', strtotime('now - 1 day'));

        $actsCount = count($actions);
        for ($i = 0; $i < $actsCount; $i++) {
            $diff = 0;
            $enterTime = null;
            $exitTime = null;
            $day = $actions[$i]['day'];

            if (!isset($daysPeriods['days'][$day])) $daysPeriods['days'][$day] = array();
            if (!isset($daysPeriods['days'][$day]['sum'])) $daysPeriods['days'][$day]['sum'] = 0;
            if (!isset($daysPeriods['days'][$day]['periods'])) $daysPeriods['days'][$day]['periods'] = array();

            //нам нужно выделить пары вход + выход, чтобы добавить их разницу к тому, сколько человек провёл в офисе
            if ($actions[$i]['direction'] == self::IN_OFFICE
                && $day != $offsetDate
                && isset($actions[$i+1])
                && $actions[$i+1]['direction'] == self::OUT_OFFICE){

                $enterTime = strtotime($actions[$i]['logtime']);
                $exitTime = strtotime($actions[$i+1]['logtime']);
                $diff = $exitTime - $enterTime;

                $daysPeriods['days'][$day]['periods'][] = array(
                    'enter' => $enterTime,
                    'exit' => $exitTime,
                    'diff' => $diff,
                    'setTimer' => false,
                );

                $i++;
            } else {
                $actionTime = strtotime($actions[$i]['logtime']);

                if ($actions[$i]['direction'] == self::IN_OFFICE) {
                    $enterTime = $actionTime;

                    if (!isset($actions[$i+1]) && ($day == $currentDate  || $day == $previousDate)) {
                        $setTimer = true;
                        $exitTime = time();
                        $diff = $exitTime - $enterTime;
                    }
                } else $exitTime = $actionTime;

                $daysPeriods['days'][$day]['periods'][] = array(
                    'enter' => $enterTime,
                    'exit' => $exitTime,
                    'diff' => $diff,
                    'setTimer' => $setTimer,
                );
            }

            $daysPeriods['days'][$day]['setTimer'] = $setTimer;
            $daysPeriods['days'][$day]['sum'] += $diff;
            $daysPeriods['total_sum'] += $diff;
        }

        return $daysPeriods;
    }

    //Возвращает дату либо из $_GET, либо текущую
    public function getDate() {
        if (!empty($_GET['date'])) {
            $unixtime = strtotime($_GET['date']);
            $date = date('Y-m-d', $unixtime);
        } else $date = date('Y-m-d');

        return $date;
    }
}