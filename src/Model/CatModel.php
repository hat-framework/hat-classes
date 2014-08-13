<?php

namespace classes\Model;
class CatModel extends \classes\Model\Model{
      
    protected $categorized_model = "";
    protected $dados = array();
    public function __construct() {
        $this->link = str_replace("_", "/", $this->tabela);
        $this->dados = array_reverse($this->dados);
        if($this->categorized_model == "") 
            throw new \classes\Exceptions\modelException(get_called_class(), "A variável categorized_model não foi inicializada");
        parent::__construct();
    }
    
    public function setModelName($model) {
        parent::setModelName($model);
        if($this->dm !== NULL){
            $this->nome_cod  = $this->dm->nome_cod;
            $this->nome_cat  = $this->dm->nome_cat;
            $this->nome_pai  = $this->dm->nome_pai;
        }
    }
    
    public function getNomeCod(){
        return $this->pkey;
    }
    
    public function getNomeCat(){
        return $this->nome_cat;
    }
    
    public function getNomePai(){
        return $this->nome_pai;
    }
    
    public function getHierarqName($cod_cat, $lista = array()){
        
        function gera($menu, &$out, $cod_cat, $baselink, $nomecat, $nomepai){
            
            if(!array_key_exists($cod_cat, $menu))return;
            $nome    = $menu[$cod_cat][$nomecat];
            $link = "$baselink/show/$cod_cat/".  GetPlainName($nome);
            $cod_pai = $menu[$cod_cat][$nomepai];
            gera($menu, $out, $cod_pai, $baselink, $nomecat, $nomepai);
            $out[$cod_cat] = array($nomecat => $nome, 'link' => $link);
            
        }
        
        //carrega os itens iniciais
        $lista = (empty ($lista))? $this->selecionar(): $lista;
        if(empty ($lista)) return array();
        
        $menu = $out = array();
        foreach($lista as $arr){
            //$consulta[$arr[$this->pkey]] = $arr;
            $link = "$this->link/show/".$arr[$this->pkey]."/".  GetPlainName($arr[$this->nome_cat]);
            if($arr[$this->nome_pai] == "") $arr[$this->nome_pai] = 0;
            $menu[$arr[$this->pkey]] = array(
                'link'    => $link,
                $this->nome_cat => $arr[$this->nome_cat],
                $this->nome_pai => $arr[$this->nome_pai]
            );
        }
        
        gera($menu, $out, $cod_cat, $this->link, $this->nome_cat, $this->nome_pai);
        return $out;
        
    }

    public function getDestaques($cod_cat, $limit = 16, $offset = 0){
        
    }

    public function getItens($cod_cat, $link, $page, $numpages = 6){
        $this->LoadModel($this->categorized_model, 'cobj');   
        $tabela = $this->cobj->getTable();
        $var = $this->cobj->paginate($page, $link, $cod_cat, "$tabela`.`$this->nome_cod", $numpages);
        //echo $this->db->getSentenca();
        return $var;
    }

    /*
     * Gera o menu infinito
     * Recebe o código da categoria a partir da qual o menu será gerado
     */
    public function geraMenu($cod_cat = 0, $niveis = 20, $where = ""){       

        $saida = array();
        //$saida[CURRENT_CONTROLLER] = array(CURRENT_CONTROLLER => $this->model_name);
        
        //gera uma lista com todas as categorias
        $menu = $this->genLista(array(), $where);
        
        //verifica se a categoria existe
        //caso negativo gera o menu do pai
        if(!array_key_exists($cod_cat, $menu)){
            $item = $this->getItem($cod_cat);
            if(empty ($item) || empty ($item[$this->nome_pai])) {
                if(empty ($menu)) return $saida;
                else {return $this->geraMenu();}
            }
            $cod_cat = array_keys($item[$this->nome_pai]);
            $cod_cat = array_shift($cod_cat); 
        }

        //verifica se a categoria existe
        //caso negativo gera o menu da categoria mais generica
        if(array_key_exists($cod_cat, $menu)){
            
            //verifica se a categoria tem filhos
            $havechildren = false;
            foreach($menu[$cod_cat] as $cat => $mn){
                if(array_key_exists($cat, $menu)){
                    $havechildren = true;
                    break;
                }
            }
            
            //se categoria nao tem filhos
            //gera o menu da categoria pai
            if(!$havechildren){
                $item = $this->getItem($cod_cat);
                $cod_cat = 0;
                if(!empty ($item)){
                    $cod_cat = ($item[$this->nome_pai] == "")?array('0' => '0'): array_keys($item[$this->nome_pai]);
                    $cod_cat = array_shift($cod_cat);
                }
                
            }
        }else $cod_cat = 0;

        $this->geraMenuInfinito( $menu , $saida, $cod_cat, $niveis);
        return $saida;
    }
    
    //se niveis for zero entao gera o menu inteiro
    protected function geraMenuInfinito( array $menuTotal , &$saida, $idPai = 0, $niveis = 10, $nivel = 0 ){
        
        if($nivel == $niveis && $niveis != 0) return;
        foreach( $menuTotal[$idPai] as $idMenu => $menuItem){
            $saida[$menuItem['name']][$menuItem['name']] = $menuItem['link'];
            if(isset( $menuTotal[$idMenu] ) ){
                $this->geraMenuInfinito( $menuTotal ,$saida[$menuItem['name']],  $idMenu , $niveis, $nivel+1);
            }
        }
        
    }
    
    protected function genLista($lista = array(), $where = ''){
        if(empty ($lista)) $lista = $this->selecionar(array(), $where, "", "", "$this->nome_cat ASC");
        $menu = array();
        if(!empty($lista)){
            foreach($lista as $arr){
                //$consulta[$arr[$this->pkey]] = $arr;
                $link = "$this->link/show/".$arr[$this->pkey]."/".  GetPlainName($arr[$this->nome_cat]);
                if($arr[$this->nome_pai] == "") $arr[$this->nome_pai] = 0;
                $menu[$arr[$this->nome_pai]][$arr[$this->pkey]] = array('link' => $link,'name' => $arr[$this->nome_cat]);
            }
        }
        return $menu;
    }

    protected function getCategorias($menu, &$lista, $cod_cat){
        //adiciona a categoria atual a lista
        $lista[] = $cod_cat;

        //se uma categoria não tem filhas, retorna
        if(!array_key_exists($cod_cat, $menu))return;
        foreach($menu[$cod_cat] as $cod_atual => $array)
            $this->getCategorias($menu, $lista, $cod_atual);
    }

}