/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/


var workingCharacterJSON = null;
var outgoingSimulationListJSON = null;
var maxActors = 3;
var maxAvailableQueueSlots = 10;

function adjustReportDimensions(container) {
	var height = $(window).height();
	
	var frameHeight = height - 500;
	
	if (frameHeight < 500) {
		frameHeight = 500;
	}
	$("#" + container).css("height", frameHeight);
}

function guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }
    
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
}

function GetHostName() {
	var protocol = parent.location.protocol + "//";
	var host = window.location.hostname;
	var port = window.location.port ? ":" + window.location.port : "";
	
	return protocol + host + port;
}

function setResimCharacter() {
	if ($("#resimCharacter").val() != "") {
		var charInfo = $("#resimCharacter").val().split(",");
		$("#characterName").val("");
		$("#server").val(charInfo[1]).change().trigger("chosen:updated");
		$("#characterName").val(charInfo[0]);
		getCharacterArmoryEntry();
	}
}

function getCharacterArmoryEntry() {
	var serverId = $("#server").val();
	var characterName = $("#characterName").val();
	
	if (serverId && characterName && characterName.length >= 2) {
		if (workingCharacterJSON == null || workingCharacterJSON.serverId != serverId || workingCharacterJSON.name != characterName) {
			getCharacterArmory(serverId, characterName);
		}
	}
}

function getCharacterArmory(serverId, characterName) {
	$("#characterNameSpinnerSpan").show();
	$("#characterNameOkSpan").hide();
	$("#characterNameErrorSpan").hide();
	
	$.ajax({
		type: "GET",
		url: GetHostName() + "/battlenet/CharacterArmory/" + serverId + "/" + characterName,
		data: null,
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		error: function (xhr, ajaxOptions, thrownError) {	
			workingCharacterJSON = null;
			$("#characterNameSpinnerSpan").hide();
			$("#characterNameErrorSpan").show();
			
			if ($("#characterSelectMessage").hasClass("alert-success")) {
				$("#characterSelectMessage").removeClass("alert-success");
			}
				
			if (!$("#characterSelectMessage").hasClass("alert-danger")) {
				$("#characterSelectMessage").addClass("alert-danger");
			}
				
			$("#characterSelectMessage").text("There was an error communicating with the Battle.net API. Please try again.");
			
			$("#submitCharacter").prop("disabled", true);
				
			$("#specializationContainer").hide();
			$("#characterSelectMessageContainer").show();
		},
		success: function (results) {
			$("#characterNameSpinnerSpan").hide();
			if (results.error) {
				workingCharacterJSON = null;
				
				$("#characterNameOkSpan").hide();
				$("#characterNameErrorSpan").show();
				
				if ($("#characterSelectMessage").hasClass("alert-success")) {
					$("#characterSelectMessage").removeClass("alert-success");
				}
				
				$("#specializationContainer").hide();
				$("#characterSelectMessageContainer").show();
				
				if (!$("#characterSelectMessage").hasClass("alert-danger")) {
					$("#characterSelectMessage").addClass("alert-danger");
				}
				
				$("#roleContainer").hide();
                $("#talentsContainer").hide();
				
				$("#characterSelectMessage").text(results.error);
				
				$("#submitCharacter").prop("disabled", true);
			}
			else {
				workingCharacterJSON = results;	
				workingCharacterJSON.serverId = serverId;
								
				$("#characterNameOkSpan").show();
				$("#characterNameErrorSpan").hide();
				
				if ($("#characterSelectMessage").hasClass("alert-danger")) {
					$("#characterSelectMessage").removeClass("alert-danger");
				}
				
				$("#specializationContainer").show();
				$("#characterSelectMessageContainer").hide();
                $("#talentsContainer").show();
				                
                for (var x = 0; x < 4; x++) {
                    if (workingCharacterJSON.talents[x] && workingCharacterJSON.talents[x].selected && workingCharacterJSON.talents[x].selected == true) {
    					$("#specialization" + x).show();
        				$("#specialization" + x).parent().show();
            			$("#spec" + x + "label").show();
                        $("#spec" + x + "label").text(workingCharacterJSON.talents[x].spec.name);
                        $("#specialization" + x).closest('.btn').button('toggle');
                        generateTalentDropDowns(x, true);
                        if (workingCharacterJSON.talents[x].spec.role == "TANK") {
                            $("#roleContainer").show();
                            $("#role1").closest('.btn').button('toggle');			
                        }
                        else {
                            $("#roleContainer").hide();
                        }
                    }
                    else {
    					$("#specialization" + x).hide();
        				$("#specialization" + x).parent().hide();
            			$("#spec" + x + "label").hide();
                    }
                } 
				
				$("#submitCharacter").prop("disabled", false);
			}
		}
	});
}

