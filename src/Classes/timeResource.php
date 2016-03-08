<?php

namespace classes\Classes;
class timeResource{
    
    
    private static $holidays = array();
    /**
    * Retorna o dia da semana de uma data
    * 
    * @param string $d <p> A data de Retorno </p> 
    * @param string $type <p> type = tipo de ternorno: I = Inteiro, S = String, F = Full String </p>
    * <p>Se a data de retorno for vazia, então retorna o dia da semana atual</p>
    */
    public static function getWeedDay($d = "", $type = ''){
        $type = strtoupper($type);
        switch ($type){
            case 'I' : $type = "N"; break;
            case 'F' : $type = "l"; break;
            default:   $type = "D";
        }
        $d = ($d == "")?@date("Y-m-d H:i:s"):$d;
        return @date($type, self::Timestamp2Time($d));
    }
    
   /**
    * Calcula a distância entre os dias da semana
    * @param string $d1 <p> A data de início </p>
    * @param string $d2 <p> A data de término. Se for vazia será usada a data e hora atuais </p>
    * @return int Retorna o número de dias da semana de diferença entre duas datas
    * <p>Se a data de retorno for vazia, então retorna o dia da semana atual</p>
    * <p>Se a segunda data for menor do que a primeira então retorna um valor menor do que zero</p>
    * <p>Se a segunda data for maior do que a primeira então retorna um valor maior do que zero</p>
    * <p>Se as datas forem iguais, retorna zero</p> 
    */
    public static function diffWeekDay($d1, $d2 = "", $abs = false){
        $d1 = self::getWeedDay($d1, "i");
        $d2 = self::WeekDay2Int($d2);
        return $d2 - $d1;
    }
    
    /**
    * CAlcula quantos dias faltam para uma determinada data
    * @param string $d1 <p> A data de início </p>
    * @param string $d2 <p> A data de término. Se for vazia será usada a data e hora atuais </p>
    * @return int Retorna o número de dias da semana de diferença entre duas datas
    * <p>Retorna um valor sempre maior do que zero</p>
    * <p>Ex: absDiffWeekDay('2013-09-18', 'seg') = 5 - a primeira data é uma quarta feira</p>
    * <p>absDiffWeekDay('2013-09-18', 'qui') = 1</p>
    */
    public static function absDiffWeekDay($d1, $d2 = ""){
        $d1 = self::getWeedDay($d1, "i");
        $d2  = self::WeekDay2Int($d2);
        $sub = $d2 - $d1;
        if($sub >= 0) return $sub;
        return $sub + 7;
    }
    
    /**
    * Retorna um número intero representando o dia da semana 1 = seg, 7 = dom
    * @param string $d <p> A data de início </p>
    */
    public static function WeekDay2Int($d2){
        switch ($d2){
            case 'Mon': case 'seg': $d2 = 1; break;
            case 'Tue': case 'ter': $d2 = 2; break;
            case 'Wed': case 'qua': $d2 = 3; break;
            case 'Thu': case 'qui': $d2 = 4; break;
            case 'Fri': case 'sex': $d2 = 5; break;
            case 'Sat': case 'sab': $d2 = 6; break;
            case 'Sun': case 'dom': $d2 = 7; break;
            default : $d2 = self::getWeedDay($d2, "N");
        }
        return $d2;
    }
    
    
   /**
    * Calcula a diferenca entre a segunda data e a primeira,
    * 
    * @param string $d1 <p> A data de início </p>
    * @param string $d2 <p> A data de término. Se for vazia será usada a data e hora atuais </p>
    * @param string $type <p> type = tipo da diferenca: A = Ano, M = mes, D = dia, H = hora, Mi = minuto, '' = segundo  </p>
    * @param string $sep <p> separador de data, o padrão é '-', pode ser '/' </p>
    * @return int diferença entre as datas. 
    * <p>Se a segunda data for menor do que a primeira então retorna um valor menor do que zero</p>
    * <p>Se a segunda data for maior do que a primeira então retorna um valor maior do que zero</p>
    * <p>Se as datas forem iguais, retorna zero</p> 
    */
    public static function diffDate($d1, $d2 = "", $type='', $sep='-'){
        $d2 = ($d2 == "")?@date("Y-m-d H:i:s"):$d2;
        $datetime1 = new \DateTime($d1);
        $datetime2 = new \DateTime($d2);
        $interval = $datetime1->diff($datetime2, false);
        $signal   = ($interval->format("%r%a") < 0)?-1:1;
        switch ($type){
            case'A': return $signal * $interval->y;
            case'M': return $signal * ($interval->y*12+$interval->m);
            case'D': return $signal * $interval->days;
            
            case'H' : return $signal * ($interval->days * 24);
            case'Mi': return $signal * ($interval->days * 24*60);
        }
        return $signal * ($interval->days * 24*3600);
    }
    
