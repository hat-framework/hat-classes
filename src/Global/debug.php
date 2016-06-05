<?php

function debugRun(){
    static $i = 0;
    echoEspecial("$i", true);
    $i++;
}

function echoLine($array){
    $str = '';
    foreach($array as $arr){
        $str.= ' - ' . $arr;
    }
    echo $str.'<br>';
}

function debugWords($words, $array = array()){
    static $style = "<style>.temp{margin-right: 20px; width:220px; overflow:auto; float:left;}</style>";
    echo $style;
    $style = "";

    foreach($words as $w){
        echo "<div class='temp'>$w</div>";
    }
    if(!empty ($array)){
        echo "<br/>";  
        print_r($arr); 
        echo "<br/>";   
    }
    echo "<br/>";
}

function debugarray($arr, $concat = "", &$last = false, $print = true){
    $out = "";
    if(!is_array($arr)) {
        if(!is_object($arr)){
            $out .= "($arr)";
            $last = true; 
            return $out;
        }else $arr = (array)$arr;
    }
    $concat .= "&nbsp&nbsp&nbsp";
    foreach($arr as $name => $a){
        $out .= "<br/><br/>$concat$name";
        $out .= debugarray($a, $concat, $last, false);
        if(!$last) $out .= "<br/>";
        $last = false;
    }
    if($print) echo $out;
    return $out;
}

function echoBr($str){
    if(!is_array($str)){
        echo "($str)<br>\n";
        return;
    }
    foreach($str as $k => $s){
        if(!is_array($s)){
            echo "($k - $s)<br>\n";
        }else{
            echo "<h5>$k</h5>";
            return echoBr($s);
        }
    }
}

function echoCount(){
    static $i = 0;
    $i++;
    echo "<br/>($i)<br/>";
}

function echoEspecial($str = "", $line = false){
    static $position = 0;
    if($str == "") {
        echo "<hr/>";
        return;
    }
    $line = ($line)?"<br/>":" ";
    $avaible = array('0' => array('[',']'), '1' => array('{','}'), '2' => array('<','>'), '3' => array('(',')'));
    if(!array_key_exists($position, $avaible)) $position = 0;
    echo $avaible[$position][0] ." $str ".$avaible[$position][1]. $line;
    $position++;
}

function print_rr($array){
    if(!is_array($array)) echoEspecial ($array);
    foreach($array as $key => $arr){
        echo "<br/>$key - ";
        print_r($arr);
    }
    echo "<hr/>";
}
function print_rrd($array){
    print_rr($array); die();
}

function print_rd($array){
    print_r($array);die();
}

function print_rh($array){
    print_r($array);
    if(isset($_REQUEST['ajax']) && in_array($_REQUEST['ajax'], array(true,'true',1,'1')))echo("\n---------------------------------\n");
    else{echo("<hr/>");}
}

function print_rrh($array){
    print_rr($array);
    if(isset($_REQUEST['ajax']) && in_array($_REQUEST['ajax'], array(true,'true',1,'1')))echo("\n---------------------------------\n");
    else{echo("<hr/>");}
}


function print_rpretty($array, $last_level_print_r = false){
    if(!is_array($array)){echoBr($array); return;}
    echo "<div style='padding:5px; margin:5px; border: 1px solid #b6b6b6;'>";
    foreach($array as $key => $arr){
        if(!is_numeric($key) && trim($key) != ""){echo "<b>$key</b>";}
        if(is_array($arr)){
            $is_array = false;
            foreach($arr as $k => $v){
                if(is_array($v)){
                    $is_array = true;
                    break;
                }
            }
            if(!$is_array){
                if(!$last_level_print_r){
                    echo "<table style='margin-bottom:5px; border-bottom:1px solid #ccc;'>";
                    foreach($arr as $k => $v){
                        echo "<tr>
                            <td><b>$k:</b></td>
                            <td>$v</td>
                        </tr>";
                    }
                    echo "</table>";
                }else{print_r($last_level_print_r);}
            }
            else{print_rpretty($arr);}
        }else{
            echo ": $arr";
        }
    }
    echo "</div>";
}


function print_rhd($array){
    print_rh($array); die();
}

function print_in_table($array){
    if(!is_array($array)) echoEspecial ($array);
    echo "<hr/> Número de colunas: (" . count($array).")<br/>";
    $obj = new classes\Classes\Object();
    $obj->LoadResource('html/table', 'tb')->draw($array);
    echo "<hr/>";
}

function printWebmasters($function, $array){
    if(!usuario_loginModel::IsWebmaster() || !function_exists($function)) {return;}
    echo "<div style='border:1px solid #ccc; margin:5px; padding:5px;'>";
    echo "<h2>Debug visível só para webmasters</h2>";
    $function($array);
    echo "</div>";
}

function debugWebmaster($str, $die = false){
    if(!usuario_loginModel::IsWebmaster()) {return;}
    is_array($str)?  debugarray($str):echoBr($str);
    if($die){die("<hr/>");}
}