<?php

namespace classes\Classes;
class Object{

    /**
     * @abstract mensagens de execussao
     */
    private $object_msg = array();

    /**
     * @abstract configuracoes da classe
     */
    protected $config;

    /**
     * @abstract seta uma nova mensagem de erro
     * @param string $msg   mensagem de erro
     * @return bool false (always)
     */
    public function setErrorMessage($msg){
        $this->setMessage("erro", $msg);
        return false;
    }
    
    /**
     * Adiciona um erro a string de erros e concatena com os erros que já existirem
     * @param type $msg
     * @param string glue separador entre as mensagens
     * @return boolean  always false
     */
    public function appendErrorMessage($msg, $glue = "<br/>"){
        $this->appendMessage("erro", $msg, $glue);
        return false;
    }

    /**
     * @abstract seta uma nova mensagem de sucesso
     * @param string $msg   mensagem de sucesso
     * @return bool true (always)
     */
    public function setSuccessMessage($msg){
        $this->setMessage("success", $msg);
        return true;
    }
    
    /**
     * Adiciona um sucesso a string de sucessos e concatena com os sucessos que já existirem
     * @param type $msg
     * @param string glue separador entre as mensagens
     * @return boolean always true
     */
    public function appendSuccessMessage($msg, $glue = "<br/>"){
        $this->appendMessage("success", $msg, $glue);
        return false;
    }
    
    /**
     * Concatena uma mensagem ao tipo
     * @param string $type
     * @param string $msg
     * @param string glue separador entre as mensagens
     */
    public function appendMessage($type, $msg, $glue){
        $msg = (is_array($msg))?  implode($glue, $msg):$msg;
        if(!isset($this->object_msg[$type])){$this->object_msg[$type] = "";}
        if(is_array($this->object_msg[$type])){
            $this->object_msg[$type][] = $msg;
        }else{
            $this->object_msg[$type] .= $msg.$glue;
        }
    }
    
    public function setMessages($msgs){
        if(is_array($msgs) && !empty ($msgs)) {
            $this->object_msg = array_merge($this->object_msg, $msgs);
        }
    }
    
    public function setAlertMessage($msg){
        $this->setMessage("alert", $msg);
    }
    
    /**
     * Adiciona um alerta a string de alertas e concatena com os alertas que já existirem
     * @param string $msg mensagem a ser concatenada
     * @param string glue separador entre as mensagens
     */
    public function appendAlertMessage($msg, $glue = "<br/>"){
        $this->appendMessage("success", $msg, $glue);
    }
    
    /**
     * @abstract seta uma nova mensagem
     * @param string $camp  nome da variável de mensagem
     * @param string $msg   mensagem da variável
     * @return nada
     */
    public function setMessage($camp, $msg){
        $msg = (is_array($msg))?  implode("<br/>", $msg):$msg;
        $this->object_msg[$camp] = $msg;
    }
    
    /**
     * @abstract seta uma nova mensagem
     * @param string $camp  nome da variável de mensagem
     * @param string $msg   mensagem da variável
     * @return nada
     */
    public function setSimpleMessage($camp, $msg){
        $this->object_msg[$camp] = $msg;
    }
    
    public function unsetMessage($camp){
        if(isset($this->object_msg[$camp])) unset($this->object_msg[$camp]);
    }
    
    public function addLog($msg){
        $this->object_msg['log'][] = $msg;
    }
    
    public function getLog(){
        return $this->object_msg['log'];
    }
    
    /**
      * @abstract retorna as mensagens de erro
      * @return string
      */
     public function getErrorMessage($unset = false){
         
         $var = "";
     	 if(is_array($this->object_msg)){
             if(array_key_exists("erro", $this->object_msg)){
                $var = $this->object_msg['erro'];
             }
         }
         if($unset) $this->unsetMessage ('erro');
         $this->object_msg['erro'] = "";
         return $var;
     }

     /**
      * @abstract retorna string contendo as mensagens de sucesso caso existam
      * @return string
      */
     public function getSuccessMessage($unset = false){
     	 if(is_array($this->object_msg)){
             if(array_key_exists("success", $this->object_msg)){
                $var = $this->object_msg['success'];
             }
         }
         if($unset) $this->unsetMessage ('erro');
         $this->object_msg['success'] = "";
         return $var;
     }
     
     public function getAlertMessage(){
     	 if(is_array($this->object_msg)){
             if(array_key_exists("alert", $this->object_msg)){
                return $this->object_msg['alert'];
             }
         }
     }

    /**
    * @abstract array contendo todas as mensagens da classe
    * @return array
    */
    public function getMessages($clear = false){
        $temp = $this->object_msg;
        if($clear) $this->object_msg = array();
        return $temp;
    }
    