function generateTalentDropDowns(spec, cleanTalents) {
    var ddData = [];
    var talentGuid = guid();
    
    if (cleanTalents) {
        $("#talentChoiceButtonsContainer").text("");
    }
    
    $("#talentChoiceButtonsContainer").append($("#simulationTalentDropdowns").render({GUID: talentGuid}));

    for (var x = 0; x < 7; x++) {
        var selectedValue = ".";
        ddData = [];    
        ddData.push({
            text: "No Talent",
            value: "0",
            selected: false,
            description: "",
            imageSrc: "http://media.blizzard.com/wow/icons/56/inv_misc_questionmark.jpg"
        });
            
        var tierTalent = jQuery.grep(workingCharacterJSON.talents[spec].talents, function(n, i) {
            if (n)
            {
                return n.tier == x; 
            }
            return false;
        });
        
        for (var y = 0; y < 3; y++) {
            var index = (3 * x) + y;
            var selectedTalent = false;
            
            if (tierTalent[0] && y == tierTalent[0].column) {
                selectedTalent = true;
                selectedValue = y+1;
            }
            
            ddData.push({
                text: workingCharacterJSON.talents[spec].dbtalents[index].TalentName,
                value: y+1,
                selected: selectedTalent,
                description: "",
                imageSrc: "http://media.blizzard.com/wow/icons/56/" + workingCharacterJSON.talents[spec].dbtalents[index].TalentIcon + ".jpg"
            });
        }
        
        $("#talent" + (x + 1) + "DropDown-" + talentGuid).ddslick('destroy');
        $("#talent" + (x + 1) + "DropDown-" + talentGuid).ddslick({
            data: ddData,
            width: 400,
            selectText: "",
            imagePosition: "left",
            onSelected: function(selectedData){
                $("#" + selectedData.original[0].id + "-value").val(selectedData.selectedData.value);
            }   
        });
        $("#talent" + (x + 1) + "DropDownvalue-" + talentGuid).val(selectedValue);
    }
    
    if (cleanTalents) {        
        $(".removeTalentsetContainer").hide();
    }
    
    updateTalentGUIDs();
}

function removeTalentset(GUID) {
    $("#talentChoiceButtons-" + GUID).remove();
    
    updateTalentGUIDs();
}

function addTalentset() {
    for (var x = 0; x < 4; x++) {
        if ($("#specialization" + x).is(":checked")) {	
            generateTalentDropDowns(x);
        }
    }
}

function updateTalentGUIDs() {
    var talentGUIDs = "";
    
    if ($(".removeTalentsetContainer").length == 1) {
        $(".removeTalentsetContainer").hide();        
    }
    else {
        $(".removeTalentsetContainer").show();
    }
    
    if ($(".removeTalentsetContainer").length >= maxActors) {
        $("#talentChoiceAddContainer").hide();        
    }
    else {
        $("#talentChoiceAddContainer").show();
    }
    
    $(".talentChoiceButtons").each(function (index, entry) {
       if (talentGUIDs.length > 0) {
           talentGUIDs += ";";
       } 
       talentGUIDs += entry.getAttribute("containerGuid");
    });
    
    $("#talentGUIDs").val(talentGUIDs);
    calculateExpectedSimulationCost();
}

function specializationRoleCheck() {
    for (var x = 0; x < 4; x ++) {    
        if ($("#specialization" + x).is(":checked")) {	
            generateTalentDropDowns(x, true);
            if (workingCharacterJSON.talents[x].spec.role == "TANK") {
                $("#roleContainer").show();		
            }
            else {
                $("#roleContainer").hide();
            }
        }
    }
	showTankOptionsCheck();
}

