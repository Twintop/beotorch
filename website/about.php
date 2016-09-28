<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/database_connect.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/Repositories/UserRepository.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
 


$UserRepository = new UserRepository($mysqli, $session);
$User = $UserRepository->IsUserLoggedIn();

$pageTitle = "FAQ / About";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
        <h2>FAQ / About Beotorch</h2>
        
               
        <div class="panel panel-default" style="width: 320px; float: right; margin-top: -55px;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body" style="padding: 10px;">

			</div>
		</div>
        
        <div class="panel panel-default" style="float: left; width: calc(100% - 350px);">
			<div class="panel-heading panel-heading-about">What is Beotorch?</div>
			<div class="panel-body">
				<p>Beotorch is a website that allows you to simulate your <a href="http://www.worldofwarcraft.com/" target="_blank">World of Warcraft</a> character in a combat situation using <a href="http://simulationcraft.org/" target="_blank">SimulationCraft</a>.</p>
			</div>
		</div>
        <div class="panel panel-default" style="float: left; width: calc(100% - 350px);">
			<div class="panel-heading panel-heading-about">What is SimulationCraft?</div>
			<div class="panel-body">
				<p>From the <a href="https://github.com/simulationcraft/simc" target="_blank">SimulationCraft GitHub Page</a>:</p>
				<blockquote class="blockquote-about">
					<p>SimulationCraft is a tool to explore combat mechanics in the popular MMO RPG World of Warcraft (tm).</p>
					<p>It is a multi-player event driven simulator written in C++ that models player character damage-per-second in various raiding scenarios.</p>
					<p>Increasing class synergy and the prevalence of proc-based combat modifiers have eroded the accuracy of traditional calculators that rely upon closed-form approximations to model very complex mechanics. The goal of this simulator is to close the accuracy gap while maintaining a performance level high enough to calculate relative stat weights to aid gear selection.</p>
					<p>
