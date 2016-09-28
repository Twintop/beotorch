<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php
            
            echo $simulationParams->PrivacyState;?>

<table class="table table-bordered table-hover table-striped display" id="simulationsTable">
                <thead>
                    <tr>
                                <?php
                if ($simulationParams->ShowControlOptions != 0)
                {
                    echo "<th style=\"width: 35px; min-width: 35px; max-width: 35px;\"></th>";
                }
                ?>
                        <th style="width: 400px; min-width: 400px; max-width: 400px;">Character</th>
                        <th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
                        <th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
                        <th style="min-width: 200px;">Result</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                                <?php
                if ($simulationParams->ShowControlOptions != 0)
                {
                    echo "<th style=\"width: 35px; min-width: 35px; max-width: 35px;\"></th>";
                }
                ?>
                        <th style="width: 400px; min-width: 400px; max-width: 400px;">Character</th>
                        <th style="width: 200px; min-width: 200px; max-width: 200px;">Status</th>
                        <th style="width: 175px; min-width: 175px; max-width: 175px;">Simulation Details</th>
                        <th style="min-width: 200px;">Result</th>
                    </tr>
                </tfoot>
                <tbody>
				</tbody>
           	</table>
           	
           	<script type="text/javascript">
           	$(document).ready(function() {
                <?php
                $jsonObject = $simulationParams;
                $jsonObject->User = null;
                ?>
                outgoingSimulationListJSON = jQuery.parseJSON('<?php echo json_encode((array)$jsonObject, true); ?>');
                
           		$("#simulationsTable").DataTable({
	           		"dom": '<"top"iflp<"clear">>rt<"bottom"iflp<"clear">>',
                    "order": [[ <?php if ($simulationParams->ShowControlOptions != 0) { echo "2"; } else { echo "1"; } ?>, "desc" ]],
    	       		"columns": [
                        <?php if ($simulationParams->ShowControlOptions != 0) { echo '{ "width": "40px", "orderable": false },'; } ?>
    	       			{ "width": "400px" },
    	       			{ "width": "200px" },
    	       			{ "width": "175px" },
    	       			null
    	       		],
    	       		"oLanguage": {
    	       			"sSearch": "Filter Table:"
    	       		},
                    "autoWidth": false,
                    "processing": true,
                    "serverSide": true,
                    "ajax": {
                        "url": GetHostName() + "/beotorch/SimulationList",
                        "type": "POST",
                        "data": outgoingSimulationListJSON
                    }
           		})
           		.on("draw.dt", function () {
				    $('[data-toggle="tooltip"]').tooltip();
                    updateDateTimeSpans();
           		});
           	} );

            var update_size = function() {
                $("#simulationsTable").css({ width: $("#simulationsTable").parent().width() });
                $("#simulationsTable").DataTable().fnAdjustColumnSizing();  
            }

            $(window).resize(function() {
                clearTimeout(window.refresh_size);
                window.refresh_size = setTimeout(function() { update_size(); }, 250);
            });
           	</script>
