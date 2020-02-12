<?php
class Config{
    const APP_VERSION = '0.006'; //Version de la Aplicacion
    const APP_DATE_VERSION = '12-02-2020';
    const APP_DEVELOPER= 'Ing. Manuel Ramirez (ManuelVR461@gmail.com)';
    
    const APP="ObjectMVCX"; //Nombre del Proyecto
    
    const DB_NAME = "db_objectmvcx";
    const USER = "root";
    const PASS = "";
    
    const SERVER ="localhost";
    const CHARSET = "utf8";
    const URL = "http://".self::SERVER."/".self::APP."/";

    const SGBD = "mysql:host=".self::SERVER.";dbname=".self::DB_NAME.";charset=".self::CHARSET;
    const METHOD="AES-256-CBC";
    
    public function __construct(){
        date_default_timezone_set('America/Santiago');
    }
    
}