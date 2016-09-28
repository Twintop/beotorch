<?php
/***********************************************************
Beotorch - Online SimulationCraft frontend.
Originally by David C. Uhrig (Twintop) - twintop@gmail.com
https://www.github.com/Twintop/beotorch
***********************************************************/
?>

<?php

?>
	
		<footer style="z-index: 5;">
			<div class="container-fluid">
				<div class="pull-left" style="line-height: 15px; padding-top: 5px; font-size: 10px;">
					World of Warcraft and associated images<br />are ® or ™ <a href="http://www.blizzard.com/" target="_blank">Blizzard Entertainment</a>
				</div>
				<div class="pull-right text-right">
					© Beotorch.com 2016 - <a href="<?php echo SITE_ADDRESS; ?>changelog.php">v.0.6.5 (2016-08-15)</a>
				</div>
				<div class="pull-right text-right footer-spacer">•</div>
				<div class="pull-right text-right">
					<a href="<?php echo SITE_ADDRESS; ?>about.php">About</a>
				</div>
				<div class="pull-right text-right footer-spacer">•</div>
				<div class="pull-right text-right">
					<a href="<?php echo SITE_ADDRESS; ?>contact.php">Contact</a>
				</div>
				<div class="pull-right text-right footer-spacer">•</div>
				<div class="pull-right text-right" style="margin-top: 5px;">
					<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://beotorch.com/" data-text="Want to know your DPS? Stat weights? Sim your character now!" data-via="Beotorch">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				</div>
				<div class="pull-right text-right footer-spacer">•</div>
				<div class="pull-right text-right" style="margin-right: -3px;">
                    <a href="https://discord.gg/0zVQ3V9e7OTH13RG" target="_blank" title="Join us on Discord!"><img src="<?php echo SITE_ADDRESS; ?>/images/discord.png" style="width: 31px; height: 30px;" /></a>
				</div>
			</div>
		</footer>
		<script>
			$(document).ready(function(){
                redrawTooltips();
				updateDateTimeSpans();
			});
		</script>
<?php
if (SECURE == TRUE)
{
?>
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
			ga('create', 'UA-74237078-1', 'auto');
			ga('send', 'pageview');
		</script>
<?php
}
?>
    </body>
</html>
