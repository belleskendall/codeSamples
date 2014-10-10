<?php
	include "functions.php";
	session_start();
	$_SESSION = array();
	session_destroy();
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Skyrim Alchemy Calculator</title>
<style>
	body {
		background: #FAEBD7;
		font: medium sans-serif;
	}
</style>
</head>
<body>
<h1>Information</h1>
<form action="ingredients.php" method="POST">
	<div>
		<h2>Character Information</h2>
		<label for="level">Alchemy Level</label>
		<select id="level" name="level">
<?php
	for($i=15; $i<101; $i++) {
		echo "			<option value=$i>$i</option>".PHP_EOL;
	}
?>
		</select>
		<br/>
		<label for="alch_perk">Alchemist Perk</label>
		<select id="alch_perk" name="alch_perk">
<?php
	for($i=0; $i<6; $i++) {
		echo "			<option value=".($i*20).">$i</option>".PHP_EOL;
	}
?>
		</select>
		<br/>
		<label for="phys_perk">Physician Perk</label>
		<select id="phys_perk" name="phys_perk">
			<option value=0>No</option>
			<option value=25>Yes</option>
		</select>
		<br/>
		<label for="bene_perk">Benefactor Perk</label>
		<select id="bene_perk" name="bene_perk">
			<option value=0>No</option>
			<option value=25>Yes</option>
		</select>
		<br/>
		<label for="pois_perk">Poisoner Perk</label>
		<select id="pois_perk" name="pois_perk">
			<option value=0>No</option>
			<option value=25>Yes</option>
		</select>
		<br/>
		<label for="pure_perk">Purity Perk</label>
		<select id="pure_perk" name="pure_perk">
			<option value="n">No</option>
			<option value="y">Yes</option>
		</select>
	</div>
	<div>
		<h2>System Information</h2>
		<label for="dg">Dawnguard DLC installed?</label>
		<select id="dg" name="dg">
			<option value="n">No</option>
			<option value="y">Yes</option>
		</select>
		<br/>
		<label for="db">Dragonborn DLC installed?</label>
		<select id="db" name="db">
			<option value="n">No</option>
			<option value="y">Yes</option>
		</select>
		<br/>
		<label for="hf">Hearthfire DLC installed?</label>
		<select id="hf" name="hf">
			<option value="n">No</option>
			<option value="y">Yes</option>
		</select>
	</div>
	<input type="submit" name="submit" value="Next">
</form>
</body>
</html>