function showTankOptionsCheck() {
	if ($("#role1").is(":checked")) {	
		if ($("#specialization0").is(":checked")) {		
			if (workingCharacterJSON.talents[0].spec.role == "TANK") {
				var tmiBoss = determineDefaultTMIBoss();
				
				$("#tmiboss").slider({
					value: tmiBoss
				});
				
				$("#tankOptionsContainer").show();
				$("#tmiwindow").slider('refresh');
				$("#tmiboss").slider('refresh');
			}
			else {
				$("#tankOptionsContainer").hide();
			}
		}
		else {		
			if (workingCharacterJSON.talents[1].spec.role == "TANK") {
				var tmiBoss = determineDefaultTMIBoss();
				
				$("#tmiboss").slider({
					value: tmiBoss
				});
				
				$("#tankOptionsContainer").show();
				$("#tmiwindow").slider('refresh');
				$("#tmiboss").slider('refresh');
			}
			else {
				$("#tankOptionsContainer").hide();
			}
		}
	}
	else {
		$("#tankOptionsContainer").hide();
	}
}

function determineDefaultTMIBoss() {
	var ilvl = workingCharacterJSON.items.averageItemLevelEquipped;
	
	if (ilvl < 650) {
		return 1;
	}
	else if (ilvl < 665) {
		return 2;
	}
	else if (ilvl < 680) {
		return 3;
	}
	else if (ilvl < 695) {
		return 4;
	}
	else if (ilvl < 710) {
		return 5;
	}
	else if (ilvl < 725) {
		return 6;
	}
	else if (ilvl >= 725 ) {
		return 7;
	}
	else {
		return 1;
	}
}

function calculateExpectedSimulationCost() {
    var bosses = parseInt($("#bosscount").val());
    var iterations = parseInt($("#iterations").val());
    var scaleFactors = $("#scaleFactors").is(":checked") ? 5 : 1;
    var talentsets = $(".talentChoiceButtons").length;
    
    var expected = Math.ceil(bosses * scaleFactors * talentsets * (iterations / 10000));
    
    $("#expectedCost").show();
    $("#expectedCostTotal").html(expected);
    
    if (expected > maxAvailableQueueSlots && expected > 0) {
        $("#expectedCostError").show();
        $("#expectedCost").removeClass("alert-success").addClass("alert-danger");
			$("#submitCharacter").prop("disabled", true);
    }
    else {
        $("#expectedCostError").hide();
        $("#expectedCost").removeClass("alert-danger").addClass("alert-success");
				$("#submitCharacter").prop("disabled", false);
    }
}

function refreshSearchBoxes() {
	$("#bosscount").slider('refresh');
	$("#iterations").slider('refresh');
	$("#itemlevel").slider('refresh');
}

function syntaxHighlight(json) {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="JSON' + cls + '">' + match + '</span>';
    });
}

function updateDateTimeSpans() {
	var timeZone = moment.tz.guess();
	$.each($(".dateTimeSpan"), function(i, o) {
		var localDate = moment($(o).text());
		var formattedDate = localDate.format('YYYY-MM-DD HH:mm:ss') + " " + localDate.tz(timeZone).format('z');
		//alert($(o).text() + " - " + localDate + " - " + formattedDate);
		$(o).text(formattedDate);
	});
}

function redrawTooltips() {
    $('[data-toggle="tooltip"]').tooltip({
        html: true
    }).show(); 
    $('[data-toggle="tooltiplarge"]').tooltip({
        html: true,
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner tooltip-inner-large"></div></div>'
    }).show();
}

function simulationToggleHidden(GUID) {
    $.ajax({
		type: "GET",
		url: GetHostName() + "/beotorch/SimulationToggleHidden/" + GUID,
		data: null,
		dataType: "json",
		contentType: "application/json; charset=utf-8",
		error: function (xhr, ajaxOptions, thrownError) {
            alert("There was an error communicating with the Beotorch API.");
		},
		success: function (results) {
			if (results.error) {
            alert("There was an error communicating with the Beotorch API.");
			}
			else {
                var icon = $("#simulationHidden-" + GUID);
                if (results.Response == 0) {
                    icon.removeClass("simulationIconEnabledRed");
                    icon.removeClass("glyphicon-eye-close");
                    icon.addClass("glyphicon-eye-open");
                    icon.addClass("simulationIconEnabledGreen");
                    icon.attr("title", "This is a public simulation. Click here to make it hidden.");
                    icon.tooltip("hide").attr("data-original-title", "This is a public simulation. Click here to make it hidden.");
                }
                else if (results.Response == 1) {
                    icon.addClass("simulationIconEnabledRed");
                    icon.addClass("glyphicon-eye-close");
                    icon.removeClass("glyphicon-eye-open");
                    icon.removeClass("simulationIconEnabledGreen");
                    icon.attr("title", "This is a hidden simulation. Click here to make it public.");
                    icon.tooltip("hide").attr("data-original-title", "This is a hidden simulation. Click here to make it public.");
                }
                else {
                    alert("You do not have authorization to change this Simulation's public/hidden state.\n\nIf you believe this to be an error, please make sure you are logged in to Beotorch.");
                }
			}
		}
	});
}

