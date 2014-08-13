<?php 

namespace classes\Model;
class SecurityCatModel extends CatModel{    
    public $tabela = "";
    public $pkey   = '';
    
    //dados especificos da categoria
    protected $categorized_model = "";
    protected $multinivel_model  = "";
    protected $nome_cod          = "";
    protected $nome_cat          = "";
    protected $nome_pai          = "";
    protected $permission_model  = "";
    protected $dados = array();
    
    
    public function __construct() {
        $this->dados['cod_autor'] = array(
            'name'     => 'Autor',
            'type'     => 'int',
            'size'     => '11',
            'grid'    => true,
            'display' => true,
            'especial' => 'autentication',
            'autentication' => array(
                'needlogin' => true
            ),
            'fkey' => array(
                'model' => 'usuario/login',
                'cardinalidade' => '1n',
                'keys' => array('cod_usuario', 'user_name'),
                'onupdate' => 'cascade',
                'ondelete' => 'set null'
            ),
        );
        $this->dados['acesso'] = array(
           'name'    => 'Permissões',
           'type'    => 'enum',
           'default' => 'privado',
           'options' => array(
               'publico'     => "Público (Qualquer usuário pode acessar)",
               'nao_listado' => 'Não listado (Qualquer usuário com link pode acessar)',
               'privado'     => 'Privado (Somente convidados pode acessar)',
               'individual'  => 'Individual (Apenas você pode acessar)'
           )
        );
        $this->dados['adicionadoem'] = array(
            'name'     => 'Data de criação',
            'type'     => 'timestamp',
            'default' => "CURRENT_TIMESTAMP",
            'notnull' => true,
            'especial' => 'hide',
            'grid'    => true,
            'display' => true,
        );
        $this->dados['excluidoem'] = array(
            'name'    => 'Data de Exclusão',
            'type'    => 'date',
            'private' => true,
            'grid'    => true,
            'display' => true,
        );
        $this->dados['status'] = array(
           'name'    => 'Status',
           'type'    => 'enum',
           'notnull' => true,
           'subtitle'     => true,
           'default' => 'bloqueado',
           'options' => array(
               'publicado' => "Publicado (Disponível para a visualização)",
               'bloqueado' => 'Bloqueado (Somente pessoas com permissão de editar poderão ver)',
               'excluido'  => 'Na Lixeira',
           )
        );
        $this->dados['ordem'] = array(
            'name'    => 'Ordem',
            'type'    => 'int',
            'size'    => '11',
            'default' => "999999999",
            'private' => true,
            'notnull' => true,
            'grid'    => true,
            //'display' => true,
        );
        parent::__construct();
        $this->LoadModel('usuario/login', 'uobj');
        $this->LoadModel($this->categorized_model, 'oc');
        $this->LoadModel($this->permission_model, 'pmd');
        $this->cod_usuario = $this->uobj->getCodUsuario();
    }
    
    /*
     * recupera dos dados de um formulário de participantes
     */
    public function getParticipantesForm(){
        return $this->pmd->getDados();
    }
    
    /*
     * recupera todos os participantes de um grupo
     */
    public function getParticipantes($grupo){
        $tabela = $this->pmd->getTable();
        $tusuarios = $this->uobj->getTable();
        $this->db->Join($tabela, $this->tabela);
        $this->db->Join($tabela, $tusuarios);
        $var = $this->pmd->selecionar(array('cod_usuario','email'), "$this->nome_cod = '$grupo'", "", "", "email ASC");
        //echo $this->db->getSentenca();
        return $var;
    }
    
    /*
     * Remove um participante de um grupo
     */
    public function removerParticipante($cod_usuario, $cod_grupo){
        if($cod_usuario == "" || $cod_grupo == ""){
            $this->setErrorMessage('Usuário ou grupo não preenchidos');
            return false;
        }
        $var[] = $cod_usuario;
        $var[] = $cod_grupo;
        if(!$this->pmd->apagar(array($cod_usuario, $cod_grupo), array('cod_usuario', $this->nome_cod))){
            $this->setErrorMessage($this->pmd->getErrorMessage());
            return false;
        }
        $this->setSuccessMessage('Usuário removido corretamente!');
        return true;
    }
    
    public function getSuperiores($cod_grupo){
        static $list = array();
        if(empty ($list)){
            $list = array("0" => '');
            foreach($this->allgroups as $t){
                $list[$t[$this->nome_cod]] = ($t[$this->nome_pai] != "")?$t[$this->nome_pai]:"0";
            }
        }
        
        $superiores = array();
        $pai = isset($list[$cod_grupo])?$list[$cod_grupo]:"";
        while($pai != ""){
            $superiores[] = $pai;
            $pai = $list[$pai];
        }
        return($superiores);
    }
        
