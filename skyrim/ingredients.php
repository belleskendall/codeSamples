<?php
	include "functions.php";
	session_start();
	$_SESSION['link'] = new skyrim_db();
	$_SESSION['link']->connect();
	if(isset($_POST['reset'])) {
		unset($_SESSION['have']);
	}
	else if(!isset($_POST['back'])){
	$_SESSION['level'] = $_POST['level'];
	$_SESSION['alch_perk'] = $_POST['alch_perk'];
	$_SESSION['phys_perk'] = $_POST['phys_perk'];
	$_SESSION['bene_perk'] = $_POST['bene_perk'];
	$_SESSION['pois_perk'] = $_POST['pois_perk'];
	$_SESSION['pure_perk'] = $_POST['pure_perk'];
	$_SESSION['dg'] = ($_POST['dg'] == 'y');
	$_SESSION['db'] = ($_POST['db'] == 'y');
	$_SESSION['hf'] = ($_POST['hf'] == 'y');
	}
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Skyrim Alchemy Calculator</title>
<style>
	#t1, #t2, #t3, #t4, #t5 {
		float:left;
	}
	div {
		float: none;
	}
	body {
		background: #FAEBD7;
		font: medium sans-serif;
	}
</style>
</head>
<body>

<h2>Available Ingredients</h2>
<form action="results.php" method="POST">
	<div>
		<input type='submit' name='submit' value='See results'>
	</div>
<?php
	$_SESSION['link']->select_ingredient_list($_SESSION['dg'], $_SESSION['db'], $_SESSION['hf']);
	$_SESSION['result'] = $_SESSION['link']->get_result();
	$i = 0;
	$x = 0;
	foreach ($_SESSION['result'] as $value) {
		if($i%24 == 0) {
			if($i == 0)
				echo " 	<table id='t1'>".PHP_EOL;			
			else if($i == 24) {
				echo "	</table>".PHP_EOL;
				echo " 	<table id='t2'>".PHP_EOL;
			}
			else if($i == 48) {
				echo "	</table>".PHP_EOL;
				echo " 	<table id='t3'>".PHP_EOL;
			}
			else if($i == 72) {
				echo "	</table>".PHP_EOL;
				echo " 	<table id='t4'>".PHP_EOL;
			}
			else if($i == 96) {
				echo "	</table>".PHP_EOL;
				echo " 	<table id='t5'>".PHP_EOL;
			}
		}
		echo "		<tr>".PHP_EOL;
		echo "			<td><label for='".$value[1]."'>".$value[0].": </label></td>".PHP_EOL;
		echo "			<td><input type='text' name='".$value[1]."' id='".$value[1]."'";
		if(isset($_SESSION['have']) && $x < count($_SESSION['have']) && $_SESSION['have'][$x][0] == $value[1]) { 
			echo " value='".$_SESSION['have'][$x][1]."'";
			$x++;
		}
		echo " placeholder='0' size=3></td>".PHP_EOL;
		echo "		</tr>".PHP_EOL;
		$i++;
	}
	unset($value);
	$_SESSION['link']->disconnect();
?>
	</table>
</form>
<form action="ingredients.php" method="POST">
	<input type="submit" name="reset" value="Reset">
</form>
<form action="index.php" method="POST">
	<input type="submit" name="back" value="Start over">
</form>
</body>
</html>