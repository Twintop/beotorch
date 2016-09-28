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

$pageTitle = "Changelog";
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/header.inc.php';
?>
        <h2>Changelog</h2>
        
        <div class="panel panel-default" style="width: calc(100% - 350px); float: left;">
			<div class="panel-body">
        <h3>v0.6.5 - 2016-08-15</h3>
        <ul>
            <li>Website
                <ul>
                    <li>Add Batched simulations reports to the UI. Who can create a batched report at this point is highly restricted; may open to a larger audience in the future.</li>
                </ul>
            </li>
            <li>Backend
                <ul>
                    <li>Modify the order parameters are used when executing custom simulations to allow for more custom profile options/overrides to be used.</li>
                </ul>
            </li>            
        </ul>
        <h3>v0.6.4 - 2016-08-07</h3>
        <ul>
            <li>Website
                <ul>
                    <li>Add Demon Hunters to the Simulation Browser menu.</li>
                </ul>
            </li>
            <li>Simulation Details
                <ul>
                    <li>Make the results table and stat weight tables sortable.</li>
                </ul>
            </li>            
        </ul>
        <h3>v0.6.3 - 2016-07-31</h3>
        <ul>
            <li>Website
                <ul>
                    <li>Many library updates:
                        <ul>
                            <li>Boostrap-Slider to v9.1.1</li>
                            <li>Chosen to v1.6.1</li>
                            <li>jQuery to v2.2.4</li>
                            <li>jQuery Datatables to v1.10.12</li>
                            <li>JSRender to v0.9.79</li>
                            <li>Moment to v2.14.1</li>
                            <li>Moment Timezone to v0.5.4-2016d</li>
                        </ul>
                    </li>
                    <li>Start using <a href="https://github.com/mrclay/minify" target="_blank">Minify</a> for serving up CSS and JavaScript.</li>
                    <li>Enable GZip compression.</li>
                    <li>Reimplement sessions and logins to avoid the constant logging out issues. <a href="https://github.com/Twintop/beotorch-issues/issues/21" target="_blank">(Fixes issue #21)</a></li>
                    <li>Specify a viewport to help mobile devices render the site properly.</li>
                </ul>
            </li>
            <li>New Simulation
                <ul>
                    <li>The currently selected specialization should now always be loaded when selecting a character from the Armory. <a href="https://github.com/Twintop/beotorch-issues/issues/20" target="_blank">(Fixes issue #20)</a></li>
                </ul>
            </li>
            <li>Simulations Table
                <ul>
                    <li>Fix screen resizing not also resizing the size of the table.</li>
                </ul>
            </li>
            <li>Backend
                <ul>
                    <li>Fix bug where simulation errors would not always be properly reported back up to the API from nodes.</li>
                </ul>
            </li>            
        </ul>
        <h3>v0.6.2 - 2016-07-21</h3>
        <ul>
            <li>Backend
                <ul>
                    <li>Fixed a bug where some talents wouldn't show up in the right tier for selection.</li>
                    <li>Fixed a bug where, for multiactor simulations, the highest DPS/TMI would not always be selected. <a href="https://github.com/Twintop/beotorch-issues/issues/18" target="_blank">(Fixes issue #18)</a></li>
                    <li>Improved Simulation listing queries. Site should be more responsive in general now.</li>
                </ul>
            </li>
        </ul>
        <h3>v0.6.1 - 2016-07-19</h3>
        <ul>
            <li>General
                <ul>
                    <li>Site prepared for the release of 7.0.3 on July 19th, 2016. (Mostly.)
                        <ul>
                            <li>Simulations done during Warlords of Draenor are now tagged with the <span class="label label-as-badge label-wod">WoD</span> badge.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>Backend
                <ul>
                    <li>Update talents for 7.0.3.</li>
                    <li>Improve auto-updating mechanism of SimulationCraft versions for computational nodes.</li>
                    <li>Fix a bug that would cause characters with extra accents in their names to not be logged separately.</li>
                </ul>
            </li>
            <li>
                Simulations Table / Simulation Details / Your Simulations
                <ul>
                    <li>Simulation Cleanup and Control Options <span class="label label-danger">New</span>
                        <ul>
                            <li>Users with appropriate permissions may now toggle if a simulation is publicly listed or hidden after the simulation is queued.</li>
                            <li>Users may now archive a specific simulation's HTML report. This is useful for users who may have reached their maximum active reports quota but don't want to have their oldest reports automatically archive.</li>
                            <li>Users may now archive away an entire Simulation. Doing so will remove it from the list of simulations under "Your Simulations" but the simulation will still be accessibly via the Simulation Browser or direct link. Archiving a Simulation also archives the HTML report. <a href="https://github.com/Twintop/beotorch-issues/issues/12" target="_blank">(Fixes issue #12)</a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>New Simulation
                <ul>
                    <li>For the time being Beotorch only supports simulating your currently active spec. Being able to choose any of your class's specs to simulate is coming Soon&trade;!</li>
                </ul>
            </li>
		    <li>Simulation Details
		    	<ul>
		    		<li>Fix a bug where the simulation name wouldn't be shown for Custom Profile Simulations.</li>
		    	</ul>
        	</li>
            <li>Accounts
                <ul>
                    <li>Show whether a user has access to queue Custom Profile Simulations.</li>
                </ul>
            </li>
        </ul>
        <h3>v0.6.0 - 2016-07-06</h3>
        <ul>
            <li>New Simulation
                <ul>
                    <li>Beotorch now supports Custom Simulation Profiles! <span class="label label-danger">New</span>
                        <ul>
                            <li><b>This is definitely for advanced or power users.</b></li>
                            <li>The main purpose of this feature is to grant Theorycrafters access to more computational resources when they would like to run a large amount of simulations. This includes myself for Shadow Priest simulations.</li>
                            <li>Users with proper access can queue up simulations with fully customizable profiles, just line within SimulationCraft.</li>
                            <li>This allows users to run raid sims with different classes and specs defined, use custom APLs, customize gear that is being used, control extra add spawns, and adjust other advanced SimulationCraft parameters.</li>
                            <li>Certain parameters are still controlled by Beotorch: Fight Type, Boss Count, Iterations, Fight Length, Fight Variance, and whether to Calculate Scale Factors.</li>
                            <li>Actors, DPS/TMI/etc., talents** and Stat Weights are automatically pulled from the results.
                                <br /><span style="font-size: smaller; font-style: italic;">**=Battle.net and SimulationCraft talent formats only at this time.</span></li>
                        </ul>
                    </li>
                    <li>Support for different SimulationCraft versions <span class="label label-danger">New</span>
                        <ul>
                            <li>Currently only usable in conjunction with Custom Simulation Profiles.</li>
                            <li>Live (6.2.4) and Beta (7.0.3) supported.</li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li>Simulations Table
                <ul>
                    <li>Denote whether or not a simulation used a Custom Simulation Profile.</li>
                </ul>
            </li>
            <li>Simulations Details
                <ul>
                    <li>If a Custom Simulation Profile exists, show it in its own section.</li>
                </ul>
            </li>
            <li>API
                <ul>
                    <li>When requesting a new work item the node will only be given work items for the appropriate SimulationCraft versions it has available.</li>
                    <li>When requesting a new work item the node will only be given Custom Simulation Profiles if they have been assigned to process them.</li>
                </ul>
            </li>
		    <li>Backend
		    	<ul>
		    		<li>Add capability to use different SimulationCraft versions for work items.</li>
                    <li>Add capability to execute Custom Simulation Profiles</li>
		    	</ul>
        	</li>
        </ul>	
        <h3>v0.5.1 - 2016-06-11</h3>
        <ul>
            <li>Processing Nodes
                <ul>
                    <li>Thanks to a friend of mine, budd/James, Beotorch now has a new (and first external) processing node to help with simulation queues (particularly when 7.0 and Legion is released)! Cheers and thanks to him!</li>
                    <li>If you have some spare CPU cycles and would like to help out by hosting a processing node, <a href="<?php echo SITE_ADDRESS; ?>contact.php" target="_blank">please contact us</a>!</li>
                </ul>
            </li>
            <li>New Simulation
                <ul>
                    <li>The simulation queue cost of each sim is now dependant on the number of iterations selected, in addition to talent combinations, bosses, and if you choose to have stat weights.
                        <ul>
                            <li>Iterations factor in at a rate of 1 queue slot per 10,000 iterations, with the total result rounded up to the nearest integer (minimum 1).
                                <ul>
                                    <li>Example: 3 talent combinations with 2 bosses and stat weights @ 7500 iterations = 3 * 2 * 5 * (7500 / 10000) = 22.5 => 23 free queue slots to queue.</li>
                                    <li>Raw formula, for those curious:<br /><pre>MAX(1, CEIL(TALENT_COMBOS * BOSS_COUNT * (1 + (IS_SCALE_FACTORS * 4)) * (ITERATIONS / 10000))) = COST</pre></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>Users with appropriate access can now choose to hide a simulation from the main listing and searches on the site. Anyone who has a direct link to the Simulation Details page for this simulation can still access the results. <a href="https://github.com/Twintop/beotorch-issues/issues/13" target="_blank">(Fixes issue #13)</a></li>
                </ul>
            </li>
            <li>Simulations Table
                <ul>
                    <li>Denote if the simulation is Hidden from other users.</li>
                </ul>
            </li>
            <li>API
                <ul>
                    <li>When requesting a new work item the number of threads available on the node can be supplied. This allows the API to select the most appropriate work item to respond with, based on resources available to the node at the time of the request. This should help keep less powerful nodes from being assigned work items that are better suited for a node with more resources available and tying it up for an extended period of time when not needed.</li>
                    <li>Example: A node with 2 threads available will prefer to get a work item that does not have stat scaling or many actors unless it has been sitting in the queue for "a while".</li>
                </ul>
            </li>
		    <li>Backend
		    	<ul>
		    		<li>Move processing nodes over to use a better configuration system.</li>
                    <li>Nodes now can report back the number of available threads for simming to the API.</li>
		    	</ul>
        	</li>
        </ul>			
        <h3>v0.5.0 - 2016-05-21</h3>
        <ul>
		    <li>General Site
		    	<ul>
                    <li>Begin leveraging <a href="https://github.com/BorisMoore/jsrender" target="_blank">JsRender</a> for parts of the site.</li>
                    <li>We have a <a href="https://discordapp.com/" target="_blank">Discord</a> server!
                        <ul>
                            <li><a href='https://discord.gg/0zVQ3V9e7OTH13RG' target='_blank'>Instant Invite: https://discord.gg/0zVQ3V9e7OTH13RG</a></li>
                            <li>Feel free to join us to ask questions, get support, suggest features, find out about SimulationCraft, and just hang out.</li>
                            <li>Add Discord link to the website footer.</li>
                        </ul>
		    	</ul>
        	</li>
            <li>New Simulation
                <ul>
                    <li>Users may now execute talent comparison (multi "actor") simulations. <span class="label label-danger">New</span></li>
                    <li>This will simulate a specified character multiple times within a single simulation queue, each with a different set of talents.
                        <ul>
                            <li>Each actor has a queue cost of 1 and follows the same calculations for queue cost as before.
                                <ul>
                                    <li>Example: 3 talent combinations with 2 bosses and stat weights = 3 * 2 * 5 = 30 free queue slots to queue.</li>
                                </ul>
                            </li>
                            <li>Duplicate talentsets selected for comparison will be removed <b>after</b> you submit your character to the queue.</li>
                            <li>The number of actors you can have per sim depends on your account level.</li>
                        </ul>
                    </li>
                    <li>Fix a bug where clicking on the Fight Length or Fight Length Variance position would not update the time range preview.</li>                        
                </ul>
            </li>
            <li>Simulations Table
                <ul>
                    <li>Alter layout of the final column significantly to reduce clutter.
                        <ul>
                            <li>This column now displays DPS for DPS sims and TMI/DPS/DTPS/HPS(APS).</li>
                            <li>For multiactor simulations, show these values for the highest DPS (for DPS) / TMI (for Tank) actors in the simulation.</li>
                            <li>This change should also better accommodate mobile and small width resolution users.</li>
                        </ul>
                    </li>
                    <li>If the simulation is multiactor, state how many talentsets are used in the first column rather than show the talents used.</li>
                </ul>
            </li>
            <li>Simulation Details
                <ul>
                    <li>Remove the 4th column that previously housed DPS/TMI/DTPS/HPS(APS) results and the the Scaling Factors results. This has moved to the new Simulation Results section.</li>
                    <li>Don't list the talents used under the character info section. This has moved to the new Simulation Results section.</li>
                    <li>Simulation Results section <span class="label label-danger">New</span>
                        <ul>
                            <li>This section shows each talentset (actor) a user selected when executing the simulation including talents and relevant metric results.</li>
                            <li>For Stat Weights, separate the base weights from the Normalized values.</li>
                            <li>Move DPS/TMI/DTPS/HPS(APS), talents, and links to talent calculators to here. </li>
                        </ul>
                    </li>
                </ul>
            </li>
        	<li>User Accounts
		    	<ul>
		    		<li>Show users how many talent combinations they can have in a simulation queue.</li>
		    	</ul>
        	</li>   
            <li>Backend
                <ul>
                    <li>Add support for multiactor simulations.</li>
                    <li>Update completed simulation email to support multiactor simulation results.</li>
                </ul>
            </li>
        </ul>
        <h3>v0.4.3 - 2016-05-17</h3>
        <ul>
		    <li>Backend
		    	<ul>
		    		<li>More backend refactoring to allow for multiactor simulations (talent comparisons, multiple different characters, comparing Live vs. PTR, etc.). This should also provide some speed increases across the site as a whole.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.4.2 - 2016-05-11</h3>
        <ul>
		    <li>Simulations Table
		    	<ul>
		    		<li>Make sure the user's timezone is used on all pages of the datatable.</li>
		    	</ul>
        	</li>
		    <li>Backend
		    	<ul>
		    		<li>Refactor how simulation data is stored in preparation for talent comparison simulations. (Soon™)</li>
		    	</ul>
        	</li>
        </ul>					
        <h3>v0.4.1 - 2016-05-06</h3>
        <ul>
		    <li>New Simulation
		    	<ul>
		    		<li>Characters without Dual Talents should not receive database errors when trying to queue. <a href="https://github.com/Twintop/beotorch-issues/issues/15" target="_blank">(Fixes issue #15)</a></li>
		    	</ul>
        	</li>
        </ul>					
        <h3>v0.4.0 - 2016-04-21</h3>
        <ul>
		    <li>New Simulation
		    	<ul>
		    		<li>Let users select what talents to use for the simulation. <span class="label label-danger">New</span></li>
		    	</ul>
        	</li>
		    <li>Simulations Table / Simulation Details
		    	<ul>
		    		<li>Display what talents were selected for the simulation. This will only be displayed for new simulations.</li>
		    	</ul>
        	</li>
		    <li>Simulation Details
		    	<ul>
		    		<li>Simulation output went MIA for a while there. Bring it back!</li>
		    	</ul>
        	</li>
		    <li>Simulations Table / Simulation Details / Notification Emails
		    	<ul>
		    		<li>Fix an issue with durations being reported incorrectly. <a href="https://github.com/Twintop/beotorch-issues/issues/10" target="_blank">(Fixes issue #10)</a></li>
		    	</ul>
        	</li>
        </ul>					
        <h3>v0.3.1 - 2016-03-14</h3>
        <ul>
		    <li>Simulations Table / Simulation Details / Notification Emails
		    	<ul>
		    		<li>For Tank sims, normalize to Stamina instead of Strength or Agility.</li>
		    	</ul>
        	</li>
		    <li>Simulation Details
		    	<ul>
		    		<li>Prettify the Armory Character JSON output.</li>
		    	</ul>
        	</li>
        	<li>General Site
        		<ul>
        			<li>Present dates and times in the user's local time zone instead of GMT.</li>
        		</ul>
        	</li>
        </ul>				
        <h3>v0.3.0 - 2016-03-13</h3>
        <ul>
        	<li>New Simulation
		    	<ul>
		    		<li>Allow users to select a character they have already simulated. <a href="https://github.com/Twintop/beotorch-issues/issues/8" target="_blank">(Fixes issue #8)</a></li>
		    		<li>If a selected character has a tank specialization selected, let the user choose whether to run a DPS or Tanking simulation. If running a tanking simulation, new options are available:
		    			<ul>
		    				<li>Choose the boss dificulty for the tanking simulation. Beotorch automatically defaults to the one closest to the character's item level.</li>
		    				<li>Choose the TMI window size, defaulting to 6 seconds.</li>
		    			</ul>
		    		</li>
		    	</ul>
        	</li>
        	<li>Your Simulations / Simulation Browser
		    	<ul>
		    		<li>Fix bug where labels for sliders wouldn't appear properly.</li>
		    	</ul>
		    </li>
		    <li>Simulations Table / Simulation Details
		    	<ul>
		    		<li>Denote if the simulation done was as a Tank or DPS role.</li>
		    		<li>Display TMI, DTPS, and HPS (& APS) for tanking simulations.</li>
		    		<li>Display TMI Boss and TMI Window under Simulation Details column for tanking simulations.</li>
		    		<li>Fix a bug causing Leech and Avoidance scaling factors from showing in the scaling factors table.</li>
		    	</ul>
        	</li>
		    <li>Simulation Details
		    	<ul>
		    		<li>Change HTML Report to use Highchards.</li>
		    	</ul>
		    </li>
        	<li>General Site
        		<ul>
        			<li>Update <a href="https://github.com/seiyria/bootstrap-slider" target="_blank">bootstrap-slider</a> to v6.1.6 and switch to minified version.</li>
        		</ul>
        	</li>
        	<li>Backend
        		<ul>
        			<li>Implement Tanking simulations.</li>
        			<li>Change report generation to use Highcharts.</li>
        		</ul>
        	</li>
        </ul>		
        <h3>v0.2.1 - 2016-03-11</h3>
        <ul>
        	<li>New Simulation
		    	<ul>
		    		<li>Fix bug preventing characters with 2-letter long names from being simulated. <a href="https://github.com/Twintop/beotorch-issues/issues/9" target="_blank">(Fixes issue #9)</a></li>
		    		<li>Improve catastrophic error handling when trying to get a character from the armory.</li>
		    	</ul>
        	</li>
        	<li>Simulation Email
		    	<ul>
		    		<li>Fix report fight length reporting bug in emails.</li>
		    	</ul>
		    </li>
        </ul>			
        <h3>v0.2.0 - 2016-03-10</h3>
        <ul>
        	<li>New Simulation
		    	<ul>
		    		<li>Character and Server validation now happens after you have entered both, rather than after pressing "Queue Simulation".</li>
		    		<li>Users can now choose which specialization they wish to use for their simulation run. <a href="https://github.com/Twintop/beotorch-issues/issues/7" target="_blank">(Fixes issue #7)</a></li>
		    	</ul>
        	</li>
        	<li>Simulation Details
		    	<ul>
		    		<li>Fix report source JSON link to point to the Simulation Details page instead of a 404 JSON link. <a href="https://github.com/Twintop/beotorch-issues/issues/5" target="_blank">(Fixes issue #5)</a></li>
		    	</ul>
		    </li>
        	<li>Backend
		    	<ul>
		    		<li>Run simulations with the "active" or "inactive" armory spec, as specified.</li>
		    		<li>Flag which nodes are able to accept new work items (simulations).</li>
		    		<li>Make sure reports source JSON links point to the Simulation Details page.</li>
		    	</ul>
        	</li>
        </ul>			
        <h3>v0.1.3 - 2016-03-06</h3>
        <ul>
        	<li>New Simulation
		    	<ul>
		    		<li>Allow users to choose how many boss targets to simulate attacking against.</li>
		    		<li>So that users can better keep track of differences between multiple simulation runs on the same character, users can now assign a friendly name to a simulation. <a href="https://github.com/Twintop/beotorch-issues/issues/6" target="_blank">(Fixes issue #6)</a></li>
		    		<li>Provide informative tooltips about each option.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations / Simulation Browser
		    	<ul>
		    		<li>Allow users to filter on the number of boss targets in a simulation.</li>
		    	</ul>
		    </li>
		    <li>Simulations Table / Simulation Details
		    	<ul>
		    		<li>Display the user entered friendly name of a simulation run if entered.</li>
		    		<li>Move the region from below the character into to beside the character name and server as a badge.</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Execute simulations with a variable number of boss targets.</li>
		    		<li>Replace 1.12.0 JQuery reference in generated reports with a local JQuery on-domain. Also apply this change to existing reports.</li>
		    		<li>Make sure reports use HTTPS for WoWDB libraries.</li>
		    	</ul>
        	</li>
        </ul>		
        <h3>v0.1.2 - 2016-03-01</h3>
        <ul>   
        	<li>User Accounts
		    	<ul>
		    		<li>Provide an option to resend the account activation email.</li>
		    		<li>Provide a way for users to reset their password if they have forgotten it. Also allow users to change their password once logged in.</li>
		    	</ul>
        	</li>
        	<li>Simulations Table
		    	<ul>
		    		<li>Change filtering search box text from "Search:" to "Filter Table:".</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Add ability to filter by a number of fields, including: Server, Character, Class, Specialization, Simulation Status, Fight Type, Iterations, Item Level, if it has Scale Factors, and if the HTML Report is still available for viewing.</li>
		    	</ul>
        	</li>
        	<li>Simulation Browser <span class="label label-danger">New</span>
		    	<ul>
		    		<li>Allow users to search and filter down on all simulations done by the system. Same options are available as with "Your Simulations" minus by character.</li>
		    	</ul>
        	</li>
        </ul>	
        <h3>v0.1.1 - 2016-02-26</h3>
        <ul>   
        	<li>User Accounts
		    	<ul>
		    		<li>Add maximum number of reports before the oldest are auto-archived to conserve space. This varies by account level and only counts simulations which completed successfully.</li>
		    	</ul>
        	</li>   
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Fix bug with the Server dropdown box scrolling to the bottom. <a href="https://github.com/harvesthq/chosen/issues/2506#issuecomment-173786231" target="_blank">(See more on this bug here.)</a></li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Provide a link to a summary of a specific simulation instead of directly to the SimulationCraft report itself.</li>
		    		<li>If an HTML report has expired, tell the user here.</li>
		    	</ul>
        	</li>
        	<li>Simulation Details <span class="label label-danger">New</span>
		    	<ul>
		    		<li>New page that shows specific details about a simulation that has been completed.</li>
		    		<li>If an HTML report has expired, tell the user here.</li>
		    		<li>If an HTML report is still valid, show it as an option in the page.</li>
		    		<li>If there is any log output from the simulation run, show it as an option in the page.</li>
		    		<li>Show the JSON pulled from the Battle.net API for the character that was used to run the simualtion.</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Expire and remove reports that are older than the maximum number of days allowed before cleanup.</li>
		    		<li>When returning simualtion results, archive oldest reports if the newly returned report will put the user over their maximum allowed number of reports.</li>
		    		<li>Update the "Simulation Complete" email to point to the new Simulation Details page.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.1.0 - 2016-02-20</h3>
        <ul>
	        <li>Beotorch is now in "Beta"!</li>
	        <li>Site Layout
	        	<ul>
	        		<li>Cleaned up and made pages more consistent in general.</li>
	        		<li>Added top navigation bar. Moved the related links to said bar.</li>
	        		<li>Added footer which will hold various notices/links/site info/etc.</li>
					<li>Prettify tooltips.</li>
					<li>Adjust the spacing of messages to not be so...big.</li>
					<li>Moved most forms from being in tables to other more bootstrap-y layout solutions.</li>
	        	</ul>
	        </li>   
	        <li>Main Page
	        	<ul>
					<li>Moved login to its own page.</li>
					<li>Now displays the most recent 100 simulations done by users.</li>
				</ul>
	        </li>
        	<li>User Accounts
		    	<ul>
		    		<li>The maximum queue size for Basic Members and HowToPriest Donors has been increased from 5 to 10 and 15 to 25, respectively.</li>
		    		<li>Add Account Settings page to let users adjust their site settings.</li>
		    		<li>Allow users to opt-out of receiving email notifications when their queued simulations have completed.</li>
		    	</ul>
        	</li>
        	<li>Queue New Simulation
		    	<ul>
			    	<li>Add support for Korea and Taiwan. Chinese realms should work Soon<sup>TM</sup> as Blizzard is fixing an issue on their end with the Chinese API.</li>
		    		<li>Change Iterations, Fight Length, and Fight Length Variance selectors to silder controls.</li>
		    		<li>Change Fight Length representation to MM:SS instead of seconds.</li>
		    		<li>Now display the expected simulation length range under the Fight Length slider.</li>
		    		<li>Scale Factors
		    			<ul>
		    				<li>Choosing to simulate a characters with Scale Factors enabled now has a weighted simulation cost of 5. If a user does not have at least 5 free queue slots available this option will be disabled.</li>
				    		<li>Tooltip explaining this cost/system added.</li>
			    		</ul>
			    	</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Changed Item Level to be a badge.</li>
		    		<li>Added status description tooltips describing what each status means.</li>
		    		<li>Change Fight Length representation to MM:SS instead of seconds.</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Force simulations to treat all characters as DPS, even if their spec is technically Healer or Tank.</li>
		    		<li>Improve UTF-8 support.</li>
		    		<li>Fix thread-priority error from appearing in results.</li>
		    		<li>Fixed link in the "Simulation Complete" notification email.</li>
		    		<li>Respect email notification user settings upon simulation completion.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.7 - 2016-02-17</h3>
        <ul>
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Prettify error/status messages when queueing a new simulation.</li>
		    		<li>The number of concurrent queued simulations an account can have is now limited by the account type.
		    			<ul>
		    				<li>The number of simulations an account can have queued up out of a maximum allowed is now displayed at the top of this page.</li>
		    				<li>If a user is already at their maximum queued simulations, they will have to wait until one of their simulations is processed before queueing another.</li>
				    		<li>If a user is not at their maximum number of queued simulations, show the queue simulation form.</li>
				    	</ul>
				    </li>
		    		<li>After a user has successfully queued up a new simulation, show the queue simulation form again if they are not at their maximum simulation queue size.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Combine the Status and Last Updated columns.</li>
		    		<li>Swap positions of "Simulation Details" and "Status" columns.</li>
		    		<li>Adjust column sizing. This page should now render properly at 720p+ resolutions.</li>
		    		<li>Adjust the Scaling Factors tables to fit better.</li>
		    		<li>Show progress bars depecting the status of the user's queued simulations.</li>
		    		<li>If a simulation is "New", show the position in the simulation queue, as "x of y in queue".</li>
		    		<li>Show if the queued simulation has scale factors enabled under "Simulation Details".</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Update to latest nightly build of SimulationCraft. This fixes some problems with detailed reports.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.6 - 2016-02-14</h3>
        <ul>   
        	<li>User Accounts
		    	<ul>
		    		<li>The baseline max iterations allowed has been increased to 10,000, up from 5,000.</li>
		    	</ul>
        	</li>   
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Server selection now displays a header/grouping of what region the server is a part of.</li>
		    		<li>Capture character's armory deatils when queueing a simulation rather than at the time the simulation is executed.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Show the character's Specialization from when the sim is queued rather than post-simulation completion.</li>
		    		<li>Show the character's equipped item level.</li>
		    		<li>Provide easier to identify link to the character's armory page.</li>
		    		<li>Provide a link to the character's talents/glyphs used for the simulation.</li>
		    		<li>Show the duration of the simulation as a range rather than Simtime +/- variance percentage. This information has now been moved in to an on-hover tooltip.</li>
		    		<li>Rearrange the column layouts slightly to better use claimed space.</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Use stored armory data to execute the simulation instead of requesting the armory data when the simulation is being executed.</li>
		    		<li>Remove some unneeded parsed data from the simulation log output.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.5 - 2016-02-11</h3>
        <ul>
	        <li>Site Layout
	        	<ul>
	        		<li>General, widespread theme changes and a move to bootstrap.css.</li>
	        	</ul>
	        </li>	        
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Fix bug with some EU realms not able to have characters queued up for simulations.</li>
		    		<li>Fix bug with how long of a simulation a user can queue.</li>
		    		<li>Change Server and Fight Type dropdowns to be a searchable combobox.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Alter layout of the "Character" column slightly.</li>
		    		<li>Add results table pagination, sorting, and filtering.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.4 - 2016-02-05</h3>
        <ul>
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Add more fight types for testing. In addition to Patchwerk there are now also: HelterSkelter, Light Movement, Heavy Movement, Ultraxion, HecticAddCleave, and Beastlord (all 1 Target).</li>
		    		<li>Allow users to choose the number of Iterations, Fight Length, and Fight Length Variance to use for their simulation. Acceptable ranges for these fields depends on the level of permission given to your account.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Combine "Simulation Type" and "Iterations" columns in to "Simulation Details".</li>
		    		<li>Show the length of the simulation and the variance underneith the fight type and iterations.</li>
		    		<li>Add Normalized scaling factors to "Results" table.</li>
		    	</ul>
        	</li>
        	<li>Notifications
		    	<ul>
		    		<li>Updated "Simulation Complete" email template to show Normalized scaling factors.</li>
		    		<li>An "opt-out" for receiving email notifications is coming, I promise! :)</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.3 - 2016-02-02</h3>
        <ul>
        	<li>General Site
        		<ul>
        			<li>Enable activation of new accounts.</li>
        		</ul>
        	</li> 
        	<li>Queue New Simulation
		    	<ul>
		    		<li>Allow users to select whether or not they want to have stat weights generated for their character.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Show the specialization used to simulate the character.</li>
		    		<li>Show the DPS done from the simulation and scaling factors (when applicable).</li>
		    	</ul>
        	</li>
        	<li>Notifications
		    	<ul>
		    		<li>Send user an email when they register with a link to follow to activate their account.</li>
		    		<li>Send user an email when their simulation has been completed.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.2 - 2016-01-30</h3>
        <ul>
        	<li>General Site
        		<ul>
        			<li>Change the color scheme to something darker. Still temporary.</li>
        		</ul>
        	</li>        	
        	<li>Queue New Simulation
		    	<ul>
		    		<li>System now references the Battle.net API to ensure that the character/server combination you have entered is valid.</li>
		    	</ul>
        	</li>
        	<li>Your Simulations
		    	<ul>
		    		<li>Show character thumbnail, level, race, class, and faction in the table.</li>
		    	</ul>
        	</li>
        	<li>Backend
		    	<ul>
		    		<li>Processing of simulations is now being handled by <i>Blowtorch</i> instead of my development VM.</li>
		    		<li>Parse out specialization, DPS, and stat weights from the results.</li>
		    	</ul>
        	</li>
        </ul>
        <h3>v0.0.1 - 2016-01-26</h3>
        <ul>
        	<li>Initial public pre-alpha release.</li>
        </ul>
	
	        </div>
        </div>       
        <div class="panel panel-default" style="width: 320px; float: right; margin-top: -55px;">
			<div class="panel-heading" style="padding-top: 5px; padding-bottom: 5px;">Advertisement</div>
			<div class="panel-body" style="padding: 10px;">

			</div>
		</div>
<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.inc.php';
?>
