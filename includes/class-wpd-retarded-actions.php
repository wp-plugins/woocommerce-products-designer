<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-postponed-actions
 *
 * @author HL
 */
class wpd_retarded_actions
{
    public static $code=array();

    public static function display_code()
    {
        foreach(self::$code as $i=>$current_code)
        {
            echo $current_code;
            unset(self::$code[$i]);
        }
    }
}
