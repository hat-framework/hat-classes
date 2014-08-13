<?php
namespace classes\Classes;
class Widget extends Object{
    
    protected static $widgets = array();
    private   static $obj = null;
    public static function getWidget($name = ""){
        if($name == "") return self::widgets;
        return (isset(self::$widgets[$name]))?  self::$widgets[$name]:array();
    }
    
    public static function setWidget($name, $config = array()){
        $var          = GetPlainName($name);
        $method       = "Listar";
        $class        = "widget_right span7";
        $model_method = 'paginate';
        $filther      = array();
        $title        = ucfirst($name);
        extract($config);
        if(!isset($model)) die(__METHOD__ . ": Widget Inválido! -- " . self::whoAmI());
        self::$widgets[$name] = array(
            'name'         => $name,
            'title'        => $title,
            'model'        => $model,
            'method'       => $method,
            'class'        => $class,
            'model_method' => $model_method,
            'filther'      => $filther,
        );
    }
    
    public static function showAllWidgets($vars = array(), $remove = true){
        self::$obj = new Object();
        foreach(self::$widgets as $name => $widget){
            self::showWidget($name, $vars, $remove);
        }
    }
    
    public static function showWidget($widget_name, $vars = array(), $remove = true){
        if($widget_name == "") return;
        $widget = self::getWidget($widget_name); 
        $result = self::paginate($widget, $vars);
        self::display($widget, $vars, $result);
        if($remove) self::removeWidget($widget_name);
    }
    
    private static function display($widget, $vars, $result){
        if(empty($result)) return;
        extract($vars);
        $method = $widget['method'];
        $gui = new \classes\Component\GUI();
        $gui->widgetOpen("widget_".$widget['name']);
        self::$obj->LoadComponent($widget['model'], 'comp')->$method($widget['model'], $result, $widget['title'], "widget {$widget['class']}");
        $gui->widgetClose();
    }
    
    private static function paginate($widget, $vars){
        $lk     =  $widget['model']."/index";
        $preset = array('page' => 0, 'link' => $lk, 'qtd' => 10, 'campos' => array(), 'filther' => "", 'order' => "");
        extract($preset);  extract($vars); 
        $method = $widget['model_method'];
        $md = self::$obj->LoadModel($widget['model'], 'md');
        if(!method_exists($md, $method))
            throw new \InvalidArgumentException(__METHOD__ . ": O método $method não existe no model {$widget['model']}");
        if($method == "paginate"){
              return $md->$method($page, $link, "", "", $qtd, $campos, $widget['filther'], $order);
        }else {
            return $md->$method($widget['filther'], $page, $link, $qtd, $order);
        }
    }
    
    public static function removeWidget($widget){
        if(isset(self::$widgets[$widget])) unset(self::$widgets[$widget]);
    }
}