    /*retorna o nome da classe que foi chamada primeiro*/
    public static function whoAmI() {
        return get_called_class();
    }

    /**
    * @abstract Carrega o um model para dentro da classe model
    * @uses Carrega o um model para dentro da classe model
    * @param string @model  Nome do arquivo do model a ser carregado
    * @param string $name   Nome do objeto a ser criado, caso nao seja informado o objeto
                              terá o nome do proprio model
    * @throws Exception 
    * @return não retorna nada
    */
    public function LoadModel($modelname, $name, $throws = true, $noObject = false){
        //echo $modelname . "<br/>";
        static $cache = array();
        if(array_key_exists($modelname, $cache)){
            $this->$name = $cache[$modelname];
            return $this->$name;
        }
        //seta o modulo e a pasta do modulo
        $model  = explode("/",$modelname);
        $plugin = array_shift($model);
        $modulo = array_shift($model);
        $class  = array_pop($model);
        if($class == ""){
            $class = $plugin."_".$modulo;
            $file  = $modulo;
        }else{
            $file   = $class;
            $class  = $plugin."_".$class;
        }
        $class .= "Model";
        $file  .= "Model";
        $path   = implode("/", $model);
        $path   = ($path == "")? "": $path . "/";

        //procura o arquivo
        $folder = Registered::getPluginLocation($plugin, true)."/$modulo/classes/$path$file.php";
        if(!file_exists($folder)){
            if(!$throws){
                $cache[$modelname] = null;
                $this->$name = $cache[$modelname];
                return $cache[$modelname];
            }
            $str = "";
            if(DEBUG){
                $erro = error_get_last();
                $dbg  = "";
                if(!empty($erro)){
                    $dbg = "<br/><br/><b>Sobre a chamada:</b> <br/> ";
                    foreach($erro as $nm => $v) $dbg .= "<b>$nm</b>: $v<br/>";
                }
                $str = "<br/><b>Arquivo procurado:</b> ($folder) $dbg";
            }
            throw new \Exception("O Model ($class) não foi encontrado ou não existe $str");

        }
        $this->LoadConfigFromPlugin($plugin);
        require_once $folder;
        if($noObject) return null;
        if(!class_exists($class)){
            if($throws) throw new \Exception("A classe ($class) não foi encontrado ou não existe");
            $cache[$modelname] = null;
            $this->$name = $cache[$modelname];
            return $cache[$modelname];
        }

        $cache[$modelname] = new $class();
        if(!is_object($cache[$modelname])){
            if($throws) throw new \Exception("Não foi possível instanciar o Model $class");
            $cache[$modelname] = null;
            $this->$name = $cache[$modelname];
            return $cache[$modelname];
        }
        $this->$name = $cache[$modelname];
        if(method_exists($this->$name, 'setModelName')) $this->$name->setModelName($modelname);
        return $this->$name;
    }
    
    public function LoadClassFromPlugin($ClassName, $name, $throws = true, $vars = array()){
        
        $model  = explode("/",$ClassName);
        $plugin = array_shift($model);
        $modulo = array_shift($model);
        $class  = array_pop($model);
        $class  = (($class == "")?$modulo:$class);
        $path   = implode("/", $model);
        $path   = ($path == "")? "": $path . "/";
        
        $folder = Registered::getPluginLocation($plugin,true) . "/$modulo/classes/$path$class.php";
        if(!file_exists($folder)){
            //echo "Arquivo $folder não encontrado!<br/>";
            if($throws) {
                $msg = "O arquivo da classe ($class) não foi encontrada ou não existe";
                //if(usuario_loginModel::IsWebmaster()){
                    $msg .= "- Pasta Procurada: $folder";
                //}
                throw new \Exception($msg);
            }
            $this->$name = NULL;
            return;
        }
        
        $this->LoadConfigFromPlugin($plugin);
        require_once $folder;

        if(!class_exists($class)){
            $class = "{$plugin}_{$class}";
            if(!class_exists($class)){
                if($throws) throw new \Exception("A classe ($class) não foi encontrado ou não existe");
                $this->$name = NULL;
                return;
            }
            
        }
        
        $this->$name = new $class($vars);
        if(!is_object($this->$name)){
            if($throws) throw new \Exception("Não foi possível instanciar a Classe $class");
            $this->$name = NULL;
        }
        return $this->$name;
    }
    
