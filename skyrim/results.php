<?php
	include "functions.php";
	session_start();
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
	table, tr, th, td {
		border-collapse: collapse;
		border: 1px solid #AAA;
		padding: 0.2em;
		background: #FFF;
	}
	th {
		background: #F5DEB3;
	}
	del {
		font: x-small sans-serif;
		color: red;
	}
</style>
</head>
<body>
<form action="ingredients.php" method="POST">
	<input type="hidden" name="back">
	<input type="submit" value="Back to ingredients list">
</form>
<form action="index.php" method="POST">
	<input type="submit" name="back" value="Start over">
</form>	
<?php
	$_SESSION['link'] = new skyrim_db();
	$_SESSION['link']->connect();
	$_SESSION['have'] = array();
	foreach ($_SESSION['result'] as $value) {
		if($_POST[$value[1]] != 0) {
			$SELECT = 'a.eid, b.eid, c.eid, d.eid';
			$FROM = 'ingredient_effect a, ingredient_effect b, ingredient_effect c, ingredient_effect d';
			$WHERE = '(a.effect_order = 1 and a.iid = '.$value[1].') and (a.iid = b.iid and b.effect_order = 2) and (a.iid = c.iid and c.effect_order = 3) and (a.iid = d.iid and d.effect_order = 4)';
			$_SESSION['link']->select($FROM, $SELECT, $WHERE);
			$res = $_SESSION['link']->get_result();
			sort($res);
			array_push($_SESSION['have'], array($value[1], $_POST[$value[1]], $res[0], $res[1], $res[2], $res[3]));
		}
	}
	unset($value);
	//foreach ($_SESSION['have'] as $value) {
	//	echo $value[0]." ".$value[1]." ".$value[2]." ".$value[3]." ".$value[4]." ".$value[5]."<br/>".PHP_EOL;
	//}
	$_SESSION['link']->find_potion($_SESSION['have']);
	$_SESSION['link']->calc_value_potion ($_SESSION['level'], $_SESSION['alch_perk'], $_SESSION['phys_perk'], $_SESSION['bene_perk'], $_SESSION['pois_perk'], $_SESSION['pure_perk']);
	$_SESSION['link']->temp_to_list();
	echo"<h2>Best potions in order of value</h2>".PHP_EOL;
	$_SESSION['link']->show_final_potions($_SESSION['pure_perk']);
	echo"<h2>All potions that can be made</h2>".PHP_EOL;
	$_SESSION['link']->show_temp_potions($_SESSION['pure_perk']);
	$_SESSION['link']->disconnect();
?>
</body>
</html>