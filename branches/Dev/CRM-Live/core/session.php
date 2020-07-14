<?php
/*
*
*
* This class will allow control of the super global $_SESSION within PHP
* It's possible to import this class within websites which are already working with session variables.
* Currently the functionality provides:
* Invoking a new session
* Getting the current session stats
* Setting a session key/value
* Getting the session value
* displaying the session array
*
*
*
*/
class Session {
    public $Session_Started = false;
    public function __construct(){
        /*
        *
        * If session is already invoked, the constructor
        * will switch the protected variable to true, and the session_start function will not be called
        * If session is not already invoked, the protected variable will remain false and provide the ability
        * To invoke a session_start() by calling $Class->init();
        */
        if (session_status() === 1){
            $this->Session_Started = false;
            return false;
        }
        return true;
    }
    public function init(){
        /*
        Invoke a session
        */

        if ($this->Session_Started === false){
            @session_start();
            $this->Session_Started = true;
            return true;
        }
        return false;
    }
    public function Status_Session(){
        /*
        Getting the current session status, and return readable information on the current status
        */
        $Return_Switch = false;
        switch (session_status()){
            case PHP_SESSION_DISABLED:
                $Return_Switch = "Session Disabled";
                break;
            case PHP_SESSION_NONE:
                $Return_Switch = "Session Enabled, None exist";
                break;
            case PHP_SESSION_ACTIVE:
                $Return_Switch = "Sessions Enabled, Sessions Exist";
                break;
        }
        return $Return_Switch;
    }
    public function Set ($Key = false, $Value){

        /*
        Set a value within the $_SESSION global
        this function will return true, if a sucessfull addition has been made
        and return false if a problem is enountered
        */
        if ($this->Session_Started === true){
            $_SESSION[$Key] = $Value;
            return true;
        }
        return false;
    }
    public function Get ($Key){
        /*
        Invoking this function will return the current value of the session key, else return false is an error is encountered
        */
        if (isset($_SESSION[$Key])){
            return $_SESSION[$Key];
        }
        return false;
    }
    public function Display(){
        /*
        If session is started, this function will return a readable and formatted array
        */
        if ($this->Session_Started === true){
            return $_SESSION;
        }
        return false;
    }


} // Close class