SimulationCraft allows raid/party creation of arbitrary size, generating detailed charts and reports for both individual and raid performance.</p>
				</blockquote>
				<p>In short, SimulationCraft plays your character through a theoretical encounter thousands of times and reports back the average results.</p>
			</div>
		</div>
        <div class="panel panel-default" style="float: left; width: calc(100% - 350px);">
			<div class="panel-heading panel-heading-about">How does Beotorch work?</div>
			<div class="panel-body">
				<p>A user (you!) requests to have their character simulated. This request is placed in to a queue. When it is time for a queued character to be simulated, one of the processing servers grabs that character's information and starts chugging away. Once done, the processing server returns the result and sends the user an email (if they so choose) letting them know their simulation has completed. Easy peasy!</p>
			</div>
		</div>
		<div style="clear: both;"></div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">How long will it take for my character to simulate?</div>
			<div class="panel-body">
				<p>
					The answer to this question largely depends on a few factors:
					<ul>
						<li><b>Queue Position:</b> If there are many people infront of you in the queue, it could take several minutes for your character's simulation to begin.</li>
						<li><b>Iterations:</b> The number of times the simulation is run to generate results. Higher iterations = longer simulations = better results. (See more below)</li>
						<li><b>Fight Length:</b> How long each simulation runs for. Longer fights = longer simulations.</li>
						<li><b>Fight Type:</b> Some fight types have extra targets. This increases the processing time of each iteration. Patchwerk is the simplest (and fastest), while Beastlord is the most complicated (and slowest).</li>
						<li><b>Scaling Factors:</b> To generate scaling factors, the simulation needs to be run an additional time per stat you wish to get stat weights for. This will easily increase the simulation time by almost an order of magnitude in most cases. (See more below)
					</ul>
					Once it is your turn in the queue, for example:
					<ul>
						<li>Running a 5 minute duration 10,000 iteration Patchwerk simulation without scaling factors will take somewhere around 10-15 seconds to execute and report back the results.</li>
						<li>Running a 5 minute duration 10,000 iteration Beastlord simulation with scaling factors will take somewhere around 1.5 - 2 minutes to execute and report back the results.</li>
					</ul>					
				</p> 
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">What are 'Iterations'?</div>
			<div class="panel-body">
				<p>Iterations are the number of times SimulationCraft runs through a each fight in the simulation. In the simplest example, you can think of it like killing Patchwerk in Naxxramas over and over again and seeing an aggregate of all of those pulls.</p>
				<p>The more iterations you do, the better results you will have. Lower iteration counts mean there will be a much larger swing in returned results, and thus will be less accurate. Beotorch allows basic users up simulate their characters using up to 10,000 iterations; other account types have access to higher iterations.</p>
				<p>For general DPS checks, anything under 5,000 iterations is generally considered an unreliable result with 10,000 being considered accurate enough for most uses. For Scaling Factors (see below), 10,000 is the minimum recommended to get accurate and usable stat weights. Beotorch does not limit users to within these guidelines as minimums, though, as some users may want to generate many reports for different characters quickly.</p>
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">What are 'Scaling Factors' / 'Stat Weights'?</div>
			<div class="panel-body">
				<p>Scaling Factors (also called Stat Weights) are a way to gauge how valuable a specific stat is for your character. These values allow you to compare two pieces of gear side by side and know which should be better for you to equip.</p>
				<p>Scaling Factors in SimulationCraft are calculated by re-running a character's simulation with adjusted stat values and comparing the damage results with a baseline of your character's raw stats. This means, for example, if you are a caster you will have 7 additional full simulation runs (for Intellect, Spell Power, Crit, Haste, Mastery, Multistrike, Versatility, and possibly Speed, in addition to your 'base' simulation) to get your stat weights.</p>
				<p>On average, a simulation queued up to generate stat weights will take about 8-times as long as a pure DPS simulation.</p>
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">Does Beotorch support Tank or Healer simulations?</div>
			<div class="panel-body">
				<p><b>Short answer:</b> yes for Tanking simulations, no for Healing Simulations.</p>
				<p><b>Long answer:</b></p>
				<p>Most tanking specs are supported by SimulationCraft. As of version 0.3.0, Beotorch supports TMI Tanking Simulations in addition to DPS simulations for Tanking specs.</p>
				<p>Very few healing specs have good support within SimulationCraft. Some specs don't have any abilities implemented while others don't have proper APLs for healing. Many of these healing specs have limited (at best) suport for DPS simulations (specifically, Restoration Druids having virtually no support and Restoration Shamans being completely unsupported). Simulating healers is a tricky thing to do since healing is, by in large, more about effective heal choices and smart cooldown usage given the current situation rather than trying to always do maximum HPS. There are currently no plans to support healing simulations in Beotorch.</p>
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">Who created Beotorch?</div>
			<div class="panel-body">
				<p>Hi there, I'm <a href="http://us.battle.net/wow/en/character/Stormrage/Twintop/advanced" target="_blank">Twintop</a>. I've been an avid World of Warcraft player since the open beta of Vanilla back in 2004. I have played since then minus a break from Naxx40 through the launch of Wrath of the Lich King, missing all of Burning Crusade in the process. Originally a main spec Holy Priest, since Cataclysm I have been playing Shadow as my main spec.</p>
				<p>I help maintain the Priest Module (specifically the Shadow parts) of SimulationCraft and dabble with other enhancements to the project. I make heavy use of SimulationCraft to help with Shadow Priest theorycrafting <a href="http://warcraft.twintop-tahoe.com/resources/2015/06_june/20150619-mythic/" target="_blank">(example from HFC, totaling over <b>500 million total iterations</b>)</a>, specifically in helping to determine stat weights for use in my <a href="http://howtopriest.com/viewtopic.php?f=19&t=7637" target="_blank">Best in Slot lists</a>. In addition to theorycrafting Shadow, I am a founding Admin of <a href="http://howtopriest.com/" target="_blank">HowToPriest</a>.</p>				
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">Why create Beotorch?</div>
			<div class="panel-body">
				<p>There are many reasons why I decided to create Beotorch. A few of the largest include:</p>
				<ul>
					<li>There is a barrier to entry in using SimulationCraft successfully. These barriers vary from user to user, but usually include some of the following: not understanding how to use SimulationCraft, SimulationCraft not being supported on their OS anymore (RIP 32bit Windows users), having an outdated version of SimulationCraft and not knowing it, lacking processing power to do longer simulations (i.e.: stat weights), or not being able to easily share the results of their simulations. Beotorch alleviates all of these concerns. <b>This isn't to say that people will not find value from running SimulationCraft on their own machines</b>, rather that Beotorch makes it easier to accomplish the most common use cases of SimulationCraft.</li>
					<li>I have a lot of extra processing power that is sitting around unused most of the time.</li>
					<li>I do massive simulation runs for my own theorycrafting. Up until this point it has largely been done by hand or with kludgy scripts that were prone to breakage. Beotorch gives me a framework to be able to queue up my own simulations fairly easily, there by reducing the overhead required for me to do my massive simualtion runs.</li>
					<li>Finally, related to the last two points, I want to try and make this simulating platform available to other class theorycrafters so that they can do more detailed analysis of their own. This isn't in place yet, but hopefully Soon<sup>TM</sup>.</li>
				</ul>
			</div>
		</div>
        <div class="panel panel-default">
			<div class="panel-heading panel-heading-about">Where did the name "Beotorch" come from?</div>
			<div class="panel-body">
				<p>Beotorch is a <a href="https://en.wikipedia.org/wiki/Portmanteau" target="_blank">portmanteau</a> of "Blowtorch" and "Beowulf {Cluster}". Allow me to explain...</p>
				<p>In November 2012, I decided that, to improve my own theorycrafting capabilities with SimulationCraft, I needed to build a dedicated computer for running simulations. This tower ended up housing an AMD FX-8350 8-Core Processor. Needless to say, when this machine was running maxed out for hours (or days) on end it put out quite a lot of heat, almost as if someone was pointing a Blowtorch in your direction. The name stuck, and that machine has been known affectionately as Blowtorch within the Priest and Shadow communities ever since.</p>				
				<p>In December 2015, in preperation for Legion, I built an additional (and far more powerful -- dual 8-Core {16 Thread} CPUs) computer for executing simulations. I then networked the original Blowtorch and this additional computer together as a <a href="https://en.wikipedia.org/wiki/Beowulf_cluster" target="_blank">beowulf cluster</a>. Beotorch executes simulations across this cluster.</p>
			</div>
		</div>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
