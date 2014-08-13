<?php

function returnConstants ($prefix) {
    foreach (get_defined_constants() as $key=>$value) 
        if (substr($key,0,strlen($prefix))==$prefix)  $dump[$key] = $value; 
    if(empty($dump)) { return "Error: No Constants found with prefix '".$prefix."'"; }
    else { return $dump; }
}

function getConstantValue($constName){
    $val = @constant($constName);
    return ($val === null)?"":$val;
}

function getBoleanConstant($constname, $const_value = ""){
    $val = @constant($constname);
    if(in_array($val, array('true', true, '1', 1))){return true;}
    if(in_array($val, array('false', false, NULL, "0", 0))){return false;}
    if($const_value !== "" && $val !== $const_value){return false;}
    return true;
}