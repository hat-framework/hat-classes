<?php

function strtolowerbr($texto){ 
    //Letras minúsculas com acentos 
    $texto = strtr($texto, " 
    ĄĆĘŁŃÓŚŹŻABCDEFGHIJKLMNOPRSTUWYZQ 
    XVЁЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ 
    ÂÀÁÄÃÊÈÉËÎÍÌÏÔÕÒÓÖÛÙÚÜÇ 
    ", " 
    ąćęłńóśźżabcdefghijklmnoprstuwyzq 
    xvёйцукенгшщзхъфывапролджэячсмитьбю 
    âàáäãêèéëîíìïôõòóöûùúüç 
    "); 
    return strtolower($texto); 
} 

function strCharset($texto){ 
    //Letras minúsculas com acentos 
    $texto = strtr($texto, " 
    €ÇåéÖA
    ", " 
    ÇÃÕÚÍE
    "); 
    return $texto; 
} 

function GetPlainName($nome, $lower = true, $remover_acentos = true){
    $nome = trim($nome);
    if($remover_acentos){
        //retira os acentos do nome
        $array1 = array( "á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç"
        , "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç", "  "," ");
        $array2 = array( "a", "a", "a", "a", "a", "e", "e", "e", "e", "i", "i", "i", "i", "o", "o", "o", "o", "o", "u", "u", "u", "u", "c"
        , "A", "A", "A", "A", "A", "E", "E", "E", "E", "I", "I", "I", "I", "O", "O", "O", "O", "O", "U", "U", "U", "U", "C", " ","-");
        $nome = str_replace( $array1, $array2, $nome);
        if($lower) $nome = strtolower($nome);
    }elseif($lower) $nome = mb_strtolower($nome, CHARSET);

    $nome = str_replace(array("--", "?", "!", ".", ";", ",", "<", ">", "^", "]", "}", "º", "ª", "[", "{", "`", "´", '"', "'",
    "#","$", "%", "¨", "&", "*", "(", ")", "__", "+", "/", "\\", "|", "º", ":", "\n", "\t"
    ), "", $nome);
    return trim($nome);
}

/**
*  @autor: Carlos Reche
*  @data:  Jan 21, 2005
*/
function Resume($string, $max_lenght = 120){

   //garante um tamanho mínimo para a string
   if($max_lenght <= 0) $max_lenght = 120;

   //remove espaços extras, e tags html
   $string = str_replace(array("  ", "   ", "\n"), " ", trim(strip_tags($string)));

   //verifica se string é maior que o tamanho definido
   if (strlen($string) > $max_lenght) {  

      $i = 0;
      //enquanto não encontrar um espaço em branco
      while (substr($string,$max_lenght,1) <> ' ' && ($max_lenght < strlen($string))){
           $i++; $max_lenght++;

           //se em 20 caracteres ainda não encontrou espaco em branco então retorna a string cortada mesmo..
           if($i == 20){
               $max_lenght -= 20;
               break;
           }
      };
   };

   if($max_lenght < 0) $max_lenght = 120;
   return substr($string,0,$max_lenght);  
}

function genKey($num){
    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $key = '';
    $max = strlen($str) - 1;
    for($i=0;$i<$num;$i++)
        $key .= substr($str,rand(0, $max),1);
    return $key;
}

function getInnerSubstring($string,$delimiterInit, $delimiterEnd){
    // "foo a foo" becomes: array(""," a ","")
    if($delimiterInit !== ""){
        $string = explode($delimiterInit, $string,2);
        array_shift($string);
        $string = array_shift($string);
    }
    
    if($delimiterEnd !== ""){
        $string = explode($delimiterEnd, $string,-2);
        $string = array_shift($string);
    }
    return trim($string);
}

function numbersOnly($str) {
    return preg_replace("/[^0-9]/", "", $str);
}

function numberExtenso($valor = 0, $real = false, $caixa_alta = false) {
    $valor = strval($valor);
    $valor = str_replace(",", ".", $valor);

    if ($real == true) {
        $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões",
            "quatrilhões");
    } else {
        $pos = strpos($valor, ".");
        $valor = substr($valor, 0, $pos);
        $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
    }
    $c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
        "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
    $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
        "sessenta", "setenta", "oitenta", "noventa");
    $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
        "dezesseis", "dezesete", "dezoito", "dezenove");
    $u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
        "sete", "oito", "nove");
    $z = 0;
    $rt = '';
    $valor = number_format($valor, 2, ".", ".");
    $inteiro = explode(".", $valor);
    $count_int = count($inteiro);
    for ($i = 0; $i < $count_int; $i++)
        for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
            $inteiro[$i] = "0" . $inteiro[$i];

    $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
    for ($i = 0; $i < count($inteiro); $i++) {
        $valor = $inteiro[$i];
        $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
        $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
        $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

        $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                $ru) ? " e " : "") . $ru;
        $t = count($inteiro) - 1 - $i;
        $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
        if ($valor == "000")
            $z++; elseif ($z > 0)
            $z--;
        if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
            $r .= (($z > 1) ? " de " : "") . $plural[$t];
        if ($r)
            $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                    ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? " e " : " e ") : " ") . $r;
    }
    if ($caixa_alta == true) {
        $rt = strtoupper($rt);
        $maiusculas = array("Á", "À", "Â", "Ã", "É", "Ê", "Í", "Ó", "Ô", "Õ", "Ú", "Û");
        $minusculas = array("á", "à", "â", "ã", "é", "ê", "í", "ó", "ô", "õ", "ú", "û");
        $count = count($maiusculas);
        for ($i = 0; $i < $count; $i++) {
            $rt = str_replace($minusculas[$i], $maiusculas[$i], $rt);
        }
    }
    return $rt;
}
/**
 * Ex: 3.200.000 = 3,200 milhões
 * @param type $value
 * @return type
 */

  function resumeNumber($value){
        $count = strlen($value);
        if($count >= 4 && $count < 7)$name = 'mil';
        elseif($count >= 7 && $count < 10)$name = 'milhões';
        elseif($count >= 10 && $count < 13)$name = 'bilhões';
        elseif($count >= 14 && $count < 17)$name = 'trilhões';
        elseif($count >= 17 && $count < 20)$name = 'quatrilhões';
        else{$name = '';}
        $value = number_format($value,0,',','.');
        $exp = explode('.', $value);
        $two = (isset($exp[1]))?','.$exp[1]:'';
        $str = $exp[0] . $two . ' ' . $name;
        return $str;
    }
