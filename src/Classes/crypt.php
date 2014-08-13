<?php
namespace classes\Classes;
class crypt extends Object{

    private static $base64key     = Crypty_base64key;
    private static $base64ivector = Crypty_base64ivector;
    private static $mode          = MCRYPT_MODE_CBC;
    private static $type          = MCRYPT_RIJNDAEL_256;
    private static $suggar        = '__secret__';
    
    public function __construct() {}
    
    /**
    * Criptografa uma string e prepara para que ela seja utilizada em inputs e urls
    * @param string $text <p> A string a ser criptografada </p>
    * @param string $key <p> Chave de segurança gerada pela própria classe, se for vazia será utilizada a chave geral do sistema</p>
    * @param string $iv <p> A chave de ivector gerada pela própria classe, se for vazia será utilizada a chave geral do sistema </p>
    * @return A string criptografada preparada
    */
    public static function encrypt_camp($text, $key = "", $iv = "") {
        return urlencode( \classes\Classes\crypt::encrypt($text, $key, $iv));
    }
    
    /**
    * Desriptografa uma string preparada para que ela seja utilizada em inputs e urls
    * @param string $text <p> A string a ser descriptografada </p>
    * @param string $key <p> Chave de segurança gerada pela própria classe, se for vazia será utilizada a chave geral do sistema</p>
    * @param string $iv <p> A chave de ivector gerada pela própria classe, se for vazia será utilizada a chave geral do sistema </p>
    * @return A string descriptografada preparada
    */
    public static function decrypt_camp($text, $key = "", $iv = "") {
        return  \classes\Classes\crypt::decrypt(urldecode($text), $key, $iv);
    }
    
   /**
    * Criptografa uma string
    * @param string $text <p> A string a ser criptografada </p>
    * @param string $key <p> Chave de segurança gerada pela própria classe, se for vazia será utilizada a chave geral do sistema</p>
    * @param string $iv <p> A chave de ivector gerada pela própria classe, se for vazia será utilizada a chave geral do sistema </p>
    * @return A string criptografada
    */
    public static function encrypt($text, $key = "", $iv = "") {
        $key = ($key == "")? self::$base64key    : $key;
        $iv  = ($iv  == "")? self::$base64ivector: $iv;
        
        $v = (function_exists('bzcompress'))?bzcompress(trim($text)):(trim($text).self::$suggar);
        return base64_encode(
               mcrypt_encrypt(
                    self::$type, 
                    base64_decode($key),
                    $v,
                    self::$mode, 
                    base64_decode($iv)
               )
        );
    }

    /**
    * Descriptografa uma string
    * @param string $text <p> A string a ser descriptografada </p>
    * @param string $key <p> Chave de segurança gerada pela própria classe, se for vazia será utilizada a chave geral do sistema</p>
    * @param string $iv <p> A chave de ivector gerada pela própria classe, se for vazia será utilizada a chave geral do sistema </p>
    * @return Uma string descriptografada caso as duas chaves possam descriptografa-la. A string de entrada caso contrário 
    */
    public static function decrypt($text, $key = "", $iv = "") {
        $key = ($key == "")? self::$base64key    : $key;
        $iv  = ($iv  == "")? self::$base64ivector: $iv;
        $v    = trim(mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256, 
                base64_decode($key),
                base64_decode($text), 
                MCRYPT_MODE_CBC, 
                base64_decode($iv))
        );
        //echo $v . "<br/>";
        if(!function_exists('bzdecompress')){
            $var = (strstr($v, self::$suggar) === false)? $text: 
            substr($v, 0, (strlen($v) - strlen(self::$suggar)));
        }else {
            $var = bzdecompress($v);
            return ($var == "-5")? $text:$var;
        }
        return $var;
        
    }
    
   /**
    * Gera uma nova chave de criptografia
    * @param int $bytes <p> Tamanho em bytes da chave a ser usada </p>
    * @param string $strong <p> Determina se o algoritmo strong será usado ou não</p>
    * @return Uma nova chave capaz de criptografar strings em conjunto com a chave ivector
    */
    public static function gen_base64_key($bytes = 24, &$strong = null) {
        return base64_encode(openssl_random_pseudo_bytes($bytes, $strong));
    }
    
   /**
    * Gera uma nova chave de criptografia
    * @return Uma nova chave ivector capaz de criptografar strings em conjunto com a chave comum
    */
    public static function gen_base64_ivector() {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size);
        return base64_encode($iv);
    }
    
}

?>