    public static function getFormatedTimestampDiff($date, $frase_ini = "", $frase_encerra = ""){
        $str = $frase_ini;
        $intervalo = \classes\Classes\timeResource::diffDate($date);
        if ($intervalo > 0)
            $str = $frase_encerra;
        else {
            $intervalo = abs(\classes\Classes\timeResource::diffDate($date, "", "D"));
            if (abs($intervalo) > 2)
                $str .= "$intervalo Dias";
            else {
                $intervalo = abs(\classes\Classes\timeResource::diffDate($date, "", "H"));
                if (abs($intervalo) > 2)
                    $str .= "$intervalo Horas";
                else {
                    $intervalo = abs(\classes\Classes\timeResource::diffDate($date, "", "MI"));
                    if ($intervalo > 1)
                        $str .= " $intervalo Minutos";
                    else
                        $str = ($frase_encerra == "")?"Agora":$frase_encerra;
                }
            }
        }
        return $str;
    }
    
    public static function Timestamp2Time($dateTime){
        $dateTime = self::getDbDate(trim($dateTime));
        $var = explode(" ", $dateTime);
        if(count($var) < 2){
            if(strstr($dateTime, ':')){
                $time = $var[0];
                $date = @date("Y-m-d");
            }
            else {
                $date = $var[0];
                $time = "00:00:00";
            }
            
        }else{
            $date = $var[0];
            $time = $var[1];
        }
        if(trim($time) == "") $time = "00:00:00";
        //echo "( $date - $time )";
        $date = explode("-", $date);
        $time = explode(":", $time);
        return mktime(@$time[0], @$time[1], @$time[2], @$date[1], @$date[2], @$date[0]);
    }
    
    
    /**
    * Adiciona dias a data
    * 
    * @param string $date <p> Data Inicial, na qual os dias serão adicionados</p>
    * @param string $days <p> Número inteiro contendo o númeor de dias a ser adicionado</p>
    * @return int uma nova data, contendo os dias somados.
    */
    public static function addDayIntoDate($date, $days){
        $exp       = explode(' ', $date);
        $date      = str_replace(array("-", "/"), "", $exp[0]);
        $thisyear  = substr ( $date, 0, 4 );
        $thismonth = substr ( $date, 4, 2 );
        $thisday   = substr ( $date, 6, 2 );
        
        $hora = $min  = $seg = 0;
        if(isset($exp[1])){
            $time = str_replace(array(":", ""), "", $exp[1]);
            $hora = substr ($time, 0, 2 );
            $min  = substr ($time, 2, 2 );
            $seg  = substr ($time, 4, 2 );
            $nextdate  = mktime ($hora, $min, $seg, $thismonth, $thisday + $days, $thisyear );
            return strftime("%Y-%m-%d %H:%M:%S", $nextdate);
        }
        $nextdate  = mktime ($hora, $min, $seg, $thismonth, $thisday + $days, $thisyear );
        return strftime("%Y-%m-%d", $nextdate);
    }

