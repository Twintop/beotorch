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
abstract class Races extends BasicEnum {
    const UNKNOWN = 0;
    const Human = 1;
    const Orc = 2;
    const Dwarf = 3;
    const NightElf = 4;
    const Undead = 5;
    const Tauren = 6;
    const Gnome = 7;
    const Troll = 8;
    const Goblin = 9;
    const BloodElf = 10;
    const Draenei = 11;
    const Worgen = 22;
    const Pandaren = 24;
    const PandarenAlliance = 25;
    const PandarenHorde = 26;     
}


?>