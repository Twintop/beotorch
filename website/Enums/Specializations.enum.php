<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Enums/BasicEnum.class.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Enum of Specializations
 *
 * @author david
 */
abstract class Specializations extends BasicEnum {
    const Arcane = 62;
    const Fire = 63;
    const FrostMage = 64;
    const HolyPaladin = 65;
    const ProtectionPaladin = 66;
    const Retribution = 70;
    const Arms = 71;
    const Fury = 72;
    const ProtectionWarrior = 73;
    const Balance = 102;
    const Feral = 103;
    const Guardian = 104;
    const RestorationDruid = 105;
    const Blood = 250;
    const FrostDeathKnight = 251;
    const Unholy = 252;
    const BeastMastery = 253;
    const Marksmanship = 254;
    const Survival = 255;
    const Discipline = 256;
    const HolyPriest = 257;
    const Shadow = 258;
    const Assassination = 259;
    const Outlaw = 260;
    /*const Combat = 260;*/
    const Subtlety = 261;
    const Elemental = 262;
    const Enhancement = 263;
    const RestorationShaman = 264;
    const Affliction = 265;
    const Demonology = 266;
    const Destruction = 267;
    const Brewmaster = 268;
    const Windwalker = 269;
    const Mistweaver = 270;
    const Havoc = 577;
    const Vengeance = 581;
    const None = 9999;
}

?>