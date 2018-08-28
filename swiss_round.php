<?php
require_once __DIR__.'/Pairings.php';
require_once __DIR__.'/MaxWeightMatching.php';
$no_of_players = (isset($_GET['players'])?$_GET['players']:15);
$no_of_rounds = (isset($_GET['rounds'])?$_GET['rounds']:6);
$players = [];
for ($i = 0; $i < $no_of_players; $i++) {
	array_push($players, ['id'=> 'Player'.($i+1),  'points'=> 0, 'opponents'=>  [], 'paired'=>false ]);
}

$bye_data = ['id'=> 'BYE', 'points'=>  0, 'opponents'=> []];

echo "$no_of_rounds rounds of swiss. The left players always won. Draws are given on specific index<br />";

For ($round_number = 1; $round_number <= $no_of_rounds ;$round_number++) {
	echo "There is ".count($players)." players <br/>";
	shuffle($players);
	if(count($players) % 2 != 0) {
		array_push($players, $bye_data);
	}
	echo "Pairing for Round $round_number <br />";
	$pairings = new Pairings($players);
	$pairing = $pairings->pairing;

	for ($i=0; $i<count($pairing); $i++) {
		if ($players[$i] != null && !$players[$i]['paired']) {
			$p1 = $players[$i];
			$p2 = $players[$pairing[$i]];
			if($p2['id'] == "BYE") {
				echo($p1['id']." (".$p1['points'].") BYE <br />");
				array_push($bye_data['opponents'], $p1['id']);
			} else {
		        echo($p1['id']." (".$p1['points'].") v ".$p2['id']." (".$p2['points'].") <br />");
			}
			if ($i % 5 == 0) {
				$players[$i]['points']+=1;
				$players[$pairing[$i]]['points']+=1;
			} else {
				$players[$i]['points']+=3;
			}
			
			array_push($players[$i]['opponents'], $p2['id']);
			array_push($players[$pairing[$i]]['opponents'], $p1['id']);
			$players[$i]['paired'] = true;
	        $players[$pairing[$i]]['paired'] = true;
		}
	}
	
	if ($players[count($players)-1]['id'] == 'BYE') {
		unset($players[count($players)-1]);
		$players = array_values($players);
	}
	
	for ($j=0; $j<count($players); $j++) {
		$players[$j]['paired'] = false;
	}
	if (count($bye_data['opponents'])>0) {
		echo "Bye receivers are ";
	}
	for ($j=0; $j<count($bye_data['opponents']); $j++) {
		echo $bye_data['opponents'][$j].", ";
	}
	if (count($bye_data['opponents'])>0) {
		echo "<br />";
	}
	echo "<br />";
}
