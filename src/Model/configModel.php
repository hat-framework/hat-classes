<?php

namespace classes\Model;
use classes\Classes\Object;
class configModel extends Object {

    
    protected $dados    = array();
    protected $filename = "";
    public    $name     = "";

    /*
     * @Explication
     * seleciona dados
     * 
     * @args
     *
     * @returns
     */
    public function select(){
        if(!file_exists($this->filename)) return array();
        $subject = file_get_contents($this->filename);
        $itens = array();
        $subject = str_replace(array("<?php", "?>", "<?", '"', "'"), "", $subject);
        $item = explode("define(", $subject);
        foreach($item as $it){
            $it = explode(");", $it);
            $it = array_shift($it);
            $it = explode(",", $it);
            if(count($it) < 2) continue;
            
            $n     = str_replace(array(" ", "'"), "", array_shift($it));
            $name  = str_replace(array(" ", "-", "_"), array("", " ", " "), $n);
            $value = implode(",", $it);
            $value = substr($value, 1);
            $itens[$n] = array(
                'name'  => ucfirst(strtolower($name)),
                'type'  => 'varchar',
                'default' => "$value"
            );
            
            if($value == 'true' || $value == 'false'){
                $itens[$n]['type'] = 'enum';
                $itens[$n]['options'] = array('true' => 'sim', 'false' => 'Não');
            }
            

        }
        return($itens);

    }

    public function setFilename($file, $class){
        $class = str_replace(array('config_','AConfig', 'Config'), "", $class);
        $this->filename = dirname($file) . "/$class.php";
    }

    public function inserir($dados){

        if(!file_exists($this->filename)){
            $this->setErrorMessage("Arquivo $this->filename não existe");
            return false;
        }
        
        if(!is_writable($this->filename)){
            if(!@chmod($this->filename, "0755")){
                $this->setErrorMessage("Arquivo ($this->filename) não tem permissão de escrita");
                return false;
            }
        }
        unset($dados['enviar']);
        unset($dados['antispam']);
        
        $this->post = $dados;
        $this->validate();
        $valores = array('true', 'false');
        $data = "<?php \n";
        foreach($this->post as $name => $valor){
            $name = str_replace (" ", "", $name);
            if(!in_array($valor, $valores))$valor = "'$valor'";
            $data .= "\n\t if(!defined('$name')) define('$name' , $valor);";
        }
        
        $data .= "\n ?>";
        if(file_put_contents($this->filename, $data) === false){
            $this->setErrorMessage("Não foi possível inserir dados no arquivo ($this->filename) ");
            return false;
        }
        
        $this->setSuccessMessage("Dados inseridos corretamente");
        return true;
    }
    
    public function editar($dados){
        return $this->inserir($dados);
    }
    
    /*
     * @Explication
     * Valida a classe de acordo com algumas regras definidas na variavel dados
     *
     * @returns
     * @boolean: true caso consiga validar, false caso contrario
     */
    protected function validate(){
        
        if(empty($this->dados)) return true;
        
        //verifica se tem dados a serem validados
        if(empty ($this->post)){ 
            $this->setErrorMessage("Dados a serem inseridos faltando");
            return false;
        }
        
        $this->LoadResource("formulario/validator", "pval");
        if(!$this->pval->validate($this->dados, $this->post)){
            $this->setMessages($this->pval->getMessages());
            //$this->setErrorMessage("Erro ao validar os dados a serem inseridos");
            return false;
    	}
    	$this->post = $this->pval->getValidPost();
    	return true;
    }


    /*
     * @Explication
     * Associa os dados enviados para a classe
     *
     * @returns
     * @boolean: true caso consiga associar, false caso contrario
     */
    protected final function associa(){
        
        $data = $this->dados;
        if(!is_array($data) || empty ($data)){
            $this->setErrorMessage("Erro no sistema! Dados a serem inseridos não foram configurados,
                consulte o administrador");
            return false;
        }
        $post = array();        
        foreach($this->dados as $tname => $arr){
            
            //se nao for chave estrangeira ou se chave estrangeira for 11 ou 1n insere normalmente
            if(!array_key_exists('fkey', $arr) || $arr['fkey']['cardinalidade'] == '11' || $arr['fkey']['cardinalidade'] == '1n'){
                if(array_key_exists($tname, $this->post)){
                     $post[$tname] = $this->post[$tname];
                }
            }
        }
            
        if(empty ($post)){
            $model = ucfirst($this->model_name);
            $this->setErrorMessage("$model: Dados a serem inseridos não foram preenchidos");
            return false;
        }
        
        $this->post = $post;
        //print_r($this->post);
        return true;
    }
    
    
    
    /*
     * @Explication
     * Retorna o nome da tabela do banco de dados
     *
     * @returns
     * @string: nome da tabela
     */
    public function getDados(){
    	return $this->dados;
    }

    public function getName(){
        return $this->name;
    }
}

?>