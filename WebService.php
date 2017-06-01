<?php

include_once './common.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebService
 *
 * @author softlock
 */
class WebService {
    
    public static function convertDate($request, $input)
    {
        $type = safeGetString($request[2], null);
        $year = safeGetInt($request[3], null);
        $month = safeGetInt($request[4], null);
                
        if($type===null || $year===null || $month===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
        
        $day = safeGetInt($request[5], null);
        
        if($day===null) {
            $day = $fromDay = 1;
            $toDay = 31;
        } else {
            $fromDay = $day;
            $toDay = $day;
        }
        
        $hgDate = new HGDate();
        $result = false;
        if($type == "hijri")
        {
            $result = $hgDate->setHijri($year, $month, $day);
        }
        else if($type == "gregorian")
        {
            $result = $hgDate->setGregorian($year, $month, $day);
        }
        
        if($result===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

                
        $dates = array();
        for ($day = $fromDay; $day<=$toDay; $day++) {
            
            $date = new stdClass();
            
            if($type == "hijri") {
                
                if($hgDate->setHijri($year, $month, $day)!==false) {
                    
                    $date->hyear = $year;
                    $date->hmonth = $month;
                    $date->hday = $day;
                    
                    $hgDate->toGregorian();
                    
                    $date->gyear = $hgDate->getYear();
                    $date->gmonth = $hgDate->getMonth();
                    $date->gday = $hgDate->getDay();
                    
                } else {
                    break;
                }
                
            } else {
                
                if($hgDate->setGregorian($year, $month, $day)!==false) {
                    
                    $date->gyear = $year;
                    $date->gmonth = $month;
                    $date->gday = $day;
                    
                    $hgDate->toHijri();
                    
                    $date->hyear = $hgDate->getYear();
                    $date->hmonth = $hgDate->getMonth();
                    $date->hday = $hgDate->getDay();
                    
                } else {
                    break;
                }
            }
            
            array_push($dates, $date);
        }

        return json_encode($dates, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function getPrayerTimes($request, $input)
    {
        $year = safeGetInt($request[2], null);
        $month = safeGetInt($request[3], null);
                
        if($year===null || $month===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));
        
        $day = safeGetInt($request[4], null);
        
        $latitude = safeGetDouble($input["latitude"], null);
        $longitude = safeGetDouble($input["longitude"], null);

        if($latitude===null || $longitude===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $timeZone = safeGetInt($input["timezone"], 0);
        $summmerTimeEnabled = safeGetInt($input["summmertimeenabled"], 0);
        $mazhab = safeGetInt($input["mazhab"], 0);
        $way = safeGetInt($input["way"], 0);
        
        if($day===null) {
            $day = $fromDay = 1;
            $toDay = 31;
        } else {
            $fromDay = $day;
            $toDay = $day;
        }
        
        $hgDate = new HGDate();
        $result = $hgDate->setGregorian($year, $month, $day);
        if($result===false)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

                
        $dates = array();
        for ($day = $fromDay; $day<=$toDay; $day++) {
            
            $date = new stdClass();
            
            if($hgDate->setGregorian($year, $month, $day)!==false) {
                
                $prayerTimes = new PrayerTimes($hgDate->getDay(), $hgDate->getMonth(), $hgDate->getYear(), $latitude, $longitude, $timeZone, $summmerTimeEnabled, $mazhab, $way);
                
                $times = $prayerTimes->get();
                
                $date->year = $hgDate->getYear();
                $date->month = $hgDate->getMonth();
                $date->day = $hgDate->getDay();
                $date->times = $times;

            } else {
                break;
            }
            
            array_push($dates, $date);
        }

        return json_encode($dates, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    
    public static function calcualteQipla($request, $input)
    {
        $latitude = safeGetDouble($input["latitude"], null);
        $longitude = safeGetDouble($input["longitude"], null);

        if($latitude===null || $longitude===null)
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $angle = PrayerTimes::calculateAngle($latitude, $longitude);

        return json_encode($angle, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public static function processCommand($method, $request, $input)
    {
        if(count($request)<2 || strtolower($request[0])!="api")
            return json_encode(array("response"=>0, "reason"=>MSG_INVALID_REQUEST_FORMAT));

        $command = strtolower($request[1]);
        
        if($command=="convertdate")
        {
            return WebService::convertDate($request, $input);
        }
        else if($command=="getprayertimes")
        {
            return WebService::getPrayerTimes($request, $input);
        }
        else if($command=="calcualteqipla")
        {
            return WebService::calcualteQipla($request, $input);
        }      
    }
}
