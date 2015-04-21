<?php

namespace classes\Interfaces;
interface WebMasterMail{
    public function __construct($dados);
    public function getAlertMailTitle();
    public function getAlertMailMessage();
}
