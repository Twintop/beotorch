<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Beotorch<?php
        if ($pageTitle)
        {
	        echo " - " . $pageTitle;
	    }
	    ?></title>
        <meta name=viewport content="width=device-width, initial-scale=1">
        <?php
        if ($_GET['jt'] != 1 && SECURE == true)
        {
        ?>
        <link type="text/css" rel="stylesheet" href="/min/g=css?v=2016_08_07" />
        <?php
        }
        else
        {
        ?>
        <link type="text/css" rel="stylesheet" href="/min/g=cssdev?v=2016_08_07" />
        <link type="text/css" rel="stylesheet" href="/styles/main.css?v=2016_08_07" />
        <?php
        }
        ?>        
    </head>
    <body>
        <?php
        if ($_GET['jt'] != 1 && SECURE == true)
        {
        ?>
        <script type="text/JavaScript" src="/min/g=js?v=2016_08_07"></script>
        <?php
        }
        else
        {
        ?>
        <script type="text/JavaScript" src="/min/g=jsdev?v=2016_08_07"></script>
        <script type="text/JavaScript" src="/scripts/sha512.js?v=2016_08_07"></script>
        <script type="text/JavaScript" src="/scripts/forms.js?v=2016_08_07"></script>
        <script type="text/JavaScript" src="/scripts/core.js?v=2016_08_07"></script>
        <?php
        }
        ?>
		<nav class="navbar navbar-fixed-top navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
                    <button type="button" style="float: left;" class="collapsed navbar-toggle" data-toggle="collapse" data-target="#beotorch-navbar-collapse" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
					<a class="navbar-brand" href="<?php echo SITE_ADDRESS; ?>">Beotorch</a>
				</div>
                <div class="collapse navbar-collapse" id="beotorch-navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/index.php" || $_SERVER["SCRIPT_NAME"] == "/") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>">Home</a></li>
                        <?php
                        if ($User)
                        {
                        ?>	 
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/yoursimulations.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>yoursimulations.php">Your Simulations</a></li>
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/newsimulation.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>newsimulation.php">Queue New Simulation</a></li> 				
                        <?php
                        }
                        else
                        {
                        ?>

                        <?php
                        }
                        ?>

                        <li class="dropdown" <?php if ($_SERVER["SCRIPT_NAME"] == "/simulation.php") { echo " class=\"active\""; } ?>>
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">Simulation Browser<span class="caret"></a></span>
                            <ul class="dropdown-menu multi-level navlink">
                                <li><a href="<?php echo SITE_ADDRESS; ?>simulations.php">All Simulations</a></li>		
                                <li><a href="<?php echo SITE_ADDRESS; ?>batchlist.php">Batched Simulations</a></li>							
                                <li class="divider"></li>
                                <li class="dropdown-submenu">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">By Class or Spec</a>
                                    <ul class="dropdown-menu navlink">
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=6" class="nav dropdown-toggle deathknightColor menuClassColor">Death Knight</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=250" class="dropdown-toggle deathknightColor menuClassColor">Blood</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=251" class="dropdown-toggle deathknightColor menuClassColor">Frost</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=252" class="dropdown-toggle deathknightColor menuClassColor">Unholy</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=12" class="nav dropdown-toggle demonhunterColor menuClassColor">Demon Hunter</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=577" class="dropdown-toggle demonhunterColor menuClassColor">Havoc</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=581" class="dropdown-toggle demonhunterColor menuClassColor">Vengeance</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=11" class="dropdown-toggle druidColor menuClassColor">Druid</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=102" class="dropdown-toggle druidColor menuClassColor">Balance</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=103" class="dropdown-toggle druidColor menuClassColor">Feral</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=104" class="dropdown-toggle druidColor menuClassColor">Guardian</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=105" class="dropdown-toggle druidColor menuClassColor">Restoration</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=3" class="dropdown-toggle hunterColor menuClassColor">Hunter</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=253" class="dropdown-toggle hunterColor menuClassColor">Beast Mastery</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=254" class="dropdown-toggle hunterColor menuClassColor">Marksmanship</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=255" class="dropdown-toggle hunterColor menuClassColor">Survival</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=8" class="dropdown-toggle mageColor menuClassColor">Mage</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=62" class="dropdown-toggle mageColor menuClassColor">Arcane</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=63" class="dropdown-toggle mageColor menuClassColor">Fire</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=64" class="dropdown-toggle mageColor menuClassColor">Frost</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=10" class="dropdown-toggle monkColor menuClassColor">Monk</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=268" class="dropdown-toggle monkColor menuClassColor">Brewmaster</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=270" class="dropdown-toggle monkColor menuClassColor">Mistweaver</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=269" class="dropdown-toggle monkColor menuClassColor">Windwalker</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=2" class="dropdown-toggle paladinColor menuClassColor">Paladin</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=65" class="dropdown-toggle paladinColor menuClassColor">Holy</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=66" class="dropdown-toggle paladinColor menuClassColor">Protection</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=70" class="dropdown-toggle paladinColor menuClassColor">Retribution</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=5" class="dropdown-toggle priestColor menuClassColor">Priest</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=256" class="dropdown-toggle priestColor menuClassColor">Discipline</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=257" class="dropdown-toggle priestColor menuClassColor">Holy</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=258" class="dropdown-toggle priestColor menuClassColor">Shadow</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=4" class="dropdown-toggle rogueColor menuClassColor">Rogue</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=259" class="dropdown-toggle rogueColor menuClassColor">Assassination</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=260" class="dropdown-toggle rogueColor menuClassColor">Combat</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=261" class="dropdown-toggle rogueColor menuClassColor">Subtlety</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=7" class="dropdown-toggle shamanColor menuClassColor">Shaman</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=262" class="dropdown-toggle shamanColor menuClassColor">Elemental</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=263" class="dropdown-toggle shamanColor menuClassColor">Enhancement</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=264" class="dropdown-toggle shamanColor menuClassColor">Restoration</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=9" class="dropdown-toggle warlockColor menuClassColor">Warlock</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=265" class="dropdown-toggle warlockColor menuClassColor">Affliction</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=266" class="dropdown-toggle warlockColor menuClassColor">Demonology</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=267" class="dropdown-toggle warlockColor menuClassColor">Destruction</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="dropdown-submenu">
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?c=1" class="dropdown-toggle warriorColor menuClassColor">Warrior</a>
                                            <ul class="dropdown-menu navlink">
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=71" class="dropdown-toggle warriorColor menuClassColor">Arms</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=72" class="dropdown-toggle warriorColor menuClassColor">Fury</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo SITE_ADDRESS; ?>simulations.php?s=73" class="dropdown-toggle warriorColor menuClassColor">Protection</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>	
                                <li class="dropdown-submenu">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">By Fight Type</a>
                                    <ul class="dropdown-menu navlink">
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=1" class="dropdown-toggle">Patchwerk</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=2" class="dropdown-toggle">HelterSkelter</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=3" class="dropdown-toggle">Light Movement</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=4" class="dropdown-toggle">Heavy Movement</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=5" class="dropdown-toggle">Ultraxion</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=6" class="dropdown-toggle">Hectic Add Cleave</a>
                                        </li>
                                        <li>
                                            <a href="<?php echo SITE_ADDRESS; ?>simulations.php?ft=7" class="dropdown-toggle">Beastlord</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>					
                        </li> 
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/about.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>about.php">FAQ / About</a></li>
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/contact.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>contact.php">Contact</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        if ($User)
                        {
                        ?>		
                        <li class="dropdown<?php if ($_SERVER["SCRIPT_NAME"] == "/account.php" || $_SERVER["SCRIPT_NAME"] == "/accountpassword.php") { echo " active"; } ?>">
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Welcome, <?php echo htmlentities($_SESSION['email']); ?>!<span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <?php

                                if ($User->UserLevelId == 9)
                                {
                                ?>
                                <li><a href="<?php echo SITE_ADDRESS; ?>view_user_sims.php">View User Sims</a></li>							
                                <li class="divider"></li>
                                <?php
                                }
                                ?>
                                <li><a href="<?php echo SITE_ADDRESS; ?>account.php">Account Settings</a></li>
                                <li><a href="<?php echo SITE_ADDRESS; ?>accountpassword.php">Change Password</a></li>
                                <li class="divider"></li>
                                <li><a href="<?php echo SITE_ADDRESS; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                        <?php
                        }
                        else
                        {
                        ?>
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/register.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>register.php"><span class="glyphicon glyphicon-user"></span> Register</a></li>
                        <li<?php if ($_SERVER["SCRIPT_NAME"] == "/login.php") { echo " class=\"active\""; } ?>><a href="<?php echo SITE_ADDRESS; ?>login.php"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
			</div>
		</nav>
<?php
/*
<div class="alert alert-short alert-info">Two Beotorch processing nodes are currently offline with an expected reactivation date of <b>Wednesday September 14th, 2016</b>. Queue and simulation times may be longer than usual until then.</div>

if ($_SERVER["REMOTE_ADDR"] != "47.33.5.184")
{
?>
<div class="alert alert-short alert-info">We are currently publishing an update to Beotorch. Hang tight for a few while we get everything updated!<br/>Thanks!</br>--Twintop</div>
<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
	exit();
}/**/
?>
