<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Enums/BasicEnum.class.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Enum of Classes
 *
 * @author david
 */
abstract class Classes extends BasicEnum {
    const UNKNOWN = 0;
    const Warrior = 1;
    const Paladin = 2;
    const Hunter = 3;
    const Rogue = 4;
    const Priest = 5;
    const DeathKnight = 6;
    const Shaman = 7;
    const Mage = 8;
    const Warlock = 9;
    const Monk = 10;
    const Druid = 11;
    const DemonHunter = 12;        
}

?>