    public static function subDayIntoDate($date,$days){
        $date = str_replace(array("-", "/"), "", $date);
         $thisyear = substr ( $date, 0, 4 );
         $thismonth = substr ( $date, 4, 2 );
         $thisday =  substr ( $date, 6, 2 );
         $nextdate = mktime ( 0, 0, 0, $thismonth, $thisday - $days, $thisyear );
         return strftime("%Y-%m-%d", $nextdate);
    }
    
    public static function isValidDate($dateTime){
        if($dateTime == "") return false;
        $matches = array();
        $dateTime = self::getDbDate($dateTime);
        $dt = explode(' ', $dateTime);
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $dt[0], $matches)) {
             if (!checkdate($matches[2], $matches[3], $matches[1])){return false;}
             return true;
        }
        return false;        
    }
    
    public static function isEmptyTime($date){
       $invalid = array("0000-00-00 00:00:00", "0000-00-00", "00/00/0000 00:00:00", "00/00/0000", "");
       return in_array($date, $invalid);
    }

    private static $br = array("Domingo", "Segunda", "Terça"  , "Quarta"   , "Quinta"  , "Sexta" , "Sábado"  , "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro" , "Outubro", "Novembro", "Dezembro"," de ");
    private static $en = array("Sunday" , "Monday" , "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "January", "February" , "March", "April", "May" , "June" , "July" , "August", "September", "October", "November", "December", " DE ");
    /**
     * Transforma uma data em uma string em português
     * @param date $dateTime data hora a ser exibida
     * @param bool $show_time Exibir hora
     * @return string
     */
    public static function Date2StrBr($dateTime, $show_time = true, $full_date = false){
        
        if($dateTime == "") {
            $dateTime = timeResource::getDbDate();
        }

        if(false === $full_date){
            //diferenca dentro de uma hora
            $minutos = \classes\Classes\timeResource::diffDate($dateTime, "", "Mi");
            if(abs($minutos) < 60) {return self::minutos($dateTime, $minutos, \classes\Classes\timeResource::$en, \classes\Classes\timeResource::$br);}

            //diferenca dentro de um dia
            $horas = \classes\Classes\timeResource::diffDate($dateTime, "", "H");
            if(abs($horas) < 24) {return self::horas($dateTime, $horas, \classes\Classes\timeResource::$en, \classes\Classes\timeResource::$br);}

            //se está no mesmo ano ou se existe uma diferença de no maximo 2 dias
            $dias = \classes\Classes\timeResource::diffDate($dateTime, "", "D");
            if(@date("Y", \classes\Classes\timeResource::Timestamp2Time($dateTime)) == @date("Y") || abs($dias) <= 7){
                 return self::dias($dateTime, $dias, \classes\Classes\timeResource::$en, \classes\Classes\timeResource::$br, $show_time);
            }
        }
        
        //diferenca dentro de mais de um ano
        $anos = \classes\Classes\timeResource::diffDate($dateTime, "", "Y");
        return self::anos($dateTime, $anos, \classes\Classes\timeResource::$en, \classes\Classes\timeResource::$br, $show_time);
    }
    
        private static function anos($dateTime, $anos, $en, $br, $show_time = true){
            $stime = ($show_time)?" \à\s H:i:s":"";
            $dateTime = \classes\Classes\timeResource::Timestamp2Time($dateTime);
            return str_replace($en, $br, @date("l\, d \DE\ F \DE\ Y{$stime}", $dateTime));
        } 
        
        private static function dias($dateTime, $dias, $en, $br, $show_time = true){
            $dateTime = \classes\Classes\timeResource::Timestamp2Time($dateTime);
            $stime = ($show_time)?", às ". @date("H:i:s", $dateTime):"";
            switch ($dias){
                case -2: $str = "Depois de amanhã $stime"; break;
                case -1: $str = "Amanhã $stime";           break;
                case  1: $str = "Ontem $stime";            break;
                case  2: $str = "Anteontem $stime";        break;
                default: 
                    $stime = ($show_time)?"\à\s H:i:s":"";
                    if(abs($dias) <= 7) $str = str_replace($en, $br, @date("l\, d \DE\ F $stime", $dateTime));
                    else                $str = str_replace($en, $br, @date("d \DE\ F $stime", $dateTime));
            }
            return $str;
        }
        
        private static function minutos($dateTime, $minutos, $en, $br){
            switch ($minutos){
                case -1: $str = "Dentro de um minuto"; break;
                case  1: $str = "Há um minuto";        break;
                case  0: $str = "Agora";               break;
                default: $str = ($minutos < 0)?"Dentro de ".abs($minutos)." minutos": "Há $minutos minutos";
            }
            return $str;
        }
        
        private static function horas($dateTime, $horas, $en, $br){
            switch ($horas){
                case -1: $str = "Dentro de uma Hora";   break;
                case  1: $str = "Há uma Hora";          break;
                case  0: $str = "Há menos de uma Hora"; break;
                default: $str = ($horas < 0)?"Dentro de ".abs($horas)." Horas":"Há $horas Horas";
            }
            return $str;
        }
    
    public static function getFormatedDate($date = ""){
        if($date == "")$date = @date("Y-m-d H:i:s");
        return (self::detectDateType($date) == 'br')?$date:self::convert($date);
    }
    
    public static function getDbDate($date = "", $patthern = "Y-m-d H:i:s"){
        if($date == "") return @date($patthern);
        return (self::detectDateType($date) == 'db')?$date:self::convert($date);
    }
    
    public static function getLastMonthDate($date = "", $usefullDate = false){
        if($date == ""){$date = self::getDbDate("", "Y-m-d");}
        $dt = date("Y-m-t", strtotime($date));
        return (!$usefullDate)?$dt:self::getLastUsefullDateOfMonth($dt);
    }
    
    public static function getFirstMonthDate($date = ""){
        if($date == ""){$date = self::getDbDate("", "Y-m-01");}
        return date("Y-m-01", strtotime($date));
    }
    
    public static function getLastDateOfNextMonth($date = "", $usefullDate = false){
        if($date == ""){$date = self::getDbDate("", "Y-m-d");}
        $e = explode('-', $date);
        if($e[1] == 12){$e[0]++; $e[1] = '01';}
        else{$e[1]++;}
        $result = self::getLastMonthDate("{$e[0]}-{$e[1]}-15");
        return (!$usefullDate)?$result:self::getLastUsefullDateOfMonth($result);
    }
    
    public static function getLastUsefullDateOfMonth($date){
        while(self::isWeekend($date)){
            $date = self::subDateTime($date);
        }
        return $date;
    }
    
    /**
    * Converte uma data ou uma datetime do padrão brasileiro para o americano
    * e vice e versa
    * 
    * @param string $date <p> Data a ser convertida</p>
    * @return string A data convertida
    */
    public static function convert($date){
        $exp  = explode(' ', $date);
        $date = array_shift($exp);
        $time = array_shift($exp);
        return convertData($date) . " $time";
    }
    
   /**
    * Detecta o tipo da data
    * 
    * @param string $date <p> Data a ser detectada o tipo</p>
    * @return string <b>db</b> se o formato for do banco de dados <br/> 
    * <b>br</b> se o formato for brasileiro
    * <b>none</b> se o formato não for detectado
    */
    public static function detectDateType($date){
        if(strstr($date, "/") !== false) {return 'br'; }
        if(strstr($date, "-") !== false) {return 'db';}
        return 'none';
    }


    /**
     * Extrai o horário de uma data
     * @param datetime $date
     * @return time
     */
    public static function getTimeOfDate($date = ""){
        return ($date == "")? @date("H:i:s"):@date("H:i:s", \classes\Classes\timeResource::Timestamp2Time($date));
    }
    
    private static $unidade = array('day', 'week', 'month', 'year', 'hour', 'minute', 'second');
    /**
     * Subtrai qtd unidades da data
     * @param type $date data aonde será subraída as unidades
     * @param type $qtd número de unidades a serem subraídas 
     * @param type $unidade tipo de unidade, pode ser {'day', 'week', 'month', 'year', 'hour', 'minute', 'second'}
     * @return datetime a nova data
     */
    public static function subDateTime($date, $qtd = 1, $unidade = 'day'){
        if($date === ""){$date = self::getDbDate();}
        $format = self::getDateFormat($date);
        if(!in_array($unidade, \classes\Classes\timeResource::$unidade)){
            die("Erro ao subtrair string, parâmetro unidade inválido");
        }
        $date = date_create($date);
        date_sub($date, date_interval_create_from_date_string("$qtd $unidade"));
        return date_format($date, $format);
    }
    
    public static function getDateFormat($date){
        return (strstr($date, ":") === false)?"Y-m-d":"Y-m-d H:i:s";
    }


    /**
     * Adiciona qtd unidades a data
     * @param type $date data aonde será adicionada as unidades
     * @param type $qtd número de unidades a serem adicionadas 
     * @param type $unidade tipo de unidade, pode ser {'day', 'week', 'month', 'year', 'hour', 'minute', 'second'}
     * @return datetime a nova data
     */
    public static function addDateTime($date, $qtd = 1, $unidade = 'day'){
        $format = self::getDateFormat($date);
        if(!in_array($unidade, \classes\Classes\timeResource::$unidade)){
            die("Erro ao subtrair string, parâmetro unidade inválido");
        }
        $date = date_create($date);
        date_add($date, date_interval_create_from_date_string("$qtd $unidade"));
        return date_format($date, $format);
    }
    
    /**
     * Sum $NDias in current Date but ignore Weekends
     * @param int $NDias
     * @return date
     */
    public static function getNextNonWeekendDay($date, $NDias = 1) {
            $DataAct = @date($date);
            $d = new \DateTime( $DataAct );
            $t = $d->getTimestamp();

            // loop for X days
            for($i=0; $i<$NDias; $i++){

                // add 1 day to timestamp
                $addDay = 86400;

                // get what day it is next day
                $nextDay = @date('w', ($t+$addDay));

                // if it's Saturday or Sunday get $i-1
                if($nextDay == 0 || $nextDay == 6) {
                    $i--;
                }

                // modify timestamp, add 1 day
                $t = $t+$addDay;
            }

            $d->setTimestamp($t);

            return $d->format( 'Y-m-d' );
        }
        
    public static function isUsefullDay($date){
        return (!self::isWeekend($date) && !self::isHoliday($date));
    }

    public static function isWeekend($date) {
        $dt = self::getDbDate($date, "Y-m-d");
        $timestamp = strtotime($dt);
        if($timestamp == 0){return false;}
        $weekDay = date('w', $timestamp);
        return ($weekDay == 0 || $weekDay == 6);
    }

    public static function isHoliday($date){
        $dt = self::getDbDate($date, "Y-m-d");
        $e = explode("-", $dt);
        $holidays = self::getHolidays($e[0]);
        return in_array($dt, $holidays);
    }
    
            private static function getHolidays($ano){
                if(isset(self::$holidays[$ano])){return self::$holidays[$ano];}
                $dia                    = 86400;
                $datas                  = array();
                $datas['pascoa']        = easter_date($ano);
                $datas['sexta_santa']   = $datas['pascoa'] - (2 * $dia);
                $datas['carnaval']      = $datas['pascoa'] - (47 * $dia);
                $datas['carnaval2']     = $datas['pascoa'] - (46 * $dia);
                $datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);
                $feriados = array (
                    "$ano-01-01",
                    date('Y-m-d',$datas['carnaval']),
                    date('Y-m-d',$datas['carnaval2']),
                    date('Y-m-d',$datas['sexta_santa']),
                    date('Y-m-d',$datas['pascoa']),
                    "$ano-04-21",
                    "$ano-05-01",
                    "$ano-09-07",
                    date('Y-m-d',$datas['corpus_cristi']),
                    "$ano-10-12",
                    "$ano-11-02",
                    "$ano-11-15",
                    "$ano-12-25",
                );
                self::$holidays[$ano] = $feriados;
                return $feriados;
            }
    
}