function simulationArchiveReport(GUID) {
    if (confirm("Are you sure you want to archive this simulation's HTML report? This cannot be undone!"))
    {    
        $.ajax({
            type: "GET",
            url: GetHostName() + "/beotorch/SimulationArchiveReport/" + GUID,
            data: null,
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            error: function (xhr, ajaxOptions, thrownError) {
                alert("There was an error communicating with the Beotorch API.");
            },
            success: function (results) {
                if (results.error) {
                alert("There was an error communicating with the Beotorch API.");
                }
                else {
                    var icon = $("#simulationArchiveReport-" + GUID);
                    if (results.Response == 1) {
                        var timeZone = moment.tz.guess();
                        var localDate = moment(results.TimeArchived);
                        var formattedDate = localDate.format('YYYY-MM-DD HH:mm:ss') + " " + localDate.tz(timeZone).format('z');

                        icon.addClass("glyphicon-floppy-saved");
                        icon.addClass("simulationIconTooltip");
                        icon.removeClass("glyphicon-floppy-remove");
                        icon.removeClass("simulationIconInteractable");
                        icon.attr("title", "This simulation's HTML report was archived at " + formattedDate + ".");
                        icon.tooltip("hide").attr("data-original-title", "This simulation's HTML report was archived at " + formattedDate + ".");
                        alert("The HTML report for this Simulation has been successfully archived.")
                    }
                    else {
                        alert("You do not have authorization to archive this Simulation's HTML report.\n\nIf you believe this to be an error, please make sure you are logged in to Beotorch.");
                    }
                }
            }
        });
    }
}

function simulationArchive(GUID) {
    if (confirm("Are you sure you want to archive this simulation, including its HTML report? This cannot be undone!"))
    {    
       $.ajax({
            type: "GET",
            url: GetHostName() + "/beotorch/SimulationArchive/" + GUID,
            data: null,
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            error: function (xhr, ajaxOptions, thrownError) {
                alert("There was an error communicating with the Beotorch API.");
            },
            success: function (results) {
                if (results.error) {
                alert("There was an error communicating with the Beotorch API.");
                }
                else {
                    var icon = $("#simulationArchiveReport-" + GUID);
                    var icon2 = $("#simulationArchive-" + GUID);
                    if (results.Response == 1) {
                        var timeZone = moment.tz.guess();
                        var localDate = moment(results.TimeArchived);
                        var formattedDate = localDate.format('YYYY-MM-DD HH:mm:ss') + " " + localDate.tz(timeZone).format('z');

                        icon.addClass("glyphicon-floppy-saved");
                        icon.addClass("simulationIconTooltip");
                        icon.removeClass("glyphicon-floppy-remove");
                        icon.removeClass("simulationIconInteractable");
                        icon.attr("title", "This simulation's HTML report was archived at " + formattedDate + ".");
                        icon.attr("data-original-title", "This simulation's HTML report was archived at " + formattedDate + ".");
                        
                        icon2.addClass("simulationIconTooltip");
                        icon2.addClass("simulationIconEnabledRed");
                        icon2.removeClass("simulationIconInteractable");
                        icon2.attr("title", "This simulation has been archived and is not displayed under 'Your Simulations'. This simulation was archived at " + formattedDate + ".")
                        icon2.tooltip("hide").attr("data-original-title", "This simulation has been archived and is not displayed under 'Your Simulations'. This simulation was archived at " + formattedDate + ".").tooltip("show");
                        
                        alert("This simulation, including the HTML report, has been successfully archived. This simulation will no longer appear under 'Your Simulations' but is still accessible via the Simulation Browser or to anyone who has a direct link.")
                    }
                    else {
                        alert("You do not have authorization to archive this Simulation's HTML report.\n\nIf you believe this to be an error, please make sure you are logged in to Beotorch.");
                    }
                }
            }
        });
    }
}
