<?php
//basic, reusable database class
class db_CRUD {
	//database connection variables (text)
	protected $db_host;
	protected $db_user;
	protected $db_pass;
	protected $db_name;
	//result of last query (varies)
	protected $result;
	//is a connection already established? (bool)
	private $con = false;
	//mysqli_connect object for internal use 
	private $db;

	//connect to backend database
	public function connect () {
		if(!$this->con) {
			$this->db = mysqli_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name) or die(mysql_error());
			$this->con = true;
		}
	}
	//disconnect from backend database
	public function disconnect () {
		if($this->con) {
			if(mysqli_close($this->db)) {
				$this->con = false;
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
	//basic select statement
	public function select($table, $col = '*', $where = null, $order = null) {
		unset($this->result);
		$q = 'SELECT '.$col.' FROM '.$table;
		if($where != null)
			$q .= ' WHERE '.$where;
        if($order != null)
			$q .= ' ORDER BY '.$order;
		$query = $this->db->query($q);
		if($query) {
			$numResults = $query->num_rows;
			if($numResults > 1) {
				for($i = 0; $i < $numResults; $i++) {
					$r = $query->fetch_array(MYSQLI_NUM);
					$key = array_keys($r);
					for($x = 0; $x < count($key); $x++) {
						$this->result[$i][$key[$x]] = $r[$key[$x]];
					}
				}
				return true;
			}
			else if($numResults == 1) {
				$r = $query->fetch_array(MYSQLI_NUM);
				$key = array_keys($r);
				for($x = 0; $x < count($key); $x++) {
					$this->result[$key[$x]] = $r[$key[$x]];
				}
				return true;
			}
			else 
				return false;
		}
		else {
			return false;
		}
    }
	public function get_result () {
		return $this->result;
	}
}

class potion {
	//ingredients included in potion (int)
	public $ingredient1;
	public $ingredient2;
	public $ingredient3;
	//active effects list (int array)
	public $effect;
	//estimated value (int)
	public $value;
	//how many of this potion can be made? (int)
	public $cnt;
	//how many ingredients are used in this potion? (int)
	public $ingredient_cnt;
	//is the potion a poison (bool)
	public $poison;
	
	function __construct() {
		$this->ingredient1 = null;
		$this->ingredient2 = null;
		$this->ingredient3 = null;
		$this->effect = array();
		$this->value = 0;
		$this->cnt = 0;
		$this->ingredient_cnt = 0;
		$this->poison = null;
	}
	
	//re-initialize potion
	function reset_potion() {
		$this->ingredient1 = null;
		$this->ingredient2 = null;
		$this->ingredient3 = null;
		if(!empty($this->effect))
			$this->effect = array();
		$this->value = 0;
		$this->cnt = 0;
		$this->ingredient_cnt = 0;
		$this->poison = null;
	}
}

//specialized class for skyrim project
class skyrim_db extends db_CRUD {
	//final list of best potions (potion array)
	private $potion_list;
	//list of all potions (potion array)
	private $temp_potion;
	//local array of available ingredients (int int array)
	private $ingredient_list;
	
	function __construct() {
		//set datbase variables
		$this->db_host = 'localhost';
		$this->db_user = 'root';
		$this->db_pass = '';
		$this->db_name = 'skyrim';
		$this->ingredient_list;
		$this->potion_list = new potion();
		$this->potion_list = array();
		$this->temp_potion = new potion();
		$this->temp_potion = array();
	}
	
	//create list of possible ingredients according to DLCs
	function select_ingredient_list($dg, $db, $hf) {
		//query batabase for all base ingredients and all included DLCs
		if($dg == false && $db == false && $hf == false)
			$where = 'dlc is null';
		else if($dg == true && $db == true && $hf == true)
			$where = null;
		else if($dg == true && $db == false && $hf == false)
			$where = 'dlc = "dg" or dlc is null';
		else if($dg == false && $db == true && $hf == false)
			$where = 'dlc = "db" or dlc is null';
		else if($dg == false && $db == false && $hf == true)
			$where = 'dlc = "hf" or dlc is null';
		else if($dg == false && $db == true && $hf == true)
			$where = 'dlc = "db" or dlc = "hf" or dlc is null';
		else if($dg == true && $db == false && $hf == true)
			$where = 'dlc = "dg" or dlc = "hf" or dlc is null';
		else if($dg == true && $db == true && $hf == false)
			$where = 'dlc = "dg" or dlc = "db" or dlc is null';
		
		db_CRUD::select('ingredient', 'i_name, iid', $where, '1');
	}
	
	//merge ingredient effects into active effect list
	function merge_effects(&$e1, $e2, &$active) {
		$temp = array();
		//for each effect in $e1 and $e2, while both pointers in arrays
		for($i=0, $k=0; $i<count($e1) && $k<count($e2); ) {
			//if $e1 = $e2: add to $active, progress both pointers
			if($e1[$i] == $e2[$k] && !in_array($e1[$i], $active)) {
				$active[] = $e1[$i];
				$i++;
				$k++;
			}
			//if $e1 < $e2: add $e1 to temp, advance $e1 pointer
			else if($e1[$i] < $e2[$k]) {
				$temp[] = $e1[$i];
				$i++;
			}
			//else: add $e2 to temp, advance $e2 pointer
			else {
				$temp[] = $e2[$k];
				$k++;
			}
		}
		//add remains of $e1 (if any) to temp
		while($i < count($e1)) {
			$temp[] = $e1[$i];
			$i++;
		}
		//add remains of $e2 (if any) to temp
		while($k < count($e2)) {
			$temp[] = $e2[$k];
			$k++;
		}
		$e1 = $temp;
	}
	
	//populate temp_potion and ingredient_list
	function find_potion($list) {
		$temp_pot = new potion();
		$ing_count = 1;
		$skip = array();
		$temp_cnt = array();
		//populate ingredient_list
		foreach($list as $l) {
			$this->ingredient_list[$l[0]] = $l[1];
		}
		//for each ingredient in $list
		for($index=0; $index<count($list); $index++) {
			$temp_pot->reset_potion();
			$temp_effect = array($list[$index][2], $list[$index][3], $list[$index][4], $list[$index][5]);
			$active_effects = array();
			$temp_cnt = array();
			$i=0;
			//for each ingredient (except found matches and already searched ingredients)
			//chech each ingredient for 2nd or 3rd match
			for($i=$index+1; $i<count($list); $i++) {
				$skip_me = false;
				if(!empty($skip)) {
					foreach($skip as $value) {
						if($i == $value) {
							$skip_me = true;
						}
					}
				}
				if($skip_me) {
					continue;
				}
				$e = 0;
				$k = 2;
				//check effect of ingredient against unused effects of current potion
				while($e < count($temp_effect) && $k < 6) {
					if($temp_effect[$e] > $list[$i][$k])
						$k++;
					else if($temp_effect[$e] < $list[$i][$k])
						$e++;
					//if match is found
					else {
						//if 2nd ingredient found: add 2 ingredient potion to temp_potion
						if($ing_count == 1) {
							$k = 6;
							$temp_pot->ingredient1 = $list[$index][0];
							$temp_cnt[] = $list[$index][1];
							skyrim_db::merge_effects($temp_effect, array($list[$i][2], $list[$i][3], $list[$i][4], $list[$i][5]), $active_effects);
							$temp_pot->ingredient2 = $list[$i][0];
							$temp_cnt[] = $list[$i][1];
							sort($active_effects);
							sort($temp_cnt);
							$ing_count++;
							//add this ingredient to $skip array
							$skip[] = $i;
							$temp_pot->effect = $active_effects;
							$temp_pot->cnt = $temp_cnt[0];
							$temp_pot->ingredient_cnt = 2;
							$this->temp_potion[] = clone $temp_pot;
						}
						//if 3rd ingredient found: add 3 ingredient potion to temp_potion
						else {
							$k = 6;
							$ing_count++;
							$temp = $temp_effect;
							$temp_a = $active_effects;
							$t_cnt = $temp_cnt;
							$temp_pot->ingredient3 = $list[$i][0];
							$t_cnt[] = $list[$i][1];
							skyrim_db::merge_effects($temp, array($list[$i][2], $list[$i][3], $list[$i][4], $list[$i][5]), $temp_a);
							sort($temp_a);
							$temp_pot->effect = $temp_a;
							$temp_pot->cnt = $t_cnt[0];
							$temp_pot->ingredient_cnt = 3;
							$this->temp_potion[] = clone $temp_pot;
						}
					}
				}
			}
			//if any potions found: search with same first ingredient, skip found 2nd ingredient
			if($ing_count != 1) {
				$ing_count = 1;
				$index--;
			}
			//no potions found, go to next ingredient
			else {
				$ing_count = 1;
				$skip = array();
			}
		}
	}
	
	//print potion_list in a table
	function show_final_potions($pure_perk) {
		echo count($this->potion_list)." potions<br/>".PHP_EOL;
		echo "<table>".PHP_EOL;
		echo "<tr>".PHP_EOL;
		echo "<th>#</th>".PHP_EOL;
		echo "<th>Count</th>".PHP_EOL;
		echo "<th>Ingredient 1</th>".PHP_EOL;
		echo "<th>Ingredient 2</th>".PHP_EOL;
		echo "<th>Ingredient 3</th>".PHP_EOL;
		echo "<th>Effects</th>".PHP_EOL;
		echo "<th>Value</th>".PHP_EOL;
		echo "</tr>".PHP_EOL;
		//for each potion: get ingredient names from database
		foreach($this->potion_list as $i => $potion) {
			$where = "iid = '$potion->ingredient1'";
			db_CRUD::select('ingredient', 'i_name', $where);
			$i_1 = db_CRUD::get_result()[0];
			$where = "iid = '$potion->ingredient2'";
			db_CRUD::select('ingredient', 'i_name', $where);
			$i_2 = db_CRUD::get_result()[0];
			if($potion->ingredient_cnt == 3) {
				$where = "iid = '$potion->ingredient3'";
				db_CRUD::select('ingredient', 'i_name', $where);
				$i_3 = db_CRUD::get_result()[0];
			}
			else
				$i_3 = ' ';
			$effect_list = '';
			//if purity perk is active: format effects to show voided effects
			if($pure_perk == 'y') {
				foreach($potion->effect as $key => $effect) {
					db_CRUD::select('effect', 'e_name, poison', 'eid = '.$effect);
					$result = db_CRUD::get_result();
					if($potion->poison == $result[1])
						$effect_list .= $result[0];
					else
						$effect_list .= "<del>".$result[0]."</del>";
					if($key < count($potion->effect)-1)
						$effect_list .= ", ";
				}
			}
			else {
				foreach($potion->effect as $key => $effect) {
					db_CRUD::select('effect', 'e_name', 'eid = '.$effect);
					$effect_list .= db_CRUD::get_result()[0];
					if($key < count($potion->effect)-1)
						$effect_list .= ", ";
				}
			}
			echo "<tr>".PHP_EOL;
			echo "<td>".($i+1)."</td>".PHP_EOL;
			echo "<td>$potion->cnt</td>".PHP_EOL;
			echo "<td>$i_1</td>".PHP_EOL;
			echo "<td>$i_2</td>".PHP_EOL;
			echo "<td>$i_3</td>".PHP_EOL;
			echo "<td>$effect_list</td>".PHP_EOL;
			echo "<td>$potion->value</td>".PHP_EOL;
			echo "</tr>".PHP_EOL;
		}
		echo "</table>".PHP_EOL;
	}
	
	//print temp_potion in a table
	function show_temp_potions($pure_perk) {
		echo count($this->temp_potion)." potions<br/>".PHP_EOL;
		echo "<table>".PHP_EOL;
		echo "<tr>".PHP_EOL;
		echo "<th>#</th>".PHP_EOL;
		echo "<th>Count</th>".PHP_EOL;
		echo "<th>Ingredient 1</th>".PHP_EOL;
		echo "<th>Ingredient 2</th>".PHP_EOL;
		echo "<th>Ingredient 3</th>".PHP_EOL;
		echo "<th>Effects</th>".PHP_EOL;
		echo "<th>Value</th>".PHP_EOL;
		echo "</tr>".PHP_EOL;
		foreach($this->temp_potion as $i => $potion) {
			$where = "iid = '$potion->ingredient1'";
			db_CRUD::select('ingredient', 'i_name', $where);
			$i_1 = db_CRUD::get_result()[0];
			$where = "iid = '$potion->ingredient2'";
			db_CRUD::select('ingredient', 'i_name', $where);
			$i_2 = db_CRUD::get_result()[0];
			if($potion->ingredient_cnt == 3) {
				$where = "iid = '$potion->ingredient3'";
				db_CRUD::select('ingredient', 'i_name', $where);
				$i_3 = db_CRUD::get_result()[0];
			}
			else
				$i_3 = ' ';
			$effect_list = '';
			//if purity perk is active: format effects to show voided effects
			if($pure_perk == 'y') {
				foreach($potion->effect as $key => $effect) {
					db_CRUD::select('effect', 'e_name, poison', 'eid = '.$effect);
					$result = db_CRUD::get_result();
					if($potion->poison == $result[1])
						$effect_list .= $result[0];
					else
						$effect_list .= "<del>".$result[0]."</del>";
					if($key < count($potion->effect)-1)
						$effect_list .= ", ";
				}
			}
			else {
				foreach($potion->effect as $key => $effect) {
				db_CRUD::select('effect', 'e_name', 'eid = '.$effect);
					$effect_list .= db_CRUD::get_result()[0];
					if($key < count($potion->effect)-1)
						$effect_list .= ", ";
				}
			}
			echo "<tr>".PHP_EOL;
			echo "<td>".($i+1)."</td>".PHP_EOL;
			echo "<td>$potion->cnt</td>".PHP_EOL;
			echo "<td>$i_1</td>".PHP_EOL;
			echo "<td>$i_2</td>".PHP_EOL;
			echo "<td>$i_3</td>".PHP_EOL;
			echo "<td>$effect_list</td>".PHP_EOL;
			echo "<td>$potion->value</td>".PHP_EOL;
			echo "</tr>".PHP_EOL;
		}
		echo "</table>".PHP_EOL;
	}
	
	//calculate the value of each potion in temp_potion
	function calc_value_potion ($level, $alch_perk, $phys_perk, $bene_perk, $pois_perk, $pure_perk) {
		db_CRUD::select('ingredient_effect', 'eid, iid, magnitude_modifier, gold_modifier', "iid in (select iid from effect where specials = 'y')", 'eid');
		$nonStandard_data = db_CRUD::get_result();
		$cost_arr = array();
		foreach($this->temp_potion as $potion) {
			$cost_arr = array();
			//for each effect in potion: calculate price of effect
			foreach($potion->effect as $eff) {
				$select =  "eid, base_cost, base_magnitude, base_duration, poison, specials";
				$where = "eid = $eff";
				db_CRUD::select('effect', $select, $where);
				$result = db_CRUD::get_result();
				$mag_mod = 0;
				$gold_mod = 0;
				//if ingredient includes special variants: check if ingredient used is non-standard
				if($result[5] == 'y') {
					foreach($nonStandard_data as $nsd) {
						if($potion->ingredient1 == $nsd[1] && $eff == $nsd[0]) {
							if(($nsd[2] * $nsd[3]) > ($mag_mod * $gold_mod)) {
								$mag_mod = $nsd[2];
								$gold_mod = $nsd[3];
							}
						}
						else if($potion->ingredient2 == $nsd[1] && $eff == $nsd[0]) {
							if(($nsd[2] * $nsd[3]) > ($mag_mod * $gold_mod)) {
								$mag_mod = $nsd[2];
								$gold_mod = $nsd[3];
							}
						}
						else if($potion->ingredient_cnt == 3 && $potion->ingredient3 == $nsd[1] && $eff == $nsd[0]) {
							if(($nsd[2] * $nsd[3]) > ($mag_mod * $gold_mod)) {
								$mag_mod = $nsd[2];
								$gold_mod = $nsd[3];
							}
						}
					}
				}
				else {
					$mag_mod = 1;
					$gold_mod = 1;					
				}
				//specialized effect magnitude formulas
				if($eff != 30 && $eff != 34 && $eff != 50 && $eff != 49) {
					$mag = $result[2] * $mag_mod * 4 * pow(1.5, $level/100) * (1 + $alch_perk/100);
					if($eff == 46 || $eff == 47 || $eff || 48)
						$mag *= (1 + $phys_perk/100);
					if($result[5] == 'y')
						$mag *= (1 + $pois_perk/100);
					else
						$mag *= (1 + $bene_perk/100);
					$dur = $result[3];
				}
				//common effect duration formulas
				else {
					$dur = $result[3] * 4 * pow(1.5, $level/100) * (1 + $alch_perk/100);
					if($result[5] == 'y')
						$dur *= (1 + $pois_perk/100);
					else
						$mag *= (1 + $bene_perk/100);
					$mag = $result[2];
				}
				if($mag != 0 && $dur != 0) 
					$eff_cost = floor($result[1] * $gold_mod * pow($mag, 1.1) * 0.0794328 * pow($dur, 1.1));
				else if($mag == 0)
					$eff_cost = floor($result[1] * $gold_mod * 0.0794328 * pow($dur, 1.1));
				else
					$eff_cost = floor($result[1] * pow($mag, 1.1));
				$cost_arr[] = array($eff_cost, $result[4]);
			} //end foreach $eff
			//if purity perk is active: find most valuable effect to decide potion/poison
			if($pure_perk == 'y') {
				$max = 0;
				$x = 0;
				foreach($cost_arr as $i => $cost) {
					if($cost[0] > $max) {
						$max = $cost[0];
						$x = $i;
					}
				}
				$potion->poison = $cost_arr[$x][1];
				$total = 0;
				foreach($cost_arr as $cost) {
					if($cost[1] == $cost_arr[$x][1]) {
						$total += $cost[0];
					}
				}
			}
			//if no purity perk, sum all effect values
			else {
				$total = 0;
				foreach($cost_arr as $cost) {
					$total += $cost[0];
				}
			}
			$potion->value = $total;
		} //end foreach $potion
	}
	
	//populate potion_list
	function temp_to_list () {
		$temp = array();
		foreach($this->temp_potion as $potion) {
			$temp[] = clone $potion;
		}
		while(count($temp) != 0) {
			//find highest value potion in temp and push clone to potion_list
			$max = 0;
			$ingredients = 0;
			$i = 0;
			foreach($temp as $index => $potion) {
				if(($potion->value > $max) || ($potion->value == $max && $potion->ingredient_cnt < $ingredients)) {
					$i = $index;
					$max = $potion->value;
					$ingredients = $potion->ingredient_cnt;
				}
			}
			$this->potion_list[] = clone $temp[$i];
			//remove newly used ingredients from ingredient_list
			$used = array($temp[$i]->ingredient1, $temp[$i]->ingredient2);
			if($temp[$i]->ingredient3 != NULL)
				$used[] = $temp[$i]->ingredient3;
			$temp_arr = array();
			foreach($this->ingredient_list as $ingredient => $count) {
				if(in_array($ingredient, $used)) {
					$temp_arr[$ingredient] = $count - $temp[$i]->cnt;
				}
				else
					$temp_arr[$ingredient] = $count;
			}
			$this->ingredient_list = $temp_arr;
			$removing = array();
			foreach($temp as $i => $potion) {
				$temp_arr = array($this->ingredient_list[$potion->ingredient1], $this->ingredient_list[$potion->ingredient2]);
				if($potion->ingredient3 != NULL)
					$temp_arr[] = $this->ingredient_list[$potion->ingredient3];
				sort($temp_arr);
				$potion->cnt = $temp_arr[0];
				if($temp_arr[0] <= 0)
					$removing[] = $i;
			}
			//remove empty potions from temp
			while(count($removing) > 0){
				array_splice($temp, array_pop($removing), 1);
			}
		}
	}
}