    /**
    * @abstract Carrega o um recurso
    * @uses Carregar um recurso
    * @param string $resource Recebe uma string com o nome do recurso a ser carregado
    * @param string $name     Recebe uma string com o nome do objeto
    * @throws Exception 
    * @return não retorna nada
    */
    public function LoadResource($resource, $name, $disable_cache = false){
        static $cache = array();
        if(!$disable_cache){
            if(array_key_exists($resource, $cache)){
                $this->$name = $cache[$resource];
                return $this->$name;
            }
        }
        $explode   = explode("/", $resource);
        $classname = array_pop($explode);
        $res_dir   = implode("/", $explode);
        $class     = $classname . "Resource";
        if($res_dir === ""){$res_dir = $classname;}
        $file      = Registered::getResourceLocation($res_dir, true)."/src/$class.php";
        getTrueDir($file);
        //echo "\n ($class) \n";
        if(!file_exists($file)){
            $msg  = __CLASS__ . ": O recurso $resource não existe! <br/> Diretório procurado: $file";
            $file = Registered::getResourceLocation($res_dir, true)."/$class.php";
            if(!file_exists($file)){
                $msg  .= "<br/> Diretório procurado: $file";
                throw new \Exception($msg);
            }
        }
        $this->LoadConfigFromResource($resource);
        require_once $file;
        
        $this->CheckClass($class);
        $obj = call_user_func("$class::getInstanceOf");
        
        if(!is_object($obj)){
            if(!is_object($obj))
                throw new \Exception(__CLASS__ . ": Não foi possível carregar o Recurso $resource");
        }
        
        $cache[$resource] = $obj;
        $this->$name = $cache[$resource];
        return $this->$name;
    }
    
    
    public function LoadData($modelname, $name, $throws = true){
        static $cache = array();
        if(array_key_exists($modelname, $cache)){
            $this->$name = $cache[$modelname];
            return $this->$name;
        }
        
        //seta o modulo e a pasta do modulo
        $model  = explode("/",$modelname);
        $plugin = array_shift($model);
        $modulo = array_shift($model);
        $class  = array_pop($model);
        if($class == ""){
            $class = $plugin."_".$modulo;
            $file  = $modulo;
        }else{
            $file   = $class;
            $class  = $plugin."_".$class;
        }
        $class .= "Data";
        $file  .= "Data";
        $path   = implode("/", $model);
        $path   = ($path == "")? "": $path . "/";

        //procura o arquivo
        $folder = Registered::getPluginLocation($plugin, true) . "/$modulo/classes/$path$file.php";
        if(!file_exists($folder)){
            if(!$throws){
                $cache[$modelname] = null;
                $this->$name = $cache[$modelname];
                return $cache[$modelname];
            }
            $str = "";
            if(DEBUG){
                $erro = error_get_last();
                $dbg  = "";
                if(!empty($erro)){
                    $dbg = "<br/><br/><b>Sobre a chamada:</b> <br/> ";
                    foreach($erro as $nm => $v) $dbg .= "<b>$nm</b>: $v<br/>";
                }
                $str = "<br/><b>Arquivo procurado:</b> ($folder) $dbg";
            }
        }
        require_once $folder;

        if(!class_exists($class)){
            $cache[$modelname] = null;
            $this->$name = $cache[$modelname];
            return $cache[$modelname];
        }

        $cache[$modelname] = new $class();
        if(!is_object($cache[$modelname])){
            $cache[$modelname] = null;
            $this->$name = $cache[$modelname];
            return $cache[$modelname];
        }
        $this->$name = $cache[$modelname];
        return $this->$name;
    }
    
    /**
    * @abstract Carrega um plugin javascript para dentro da classe model
    * @param string @plugin  Nome do arquivo do plugin a ser carregado
    * @param string $name   Nome do objeto a ser criado, caso nao seja informado o objeto
                              terá o nome do proprio model
    * @throws Exception 
    * @return não retorna nada
    */
    public function LoadJsPlugin($plugin, $name, $scripts = true){

        static $cache = array();
        if(array_key_exists($plugin, $cache)){
            $this->$name = $cache[$plugin];
            return $this->$name;
        }
        
        $pvar = $plugin;
        //seta o modulo e a pasta do modulo
        $plugin   = explode("/",$plugin);
        $resource = array_shift($plugin);
        $modulo   = array_shift($plugin);
        $class    = array_pop($plugin);
        $class    = (($class == "")?$modulo:$class)."Js";
        
        //procura o arquivo
        $this->LoadConfigFromResource($resource);
        
        $loaded = false;
        if(defined('CURRENT_TEMPLATE')){
            $file = Registered::getTemplateLocation(CURRENT_TEMPLATE, true)."/hat/jsplugins/{$pvar}Js.php";
            getTrueDir($file);
            if(file_exists($file)){
                require_once $file;
                if(class_exists($class, false)){
                    $loaded = true;
                }
            }
        }
        
        if(false === $loaded){
            $path     = implode("/", $plugin);
            $path     = ($path == "")? "": $path . "/";

            $file      = Registered::getResourceLocation($resource, true)."/src/jsplugins/$modulo/$path$class.php";
            getTrueDir($file);
            $this->LoadFile($file);
            $this->CheckClass($class);
        }
        
        $obj = call_user_func("$class::getInstanceOf", $pvar);
        if(!is_object($obj)) 
            throw new \CannotInstanceJsException(__CLASS__, $class);
        $obj->start_scripts($scripts);
        
        $cache[$resource] = $obj;
        $this->$name = $cache[$resource];
        return $this->$name;
    }
    