    public function getSubordinados($cod_grupo){
        
        static $subordinados_cache = array();
        if(array_key_exists($cod_grupo, $subordinados_cache))
                return $subordinados_cache[$cod_grupo];
        
        foreach($this->allgroups as $t){
            $list[($t[$this->nome_pai] == "")?"0":$t[$this->nome_pai]][] = $t[$this->nome_cod];
        }
        
        $subordinados = $netos = array();
        if(!array_key_exists($cod_grupo, $list)) return array();
        $filhos = $list[$cod_grupo];
        while(!empty ($filhos)){
            $filho = array_shift($filhos);
            if(array_key_exists($filho, $list)) {
                if(!empty ($list[$filho]))
                    $netos[] = $list[$filho];
            }
            $subordinados[] = $filho;
            if(empty ($filhos)) $filhos = array_shift($netos);
        }
        $subordinados_cache[$cod_grupo] = $subordinados;
        return($subordinados);
    }
    
    public function getListOfAccess($cod_usuario = ""){
        static $listof = array();
        if(!empty ($listof)) return $listof;
        
        $var = $this->getPertenceList($cod_usuario);
        //procura os grupos subordinados
        $out = array();
        foreach($var as $v){
            $temp = $this->getSubordinados($v[$this->nome_cod]);
            $out = array_merge($temp, $out);
            $out[] = $v[$this->nome_cod];
        }
        
        $listof = $out;
        return $out;
    }
    
    /*
     * retorna uma lista de todos os grupos nos quais o usuário é 
     * superior hierárquico ou que ele pertence (ou seja, todos que ele tem acesso)
     */
    public function getListOfSubordinados($cod_usuario = ""){
        static $listof = array();
        if(!empty ($listof)) return $listof;
        $var = $this->getPertenceList($cod_usuario);
        //procura os grupos subordinados
        $out = array();
        foreach($var as $v){
            $temp = $this->getSubordinados($v[$this->nome_cod]);
            $out = array_merge($temp, $out);
        }
        $listof = $out;
        return $out;
    }
    
    //seleciona os grupos nos quais o usuário pertence
    public function getPertenceList($cod_usuario = ""){
        static $pertencelist = array();
        if(!empty ($pertencelist)) return $pertencelist;
        
        $tb = $this->pmd->getTable();
        $this->db->Join($this->tabela, "$tb as user", array($this->nome_cod), array($this->nome_cod), "LEFT");
        $cod = $this->cod_usuario;
        if($cod_usuario != ""){
            if($cod != $cod_usuario){
                $this->db->Join($this->tabela, "$tb as otheruser", 
                        array($this->nome_cod), 
                        array($this->nome_cod), 
                        "LEFT");
                $where = "user.cod_usuario = '$cod_usuario' and otheruser.cod_usuario='$cod'";
                //$where = "cod_usuario = '$cod_usuario'";
            }
            else $where = "cod_usuario = '$cod'";
        }else $where = "cod_usuario = '$cod'";
        $pertencelist = parent::selecionar(array('user.'.$this->nome_cod, $this->nome_cat, $this->nome_pai), $where);
        return $pertencelist;
    }

    
    /*
     * retorna todos os grupos que o usuário tem acesso
     */
    public function getGroups($cod_usuario = "", $where = ""){
        
        static $groups = array();
        $save = ($cod_usuario == "")?true:false;
        if($save && !empty ($groups)) {return $groups;}
        $cod_usuario = ($cod_usuario == "")?$this->cod_usuario:$cod_usuario;
        
        $out = $this->getPertenceList($cod_usuario);
        if(empty ($out)) return array();
        $virg     = "";
        $wh       = "cod_grupo IN(";
        foreach($out as $o){
            $wh  .= $virg . $o['cod_grupo'];
            $virg = ", ";
        }
        $wh      .= ") ";
        $campos = $this->getCampos();
        $var = parent::selecionar($campos, $wh);
        
        if(empty ($var)) return $var;
        
        $virg = "";
        $list = "grupo IN(";
        foreach($var as $v){
            if(empty ($v)) continue;
            $var2[$v['cod_grupo']] = $v;
            $list .= $virg. $v['cod_grupo'];
            $virg = ", ";
        }
        $list .= ") ";
        $where = ($where == "")?$list:"$where AND $list";
        $oc = $this->oc->selecionar(
            array('grupo', 'cod_ocorrencia', 'assunto', 'descricao', 'criadoem'), 
            $where .' GROUP BY grupo', "", "", 'criadoem DESC'
        );
        foreach($oc as $o) $var2[$o['grupo']]['ocorrencia_ocorrencias'][$o['cod_ocorrencia']] = $o;
        if($save) {$groups = $var2;}
        return $var2;
    }
       
