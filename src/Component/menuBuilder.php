<?php
namespace classes\Component;
class menuBuilder{
    
    private $nome_pai = '';
    private $id       = '';
    public function __construct($list) {
        $this->lista = $list;
    }
    
    public function setNomePai($pai){
        $this->nome_pai = $pai;
    }
    
    public function setMenuId($menuid){
        $this->id = $menuid;
    }
    
    /*
     * Gera o menu infinito
     * Recebe o código da categoria a partir da qual o menu será gerado
     */
    public function geraMenu(){   
        if(empty($this->lista)) return $this->lista;
        $saida = array();
        $menu = $this->genLista($this->lista);
        $this->geraMenuInfinito($menu, $saida);
        return $saida;
    }
    
    //se niveis for zero entao gera o menu inteiro
    private function geraMenuInfinito( array $menuTotal , &$saida, $idPai = 0, $niveis = 10, $nivel = 0 ){
        
        if($nivel == $niveis && $niveis != 0) return;
        foreach( $menuTotal[$idPai] as $idMenu => $menuItem){
            $saida[$menuItem['name']][$menuItem['name']] = $menuItem['link'];
            $saida[$menuItem['name']]['__id']            = $menuItem['__id'];
            if(isset($menuItem['__icon'])){$saida[$menuItem['name']]['__icon'] = $menuItem['__icon'];}
            if(isset( $menuTotal[$idMenu] ) ){
                $this->geraMenuInfinito( $menuTotal ,$saida[$menuItem['name']],  $idMenu , $niveis, $nivel+1);
            }
        }
        
    }
    
    private function genLista($lista){
        if(empty($lista)) return array();
        $menu = array();
        foreach($lista as $arr){
            if($arr[$this->nome_pai] == "") $arr[$this->nome_pai] = 0;
            $menu[$arr[$this->nome_pai]][$arr[$this->id]] = array(
                'link' => $arr['url'],
                'name' => $arr['menu'],
                '__id' => $arr[$this->id]
            );
            
            if(isset($arr['icon']) && trim($arr['icon']) !== ""){
                $menu[$arr[$this->nome_pai]][$arr[$this->id]]['__icon'] = $arr['icon'];
            }
        }
        return $menu;
    }
}