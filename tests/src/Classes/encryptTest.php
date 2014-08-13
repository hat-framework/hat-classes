<?php

require_once(dirname(__FILE__)."/../../../initTests.php");

class encryptTest extends PHPUnit_Framework_TestCase{
    
    private $testkey       = "123AeqdCC$%&\"";
    
    //verifica se a encriptação e a decriptação estão corretas
    public function testEncripty(){
        $key =  \classes\Classes\crypt::decrypt( \classes\Classes\crypt::encrypt($this->testkey));
        $this->assertEquals($key, $this->testkey, "Erro ao aplicar a criptografia e a descriptografia simultaneamente \n
            o resultado gerado é diferente do que era esperado! ");
    }
    
    public function testWithGeneratedKeys(){
        $key =  \classes\Classes\crypt::gen_base64_key();
        $iv  =  \classes\Classes\crypt::gen_base64_ivector();
        
        $word =  \classes\Classes\crypt::decrypt( \classes\Classes\crypt::encrypt($this->testkey, $key, $iv), $key, $iv);
        $this->assertEquals($word, $this->testkey, "Erro ao aplicar a criptografia e a descriptografia simultaneamente \n
            o resultado gerado é diferente do que era esperado para o caso das chaves geradas no momento do teste! ");
    }
}

?>