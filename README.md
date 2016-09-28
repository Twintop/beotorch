# beotorch

## What is Beotorch?
Beotorch is a website that allows you to simulate your World of Warcraft character in a combat situation using SimulationCraft.

## What is SimulationCraft?
From the SimulationCraft GitHub Page:

>SimulationCraft is a tool to explore combat mechanics in the popular MMO RPG World of Warcraft (tm).
>
>It is a multi-player event driven simulator written in C++ that models player character damage-per-second in various raiding scenarios.
>
>Increasing class synergy and the prevalence of proc-based combat modifiers have eroded the accuracy of traditional calculators that rely upon closed-form approximations to model very complex mechanics. The goal of this simulator is to close the accuracy gap while maintaining a performance level high enough to calculate relative stat weights to aid gear selection.
>
>SimulationCraft allows raid/party creation of arbitrary size, generating detailed charts and reports for both individual and raid performance.

In short, SimulationCraft plays your character through a theoretical encounter thousands of times and reports back the average results.

## How does Beotorch work?
A user (you!) requests to have their character simulated. This request is placed in to a queue. When it is time for a queued character to be simulated, one of the processing servers grabs that character's information and starts chugging away. Once done, the processing server returns the result and sends the user an email (if they so choose) letting them know their simulation has completed. Easy peasy!

## How long will it take for my character to simulate?
The answer to this question largely depends on a few factors:

Queue Position: If there are many people infront of you in the queue, it could take several minutes for your character's simulation to begin.
Iterations: The number of times the simulation is run to generate results. Higher iterations = longer simulations = better results. (See more below)
Fight Length: How long each simulation runs for. Longer fights = longer simulations.
Fight Type: Some fight types have extra targets. This increases the processing time of each iteration. Patchwerk is the simplest (and fastest), while Beastlord is the most complicated (and slowest).
Scaling Factors: To generate scaling factors, the simulation needs to be run an additional time per stat you wish to get stat weights for. This will easily increase the simulation time by almost an order of magnitude in most cases. (See more below)
Once it is your turn in the queue, for example:
Running a 5 minute duration 10,000 iteration Patchwerk simulation without scaling factors will take somewhere around 10-15 seconds to execute and report back the results.
Running a 5 minute duration 10,000 iteration Beastlord simulation with scaling factors will take somewhere around 1.5 - 2 minutes to execute and report back the results.
What are 'Iterations'?
Iterations are the number of times SimulationCraft runs through a each fight in the simulation. In the simplest example, you can think of it like killing Patchwerk in Naxxramas over and over again and seeing an aggregate of all of those pulls.

The more iterations you do, the better results you will have. Lower iteration counts mean there will be a much larger swing in returned results, and thus will be less accurate. Beotorch allows basic users up simulate their characters using up to 10,000 iterations; other account types have access to higher iterations.

For general DPS checks, anything under 5,000 iterations is generally considered an unreliable result with 10,000 being considered accurate enough for most uses. For Scaling Factors (see below), 10,000 is the minimum recommended to get accurate and usable stat weights. Beotorch does not limit users to within these guidelines as minimums, though, as some users may want to generate many reports for different characters quickly.

## What are 'Scaling Factors' / 'Stat Weights'?
Scaling Factors (also called Stat Weights) are a way to gauge how valuable a specific stat is for your character. These values allow you to compare two pieces of gear side by side and know which should be better for you to equip.

Scaling Factors in SimulationCraft are calculated by re-running a character's simulation with adjusted stat values and comparing the damage results with a baseline of your character's raw stats. This means, for example, if you are a caster you will have 7 additional full simulation runs (for Intellect, Spell Power, Crit, Haste, Mastery, Multistrike, Versatility, and possibly Speed, in addition to your 'base' simulation) to get your stat weights.

On average, a simulation queued up to generate stat weights will take about 8-times as long as a pure DPS simulation.

## Does Beotorch support Tank or Healer simulations?
Short answer: yes for Tanking simulations, no for Healing Simulations.

Long answer:

Most tanking specs are supported by SimulationCraft. As of version 0.3.0, Beotorch supports TMI Tanking Simulations in addition to DPS simulations for Tanking specs.

Very few healing specs have good support within SimulationCraft. Some specs don't have any abilities implemented while others don't have proper APLs for healing. Many of these healing specs have limited (at best) suport for DPS simulations (specifically, Restoration Druids having virtually no support and Restoration Shamans being completely unsupported). Simulating healers is a tricky thing to do since healing is, by in large, more about effective heal choices and smart cooldown usage given the current situation rather than trying to always do maximum HPS. There are currently no plans to support healing simulations in Beotorch.

## Who created Beotorch?
Hi there, I'm Twintop. I've been an avid World of Warcraft player since the open beta of Vanilla back in 2004. I have played since then minus a break from Naxx40 through the launch of Wrath of the Lich King, missing all of Burning Crusade in the process. Originally a main spec Holy Priest, since Cataclysm I have been playing Shadow as my main spec.

I help maintain the Priest Module (specifically the Shadow parts) of SimulationCraft and dabble with other enhancements to the project. I make heavy use of SimulationCraft to help with Shadow Priest theorycrafting (example from HFC, totaling over 500 million total iterations), specifically in helping to determine stat weights for use in my Best in Slot lists. In addition to theorycrafting Shadow, I am a founding Admin of HowToPriest.

## Why create Beotorch?
There are many reasons why I decided to create Beotorch. A few of the largest include:

There is a barrier to entry in using SimulationCraft successfully. These barriers vary from user to user, but usually include some of the following: not understanding how to use SimulationCraft, SimulationCraft not being supported on their OS anymore (RIP 32bit Windows users), having an outdated version of SimulationCraft and not knowing it, lacking processing power to do longer simulations (i.e.: stat weights), or not being able to easily share the results of their simulations. Beotorch alleviates all of these concerns. This isn't to say that people will not find value from running SimulationCraft on their own machines, rather that Beotorch makes it easier to accomplish the most common use cases of SimulationCraft.
I have a lot of extra processing power that is sitting around unused most of the time.
I do massive simulation runs for my own theorycrafting. Up until this point it has largely been done by hand or with kludgy scripts that were prone to breakage. Beotorch gives me a framework to be able to queue up my own simulations fairly easily, there by reducing the overhead required for me to do my massive simualtion runs.
Finally, related to the last two points, I want to try and make this simulating platform available to other class theorycrafters so that they can do more detailed analysis of their own. This isn't in place yet, but hopefully SoonTM.
Where did the name "Beotorch" come from?
Beotorch is a portmanteau of "Blowtorch" and "Beowulf {Cluster}". Allow me to explain...

In November 2012, I decided that, to improve my own theorycrafting capabilities with SimulationCraft, I needed to build a dedicated computer for running simulations. This tower ended up housing an AMD FX-8350 8-Core Processor. Needless to say, when this machine was running maxed out for hours (or days) on end it put out quite a lot of heat, almost as if someone was pointing a Blowtorch in your direction. The name stuck, and that machine has been known affectionately as Blowtorch within the Priest and Shadow communities ever since.

In December 2015, in preperation for Legion, I built an additional (and far more powerful -- dual 8-Core {16 Thread} CPUs) computer for executing simulations. I then networked the original Blowtorch and this additional computer together as a beowulf cluster. Beotorch executes simulations across this cluster.
