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
    echo "($str)<br>\n";
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

function debugWebmaster($str){
    if(!usuario_loginModel::IsWebmaster()) return;
    is_array($str)?  debugarray($str):echoBr($str);
}