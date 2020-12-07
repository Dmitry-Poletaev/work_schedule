<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use DatePeriod;
use DateInterval;
use Illuminate\Support\Arr;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'workers';

    public function getSchedule($startDate, $endDate, $id)
    {
        //получаем интервал дат
        $dates = $this->intervalDate($startDate, $endDate);
        //удаляем выходные
        $result = $this->isWeekend($startDate, $endDate, $dates);
        //удаляем даты отпуска
        $result = $this->isVacation($result, $id);
        //удаляем праздники
        $result = $this->isHoliday($result);
        //получаем итоговый json-ответ
        $result = $this->makeJson($result, $id);
        dd($result);


    }

    public function  intervalDate($startDate, $endDate)
    {
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);
        $end = $end->modify( '+1 day' );

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval ,$end);
        
        $arr = [];
        foreach($daterange as $date){
            $date = $date->format("Y-m-d");
            array_push($arr, $date);
            
        }

        return $arr;
    }

    public function isVacation($arr,$id)
    {
        //получаем даты отпуска
        $vacationFirst = Schedule::findOrFail($id)
                    ->where('id', $id)
                    ->select('first_vacation_start', 'first_vacation_end')
                    ->first();

        $vacationSecond = Schedule::findOrFail($id)
                    ->where('id', $id)
                    ->select('second_vacation_start', 'second_vacation_end')
                    ->first();

        //получаем массивы с датами отпуска
        $vacationFirst = $this->intervalDate($vacationFirst->first_vacation_start, $vacationFirst->first_vacation_end);

        if (!empty($vacationSecond)) {
            $vacationSecond = $this->intervalDate($vacationSecond->second_vacation_start, $vacationSecond->second_vacation_end);
        }

        $vacation = array_merge($vacationFirst, $vacationSecond);
        //получаем результат без отпуска
        return array_diff($arr, $vacation);;


    }

    public function isHoliday($arr)
    {
        $holidays = $this->getHolidays();
        //убираем праздники
        return array_diff($arr, $holidays);
    }

    public function isWorkParty($arr)
    {   //время корпоратива
        $format = 'Y-m-d';
        $party = DateTime::createFromFormat($format, '2020-01-10');
        $party = $party->format('Y-m-d');
        //убираем время корпоратива из расписания
        if (array_key_exists($party, $arr)) {
            unset($arr[$party]['timeRanges'][1]);
        }
        return $arr;

    }

    public function isWeekend($startDate, $endDate, $arr)
    {
        $begin  = strtotime($startDate);
        $end = strtotime($endDate);
        $result = [];
        //получаем массив с выходными
        while ($begin <= $end)
        {
            if (date('N', $begin) >= 6)
            {
                $current = date('Y-m-d', $begin);
                $result[] = $current;
            } $begin += 86400;
        }
        
        return array_diff($arr, $result);
    }

    public function getHolidays()
    {
        //получаем даты всех праздников РФ
        $allDates = json_decode(file_get_contents('https://www.googleapis.com/calendar/v3/calendars/russian__ru%40holiday.calendar.google.com/events?key=AIzaSyB0oJnMuGDjgMAbpEzCbT12K3mXRt2Nh2U'), 2);
        $holidayDate = Arr::pluck($allDates['items'], 'start.date');
        $count = count($holidayDate);

        $dateName = [];
        //создаем массив с датами праздников
        for ($i = 0; $i < $count; $i++)
        {
            $dateName[] =  $holidayDate[$i];
        }
        return $dateName;
    }

    public function makeJson($arr, $id)
    {   // получаем рабочее время
        $timeFirst = Schedule::findOrFail($id)
                ->where('id', $id)
                ->select('first_work_schedule_start', 'first_work_schedule_end')
                ->first();
        
        $timeSecond = Schedule::findOrFail($id)
                ->where('id', $id)
                ->select('second_work_schedule_start', 'second_work_schedule_end')
                ->first();
        
        $timeRange = [
            'timeRanges' => [
                [
                    'start:' => $timeFirst->first_work_schedule_start,
                    'end:' => $timeFirst->first_work_schedule_end,
                ],
                [
                    'start:' => $timeSecond->second_work_schedule_start,
                    'end:' => $timeSecond->second_work_schedule_end,
                ],
            ]
        ];
        
        //создаем массив для json
        $newArr = [];
        foreach($arr as $key => $value){
            $newArr['day '. ( $key + 1 ) . ':'] = $value;
        }
        
        $data =  array_fill_keys($newArr, $timeRange);

        $result = $this->isWorkParty($data);
        $result = ['schedule' => $result];


        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
