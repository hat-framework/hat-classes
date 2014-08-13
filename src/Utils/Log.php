<?php

namespace classes\Utils;
class Log{
    
    public static $file_dir = DIR_LOG;

    public static function backTraceLog($cache_name){
        ob_start();
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $str = ob_get_contents();
        ob_end_clean();
        return self::save($cache_name, $str);
    }
    
    /**
     * salva em cachename o conteúdo
     * @param type $cache_name
     * @param type $conteudo
     * @param string $format (json, noDate, simple)
     * @return boolean
     */
    public static function save($cache_name, $conteudo, $format = 'simple') {
        if(is_array($conteudo)){
            $bool = false;
            $conteudo = debugarray($conteudo, "", $bool , false);
        }
        if(strstr($conteudo, 'inici')){ $conteudo = "<hr/>$conteudo";}
        if(strstr($conteudo, 'concl')){ $conteudo = "$conteudo<hr/>";}
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        $date = \classes\Classes\timeResource::getDbDate('');
        if($format == 'json'){ $conteudo = "{data:'$date', conteudo:'$conteudo'}";}
        if($format != 'noDate'){ $conteudo = "$date - $conteudo \n";}
        $conteudo = $obj->prepareContent($conteudo);
        if(!$obj->exists($cache_name)){
            return $obj->create($cache_name, $conteudo);
        }
        
        $file = $obj->getFileName($cache_name);
        $res  = $obj->LoadResource('files/file');
        if($res->append($file, $conteudo) === false){
            $obj->setError('Erro ao salvar arquivo de log - ' .$res->getErrorMessage());
            return FALSE;
        }
        return TRUE;
    }
    
    public static function getError(){
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->getError();
    }
    
    public static function get($cache_name) {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->get($cache_name);
    }
    
    public static function delete($cache_name) {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->delete($cache_name);
    }
    
    public static function deleteFolder($folder_name) {
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->deleteFolder($folder_name);
    }
    
    public static function exists($cache_name){
        $obj = fileSaver::LoadFileSaver(self::$file_dir, false);
        return $obj->exists($cache_name);
    }
    
}