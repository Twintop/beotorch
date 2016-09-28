#!/usr/bin/perl

use strict;
use warnings;
use LWP::Simple;
use LWP::UserAgent;
use LWP::Protocol::https;
use File::Slurp;
use JSON;
use Config::Simple;
use open ':std', ':encoding(UTF-8)';

my $haveWorkItem = 1;

my @ReportReplace;

my $cfg = new Config::Simple('processworkitem.config');

my $apikey = $cfg->param("apikey");

my $base_site_url = $cfg->param("base_site_url");

my $sid_base_site_url = $cfg->param("sid_base_site_url"); 

my $base_api_url = $cfg->param("base_api_url"); 

my $currentDir = $cfg->param("currentDir");

my $threadCount = $cfg->param("threadCount");

do {
	my @timestampStart = localtime();
	my $timeStart = sprintf("%04d%02d%02d%02d%02d%02d", 
		                    $timestampStart[5]+1900, $timestampStart[4]+1, $timestampStart[3],
		                    $timestampStart[2],      $timestampStart[1],   $timestampStart[0]);

	my $gitRevisionTemp = read_file($currentDir . "/simc-legion/simc/.git/HEAD");
	my $gitRevision = substr $gitRevisionTemp, 0, 40;

	my $ua = LWP::UserAgent->new;
	my $json_obj = new JSON;

	my $gitRevision_json_data_raw_trim;
	my $gitRevision_json_data_raw = "";
	my $gitRevision_json_data = "";
	my @gitRevision_data;

	$gitRevision_json_data_raw = $ua->get($base_api_url . "GitCheck/" . $apikey . "/" . $gitRevision);
	die "Couldn't get " . $base_api_url . "GitCheck/" . $apikey . "/" . $gitRevision unless defined $gitRevision_json_data_raw;
	
	$gitRevision_json_data_raw_trim = $gitRevision_json_data_raw->content;
	$gitRevision_json_data = substr($gitRevision_json_data_raw_trim, 1, length($gitRevision_json_data_raw_trim)-2);

	$gitRevision_json_data =~ s/^\s+|\s+$//g;

	@gitRevision_data = $json_obj->decode($gitRevision_json_data);
	
	if ($gitRevision_data[0]->{"Result"} ne "1") {
		print $gitRevision_data[0]->{"Result"} . " NEW - " . $gitRevision . " OLD\n\n";
		
		system("cd " . $currentDir . "/simc-legion/simc/ && git pull https://github.com/simulationcraft/simc legion-dev && git checkout " . $gitRevision_data[0]->{"Result"} . " && cd engine && make clean && make optimized LTO=1 -j " . $threadCount . " && cp simc " . $currentDir . "/simc && cd " . $currentDir);
	}
	else {
		my $request_json_data_raw = "";
		my $request_json_data = "";
		my @request_data;

		my $claim_json_data_raw = "";
		my $claim_json_data = "";
		my @claim_data;

		$request_json_data_raw = $ua->get($base_api_url . "request/" . $apikey);
		die "Couldn't get " . $base_api_url . "request/" . $apikey unless defined $request_json_data_raw;
		$request_json_data = $request_json_data_raw->content;

		$request_json_data =~ s/^\s+|\s+$//g;	

		if (length($request_json_data) == 0 || $request_json_data eq '""') {
			print "No work items.";
			$haveWorkItem = 0;
		}
		else {
			@request_data = $json_obj->decode($request_json_data);

			if (scalar @request_data == 1) {
				$claim_json_data_raw = $ua->get($base_api_url . "claim/" . $apikey . "/" . $request_data[0]->{"SimulationId"});
			
				my $claim_json_data_raw_trim = $claim_json_data_raw->content;
				$claim_json_data_raw_trim =~ s/^\s+|\s+$//g;
				$claim_json_data = substr($claim_json_data_raw_trim, 1, length($claim_json_data_raw_trim)-2);
			
				@claim_data = $json_obj->decode($claim_json_data);
	
				if ($claim_data[0]->{"RequestResult"} == 1) {
					my $simcCommand;
				
					my $simcVersion;
				
					if ($request_data[0]->{"SimulationCraftVersion"} eq "ptr") {
						$simcVersion = "./simc ptr=1";
					}
					elsif ($request_data[0]->{"SimulationCraftVersion"} eq "beta") {
						$simcVersion = "./simc_legion";
					}
					else {
						$simcVersion = "./simc";
					}
			
					if (length ($request_data[0]->{"CustomProfile"}) == 0) {					
						$request_data[0]->{"Actors"}[0]->{"ServerName"} =~ s/\'/\\\'/g;
						$request_data[0]->{"Actors"}[0]->{"ServerName"} =~ s/ /%20/g;
			
						my $characterJSONFile = $request_data[0]->{"SimulationGUID"} . ".json";
						open(my $jsonFH, '>:encoding(UTF-8)', $characterJSONFile) or die "Could not open file '$characterJSONFile' $!";
						print $jsonFH $request_data[0]->{"Actors"}[0]->{"CharacterJSON"};
						close $jsonFH;
			
						my $tankingCommand = "";
						my $positionCommand = "";
						my $scaleTMICommand = "";
			
						if (lc($request_data[0]->{"Actors"}[0]->{"SimulationRole"}) eq "tank") {
							$tankingCommand = sprintf("tmi_boss=TMI_Standard_Boss_%s tmi_boss_type=%s tmi_window_global=%i", $request_data[0]->{"TMIBoss"}, $request_data[0]->{"TMIBoss"}, $request_data[0]->{"TMIWindow"});
				
							$positionCommand = "position=front";
					
							if ($request_data[0]->{"ScaleFactors"} == 1) {
								$scaleTMICommand = "scale_over=TMI";
							}
						}

						my $actorsCommand = sprintf("name=%s talents=%s", $request_data[0]->{"Actors"}[0]->{"CharacterName"}, $request_data[0]->{"Actors"}[0]->{"SimTalent"});
						my $actorsCommand2 = "";
			
						for (my $x = 1; $x < $request_data[0]->{"ActorCount"}; $x++) {
							$actorsCommand2 = sprintf(" copy=%s_%i talents=%s", $request_data[0]->{"Actors"}[$x]->{"CharacterName"}, $x+1, $request_data[0]->{"Actors"}[$x]->{"SimTalent"});
							$actorsCommand .= $actorsCommand2;
						}

						$simcCommand = sprintf("%s %s %s local_json=%s.json,%s,spec=%s role=%s %s iterations=%i calculate_scale_factors=%i max_time=%i vary_combat_length=%.02f fight_style=%s desired_targets=%i threads=%i %s html=%s.html > %s.log 2>&1", $simcVersion, $tankingCommand, $scaleTMICommand, $request_data[0]->{"SimulationGUID"}, $request_data[0]->{"Actors"}[0]->{"CharacterName"}, $request_data[0]->{"Actors"}[0]->{"ArmorySpec"}, lc($request_data[0]->{"Actors"}[0]->{"SimulationRole"}), $positionCommand, $request_data[0]->{"Iterations"}, $request_data[0]->{"ScaleFactors"}, $request_data[0]->{"SimulationLength"}, $request_data[0]->{"SimulationLengthVariance"}, $request_data[0]->{"SimulationTypeSystemName"}, $request_data[0]->{"BossCount"}, $threadCount, $actorsCommand, $request_data[0]->{"SimulationGUID"}, $request_data[0]->{"SimulationGUID"});
					}
					else {			
						my $customProfileFile = $request_data[0]->{"SimulationGUID"} . ".simc";
						open(my $profileFH, '>:encoding(UTF-8)', $customProfileFile) or die "Could not open file '$customProfileFile' $!";
						print $profileFH $request_data[0]->{"CustomProfile"};
						close $profileFH;
					
						$simcCommand = sprintf("%s iterations=%i calculate_scale_factors=%i max_time=%i vary_combat_length=%.02f fight_style=%s desired_targets=%i threads=%i html=%s.html %s.simc > %s.log 2>&1", $simcVersion, $request_data[0]->{"Iterations"}, $request_data[0]->{"ScaleFactors"}, $request_data[0]->{"SimulationLength"}, $request_data[0]->{"SimulationLengthVariance"}, $request_data[0]->{"SimulationTypeSystemName"}, $request_data[0]->{"BossCount"}, $threadCount, $request_data[0]->{"SimulationGUID"}, $request_data[0]->{"SimulationGUID"}, $request_data[0]->{"SimulationGUID"});
					}
				
					system($simcCommand);
			
					my %returnData;
	
					my $logfile_raw = read_file($request_data[0]->{"SimulationGUID"} . ".log");
	
					open my $logfile, '<:encoding(UTF-8)', $request_data[0]->{"SimulationGUID"} . ".log";
					my $firstLine = <$logfile>;
					my $lastLine = "";
	
					if (index($firstLine, "Character not found") != -1) {
						%returnData = ('guid' => $request_data[0]->{"SimulationGUID"}, 'log' => $logfile_raw, 'html' => "", 'result' => "5");	
					}
					elsif (index($firstLine, "ERROR!") != -1){
						%returnData = ('guid' => $request_data[0]->{"SimulationGUID"}, 'log' => $logfile_raw, 'html' => "", 'result' => "4");	
					}
					else {				
						my $gettingDpsRanking = 0;
						my $gettingHpsRanking = 0;
						my $gotApsRanking = 0;
						my $gotDtpsRanking = 0;
						my $gotTmiRanking = 0;
						my $gotTalents = 0;
						my $gettingScalingFactors = 0;
						my %simulationResults;
						my $actorNumber = -1;
						my $simulationError = 0;

						if (length($request_data[0]->{"CustomProfile"}) == 0) {
							for (my $x = 0; $x < $request_data[0]->{"ActorCount"}; $x++) {
								$simulationResults{"Actors"}[$x]->{"SimulationActorId"} = $request_data[0]->{"Actors"}[$x]->{"SimulationActorId"};
							}
						}
						else {
							$simulationResults{"CustomProfile"} = 1;
						}

						while (my $line = <$logfile>) {
							$line =~ s/^\s+|\s+$|\s+(?=\s)//g; #Trim whitespace and duplicate spaces
							my $workingLine = lc($line);
							$lastLine = $workingLine;

							if (index($workingLine, "segmentation fault") != -1) { #Seg fault, break and throw an error back to the API
								$simulationError = 1;
								last;
							}

							if ($gettingDpsRanking) {	
								if (length $line == 0) { #End of DPS results
									$gettingDpsRanking = 0;
								}
								elsif (index($workingLine, "100.0% raid") != -1) { #Raid DPS; Throw away
						
								}
								else { #TODO: Go back and potentially make this take multiple toons. One works for now.	
									my @splitVals = split(/ /, $line);								
								
									if (length($request_data[0]->{"CustomProfile"}) == 0) {
										my @nameVals = split(/_/, $splitVals[2]);
										if ($nameVals[0] ne $splitVals[2]) {
											$simulationResults{"Actors"}[$nameVals[1]-1]->{'dps'} = $splitVals[0];
										}
										else {			
											$simulationResults{"Actors"}[0]->{'dps'} = $splitVals[0];							
										}		
									}
									else {
										my $actorId = -1;
										if ($simulationResults{"Actors"}) {
											for (my $x = 0; $x < @{$simulationResults{"Actors"}}; $x++) {
												if ($simulationResults{"Actors"}[$x]->{'name'} && $simulationResults{"Actors"}[$x]->{'name'} eq $splitVals[2]) {
													$actorId = $x;
													last;
												}
											}
										
											if ($actorId == -1) {
												$actorId = @{$simulationResults{"Actors"}};
												$simulationResults{"Actors"}[$actorId]->{'name'} = $splitVals[2];
											}
										}
										else {
											$actorId = 0;
											$simulationResults{"Actors"}[$actorId]->{'name'} = $splitVals[2];
										}
									
										$simulationResults{"Actors"}[$actorId]->{'dps'} = $splitVals[0];
									}					
								}
							}	
							elsif ($gettingHpsRanking) {	
								if (length $line == 0) { #End of HPS+APS results
									$gettingHpsRanking = 0;
								}
								elsif (index($workingLine, "100.0% raid") != -1) { #Raid DPS; Throw away
								
								}
								else { #TODO: Go back and potentially make this take multiple toons. One works for now.	
									my @splitVals = split(/ /, $line);
								
									if (length($request_data[0]->{"CustomProfile"}) == 0) {
										my @nameVals = split(/_/, $splitVals[2]);
										if ($nameVals[0] ne $splitVals[2]) {
											$simulationResults{"Actors"}[$nameVals[1]-1]->{'hpsraw'} = $splitVals[0];
										}
										else {			
											$simulationResults{"Actors"}[0]->{'hpsraw'} = $splitVals[0];
										}		
									}
									else {
										my $actorId = -1;
										if ($simulationResults{"Actors"}) {
											for (my $x = 0; $x < @{$simulationResults{"Actors"}}; $x++) {
												if ($simulationResults{"Actors"}[$x]->{'name'} eq $splitVals[2]) {
													$actorId = $x;
													last;
												}
											}
									
											if ($actorId == -1) {
												$actorId = @{$simulationResults{"Actors"}};
												$simulationResults{"Actors"}[$actorId]->{'name'} = $splitVals[2];
											}
										}
										else {
											$actorId = 0;
											$simulationResults{"Actors"}[$actorId]->{'name'} = $splitVals[2];
										}
									
										$simulationResults{"Actors"}[$actorId]->{'hpsraw'} = $splitVals[0];
									}		
								}
							}
							elsif ($gotApsRanking == 0 && index($workingLine, "hps: ") != -1) {
								my @splitVals = split(/ /, $line);
					
								my @trunkVals = split(/\./, $splitVals[1]);
					
								if ($request_data[0]->{"Actors"}[$actorNumber]->{"SimulationRole"} && lc($request_data[0]->{"Actors"}[$actorNumber]->{"SimulationRole"}) eq "tank") {
									$simulationResults{"Actors"}[$actorNumber]->{'aps'} = $simulationResults{"Actors"}[$actorNumber]->{'hpsraw'} - $trunkVals[0];
								}
								$simulationResults{"Actors"}[$actorNumber]->{'hps'} = $trunkVals[0];

								$gotApsRanking = 1;
							}
							elsif ($gotDtpsRanking == 0 && index($workingLine, "dtps: ") != -1) {
								my @splitVals = split(/ /, $line);
					
								my @trunkVals = split(/\./, $splitVals[1]);
	
								$simulationResults{"Actors"}[$actorNumber]->{'dtps'} = $trunkVals[0];

								$gotDtpsRanking = 1;
							}
							elsif ($gotTmiRanking == 0 && index($workingLine, "tmi: ") != -1) {
								my @splitVals = split(/ /, $line);
					
								my @trunkVals = split(/\./, $splitVals[1]);
	
								$simulationResults{"Actors"}[$actorNumber]->{'tmi'} = $trunkVals[0];

								$gotTmiRanking = 1;
							}
							elsif ($gettingScalingFactors) {	
								if (length $line == 0) { #End of scaling
									$gettingScalingFactors = 0;
									$actorNumber = 0;
								}
								elsif (index($workingLine, "weights :") != -1) { #This is in-line; we want to grab from summary!
									$gettingScalingFactors = 0;
									$actorNumber = 0;	
								}
								else { #TODO: Go back and potentially make this take multiple toons. One works for now.	
									my @splitVals = split(/ /, $line);
								
									if (length($request_data[0]->{"CustomProfile"}) == 0) {								
										my @nameVals = split(/_/, $splitVals[0]);
																						
										if ($nameVals[0] ne $splitVals[0]) {
											$actorNumber = $nameVals[1]-1;
										}
										else {
											$actorNumber = 0;								
										}
									}
									else {
										$actorNumber = 0;
										for (my $x = 0; $x < @{$simulationResults{"Actors"}}; $x++) {
											if ($simulationResults{"Actors"}[$x]->{'name'} eq $splitVals[0]) {
												$actorNumber = $x;
												last;
											}
										}
									}	
							
									for (my $x = 1; $x < scalar @splitVals; $x++) {
										my @scaleFactor = split(/[=()]/, $splitVals[$x]);
						
										if ($scaleFactor[0] eq "int" || $scaleFactor[0] eq "agi" || $scaleFactor[0] eq "str") {
											$simulationResults{"Actors"}[$actorNumber]->{primary} = $scaleFactor[1];
										}
										else {
											$simulationResults{"Actors"}[$actorNumber]->{lc($scaleFactor[0])} = $scaleFactor[1];
										}
									}		
								}		
							}
							elsif (index($workingLine, "player: ") != -1) { #Next lines contain character values
								my @splitVals = split(/ /, $line);
								
								if (length($request_data[0]->{"CustomProfile"}) == 0) {								
									my @nameVals = split(/_/, $splitVals[1]);
																					
									if ($nameVals[0] ne $splitVals[1]) {
										$actorNumber = $nameVals[1]-1;
									}
									else {
										$actorNumber = 0;								
									}
								}
								else {
									$actorNumber = 0;
									for (my $x = 0; $x < @{$simulationResults{"Actors"}}; $x++) {
										if ($simulationResults{"Actors"}[$x]->{'name'} eq $splitVals[1]) {
											$actorNumber = $x;
											last;
										}
									}
								
									$simulationResults{"Actors"}[$actorNumber]->{'race'} = $splitVals[2];
									$simulationResults{"Actors"}[$actorNumber]->{'class'} = $splitVals[3];
									$simulationResults{"Actors"}[$actorNumber]->{'spec'} = $splitVals[4];
									$simulationResults{"Actors"}[$actorNumber]->{'level'} = $splitVals[5];
								}
						
								$gotTmiRanking = 0;
								$gotApsRanking = 0;
								$gotDtpsRanking = 0;
								$gotTalents = 0;
							}	
							elsif (index($workingLine, "dps ranking:") != -1) { #Next lines contain DPS values
								$gettingDpsRanking = 1;
							}	
							elsif (index($workingLine, "hps ranking:") != -1) { #Next lines contain HPS+APS values
								$gettingHpsRanking = 1;
							}
							elsif (index($workingLine, "scale factors:") != -1) { #Next lines contain Scaling Factors
								$gettingScalingFactors = 1;
							}
							elsif ($gotTalents == 0 && length($request_data[0]->{"CustomProfile"}) != 0 && index($workingLine, "talents: ") != -1) { #Custom profile, get talents used
							$gotTalents = 1;
								my @splitVals = split(/ /, $line);
								$simulationResults{"Actors"}[$actorNumber]->{'talents'} = $splitVals[1];
							}
						}
			
						if ($simulationError == 1 || $lastLine eq "aborted" || index($lastLine, "error!") != -1) {
							%returnData = ('guid' => $request_data[0]->{"SimulationGUID"}, 'log' => $logfile_raw, 'result' => "4", 'html' => "");
						}
						else {
							my $replaceJqueryCommand1 = sprintf("sed -i -- 's/<script type=\"text\\\/javascript\" src=\"http:\\\/\\\/code.jquery.com\\\/jquery-1.11.2.min.js\"><\\\/script>/<script type=\"text\\\/JavaScript\" src=\"https:\\\/\\\/www.beotorch.com\\\/js\\\/jquery-2.2.0.min.js?v=2016_03_13\"><\\\/script>/g' %s.html", $request_data[0]->{"SimulationGUID"});
			
							system($replaceJqueryCommand1);
			
							my $replaceJqueryCommand2 = sprintf("sed -i -- 's/<script type=\"text\\\/javascript\" src=\"http:\\\/\\\/code.jquery.com\\\/jquery-1.11.3.min.js\"><\\\/script>/<script type=\"text\\\/JavaScript\" src=\"https:\\\/\\\/www.beotorch.com\\\/js\\\/jquery-2.2.0.min.js?v=2016_03_13\"><\\\/script>/g' %s.html", $request_data[0]->{"SimulationGUID"});
			
							system($replaceJqueryCommand2);
			
							my $replaceWoWDBCommand = sprintf("sed -i -- 's/http:\\\/\\\/static-azeroth.cursecdn.com/https:\\\/\\\/static-azeroth.cursecdn.com/g' %s.html", $request_data[0]->{"SimulationGUID"});
			
							system($replaceWoWDBCommand);
						
							my $replaceJSONCommand = sprintf("sed -i -- 's/%s.json/%ssimulationdetails.php?r=%s/g' %s.html", $request_data[0]->{"SimulationGUID"}, $sid_base_site_url, $request_data[0]->{"SimulationGUID"}, $request_data[0]->{"SimulationGUID"});
						
							system($replaceJSONCommand);
						
							my $replaceHighChartsCommand = sprintf("sed -i -- 's/http:\\\/\\\/code.highcharts.com/https:\\\/\\\/code.highcharts.com/g' %s.html", $request_data[0]->{"SimulationGUID"});
						
							system($replaceHighChartsCommand);
						
							my $resultfile_raw = read_file($request_data[0]->{"SimulationGUID"} . ".html");
			
							my $parsed_log_json = encode_json(\%simulationResults);

							%returnData = ('guid' => $request_data[0]->{"SimulationGUID"}, 'log' => $logfile_raw, 'html' => $resultfile_raw, 'result' => "3", 'simcVersion' => $request_data[0]->{"SimulationCraftVersion"});

							%returnData = (%returnData, %simulationResults);
						}
					}
		
					close $logfile;	
			
					my $returnData_json = encode_json(\%returnData);
	
					my $apiPut = HTTP::Request->new(PUT => $base_api_url . "storeresults/" . $apikey . "/" . $request_data[0]->{"SimulationId"});
			
					$apiPut->content_type('application/json');
					$apiPut->content($returnData_json);

					my $response = $ua->request($apiPut);
			
					if ($response->is_success()) {
						print("Success - " . $request_data[0]->{"SimulationGUID"});
					}
					else {
						print("Error: " . $response->status_line());
					}
				}	
			}
			else {
				print "No work items.";
			}
		}

		my @timestampEnd = localtime();
		my $timeEnd = sprintf("%04d%02d%02d%02d%02d%02d", 
				                $timestampEnd[5]+1900, $timestampEnd[4]+1, $timestampEnd[3],
				                $timestampEnd[2],      $timestampEnd[1],   $timestampEnd[0]);

		print(" - " . $timeStart . " - " . $timeEnd . "\n");
	}
} while ($haveWorkItem == 1);

#Clean up old files > 7 days
my $logCmd = "find " . $currentDir . "/ -maxdepth 1 -iname \"*-*-*-*-*.log\" -mtime +6 -exec rm {} \\\;";
my $htmlCmd = "find " . $currentDir . "/ -maxdepth 1 -iname \"*-*-*-*-*.html\" -mtime +6 -exec rm {} \\\;";
my $jsonCmd = "find " . $currentDir . "/ -maxdepth 1 -iname \"*-*-*-*-*.json\" -mtime +6 -exec rm {} \\\;";
my $simcCmd = "find " . $currentDir . "/ -maxdepth 1 -iname \"*-*-*-*-*.simc\" -mtime +6 -exec rm {} \\\;";


system($logCmd);
system($htmlCmd);
system($jsonCmd);
system($simcCmd);

exit(0);
