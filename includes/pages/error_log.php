<div class="error-log">
<?php
	$filename = 'discord_api_logs.txt';
	$handle = fopen(ETS_PMPRO_DISCORD_PATH.$filename, "r");
	while ( ! feof($handle) ) {
	  echo fgets($handle). "<br />";
	 }
	fclose($handle);
?>
</div>
<div class="clrbtndiv">
	<input type="button" class="clrbtn btn btn-sm btn-danger" id="clrbtn" name="clrbtn" value="Clear Logs !">
	<input type="button" value="Refresh" onClick="window.location.reload();">
</div>