    //recupera a lista de permissões dos grupos que um usuário tem permissão
    private function getAssociado(&$vout, &$perm){
        $vout = $perm = array();
        $cod_usuario = $this->cod_usuario;
        $tb = $this->pmd->getTable();
        $this->db->Join($tb, $this->tabela);
        $var = $this->pmd->selecionar(
                array($this->nome_cod, $this->nome_cat, $this->nome_pai), 
                "cod_usuario = '$cod_usuario'", "", "", 
                "cod_pai, cod_grupo ASC"
        );
        
        if(empty ($var)) return;
        foreach($var as $a){
            $perm[] = $a[$this->nome_cod];
        }
        
        $var = $this->allgroups;
        foreach($var as $a ){
            $vout[$a[$this->nome_cod]] = $a[$this->nome_cat];
        }
    }
    
    /*
     * retorna um array com dados associados que mostram os filhos de um pai
     */
    public function getMenuAssoc(){
        static $save = array();
        if(!empty ($save)) return $save; 
        $all  = $this->allgroups;
        
        $arr = array();
        foreach($all as $a){
            if($a[$this->nome_pai] == "") $a[$this->nome_pai] = 0;
            $arr[$a[$this->nome_pai]][$a[$this->nome_cod]] = $a[$this->nome_cat];
        }
        $save = $arr;
        return $arr;
    }
    
    public function geraMenu($cod_cat = 0, $niveis = 10, $where = ""){
        if($this->uobj->UserIsAdmin()) return parent::geraMenu($cod_cat, $niveis, $where);
        
        $this->getAssociado($vout, $permissoes);
        $menu_assoc = $this->getMenuAssoc();
        $out = array();
        
        $v = $this->PermissaoHierarquica($menu_assoc, $permissoes);
        $this->geraMenuPermissoes($v, $menu_assoc, $vout, $out);
        return $out;
    }
    
    private function PermissaoHierarquica($menu_assoc, $perm){
        
        function checa($in, $perm, &$out, $i = 0){
            if(empty ($in)) return;
            if(in_array($i, $perm)){
                $out[$i] = $in;
                return;
            }
            
            if(!array_key_exists($i, $in)) return;
            if(!is_array($in[$i])) return;
            foreach($in[$i] as $c => $a){
                if(in_array($c, $perm)) $out[$c] = $a;
                else checa($in[$i], $perm, $out, $c);
            }
        }
        $out = array();
        $this->GeraHierarquia($menu_assoc, $out);
        $o = array();
        checa($out, $perm, $o);
        return $o;
    }
    
    private function geraMenuPermissoes($hierarquia, $menuTotal, $vout, &$out){
        foreach($hierarquia as $cod => $arr){
            if(!is_array($arr)){
                $link = "$this->link/show/$arr/".  GetPlainName($vout[$arr]);
                $out[$vout[$cod]][$vout[$arr]] = $link;
            }else{
                $link = "$this->link/show/$cod/".  GetPlainName($vout[$cod]);
                $out[$vout[$cod]][$vout[$cod]] = $link;
            }
            if(is_array($arr)) $this->geraMenuPermissoes ($hierarquia[$cod], $menuTotal, $vout, $out[$vout[$cod]]);
        }
    }
    
    /*
     * Gera a hieraquia de grupos de trabalho
     */
    public function GeraHierarquia($menu_assoc, &$out, $cod = 0){
        if(!isset($menu_assoc[$cod])) return;
        foreach($menu_assoc[$cod] as $c => $a){
            if(!array_key_exists($c, $menu_assoc)){
                $out[$cod][$c] = $c;
            }else{
                if(!isset($out[$cod])) $out[$cod] = array();
                $this->GeraHierarquia($menu_assoc, $out[$cod], $c);
            }
        }
    }
    
    public function CheckPermissao($cod_grupo){
        
        //usuário admin pode tudo!
        if($this->uobj->UserIsAdmin())return true;
        
        $this->getAssociado($vout, $perm);
        //print_r($perm); die("<br/>$cod_grupo");
        //se existe no array simples de permissoes então retorna
        if(in_array($cod_grupo, $perm)) return true;
        
        //se não existe no array de itens então grupo não existe
        if(!array_key_exists($cod_grupo, $vout)) return false;
        
        $out = array();
        $arr = $this->getMenuAssoc();
        $this->GeraHierarquia($arr, $out);
    }
    
}
?>