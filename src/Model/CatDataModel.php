<?php

namespace classes\Model;
class CatDataModel extends DataModel{
    
    private $ncod      = "cod_categoria";
    private $ncat      = "catnome";
    private $npai      = "cod_pai";
    public  $nome_cod  = "cod_categoria";
    public  $nome_cat  = "catnome";
    public  $nome_pai  = "cod_pai";
    public function getDados() {
        $this->modelname = func_get_arg(0);
        $this->setDados();
        return parent::getDados();
    }
    public function setDados() {
        $this->nome_cod = ($this->nome_cod == "")?$this->ncod:$this->nome_cod;
        $this->nome_cat = ($this->nome_cat == "")?$this->ncat:$this->nome_cat;
        $this->nome_pai = ($this->nome_pai == "")?$this->npai:$this->nome_pai;
        
        $this->dados[$this->nome_cod] = array(
            'name'    => "Código",
            'pkey'    => true,
            'ai'      => true,
            'type'    => 'int',
            'size'    => '4',
            'display' => true,
            'private' => true,
            'grid'    => true,
            'notnull' => true
        );
        
        $this->dados[$this->nome_cat] = array(
            'name' 	=> 'Nome',
            'type' 	=> 'varchar',
            'title'     => true,
            'search'  => true,
            'size' 	=> '50',
            'display'   => true,
            //'search'    => true,
            'grid'      => true,
            'notnull'   => true
        );
        
        $this->dados[$this->nome_pai] = array(
            'name'    => 'Pai',
            'grid'    => true,
            'fkey'    => array(
                'model'         => $this->modelname, 
                'cardinalidade' => '1n', //nn 1n 11
                'keys'          => array($this->nome_cod, $this->nome_cat),
                'onupdate'      => 'cascade',
                'ondelete'      => 'restrict'
            )
        );
        
        $this->dados['button'] = array(
            'button' => "Salvar Categoria"
        );
        
        
    }
}
