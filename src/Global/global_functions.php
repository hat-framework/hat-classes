<?php
    
    //if(!defined('DIR_BASIC')){exit('No direct file access allowed!');}
    function get_execution_time(){
        static $microtime_start = null;
        if($microtime_start === null){
            $microtime_start = microtime(true);
            return 0.0; 
        }    
        return microtime(true) - $microtime_start; 
    }
    
    function DefConstant($constant, $valor){
        if (!defined($constant)) 
            define($constant, $valor);
    }

    function Redirect($page, $time = 0, $args = "", $dados = array()){
        if(!is_numeric($time)) $time = 0;
        $after = "";
        if($args == "") $after = (is_admin)?"admin/":"";
        else $args = "&$args";
        
        if($page == "") SRedirect(URL, $time); //return;
        $amigavel  = (is_amigavel)?($args == "")?"":"index.php?url=":"index.php?url=";
        $url       = URL.$after.$amigavel.$page.$args.getSystemParams();
        SRedirect($url, $time, $dados);
        
    }

    //redirecionamento simples
    function SRedirect($url, $time = 0, $dados = array()){
        if(defined('AJAX_ENABLED') && AJAX_ENABLED == true){
            echo json_encode($dados);
        }
        elseif(isset($_REQUEST['ajax'])) {
            $dados['redirect'] = $url;
            echo json_encode($dados);
        }else{
            echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
                  <meta http-equiv='refresh' content='$time;URL=$url' />";
        }
        die();
    }

    function convertData($data){
        $data = explode(" ", $data);
        if($data[0] == '0000-00-00'|| $data[0] == '00/00/0000') return "";
        $data[0] = implode(
            preg_match("~\/~", $data[0]) == 0 ? "/" : "-", 
            array_reverse(
                explode(
                        preg_match("~\/~", $data[0]) == 0 ? "-" : "/", $data[0]
                )
            )
        );
        return implode(" ", $data);
    }
    
    function array_unshift_assoc(&$arr, $key, $val){
       $arr = array_reverse($arr, true); 
       $arr[$key] = $val; 
       $arr = array_reverse($arr, true); 
       return $arr;
    }
    
    /*
     * Verifica se os dados enviados estão sendo feitos pelo próprio site. 
     */
    function requestFromThisSite(){
        
        //se não tem um referer o usuário está no próprio site
        if(!isset($_SERVER['HTTP_REFERER'])) return true;
        
        
        //se o envio dos dados não partiu diretamente deste site, retorna false
        if(strpos($_SERVER['HTTP_REFERER'], URL) === false) return false;
        

        return true;
    }
    
    function safeUnset($key, &$array){
        if(empty($array)) return;
        if(!is_array($key)) $key = array($key);
        
        foreach($key as $k){
            if(!isset($array[$k])) continue;
            unset($array[$k]);
        }
    }
    
    /**
     * 
     * @param bool $hardcore Se ativado, bloqueia o acesso ao site caso não exista a 
     * @return string|boolean
     */
    function validaUrl(){
        if(isset($_REQUEST['Crypty_base64key']) && $_REQUEST['Crypty_base64key'] == Crypty_base64key)return true;
        $whitelist = array('https://www.facebook.com/','http://rec/importacao/index/reimport');
        
        //para inserir um dado 
        if(!isset($_SERVER['HTTP_REFERER'])) {
            if(isset($_SERVER['HTTP_USER_AGENT'])) return true;
            return 'Os dados só podem ser requisitados atravéz do site';
        }
        
        //se o envio dos dados não partiu diretamente deste site, retorna um erro
        if(strpos($_SERVER['HTTP_REFERER'], URL) === false){
            if(in_array($_SERVER['HTTP_REFERER'], $whitelist)) return true;
            return 'Você só pode enviar dados atravéz do próprio site!';
        }

        return true;
    }
    
    function getSimplePath($str){
        return str_replace(array("///", "//"), "/", str_replace(array("///", "//"), "/", "$str"));
    }
    
    function getTrueDir(&$diretorio){
        $dir       = str_replace(array('///','//', "/", "\\\\\\",  '\\\\', '\\'), DIRECTORY_SEPARATOR, $diretorio);
        $diretorio = str_replace(array('\/', '/\\'), DIRECTORY_SEPARATOR, $dir);
        return $diretorio;
    }
    
    function getTrueUrl(&$url){
        $dir = str_replace(array("\\\\\\",  '\\\\', '\\'), '/', $url);
        $url = str_replace(array('\/', '/\\'), '/', $dir);
        return $url;
    }
    
    function array_merge_recursive2($paArray1, $paArray2)
{
    if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
    foreach ($paArray2 AS $sKey2 => $sValue2)
    {
        $paArray1[$sKey2] = array_merge_recursive2(@$paArray1[$sKey2], $sValue2);
    }
    return $paArray1;
}

function sendEmailToWebmasters($assunto, $msg){
    $obj             = new \classes\Classes\Object();
    $mail            = $obj->LoadResource('email', 'mail');
    $msg            .= "<hr/>Horário: ". \classes\Classes\timeResource::getDbDate()."<br/>url: (http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']})";
    $emails          = $obj->LoadModel('usuario/login', 'uboj')->getWebmastersMail();
    if(empty($emails)){
        \classes\Utils\Log::save("system/mail/error", "Nenhum webmaster encontrado no método getWebmastersMail");
        return;
    }
    if(false == $mail->sendMail($assunto, $msg, $emails)){
        \classes\Utils\Log::save("system/mail/error", 
            "<div class='email_trouble' style='border:1px solid red;'>"
            ."<h2>$assunto</h2><div class='msg'><p>$msg</p></div></div><hr/>");
    }
}

function genericException($erro, $msg){
    echo "<div style='border 1px solid gray; color red;'>
            <span>Código de Erro: </span>
            <br/> $erro <br/><br/>
            <span>Mensagem:</span>
            <br/> $msg 
          </div> ";
    try{
        usuario_loginModel::user_action_log('exception', "erro:$erro  msg:$msg");
        \classes\Utils\Log::save("Sytem/Catastrophic", "$erro - $msg");
    }catch (Exception $ee){}
}

function getSystemParams(){
    $get = $_GET;
    static $append = ""; static $checked = false;
    if(!empty($get) && false === $checked){
        $checked = true;
        foreach($get as $name => $val){
            if(substr($name, 0,1) === "_"){
                $append = "&$name=$val";
            }
        }
    }
    return $append;
}