    /**
    * @abstract Carrega um componente da pasta de um plugin
    */
    public function LoadComponent($component, $name){
        
        $comp_name = $component;
        static $cache = array();
        if(array_key_exists($comp_name, $cache)){
            $this->$name = $cache[$comp_name];
            return $this->$name;
        }
        
        //seta o modulo e a pasta do modulo
        $component = explode("/",$component);
        $plugin = array_shift($component);
        $modulo = array_shift($component);
        $class  = array_pop($component);
        $class  = (($class == "")?$modulo:$class)."Component";
        $path   = implode("/", $component);
        $path   = ($path == "")? "": $path . "/";
        
        //procura o arquivo
        $folder = Registered::getPluginLocation($plugin, true) . "/$modulo/components/$path$class.php";
        $classname = $class;
        if(!file_exists($folder)){
            $folder = Registered::getPluginLocation($plugin, true) . "/$modulo/classes/$path$class.php";
            if(!file_exists($folder)) $classname = "\classes\Component\Component";
            else require_once $folder;
        }
        else {
            $this->LoadConfigFromPlugin($plugin);
            require_once $folder;
        }
        
        $this->$name = new $classname();
        if(!is_object($this->$name)){
            throw new \Exception("Não foi possível instanciar o Componente $class");
        }
        
        $cache[$comp_name] = $this->$name;
        return $this->$name;
    }
    
    public function LoadFile($folder){
        if(!file_exists($folder)) 
            throw new \Exception("O arquivo $folder não foi encontrado!");
        require_once $folder;
    }
    
    public function LoadConfigFromPlugin($plugin){
        static $loaded = array();
        if(in_array($plugin, $loaded)) return;
        $loaded[] = $plugin;
        $dir      = Registered::getPluginLocation($plugin, true);
        $this->LoadAllFilesFromDir(SUBDOMAIN_MODULOS . "$plugin");
        $this->LoadAllFilesFromDir("$dir/Config/defines");
        $this->LoadAllFilesFromDir("$dir/Config/interfaces");
    }

    public function LoadConfigFromResource($resource){
        $resourceLocation = Registered::getResourceLocation($resource,true);
        $autoload  = "$resourceLocation/autoload.php";
        getTrueDir($autoload);
        if(file_exists($autoload)){require_once $autoload;}
        
        if($resource == "files/dir") return;
        static $loaded = array();
        if(in_array($resource, $loaded)) return;
        $loaded[] = $resource;
        $this->LoadAllFilesFromDir(SUBDOMAIN_RESOURCES . "/$resource");
        $this->LoadAllFilesFromDir("$resourceLocation/src/defines");
        $this->LoadAllFilesFromDir("$resourceLocation/src/interfaces");
    }
    
    private function LoadAllFilesFromDir($diretorio){
        $this->LoadResource('files/dir', "object_temp_dir_obj");
        $files = $this->object_temp_dir_obj->getArquivos($diretorio);
        if(!empty ($files)){
            foreach($files as $fname){
                $dir = $diretorio . "/$fname";
                if(file_exists($dir)) require_once $dir;
            }
        }
    }
    
    public function CheckClass($class){
        if(!class_exists($class, false))
            throw new \Exception("A classe $class não existe");
    }
    
    protected function LogError($msg, $logname = "", $aditionalError = array()){
        if($logname !== "") {
            \classes\Utils\Log::save($logname, $msg);
            if(!empty($aditionalError)){
                $this->setMessages($aditionalError);
                \classes\Utils\Log::save($logname, $aditionalError);
            }
        }
        return $this->setErrorMessage($msg);
    }
    
    public function propagateMessage($obj, $method){
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        if(call_user_func_array(array($obj, $method), $args) === false){
            $this->setMessages($obj->getMessages());
            return false;
        }
        return true;
    }
    
}