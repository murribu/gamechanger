<?php 
require_once('../Connections/main.php');
require_once('../Connections/glogs.php');

// Sorts multidimensional array by key
function sortByOrder($a, $b) {
	return $a['priority'] - $b['priority'];
}

include_once('includes/functions.php');
sec_session_start();

include_once('includes/metric_variables.php');



// Sets variables to blank/default before going through data
for($i = 1; $i <= 50; $i++){
	$type_name = 'type_' . $i;
	$data_name = 'data_' . $i;
	$priority_name = 'priority_' . $i;
	
	$$type_name = '';
	$$data_name = '';
	$$priority_name = 0;
}


$finish_ab = 'Y';
$on_deck = 'N';
$include_CLI = 'N';
$vid_player = 'reg';
$delay = 50;
$ignore = array();
$priority = array();

// If form was submitted
if(isset($_POST['submit'])){
	
	// Finish AB
	if(isset($_POST['finish_ab'])){
		if($_POST['finish_ab'] == 'Y'){
			$finish_ab = 'Y';
		}else if($_POST['finish_ab'] == 'N'){
			$finish_ab = 'N';
		}
	}
	setcookie("finish_ab", $finish_ab,time()+3600*24*180);
	$_COOKIE['finish_ab'] = $finish_ab;
  
  // On Deck
	if(isset($_POST['on_deck'])){
		if($_POST['on_deck'] == 'Y'){
			$on_deck = 'Y';
		}else if($_POST['on_deck'] == 'N'){
			$on_deck = 'N';
		}
	}
	setcookie("on_deck", $on_deck,time()+3600*24*180);
	$_COOKIE['on_deck'] = $on_deck;
	
	// Include CLI
	if(isset($_POST['include_CLI'])){
		if($_POST['include_CLI'] == 'Y'){
			$include_CLI = 'Y';
		}else if($_POST['include_CLI'] == 'N'){
			$include_CLI = 'N';
		}
	}
	setcookie("include_CLI", $include_CLI,time()+3600*24*180);
	$_COOKIE['include_CLI'] = $include_CLI;
	
	// Video Player
	if(isset($_POST['vid_player'])){
		if($_POST['vid_player'] == 'reg'){
			$vid_player = 'reg';
		}else if($_POST['vid_player'] == 'old'){
			$vid_player = 'old';
		}
	}
	setcookie("vid_player", $vid_player,time()+3600*24*180);
	$_COOKIE['vid_player'] = $vid_player;
	
	// Ignore
	$ignore = array();
	if(isset($_POST['xARI'])){
		$ignore[] = 'ARI';
	}
	if(isset($_POST['xATL'])){
		$ignore[] = 'ATL';
	}
	if(isset($_POST['xBAL'])){
		$ignore[] = 'BAL';
	}
	if(isset($_POST['xBOS'])){
		$ignore[] = 'BOS';
	}
	if(isset($_POST['xCHC'])){
		$ignore[] = 'CHC';
	}
	if(isset($_POST['xCWS'])){
		$ignore[] = 'CWS';
	}
	if(isset($_POST['xCIN'])){
		$ignore[] = 'CIN';
	}
	if(isset($_POST['xCLE'])){
		$ignore[] = 'CLE';
	}
	if(isset($_POST['xCOL'])){
		$ignore[] = 'COL';
	}
	if(isset($_POST['xDET'])){
		$ignore[] = 'DET';
	}
	if(isset($_POST['xHOU'])){
		$ignore[] = 'HOU';
	}
	if(isset($_POST['xKC'])){
		$ignore[] = 'KC';
	}
	if(isset($_POST['xLAA'])){
		$ignore[] = 'LAA';
	}
	if(isset($_POST['xLAD'])){
		$ignore[] = 'LAD';
	}
	if(isset($_POST['xMIA'])){
		$ignore[] = 'MIA';
	}
	if(isset($_POST['xMIL'])){
		$ignore[] = 'MIL';
	}
	if(isset($_POST['xMIN'])){
		$ignore[] = 'MIN';
	}
	if(isset($_POST['xNYM'])){
		$ignore[] = 'NYM';
	}
	if(isset($_POST['xNYY'])){
		$ignore[] = 'NYY';
	}
	if(isset($_POST['xOAK'])){
		$ignore[] = 'OAK';
	}
	if(isset($_POST['xPHI'])){
		$ignore[] = 'PHI';
	}
	if(isset($_POST['xPIT'])){
		$ignore[] = 'PIT';
	}
	if(isset($_POST['xSD'])){
		$ignore[] = 'SD';
	}
	if(isset($_POST['xSF'])){
		$ignore[] = 'SF';
	}
	if(isset($_POST['xSEA'])){
		$ignore[] = 'SEA';
	}
	if(isset($_POST['xSTL'])){
		$ignore[] = 'STL';
	}
	if(isset($_POST['xTB'])){
		$ignore[] = 'TB';
	}
	if(isset($_POST['xTEX'])){
		$ignore[] = 'TEX';
	}
	if(isset($_POST['xTOR'])){
		$ignore[] = 'TOR';
	}
	if(isset($_POST['xWSH'])){
		$ignore[] = 'WSH';
	}
	if(empty($ignore)){
		setcookie("ignore", '',time()+3600*24*180);
		$_COOKIE['ignore'] = '';
	}else{
		setcookie("ignore", json_encode($ignore),time()+3600*24*180);
		$_COOKIE['ignore'] = json_encode($ignore);
	}
	
	// Delay
	if(isset($_POST['delay'])){
		if($_POST['delay'] >= 0 && $_POST['delay'] <= 100){
			$delay = $_POST['delay'];
		}
	}
	setcookie("delay", $delay,time()+3600*24*180);
	$_COOKIE['delay'] = $delay;
	
	// Priority
	$priority_pre = array();
	
	$counter = 0;
	for($i = 1; $i <= 50; $i++){
		$type_name = 'type_' . $i;
		$data_name = 'data_' . $i;
		$priority_name = 'priority_' . $i;
		
		if($_POST[$type_name] != '' && isset($_POST[$data_name]) && $_POST[$priority_name] != 0){
			$priority_pre[$counter]['type'] = $_POST[$type_name];
			$priority_pre[$counter]['data'] = $_POST[$data_name];
			$priority_pre[$counter]['priority'] = $_POST[$priority_name];
			$counter++;
		}
	}
	
	usort($priority_pre, 'sortByOrder');// uses function set above
	
	// Goes through again to eliminate any missing priority numbers
	$priority = array();
	for($i = 0; $i < count($priority_pre); $i++){
		$ii = $i + 1;
		
		$priority[$i]['type'] = $priority_pre[$i]['type'];
		$priority[$i]['data'] = $priority_pre[$i]['data'];
		$priority[$i]['priority'] = $ii;
	}
	
	setcookie("priority", json_encode($priority),time()+3600*24*180);
	$_COOKIE['priority'] = json_encode($priority);
}



if(isset($_COOKIE['finish_ab'])){
	$finish_ab = $_COOKIE['finish_ab'];
}

if(isset($_COOKIE['on_deck'])){
	$on_deck = $_COOKIE['on_deck'];
}

if(isset($_COOKIE['include_CLI'])){
	$include_CLI = $_COOKIE['include_CLI'];
}

if(isset($_COOKIE['vid_player'])){
	$vid_player = $_COOKIE['vid_player'];
}

if(isset($_COOKIE['delay']) && $_COOKIE['delay'] >= 0 && $_COOKIE['delay'] <= 100){
	$delay = $_COOKIE['delay'];
}

if(isset($_COOKIE['ignore'])){
	$ignore = json_decode(stripslashes($_COOKIE['ignore']), true);
}

if(isset($_COOKIE['priority'])){
	$priority = json_decode(stripslashes($_COOKIE['priority']), true);
	for($i = 0; $i < 50; $i++){
		$ii = $i + 1;
		$type_name = 'type_' . $ii;
		$data_name = 'data_' . $ii;
		$priority_name = 'priority_' . $ii;
		
		if(is_array($priority) && array_key_exists($i,$priority)){
			$$type_name = $priority[$i]['type'];
			$$data_name = $priority[$i]['data'];
			$$priority_name = $priority[$i]['priority'];
		}
	}
}

$settings = array('finish_ab' => $finish_ab, 'on_deck' => $on_deck, 'include_CLI' => $include_CLI, 'vid_player' => $vid_player, 'delay' => $delay, 'ignore' => $ignore, 'priority' => $priority);




$player = '';
$type = '';

$year = date("Y");

$query_plyrs = "
SELECT nameFirst, nameLast, mlbam.ID, p.Pos, IFNULL(cur.teamID,'') AS teamID
FROM players AS p 
LEFT JOIN mlbam USING (playerID) 
LEFT JOIN Master USING (playerID) 
LEFT JOIN (
  SELECT players.playerID, players.teamID
  FROM players
  INNER JOIN (
    SELECT playerID, MAX(stint) AS stint
    FROM players
    WHERE yearID = $year
    GROUP BY playerID
  ) AS c ON (players.playerID = c.playerID AND players.stint = c.stint)
  WHERE players.yearID = $year
) AS cur USING (playerID)
WHERE p.yearID >= $year - 1 AND p.stint > 0 AND mlbam.ID IS NOT NULL 
GROUP BY p.playerID 
ORDER BY nameLast, nameFirst
";
$stmt_plyrs = $db_main->prepare($query_plyrs);
$stmt_plyrs->execute();
while($row_plyrs = $stmt_plyrs->fetch(PDO::FETCH_ASSOC)){
	$player_display = $row_plyrs['nameLast'] . ", " . $row_plyrs['nameFirst'];
  if($row_plyrs['teamID'] != ''){
    $player_display .=  " (" . $row_plyrs['teamID'] . ")";
  }
  
  $players[] = array('display' => $player_display, 'id' => $row_plyrs['ID']);
	
	if($row_plyrs['Pos'] == 'P'){
		$pos = 'P';
	}else{
		$pos = 'PosP';
	}
	$posPlayers[$row_plyrs['ID']] = $pos;
}





$query_CLI = "SELECT home AS home_teamID, (away_CLI + home_CLI) / 2 AS aCLI FROM todaysGames";
$stmt_CLI = $db_glogs->prepare($query_CLI);
$stmt_CLI->execute();
$games_CLI = array();
while($row_CLI = $stmt_CLI->fetch(PDO::FETCH_ASSOC)){
	
	if($row_CLI['home_teamID'] == 'CHW'){
		$home_teamID = 'CWS';
	}else if($row_CLI['home_teamID'] == 'KCR'){
		$home_teamID = 'KC';
	}else if($row_CLI['home_teamID'] == 'SDP'){
		$home_teamID = 'SD';
	}else if($row_CLI['home_teamID'] == 'SFG'){
		$home_teamID = 'SF';
	}else if($row_CLI['home_teamID'] == 'TBR'){
		$home_teamID = 'TB';
	}else if($row_CLI['home_teamID'] == 'WAS'){
		$home_teamID = 'WSH';
	}else{
		$home_teamID = $row_CLI['home_teamID'];
	}
	
  if(is_numeric($row_CLI['aCLI'])){
    $games_CLI[$home_teamID] = $row_CLI['aCLI'];
  }
}





$teams = array(
	array('display' => 'Arizona Diamondbacks', 'id' => 'ARI'),
	array('display' => 'Atlanta Braves', 'id' => 'ATL'),
	array('display' => 'Baltimore Orioles', 'id' => 'BAL'),
	array('display' => 'Boston Red Sox', 'id' => 'BOS'),
	array('display' => 'Chicago Cubs', 'id' => 'CHC'),
	array('display' => 'Chicago White Sox', 'id' => 'CWS'),
	array('display' => 'Cincinnati Reds', 'id' => 'CIN'),
	array('display' => 'Cleveland Indians', 'id' => 'CLE'),
	array('display' => 'Colorado Rockies', 'id' => 'COL'),
	array('display' => 'Detroit Tigers', 'id' => 'DET'),
	array('display' => 'Houston Astros', 'id' => 'HOU'),
	array('display' => 'Kansas City Royals', 'id' => 'KC'),
	array('display' => 'Los Angeles Angels of Anaheim', 'id' => 'LAA'),
	array('display' => 'Los Angeles Dodgers', 'id' => 'LAD'),
	array('display' => 'Miami Marlins', 'id' => 'MIA'),
	array('display' => 'Milwaukee Brewers', 'id' => 'MIL'),
	array('display' => 'Minnesota Twins', 'id' => 'MIN'),
	array('display' => 'New York Mets', 'id' => 'NYM'),
	array('display' => 'New York Yankees', 'id' => 'NYY'),
	array('display' => 'Oakland Athletics', 'id' => 'OAK'),
	array('display' => 'Philadelphia Phillies', 'id' => 'PHI'),
	array('display' => 'Pittsburgh Pirates', 'id' => 'PIT'),
	array('display' => 'San Diego Padres', 'id' => 'SD'),
	array('display' => 'San Francisco Giants', 'id' => 'SF'),
	array('display' => 'Seattle Mariners', 'id' => 'SEA'),
	array('display' => 'St. Louis Cardinals', 'id' => 'STL'),
	array('display' => 'Tampa Bay Rays', 'id' => 'TB'),
	array('display' => 'Texas Rangers', 'id' => 'TEX'),
	array('display' => 'Toronto Blue Jays', 'id' => 'TOR'),
	array('display' => 'Washington Nationals', 'id' => 'WSH')
);

$LI = array(
	array('display' => '>= 10.0', 'id' => 10.0),
	array('display' => '>= 9.0', 'id' => 9.0),
	array('display' => '>= 8.0', 'id' => 8.0),
	array('display' => '>= 7.0', 'id' => 7.0),
	array('display' => '>= 6.0', 'id' => 6.0),
	array('display' => '>= 5.0', 'id' => 5.0),
	array('display' => '>= 4.5', 'id' => 4.5),
	array('display' => '>= 4.0', 'id' => 4.0),
	array('display' => '>= 3.5', 'id' => 3.5),
	array('display' => '>= 3.0', 'id' => 3.0),
	array('display' => '>= 2.5', 'id' => 2.5),
	array('display' => '>= 2.0', 'id' => 2.0),
	array('display' => '>= 1.5', 'id' => 1.5),
	array('display' => '>= 1.0', 'id' => 1.0)	
);

$NoNo = array(
	array('display' => 'Through 8 innings', 'id' => 8),
	array('display' => 'Through 7 innings', 'id' => 7),
	array('display' => 'Through 6 innings', 'id' => 6),
	array('display' => 'Through 5 innings', 'id' => 5),
	array('display' => 'Through 4 innings', 'id' => 4),
	array('display' => 'Through 3 innings', 'id' => 3),
	array('display' => 'Through 2 innings', 'id' => 2),
	array('display' => 'Through 1 inning', 'id' => 1)
);

$Misc = array(
	array('display' => 'Position Player Pitching', 'id' => 'PosP_pit'),
	array('display' => 'Extra Innings', 'id' => 'extra'),
	array('display' => 'Replay Challenge/Review', 'id' => 'replay')
);

$GameSit = array(
	array('display' => 'Tie game through 5 innings', 'id' => 'through5_tie'),
	array('display' => 'Tie game through 6 innings', 'id' => 'through6_tie'),
	array('display' => 'Tie game through 7 innings', 'id' => 'through7_tie'),
	array('display' => 'Tie game through 8 innings', 'id' => 'through8_tie'),
	array('display' => '1-run game through 5 innings', 'id' => 'through5_1run'),
	array('display' => '1-run game through 6 innings', 'id' => 'through6_1run'),
	array('display' => '1-run game through 7 innings', 'id' => 'through7_1run'),
	array('display' => '1-run game through 8 innings', 'id' => 'through8_1run')
);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?php include('header.php') ?>
    <title>MLB.tv Game Changer</title>
  </head>
  <body>
    <div id="shadow">
      <div id="layout" align="center">
      	<div class="subpage_title_stripe">MLB.tv Game Changer</div>
        <div class="subpage_bg">
        	<a href="#FAQ">Requirements / Instructions</a><br><br>
  				<button class="pause" id="launch">Launch Video</button>
          <br>
          *Video loads approx. 5 sec after tab is launched<br>
          Note: Unfortunately, Full-screen mode cannot be maintained after a game changes
          <br><br>
          <div style="display:inline-block">
            <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" name="priority">
            <div align="left" style="float:left; width:200px;">
            	<input type="submit" name="submit" class="form_submit" value="Save Settings">
              <br>
              
              <h2>If Higher Priority Game Becomes Available:</h2><br>
              <input type="radio" id="finish_ab_Y" name="finish_ab" value="Y" <?php if($finish_ab == 'Y'){echo 'checked="checked"';}?> />
              <label for="finish_ab_Y"><span></span> Finish current at bat</label>
              <br>
              <input type="radio" id="finish_ab_N" name="finish_ab" value="N" <?php if($finish_ab == 'N'){echo 'checked="checked"';}?> />
              <label for="finish_ab_N"><span></span> Leave current at bat</label>
              <br><br>
              
              <h2>If Batter is On Deck with < 2 Outs:</h2><br>
              <input type="radio" id="on_deck_Y" name="on_deck" value="Y" <?php if($on_deck == 'Y'){echo 'checked="checked"';}?> />
              <label for="on_deck_Y"><span></span> Switch to Game</label>
              <br>
              <input type="radio" id="on_deck_N" name="on_deck" value="N" <?php if($on_deck == 'N'){echo 'checked="checked"';}?> />
              <label for="on_deck_N"><span></span> Wait Until At Bat</label>
              <br><br>
              
              <h2>Teams to Ignore</h2><br>
            	<input name="xARI" type="checkbox" value="ARI" id="xARI" <?php if(is_array($ignore) && in_array('ARI',$ignore)){echo 'checked="checked"';}?> />
              <label for="xARI"><span></span>ARI</label>
              <br>
              <input name="xATL" type="checkbox" value="ATL" id="xATL" <?php if(is_array($ignore) && in_array('ATL',$ignore)){echo 'checked="checked"';}?> />
              <label for="xATL"><span></span>ATL</label>
              <br>
              <input name="xBAL" type="checkbox" value="BAL" id="xBAL" <?php if(is_array($ignore) && in_array('BAL',$ignore)){echo 'checked="checked"';}?> />
              <label for="xBAL"><span></span>BAL</label>
              <br>
              <input name="xBOS" type="checkbox" value="BOS" id="xBOS" <?php if(is_array($ignore) && in_array('BOS',$ignore)){echo 'checked="checked"';}?> />
              <label for="xBOS"><span></span>BOS</label>
              <br>
              <input name="xCHC" type="checkbox" value="CHC" id="xCHC" <?php if(is_array($ignore) && in_array('CHC',$ignore)){echo 'checked="checked"';}?> />
              <label for="xCHC"><span></span>CHC</label>
              <br>
              <input name="xCWS" type="checkbox" value="CWS" id="xCWS" <?php if(is_array($ignore) && in_array('CWS',$ignore)){echo 'checked="checked"';}?> />
              <label for="xCWS"><span></span>CWS</label>
              <br>
              <input name="xCIN" type="checkbox" value="CIN" id="xCIN" <?php if(is_array($ignore) && in_array('CIN',$ignore)){echo 'checked="checked"';}?> />
              <label for="xCIN"><span></span>CIN</label>
              <br>
              <input name="xCLE" type="checkbox" value="CLE" id="xCLE" <?php if(is_array($ignore) && in_array('CLE',$ignore)){echo 'checked="checked"';}?> />
              <label for="xCLE"><span></span>CLE</label>
              <br>
              <input name="xCOL" type="checkbox" value="COL" id="xCOL" <?php if(is_array($ignore) && in_array('COL',$ignore)){echo 'checked="checked"';}?> />
              <label for="xCOL"><span></span>COL</label>
              <br>
              <input name="xDET" type="checkbox" value="DET" id="xDET" <?php if(is_array($ignore) && in_array('DET',$ignore)){echo 'checked="checked"';}?> />
              <label for="xDET"><span></span>DET</label>
              <br>
              <input name="xHOU" type="checkbox" value="HOU" id="xHOU" <?php if(is_array($ignore) && in_array('HOU',$ignore)){echo 'checked="checked"';}?> />
              <label for="xHOU"><span></span>HOU</label>
              <br>
              <input name="xKC" type="checkbox" value="KC" id="xKC" <?php if(is_array($ignore) && in_array('KC',$ignore)){echo 'checked="checked"';}?> />
              <label for="xKC"><span></span>KC</label>
              <br>
              <input name="xLAA" type="checkbox" value="LAA" id="xLAA" <?php if(is_array($ignore) && in_array('LAA',$ignore)){echo 'checked="checked"';}?> />
              <label for="xLAA"><span></span>LAA</label>
              <br>
              <input name="xLAD" type="checkbox" value="LAD" id="xLAD" <?php if(is_array($ignore) && in_array('LAD',$ignore)){echo 'checked="checked"';}?> />
              <label for="xLAD"><span></span>LAD</label>
              <br>
              <input name="xMIA" type="checkbox" value="MIA" id="xMIA" <?php if(is_array($ignore) && in_array('MIA',$ignore)){echo 'checked="checked"';}?> />
              <label for="xMIA"><span></span>MIA</label>
              <br>
              <input name="xMIL" type="checkbox" value="MIL" id="xMIL" <?php if(is_array($ignore) && in_array('MIL',$ignore)){echo 'checked="checked"';}?> />
              <label for="xMIL"><span></span>MIL</label>
              <br>
              <input name="xMIN" type="checkbox" value="MIN" id="xMIN" <?php if(is_array($ignore) && in_array('MIN',$ignore)){echo 'checked="checked"';}?> />
              <label for="xMIN"><span></span>MIN</label>
              <br>
              <input name="xNYM" type="checkbox" value="NYM" id="xNYM" <?php if(is_array($ignore) && in_array('NYM',$ignore)){echo 'checked="checked"';}?> />
              <label for="xNYM"><span></span>NYM</label>
              <br>
              <input name="xNYY" type="checkbox" value="NYY" id="xNYY" <?php if(is_array($ignore) && in_array('NYY',$ignore)){echo 'checked="checked"';}?> />
              <label for="xNYY"><span></span>NYY</label>
              <br>
              <input name="xOAK" type="checkbox" value="OAK" id="xOAK" <?php if(is_array($ignore) && in_array('OAK',$ignore)){echo 'checked="checked"';}?> />
              <label for="xOAK"><span></span>OAK</label>
              <br>
              <input name="xPHI" type="checkbox" value="PHI" id="xPHI" <?php if(is_array($ignore) && in_array('PHI',$ignore)){echo 'checked="checked"';}?> />
              <label for="xPHI"><span></span>PHI</label>
              <br>
              <input name="xPIT" type="checkbox" value="PIT" id="xPIT" <?php if(is_array($ignore) && in_array('PIT',$ignore)){echo 'checked="checked"';}?> />
              <label for="xPIT"><span></span>PIT</label>
              <br>
              <input name="xSD" type="checkbox" value="SD" id="xSD" <?php if(is_array($ignore) && in_array('SD',$ignore)){echo 'checked="checked"';}?> />
              <label for="xSD"><span></span>SD</label>
              <br>
              <input name="xSF" type="checkbox" value="SF" id="xSF" <?php if(is_array($ignore) && in_array('SF',$ignore)){echo 'checked="checked"';}?> />
              <label for="xSF"><span></span>SF</label>
              <br>
              <input name="xSEA" type="checkbox" value="SEA" id="xSEA" <?php if(is_array($ignore) && in_array('SEA',$ignore)){echo 'checked="checked"';}?> />
              <label for="xSEA"><span></span>SEA</label>
              <br>
              <input name="xSTL" type="checkbox" value="STL" id="xSTL" <?php if(is_array($ignore) && in_array('STL',$ignore)){echo 'checked="checked"';}?> />
              <label for="xSTL"><span></span>STL</label>
              <br>
              <input name="xTB" type="checkbox" value="TB" id="xTB" <?php if(is_array($ignore) && in_array('TB',$ignore)){echo 'checked="checked"';}?> />
              <label for="xTB"><span></span>TB</label>
              <br>
              <input name="xTEX" type="checkbox" value="TEX" id="xTEX" <?php if(is_array($ignore) && in_array('TEX',$ignore)){echo 'checked="checked"';}?> />
              <label for="xTEX"><span></span>TEX</label>
              <br>
              <input name="xTOR" type="checkbox" value="TOR" id="xTOR" <?php if(is_array($ignore) && in_array('TOR',$ignore)){echo 'checked="checked"';}?> />
              <label for="xTOR"><span></span>TOR</label>
              <br>
              <input name="xWSH" type="checkbox" value="WSH" id="xWSH" <?php if(is_array($ignore) && in_array('WSH',$ignore)){echo 'checked="checked"';}?> />
              <label for="xWSH"><span></span>WSH</label>
              <br><br>
              <h2>Championship Leverage Index:</h2><br>
              <input type="radio" id="include_CLI_Y" name="include_CLI" value="Y" <?php if($include_CLI == 'Y'){echo 'checked="checked"';}?> />
              <label for="include_CLI_Y"><span></span> Include in Leverage Index</label>
              <br>
              <input type="radio" id="include_CLI_N" name="include_CLI" value="N" <?php if($include_CLI == 'N'){echo 'checked="checked"';}?> />
              <label for="include_CLI_N"><span></span> Do not include in Leverage Index</label>
              <br><br>
              <h2>Delay:</h2>
              <select class="dropdown_select" id="delay" name="delay">
              <?php for($i = 0; $i <= 60; $i++){?>
                <option value="<?php echo $i;?>" <?php if($delay == $i){echo 'selected';}?>><?php echo $i;?> sec</option>
              <?php }?>
            	</select>
              <br><br>
              <h2>Video Player:</h2><br>
              <input type="radio" id="vid_player_reg" name="vid_player" value="reg" <?php if($vid_player == 'reg'){echo 'checked="checked"';}?> />
              <label for="vid_player_reg"><span></span> Regular MLB.tv video player</label>
              <br>
              <input type="radio" id="vid_player_old" name="vid_player" value="old" <?php if($vid_player == 'old'){echo 'checked="checked"';}?> />
              <label for="vid_player_old"><span></span> Old MLB.tv video player</label>
              <br><br>
              <input type="submit" name="submit" class="form_submit" value="Save Settings">
            </div>
            <div style="float:left; width:400px;" align="center">
              <h2>Priority List</h2><br>
              <table>
              <thead>
              <tr>
                <th>Type</th>
                <th width="200">Player/Team/Value</th>
                <th>Priority</th>
              </tr>
              </thead>
              <tbody>
              
              <?php 
              for($i = 1; $i <= 50; $i++){
                $type_name = 'type_' . $i;
                $data_name = 'data_' . $i;
                $priority_name = 'priority_' . $i;
                ?>
                <tr>
                  <td>
                  <select class="dropdown_select" id="<?php echo $type_name;?>" name="<?php echo $type_name;?>" onchange="configureDropDownLists(this,document.getElementById('<?php echo $data_name;?>'))">
                    <option value=""></option>
                    <option value="bat" <?php if($$type_name == 'bat'){echo 'selected';}?>>Batter</option>
                    <option value="pit" <?php if($$type_name == 'pit'){echo 'selected';}?>>Pitcher</option>
                    <option value="run" <?php if($$type_name == 'run'){echo 'selected';}?>>Runner</option>
                    <option value="LI" <?php if($$type_name == 'LI'){echo 'selected';}?>>Leverage Index</option>
                    <option value="team" <?php if($$type_name == 'team'){echo 'selected';}?>>Team</option>
                    <option value="NoNo" <?php if($$type_name == 'NoNo'){echo 'selected';}?>>No-Hitter</option>
                    <option value="GameSit" <?php if($$type_name == 'GameSit'){echo 'selected';}?>>Game Situation</option>
                    <option value="team_bat" <?php if($$type_name == 'team_bat'){echo 'selected';}?>>Team Batting</option>
                    <option value="team_pit" <?php if($$type_name == 'team_pit'){echo 'selected';}?>>Team Pitching</option>
                    <option value="Misc" <?php if($$type_name == 'Misc'){echo 'selected';}?>>Miscellaneous</option>
                  </select>
                  </td>
                  <?php
                  if($$type_name == ''){
                    ?>
                    <td>
                    <select class="dropdown_select" id="<?php echo $data_name;?>" name="<?php echo $data_name;?>">
                    </select>
                    </td>
                  <?php
                  }else{
                    if($$type_name == 'bat' || $$type_name == 'pit' || $$type_name == 'run'){
                      $data_array = $players;
                    }else if($$type_name == 'LI'){
                      $data_array = $LI;
                    }else if($$type_name == 'team' || $$type_name == 'team_bat' || $$type_name == 'team_pit'){
                      $data_array = $teams;
                    }else if($$type_name == 'NoNo'){
                      $data_array = $NoNo;
                    }else if($$type_name == 'GameSit'){
                      $data_array = $GameSit;
                    }else if($$type_name == 'Misc'){
                      $data_array = $Misc;
                    }?>
                    
                    <td>
                    <select class="dropdown_select" id="<?php echo $data_name;?>" name="<?php echo $data_name;?>">
                      <?php
                      for($a = 0; $a < count($data_array); $a++){
                        ?>
                        <option value="<?php echo $data_array[$a]['id'];?>" <?php if($data_array[$a]['id'] == $$data_name){echo 'selected';}?>><?php echo $data_array[$a]['display'];?></option>
                      <?php }?>
                    </select>
                    </td>
                    
                  <?php }?>
                  <td>
                  <select class="dropdown_select" id="<?php echo $priority_name;?>" name="<?php echo $priority_name;?>">
                    <option value="0"></option>
                    <option value="1" <?php if($$priority_name == '1'){echo 'selected';}?>>1</option>
                    <option value="2" <?php if($$priority_name == '2'){echo 'selected';}?>>2</option>
                    <option value="3" <?php if($$priority_name == '3'){echo 'selected';}?>>3</option>
                    <option value="4" <?php if($$priority_name == '4'){echo 'selected';}?>>4</option>
                    <option value="5" <?php if($$priority_name == '5'){echo 'selected';}?>>5</option>
                    <option value="6" <?php if($$priority_name == '6'){echo 'selected';}?>>6</option>
                    <option value="7" <?php if($$priority_name == '7'){echo 'selected';}?>>7</option>
                    <option value="8" <?php if($$priority_name == '8'){echo 'selected';}?>>8</option>
                    <option value="9" <?php if($$priority_name == '9'){echo 'selected';}?>>9</option>
                    <option value="10" <?php if($$priority_name == '10'){echo 'selected';}?>>10</option>
                    <option value="11" <?php if($$priority_name == '11'){echo 'selected';}?>>11</option>
                    <option value="12" <?php if($$priority_name == '12'){echo 'selected';}?>>12</option>
                    <option value="13" <?php if($$priority_name == '13'){echo 'selected';}?>>13</option>
                    <option value="14" <?php if($$priority_name == '14'){echo 'selected';}?>>14</option>
                    <option value="15" <?php if($$priority_name == '15'){echo 'selected';}?>>15</option>
                    <option value="16" <?php if($$priority_name == '16'){echo 'selected';}?>>16</option>
                    <option value="17" <?php if($$priority_name == '17'){echo 'selected';}?>>17</option>
                    <option value="18" <?php if($$priority_name == '18'){echo 'selected';}?>>18</option>
                    <option value="19" <?php if($$priority_name == '19'){echo 'selected';}?>>19</option>
                    <option value="20" <?php if($$priority_name == '20'){echo 'selected';}?>>20</option>
                    <option value="21" <?php if($$priority_name == '21'){echo 'selected';}?>>21</option>
                    <option value="22" <?php if($$priority_name == '22'){echo 'selected';}?>>22</option>
                    <option value="23" <?php if($$priority_name == '23'){echo 'selected';}?>>23</option>
                    <option value="24" <?php if($$priority_name == '24'){echo 'selected';}?>>24</option>
                    <option value="25" <?php if($$priority_name == '25'){echo 'selected';}?>>25</option>
                    <option value="26" <?php if($$priority_name == '26'){echo 'selected';}?>>26</option>
                    <option value="27" <?php if($$priority_name == '27'){echo 'selected';}?>>27</option>
                    <option value="28" <?php if($$priority_name == '28'){echo 'selected';}?>>28</option>
                    <option value="29" <?php if($$priority_name == '29'){echo 'selected';}?>>29</option>
                    <option value="30" <?php if($$priority_name == '30'){echo 'selected';}?>>30</option>
                    <option value="31" <?php if($$priority_name == '31'){echo 'selected';}?>>31</option>
                    <option value="32" <?php if($$priority_name == '32'){echo 'selected';}?>>32</option>
                    <option value="33" <?php if($$priority_name == '33'){echo 'selected';}?>>33</option>
                    <option value="34" <?php if($$priority_name == '34'){echo 'selected';}?>>34</option>
                    <option value="35" <?php if($$priority_name == '35'){echo 'selected';}?>>35</option>
                    <option value="36" <?php if($$priority_name == '36'){echo 'selected';}?>>36</option>
                    <option value="37" <?php if($$priority_name == '37'){echo 'selected';}?>>37</option>
                    <option value="38" <?php if($$priority_name == '38'){echo 'selected';}?>>38</option>
                    <option value="39" <?php if($$priority_name == '39'){echo 'selected';}?>>39</option>
                    <option value="40" <?php if($$priority_name == '40'){echo 'selected';}?>>40</option>
                    <option value="41" <?php if($$priority_name == '41'){echo 'selected';}?>>41</option>
                    <option value="42" <?php if($$priority_name == '42'){echo 'selected';}?>>42</option>
                    <option value="43" <?php if($$priority_name == '43'){echo 'selected';}?>>43</option>
                    <option value="44" <?php if($$priority_name == '44'){echo 'selected';}?>>44</option>
                    <option value="45" <?php if($$priority_name == '45'){echo 'selected';}?>>45</option>
                    <option value="46" <?php if($$priority_name == '46'){echo 'selected';}?>>46</option>
                    <option value="47" <?php if($$priority_name == '47'){echo 'selected';}?>>47</option>
                    <option value="48" <?php if($$priority_name == '48'){echo 'selected';}?>>48</option>
                    <option value="49" <?php if($$priority_name == '49'){echo 'selected';}?>>49</option>
                    <option value="50" <?php if($$priority_name == '50'){echo 'selected';}?>>50</option>
                    <option value="51" <?php if($$priority_name == '51'){echo 'selected';}?>>51</option>
                    <option value="52" <?php if($$priority_name == '52'){echo 'selected';}?>>52</option>
                    <option value="53" <?php if($$priority_name == '53'){echo 'selected';}?>>53</option>
                    <option value="54" <?php if($$priority_name == '54'){echo 'selected';}?>>54</option>
                    <option value="55" <?php if($$priority_name == '55'){echo 'selected';}?>>55</option>
                  </select>
                  </td>
                </tr>
              <?php }?>
              
              </tbody>
              </table>
            </div>
            <div style="float:left; width:300px;" id="games">
            	<div id="current_game"></div>
              <br>
              <h2>Games</h2><br>
              <div id="active"></div>
              <br>
              <div id="commercial"></div>
              <br>
              <div id="pre"></div>
              <br>
              <div id="post"></div>
            </div>
            <div style="clear:both"></div>
            </form>
					</div>
          <br><br>
          
          <div style="float: left; text-align: left; padding-left: 10px;">
            <button id="create_export" class="form_submit">Create file to export settings:</button><br>
            <a download="GameChanger.txt" id="downloadlink" style="display: none" >Download Settings</a>
            *Save settings prior to exporting<br><br>
            
            <br>
            Choose file to import:<br>
            <form action="GameChanger_upload.php" method="post" enctype="multipart/form-data">
              <input type="file" name="file" id="file" accept=".txt, text/plain" />
              <br><br>
              <input type="submit" name="submit" id="file_upload" value="Import Settings" style="display: none;" />
            </form>
          </div>
          <div style="clear: both"></div>
          
        	<br><br>
          <div style="float:left; width:465px;">
            <h2 id="FAQ">Requirements</h2><br>
            <div style="width:445px;" align="left">
              &#149; Must have a subscription to <a href="http://mlb.mlb.com/mlb/subscriptions/index.jsp?c_id=mlb&affiliateId=mlbMENU" target="_blank">MLB.tv</a>. This will probably work with a Radio-only subscription, however, you may need to adjust the delay setting to sync your audio feed.
              <br><br>
              &#149; Browser cookies must be enabled to save your settings. Cookies save data on your browser, which is required to save your priority list for future use. Cookies are enabled by default, so if you are unsure about this, they are most likely already enabled.
              <br><br>
              &#149; Pop-ups must be enabled on this page. The Game Changer opens games in a second browser tab/window. This will not work if pop-ups are blocked.
              <br><br>
              &#149; This will only work using a web browser, and not on a Roku/Amazon Fire TV/Chromecast type device.
              <br><br>
              &#149; This window needs to remain open in your browser for this to work. If you close this window, the game currently showing will no longer change based on your priority list.
            </div>
					</div>
          
          <div style="float:left; width:465px;">
            <h2>Instructions</h2><br>
            <div style="width:445px;" align="left">
              &#149; <b>Finish Current At Bat: </b>If another game that is of a higher priority than the current game being shown becomes available, the application will wait until the current at bat ends before switching games. If the user has "Leave current at bat" set, the application will switch games immediately.
              <br><br>
              &#149; <b>Teams to ignore: </b>Any teams checked will be ignored. This is if you are blacked out from viewing certain teams, or if you have a certain game on a different device, or if you just can't stand watching a certain team. For example, I usually keep KC and TB checked since I am blacked out from Royals games and I usually have Rays games on a tablet, since I want to avoid having the same game on two screens.
              <br><br>
              &#149; <b>Delay: </b>There is a delay in MLB.tv feeds, from when the mlb.com's gameday data updates to when MLB.tv shows the game. I have made it adjustable for the user just in case there is a variation in the delay for certain people or certain times. If games are switching before at bats finish, you can increase the delay time.
              <br><br>
              &#149; <b>Priority List: </b>This application constantly checks to see if any of the current games match the user's priority criteria. If there are any matches, it will switch to the game with the highest priority. If there are no games that match any of the criteria on the priority list, the application will choose the game with the highest current leverage index (LI) and will switch between games with the highest LI until there are any matches on the priority list.<br><br>
              If you want to see a pitcher like Madison Bumgarner pitch AND hit, you need to include them on the priority list in both "Batter" and "Pitcher" categories.<br><br>
              The Runner category is for when that runner is on 1st or 2nd base with the next base open. This is for stolen base threats like Billy Hamilton.
              <br><br>
              When an inning ends, the application will switch games. However, there is currently no way to detect a pitching change. So if you have Bryce Harper as your highest priority, and let's say when he comes to bat, the opposing team decides to bring in a LHP. Since MLB's gameday data shows him "at bat" during the entire pitching change, the game will stay on during the commercial break.
              <br><br>
              &#149; <b>Championship Leverage Index: </b>CLI is the measurement of a game's importance to a team's probability of winning the World Series, with 1 equaling the importance of a game on opening day. By choosing to include CLI, the in-game leverage index is multiplied by the championship leverage index.
            </div>
          </div>
          <div style="clear:both"></div>
        </div>
      </div>
    </div>
  </body>
  <?php include('footer.php') ?>
</html>
<script>
// Populates 2nd dropdown list based on first (category)
function configureDropDownLists(ddl1,ddl2) {
	var teams = <?php echo json_encode($teams); ?>;
	var players = <?php echo json_encode($players); ?>;
	var LI = <?php echo json_encode($LI); ?>;
	var NoNo = <?php echo json_encode($NoNo); ?>;
	var GameSit = <?php echo json_encode($GameSit); ?>;
	var Misc = <?php echo json_encode($Misc); ?>;
	
	switch (ddl1.value) {
		case 'bat':
			ddl2.options.length = 0;
			for (i = 0; i < players.length; i++) {
				createOption(ddl2, players[i]['display'], players[i]['id']);
			}
			break;
		case 'pit':
			ddl2.options.length = 0;
			for (i = 0; i < players.length; i++) {
				createOption(ddl2, players[i]['display'], players[i]['id']);
			}
			break;
		case 'run':
			ddl2.options.length = 0;
			for (i = 0; i < players.length; i++) {
				createOption(ddl2, players[i]['display'], players[i]['id']);
			}
			break;
		case 'LI':
			ddl2.options.length = 0;
			for (i = 0; i < LI.length; i++) {
				createOption(ddl2, LI[i]['display'], LI[i]['id']);
			}
			break;
		case 'team':
			ddl2.options.length = 0;
			for (i = 0; i < teams.length; i++) {
				createOption(ddl2, teams[i]['display'], teams[i]['id']);
			}
			break;
		case 'team_bat':
			ddl2.options.length = 0;
			for (i = 0; i < teams.length; i++) {
				createOption(ddl2, teams[i]['display'], teams[i]['id']);
			}
			break;
		case 'team_pit':
			ddl2.options.length = 0;
			for (i = 0; i < teams.length; i++) {
				createOption(ddl2, teams[i]['display'], teams[i]['id']);
			}
			break;
		case 'NoNo':
			ddl2.options.length = 0;
			for (i = 0; i < NoNo.length; i++) {
				createOption(ddl2, NoNo[i]['display'], NoNo[i]['id']);
			}
			break;
		case 'GameSit':
			ddl2.options.length = 0;
			for (i = 0; i < GameSit.length; i++) {
				createOption(ddl2, GameSit[i]['display'], GameSit[i]['id']);
			}
			break;
		case 'Misc':
			ddl2.options.length = 0;
			for (i = 0; i < Misc.length; i++) {
				createOption(ddl2, Misc[i]['display'], Misc[i]['id']);
			}
			break;
		default:
			ddl2.options.length = 0;
			break;
	}
}
function createOption(ddl, text, value) {
	var opt = document.createElement('option');
	opt.value = value;
	opt.text = text;
	ddl.options.add(opt);
}

// Leverage Index
function getLI(InnBaseOut,RunDiff){
	var LI = {
	'1110': {'-4': 0.4, '-3': 0.6, '-2': 0.7, '-1': 0.8, '0': 0.9, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1120': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1130': {'-4': 0.6, '-3': 0.7, '-2': 0.9, '-1': 1, '0': 1.2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1150': {'-4': 0.5, '-3': 0.6, '-2': 0.8, '-1': 0.9, '0': 1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1140': {'-4': 0.8, '-3': 1.1, '-2': 1.3, '-1': 1.6, '0': 1.8, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1160': {'-4': 0.6, '-3': 0.8, '-2': 1.1, '-1': 1.3, '0': 1.5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1170': {'-4': 0.6, '-3': 0.8, '-2': 1, '-1': 1.2, '0': 1.3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1180': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.7, '0': 2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1111': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.6, '0': 0.6, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1121': {'-4': 0.6, '-3': 0.7, '-2': 0.9, '-1': 1, '0': 1.1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1131': {'-4': 0.6, '-3': 0.8, '-2': 0.9, '-1': 1.1, '0': 1.2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1151': {'-4': 0.7, '-3': 0.9, '-2': 1, '-1': 1.2, '0': 1.3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1141': {'-4': 0.9, '-3': 1.2, '-2': 1.5, '-1': 1.7, '0': 1.9, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1161': {'-4': 0.9, '-3': 1.1, '-2': 1.3, '-1': 1.6, '0': 1.7, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1171': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1181': {'-4': 1.1, '-3': 1.5, '-2': 1.8, '-1': 2.1, '0': 2.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1112': {'-4': 0.2, '-3': 0.3, '-2': 0.3, '-1': 0.4, '0': 0.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1122': {'-4': 0.4, '-3': 0.5, '-2': 0.6, '-1': 0.7, '0': 0.8, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1132': {'-4': 0.6, '-3': 0.7, '-2': 0.9, '-1': 1, '0': 1.1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1152': {'-4': 0.7, '-3': 0.9, '-2': 1, '-1': 1.2, '0': 1.3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1142': {'-4': 0.8, '-3': 1, '-2': 1.3, '-1': 1.5, '0': 1.6, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1162': {'-4': 0.9, '-3': 1.1, '-2': 1.4, '-1': 1.6, '0': 1.7, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1172': {'-4': 1, '-3': 1.2, '-2': 1.5, '-1': 1.7, '0': 1.9, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1182': {'-4': 1.4, '-3': 1.8, '-2': 2.1, '-1': 2.5, '0': 2.7, '1': 0, '2': 0, '3': 0, '4': 0}
	,'1210': {'-4': 0.7, '-3': 0.8, '-2': 0.9, '-1': 0.9, '0': 0.9, '1': 0.8, '2': 0.6, '3': 0.5, '4': 0.4}
	,'1220': {'-4': 1.2, '-3': 1.4, '-2': 1.5, '-1': 1.5, '0': 1.4, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'1230': {'-4': 1.1, '-3': 1.2, '-2': 1.3, '-1': 1.2, '0': 1.1, '1': 1, '2': 0.8, '3': 0.6, '4': 0.5}
	,'1250': {'-4': 1, '-3': 1.1, '-2': 1.1, '-1': 1.1, '0': 1, '1': 0.8, '2': 0.7, '3': 0.5, '4': 0.4}
	,'1240': {'-4': 1.7, '-3': 1.9, '-2': 2, '-1': 1.9, '0': 1.7, '1': 1.5, '2': 1.2, '3': 0.9, '4': 0.7}
	,'1260': {'-4': 1.6, '-3': 1.7, '-2': 1.7, '-1': 1.6, '0': 1.4, '1': 1.2, '2': 1, '3': 0.7, '4': 0.5}
	,'1270': {'-4': 1.4, '-3': 1.5, '-2': 1.5, '-1': 1.4, '0': 1.3, '1': 1.1, '2': 0.9, '3': 0.7, '4': 0.5}
	,'1280': {'-4': 2.2, '-3': 2.3, '-2': 2.3, '-1': 2.1, '0': 1.9, '1': 1.6, '2': 1.2, '3': 0.9, '4': 0.7}
	,'1211': {'-4': 0.5, '-3': 0.6, '-2': 0.6, '-1': 0.7, '0': 0.6, '1': 0.6, '2': 0.5, '3': 0.4, '4': 0.3}
	,'1221': {'-4': 1, '-3': 1.1, '-2': 1.2, '-1': 1.2, '0': 1.1, '1': 1, '2': 0.8, '3': 0.7, '4': 0.5}
	,'1231': {'-4': 1, '-3': 1.1, '-2': 1.2, '-1': 1.2, '0': 1.2, '1': 1, '2': 0.9, '3': 0.7, '4': 0.5}
	,'1251': {'-4': 1, '-3': 1.1, '-2': 1.3, '-1': 1.3, '0': 1.3, '1': 1.1, '2': 1, '3': 0.8, '4': 0.6}
	,'1241': {'-4': 1.7, '-3': 1.9, '-2': 2, '-1': 2, '0': 1.8, '1': 1.6, '2': 1.3, '3': 1, '4': 0.8}
	,'1261': {'-4': 1.5, '-3': 1.7, '-2': 1.8, '-1': 1.8, '0': 1.7, '1': 1.5, '2': 1.2, '3': 1, '4': 0.8}
	,'1271': {'-4': 1.4, '-3': 1.5, '-2': 1.6, '-1': 1.5, '0': 1.4, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'1281': {'-4': 2.4, '-3': 2.6, '-2': 2.6, '-1': 2.6, '0': 2.3, '1': 2, '2': 1.6, '3': 1.3, '4': 1}
	,'1212': {'-4': 0.3, '-3': 0.4, '-2': 0.4, '-1': 0.4, '0': 0.4, '1': 0.4, '2': 0.3, '3': 0.3, '4': 0.2}
	,'1222': {'-4': 0.6, '-3': 0.7, '-2': 0.8, '-1': 0.8, '0': 0.8, '1': 0.7, '2': 0.6, '3': 0.5, '4': 0.4}
	,'1232': {'-4': 0.8, '-3': 1, '-2': 1.1, '-1': 1.1, '0': 1.1, '1': 1, '2': 0.8, '3': 0.7, '4': 0.5}
	,'1252': {'-4': 1, '-3': 1.1, '-2': 1.3, '-1': 1.3, '0': 1.3, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'1242': {'-4': 1.3, '-3': 1.5, '-2': 1.6, '-1': 1.7, '0': 1.6, '1': 1.4, '2': 1.2, '3': 0.9, '4': 0.7}
	,'1262': {'-4': 1.4, '-3': 1.6, '-2': 1.7, '-1': 1.8, '0': 1.7, '1': 1.5, '2': 1.3, '3': 1, '4': 0.8}
	,'1272': {'-4': 1.6, '-3': 1.8, '-2': 2, '-1': 2, '0': 1.9, '1': 1.7, '2': 1.4, '3': 1.1, '4': 0.8}
	,'1282': {'-4': 2.4, '-3': 2.7, '-2': 2.9, '-1': 2.9, '0': 2.7, '1': 2.4, '2': 2, '3': 1.5, '4': 1.2}
	,'2110': {'-4': 0.4, '-3': 0.6, '-2': 0.7, '-1': 0.8, '0': 0.9, '1': 1, '2': 0.9, '3': 0.8, '4': 0.7}
	,'2120': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.5, '1': 1.5, '2': 1.5, '3': 1.4, '4': 1.2}
	,'2130': {'-4': 0.5, '-3': 0.7, '-2': 0.9, '-1': 1.1, '0': 1.2, '1': 1.3, '2': 1.3, '3': 1.2, '4': 1}
	,'2150': {'-4': 0.4, '-3': 0.6, '-2': 0.8, '-1': 0.9, '0': 1.1, '1': 1.1, '2': 1.2, '3': 1.1, '4': 0.9}
	,'2140': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.6, '0': 1.9, '1': 2, '2': 2, '3': 1.9, '4': 1.7}
	,'2160': {'-4': 0.6, '-3': 0.8, '-2': 1.1, '-1': 1.3, '0': 1.6, '1': 1.7, '2': 1.8, '3': 1.7, '4': 1.5}
	,'2170': {'-4': 0.6, '-3': 0.8, '-2': 1, '-1': 1.2, '0': 1.4, '1': 1.5, '2': 1.6, '3': 1.5, '4': 1.4}
	,'2180': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.7, '0': 2, '1': 2.3, '2': 2.4, '3': 2.3, '4': 2.1}
	,'2111': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.6, '0': 0.7, '1': 0.7, '2': 0.6, '3': 0.6, '4': 0.5}
	,'2121': {'-4': 0.6, '-3': 0.7, '-2': 0.9, '-1': 1.1, '0': 1.2, '1': 1.3, '2': 1.2, '3': 1.1, '4': 0.9}
	,'2131': {'-4': 0.6, '-3': 0.8, '-2': 1, '-1': 1.1, '0': 1.2, '1': 1.3, '2': 1.2, '3': 1.1, '4': 0.9}
	,'2151': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.2, '0': 1.3, '1': 1.3, '2': 1.3, '3': 1.1, '4': 0.9}
	,'2141': {'-4': 0.9, '-3': 1.2, '-2': 1.5, '-1': 1.8, '0': 2, '1': 2.1, '2': 2, '3': 1.8, '4': 1.6}
	,'2161': {'-4': 0.9, '-3': 1.1, '-2': 1.4, '-1': 1.6, '0': 1.8, '1': 1.8, '2': 1.8, '3': 1.6, '4': 1.4}
	,'2171': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.5, '1': 1.6, '2': 1.6, '3': 1.5, '4': 1.3}
	,'2181': {'-4': 1.1, '-3': 1.5, '-2': 1.8, '-1': 2.2, '0': 2.5, '1': 2.7, '2': 2.7, '3': 2.6, '4': 2.3}
	,'2112': {'-4': 0.2, '-3': 0.3, '-2': 0.3, '-1': 0.4, '0': 0.4, '1': 0.4, '2': 0.4, '3': 0.3, '4': 0.3}
	,'2122': {'-4': 0.4, '-3': 0.5, '-2': 0.7, '-1': 0.8, '0': 0.8, '1': 0.9, '2': 0.8, '3': 0.7, '4': 0.6}
	,'2132': {'-4': 0.6, '-3': 0.8, '-2': 0.9, '-1': 1.1, '0': 1.2, '1': 1.2, '2': 1.1, '3': 0.9, '4': 0.8}
	,'2152': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.4, '1': 1.4, '2': 1.2, '3': 1.1, '4': 0.9}
	,'2142': {'-4': 0.8, '-3': 1, '-2': 1.3, '-1': 1.5, '0': 1.7, '1': 1.7, '2': 1.6, '3': 1.4, '4': 1.2}
	,'2162': {'-4': 0.9, '-3': 1.1, '-2': 1.4, '-1': 1.6, '0': 1.8, '1': 1.8, '2': 1.7, '3': 1.5, '4': 1.3}
	,'2172': {'-4': 1, '-3': 1.2, '-2': 1.5, '-1': 1.8, '0': 2, '1': 2.1, '2': 2, '3': 1.7, '4': 1.4}
	,'2182': {'-4': 1.3, '-3': 1.7, '-2': 2.2, '-1': 2.6, '0': 2.9, '1': 3, '2': 2.9, '3': 2.6, '4': 2.2}
	,'2210': {'-4': 0.8, '-3': 0.9, '-2': 1, '-1': 1, '0': 0.9, '1': 0.8, '2': 0.6, '3': 0.5, '4': 0.4}
	,'2220': {'-4': 1.3, '-3': 1.5, '-2': 1.6, '-1': 1.6, '0': 1.5, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'2230': {'-4': 1.1, '-3': 1.3, '-2': 1.3, '-1': 1.3, '0': 1.2, '1': 1, '2': 0.8, '3': 0.6, '4': 0.4}
	,'2250': {'-4': 1, '-3': 1.2, '-2': 1.2, '-1': 1.2, '0': 1, '1': 0.9, '2': 0.7, '3': 0.5, '4': 0.4}
	,'2240': {'-4': 1.8, '-3': 2, '-2': 2.1, '-1': 2, '0': 1.8, '1': 1.5, '2': 1.2, '3': 0.9, '4': 0.7}
	,'2260': {'-4': 1.6, '-3': 1.8, '-2': 1.8, '-1': 1.7, '0': 1.5, '1': 1.2, '2': 0.9, '3': 0.7, '4': 0.5}
	,'2270': {'-4': 1.5, '-3': 1.6, '-2': 1.6, '-1': 1.5, '0': 1.3, '1': 1.1, '2': 0.9, '3': 0.7, '4': 0.5}
	,'2280': {'-4': 2.3, '-3': 2.4, '-2': 2.4, '-1': 2.2, '0': 1.9, '1': 1.6, '2': 1.2, '3': 0.9, '4': 0.6}
	,'2211': {'-4': 0.5, '-3': 0.6, '-2': 0.7, '-1': 0.7, '0': 0.7, '1': 0.6, '2': 0.5, '3': 0.4, '4': 0.3}
	,'2221': {'-4': 1, '-3': 1.2, '-2': 1.3, '-1': 1.3, '0': 1.2, '1': 1, '2': 0.8, '3': 0.6, '4': 0.5}
	,'2231': {'-4': 1, '-3': 1.2, '-2': 1.3, '-1': 1.3, '0': 1.2, '1': 1.1, '2': 0.9, '3': 0.7, '4': 0.5}
	,'2251': {'-4': 1, '-3': 1.2, '-2': 1.3, '-1': 1.4, '0': 1.4, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'2241': {'-4': 1.7, '-3': 2, '-2': 2.1, '-1': 2.1, '0': 2, '1': 1.7, '2': 1.3, '3': 1, '4': 0.7}
	,'2261': {'-4': 1.5, '-3': 1.8, '-2': 1.9, '-1': 1.9, '0': 1.8, '1': 1.6, '2': 1.3, '3': 1, '4': 0.7}
	,'2271': {'-4': 1.4, '-3': 1.6, '-2': 1.7, '-1': 1.6, '0': 1.5, '1': 1.3, '2': 1, '3': 0.8, '4': 0.6}
	,'2281': {'-4': 2.5, '-3': 2.7, '-2': 2.8, '-1': 2.7, '0': 2.4, '1': 2.1, '2': 1.7, '3': 1.3, '4': 0.9}
	,'2212': {'-4': 0.3, '-3': 0.4, '-2': 0.4, '-1': 0.5, '0': 0.4, '1': 0.4, '2': 0.3, '3': 0.2, '4': 0.2}
	,'2222': {'-4': 0.6, '-3': 0.8, '-2': 0.9, '-1': 0.9, '0': 0.8, '1': 0.7, '2': 0.6, '3': 0.5, '4': 0.3}
	,'2232': {'-4': 0.8, '-3': 1, '-2': 1.2, '-1': 1.2, '0': 1.2, '1': 1, '2': 0.9, '3': 0.7, '4': 0.5}
	,'2252': {'-4': 1, '-3': 1.2, '-2': 1.3, '-1': 1.4, '0': 1.4, '1': 1.2, '2': 1, '3': 0.8, '4': 0.6}
	,'2242': {'-4': 1.3, '-3': 1.6, '-2': 1.8, '-1': 1.8, '0': 1.7, '1': 1.5, '2': 1.2, '3': 0.9, '4': 0.7}
	,'2262': {'-4': 1.4, '-3': 1.7, '-2': 1.9, '-1': 1.9, '0': 1.8, '1': 1.6, '2': 1.3, '3': 1, '4': 0.7}
	,'2272': {'-4': 1.6, '-3': 1.9, '-2': 2.1, '-1': 2.1, '0': 2, '1': 1.7, '2': 1.4, '3': 1.1, '4': 0.8}
	,'2282': {'-4': 2.5, '-3': 2.8, '-2': 3.1, '-1': 3.1, '0': 2.9, '1': 2.5, '2': 2, '3': 1.5, '4': 1.1}
	,'3110': {'-4': 0.4, '-3': 0.6, '-2': 0.7, '-1': 0.9, '0': 1, '1': 1, '2': 1, '3': 0.9, '4': 0.7}
	,'3120': {'-4': 0.6, '-3': 0.9, '-2': 1.1, '-1': 1.4, '0': 1.6, '1': 1.7, '2': 1.6, '3': 1.4, '4': 1.2}
	,'3130': {'-4': 0.5, '-3': 0.7, '-2': 0.9, '-1': 1.1, '0': 1.3, '1': 1.4, '2': 1.4, '3': 1.2, '4': 1}
	,'3150': {'-4': 0.4, '-3': 0.6, '-2': 0.8, '-1': 1, '0': 1.1, '1': 1.2, '2': 1.2, '3': 1.1, '4': 1}
	,'3140': {'-4': 0.8, '-3': 1, '-2': 1.4, '-1': 1.7, '0': 2, '1': 2.2, '2': 2.1, '3': 2, '4': 1.7}
	,'3160': {'-4': 0.6, '-3': 0.8, '-2': 1.1, '-1': 1.4, '0': 1.6, '1': 1.8, '2': 1.9, '3': 1.8, '4': 1.6}
	,'3170': {'-4': 0.5, '-3': 0.8, '-2': 1, '-1': 1.2, '0': 1.5, '1': 1.6, '2': 1.7, '3': 1.6, '4': 1.4}
	,'3180': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.8, '0': 2.1, '1': 2.4, '2': 2.6, '3': 2.5, '4': 2.3}
	,'3111': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.6, '0': 0.7, '1': 0.7, '2': 0.7, '3': 0.6, '4': 0.5}
	,'3121': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.2, '0': 1.3, '1': 1.4, '2': 1.3, '3': 1.1, '4': 0.9}
	,'3131': {'-4': 0.6, '-3': 0.8, '-2': 1, '-1': 1.2, '0': 1.3, '1': 1.4, '2': 1.3, '3': 1.1, '4': 0.9}
	,'3151': {'-4': 0.6, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.5, '1': 1.5, '2': 1.3, '3': 1.1, '4': 0.9}
	,'3141': {'-4': 0.9, '-3': 1.2, '-2': 1.5, '-1': 1.8, '0': 2.1, '1': 2.2, '2': 2.1, '3': 1.9, '4': 1.6}
	,'3161': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.7, '0': 1.9, '1': 2, '2': 1.9, '3': 1.7, '4': 1.5}
	,'3171': {'-4': 0.7, '-3': 0.9, '-2': 1.2, '-1': 1.4, '0': 1.6, '1': 1.7, '2': 1.7, '3': 1.6, '4': 1.3}
	,'3181': {'-4': 1.1, '-3': 1.4, '-2': 1.9, '-1': 2.3, '0': 2.7, '1': 2.9, '2': 2.9, '3': 2.7, '4': 2.4}
	,'3112': {'-4': 0.2, '-3': 0.3, '-2': 0.4, '-1': 0.4, '0': 0.5, '1': 0.5, '2': 0.4, '3': 0.4, '4': 0.3}
	,'3122': {'-4': 0.4, '-3': 0.5, '-2': 0.7, '-1': 0.8, '0': 0.9, '1': 0.9, '2': 0.8, '3': 0.7, '4': 0.6}
	,'3132': {'-4': 0.6, '-3': 0.8, '-2': 1, '-1': 1.1, '0': 1.3, '1': 1.3, '2': 1.1, '3': 1, '4': 0.8}
	,'3152': {'-4': 0.7, '-3': 0.9, '-2': 1.1, '-1': 1.3, '0': 1.5, '1': 1.5, '2': 1.3, '3': 1.1, '4': 0.9}
	,'3142': {'-4': 0.8, '-3': 1, '-2': 1.3, '-1': 1.6, '0': 1.8, '1': 1.9, '2': 1.8, '3': 1.5, '4': 1.2}
	,'3162': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.7, '0': 1.9, '1': 2, '2': 1.9, '3': 1.6, '4': 1.3}
	,'3172': {'-4': 0.9, '-3': 1.2, '-2': 1.6, '-1': 1.9, '0': 2.2, '1': 2.2, '2': 2.1, '3': 1.8, '4': 1.5}
	,'3182': {'-4': 1.3, '-3': 1.7, '-2': 2.2, '-1': 2.7, '0': 3.1, '1': 3.3, '2': 3.1, '3': 2.7, '4': 2.3}
	,'3210': {'-4': 0.8, '-3': 0.9, '-2': 1, '-1': 1.1, '0': 1, '1': 0.8, '2': 0.6, '3': 0.5, '4': 0.3}
	,'3220': {'-4': 1.3, '-3': 1.6, '-2': 1.7, '-1': 1.7, '0': 1.5, '1': 1.3, '2': 1, '3': 0.7, '4': 0.5}
	,'3230': {'-4': 1.2, '-3': 1.3, '-2': 1.5, '-1': 1.4, '0': 1.3, '1': 1.1, '2': 0.8, '3': 0.6, '4': 0.4}
	,'3250': {'-4': 1.1, '-3': 1.2, '-2': 1.3, '-1': 1.2, '0': 1.1, '1': 0.9, '2': 0.7, '3': 0.5, '4': 0.3}
	,'3240': {'-4': 1.9, '-3': 2.1, '-2': 2.3, '-1': 2.2, '0': 1.9, '1': 1.6, '2': 1.2, '3': 0.9, '4': 0.6}
	,'3260': {'-4': 1.7, '-3': 1.9, '-2': 2, '-1': 1.8, '0': 1.5, '1': 1.2, '2': 0.9, '3': 0.7, '4': 0.4}
	,'3270': {'-4': 1.6, '-3': 1.7, '-2': 1.8, '-1': 1.6, '0': 1.4, '1': 1.1, '2': 0.9, '3': 0.6, '4': 0.4}
	,'3280': {'-4': 2.4, '-3': 2.6, '-2': 2.6, '-1': 2.4, '0': 2, '1': 1.6, '2': 1.2, '3': 0.8, '4': 0.6}
	,'3211': {'-4': 0.5, '-3': 0.7, '-2': 0.7, '-1': 0.8, '0': 0.7, '1': 0.6, '2': 0.5, '3': 0.4, '4': 0.3}
	,'3221': {'-4': 1, '-3': 1.2, '-2': 1.4, '-1': 1.4, '0': 1.3, '1': 1.1, '2': 0.8, '3': 0.6, '4': 0.4}
	,'3231': {'-4': 1, '-3': 1.3, '-2': 1.4, '-1': 1.4, '0': 1.3, '1': 1.1, '2': 0.9, '3': 0.6, '4': 0.5}
	,'3251': {'-4': 1, '-3': 1.3, '-2': 1.4, '-1': 1.5, '0': 1.5, '1': 1.3, '2': 1, '3': 0.7, '4': 0.5}
	,'3241': {'-4': 1.8, '-3': 2.1, '-2': 2.3, '-1': 2.3, '0': 2.1, '1': 1.7, '2': 1.3, '3': 1, '4': 0.7}
	,'3261': {'-4': 1.6, '-3': 1.9, '-2': 2, '-1': 2.1, '0': 1.9, '1': 1.6, '2': 1.3, '3': 0.9, '4': 0.7}
	,'3271': {'-4': 1.5, '-3': 1.7, '-2': 1.8, '-1': 1.7, '0': 1.6, '1': 1.3, '2': 1, '3': 0.8, '4': 0.5}
	,'3281': {'-4': 2.6, '-3': 2.9, '-2': 3.1, '-1': 2.9, '0': 2.6, '1': 2.1, '2': 1.6, '3': 1.2, '4': 0.8}
	,'3212': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.5, '0': 0.5, '1': 0.4, '2': 0.3, '3': 0.2, '4': 0.2}
	,'3222': {'-4': 0.6, '-3': 0.8, '-2': 0.9, '-1': 1, '0': 0.9, '1': 0.8, '2': 0.6, '3': 0.5, '4': 0.3}
	,'3232': {'-4': 0.9, '-3': 1.1, '-2': 1.3, '-1': 1.3, '0': 1.3, '1': 1.1, '2': 0.9, '3': 0.6, '4': 0.5}
	,'3252': {'-4': 1, '-3': 1.2, '-2': 1.4, '-1': 1.6, '0': 1.5, '1': 1.3, '2': 1, '3': 0.8, '4': 0.5}
	,'3242': {'-4': 1.4, '-3': 1.7, '-2': 1.9, '-1': 2, '0': 1.8, '1': 1.5, '2': 1.2, '3': 0.9, '4': 0.6}
	,'3262': {'-4': 1.4, '-3': 1.8, '-2': 2, '-1': 2.1, '0': 1.9, '1': 1.6, '2': 1.3, '3': 1, '4': 0.7}
	,'3272': {'-4': 1.6, '-3': 2, '-2': 2.3, '-1': 2.3, '0': 2.2, '1': 1.8, '2': 1.4, '3': 1, '4': 0.7}
	,'3282': {'-4': 2.5, '-3': 3, '-2': 3.3, '-1': 3.4, '0': 3.1, '1': 2.5, '2': 2, '3': 1.5, '4': 1}
	,'4110': {'-4': 0.4, '-3': 0.5, '-2': 0.7, '-1': 0.9, '0': 1.1, '1': 1.1, '2': 1.1, '3': 0.9, '4': 0.7}
	,'4120': {'-4': 0.6, '-3': 0.8, '-2': 1.1, '-1': 1.4, '0': 1.7, '1': 1.8, '2': 1.7, '3': 1.5, '4': 1.2}
	,'4130': {'-4': 0.5, '-3': 0.7, '-2': 0.9, '-1': 1.2, '0': 1.4, '1': 1.5, '2': 1.5, '3': 1.3, '4': 1.1}
	,'4150': {'-4': 0.4, '-3': 0.6, '-2': 0.8, '-1': 1, '0': 1.2, '1': 1.3, '2': 1.3, '3': 1.2, '4': 1}
	,'4140': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.8, '0': 2.1, '1': 2.3, '2': 2.3, '3': 2.1, '4': 1.8}
	,'4160': {'-4': 0.5, '-3': 0.8, '-2': 1, '-1': 1.4, '0': 1.7, '1': 2, '2': 2.1, '3': 1.9, '4': 1.7}
	,'4170': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.3, '0': 1.6, '1': 1.8, '2': 1.8, '3': 1.7, '4': 1.5}
	,'4180': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.8, '0': 2.2, '1': 2.6, '2': 2.8, '3': 2.7, '4': 2.4}
	,'4111': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.7, '0': 0.8, '1': 0.8, '2': 0.7, '3': 0.6, '4': 0.5}
	,'4121': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.2, '0': 1.4, '1': 1.5, '2': 1.4, '3': 1.2, '4': 0.9}
	,'4131': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.2, '0': 1.5, '1': 1.5, '2': 1.4, '3': 1.2, '4': 0.9}
	,'4151': {'-4': 0.6, '-3': 0.9, '-2': 1.1, '-1': 1.4, '0': 1.6, '1': 1.6, '2': 1.4, '3': 1.2, '4': 0.9}
	,'4141': {'-4': 0.8, '-3': 1.1, '-2': 1.5, '-1': 1.9, '0': 2.3, '1': 2.4, '2': 2.3, '3': 2, '4': 1.6}
	,'4161': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.8, '0': 2.1, '1': 2.2, '2': 2.1, '3': 1.8, '4': 1.5}
	,'4171': {'-4': 0.6, '-3': 0.9, '-2': 1.2, '-1': 1.5, '0': 1.7, '1': 1.9, '2': 1.9, '3': 1.7, '4': 1.4}
	,'4181': {'-4': 1, '-3': 1.4, '-2': 1.9, '-1': 2.4, '0': 2.9, '1': 3.1, '2': 3.2, '3': 2.9, '4': 2.4}
	,'4112': {'-4': 0.2, '-3': 0.3, '-2': 0.4, '-1': 0.5, '0': 0.5, '1': 0.5, '2': 0.5, '3': 0.4, '4': 0.3}
	,'4122': {'-4': 0.4, '-3': 0.5, '-2': 0.7, '-1': 0.9, '0': 1, '1': 1, '2': 0.9, '3': 0.7, '4': 0.6}
	,'4132': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.2, '0': 1.4, '1': 1.4, '2': 1.2, '3': 1, '4': 0.7}
	,'4152': {'-4': 0.6, '-3': 0.9, '-2': 1.2, '-1': 1.4, '0': 1.6, '1': 1.6, '2': 1.4, '3': 1.1, '4': 0.8}
	,'4142': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.7, '0': 2, '1': 2.1, '2': 1.9, '3': 1.6, '4': 1.2}
	,'4162': {'-4': 0.8, '-3': 1.1, '-2': 1.5, '-1': 1.8, '0': 2.1, '1': 2.2, '2': 2, '3': 1.7, '4': 1.3}
	,'4172': {'-4': 0.9, '-3': 1.2, '-2': 1.6, '-1': 2, '0': 2.4, '1': 2.5, '2': 2.3, '3': 1.9, '4': 1.5}
	,'4182': {'-4': 1.2, '-3': 1.7, '-2': 2.3, '-1': 2.9, '0': 3.4, '1': 3.6, '2': 3.4, '3': 2.9, '4': 2.3}
	,'4210': {'-4': 0.8, '-3': 1, '-2': 1.1, '-1': 1.2, '0': 1.1, '1': 0.9, '2': 0.6, '3': 0.4, '4': 0.3}
	,'4220': {'-4': 1.4, '-3': 1.7, '-2': 1.9, '-1': 1.9, '0': 1.7, '1': 1.3, '2': 1, '3': 0.7, '4': 0.5}
	,'4230': {'-4': 1.2, '-3': 1.4, '-2': 1.6, '-1': 1.6, '0': 1.4, '1': 1.1, '2': 0.8, '3': 0.5, '4': 0.4}
	,'4250': {'-4': 1.1, '-3': 1.3, '-2': 1.4, '-1': 1.4, '0': 1.1, '1': 0.9, '2': 0.6, '3': 0.4, '4': 0.3}
	,'4240': {'-4': 2, '-3': 2.3, '-2': 2.5, '-1': 2.4, '0': 2, '1': 1.6, '2': 1.1, '3': 0.8, '4': 0.5}
	,'4260': {'-4': 1.8, '-3': 2.1, '-2': 2.1, '-1': 2, '0': 1.6, '1': 1.2, '2': 0.9, '3': 0.6, '4': 0.4}
	,'4270': {'-4': 1.7, '-3': 1.9, '-2': 1.9, '-1': 1.8, '0': 1.5, '1': 1.1, '2': 0.8, '3': 0.6, '4': 0.4}
	,'4280': {'-4': 2.6, '-3': 2.8, '-2': 2.8, '-1': 2.6, '0': 2.1, '1': 1.6, '2': 1.1, '3': 0.8, '4': 0.5}
	,'4211': {'-4': 0.5, '-3': 0.7, '-2': 0.8, '-1': 0.9, '0': 0.8, '1': 0.6, '2': 0.5, '3': 0.3, '4': 0.2}
	,'4221': {'-4': 1, '-3': 1.3, '-2': 1.5, '-1': 1.6, '0': 1.4, '1': 1.1, '2': 0.8, '3': 0.6, '4': 0.4}
	,'4231': {'-4': 1.1, '-3': 1.3, '-2': 1.5, '-1': 1.6, '0': 1.4, '1': 1.2, '2': 0.9, '3': 0.6, '4': 0.4}
	,'4251': {'-4': 1, '-3': 1.3, '-2': 1.6, '-1': 1.7, '0': 1.6, '1': 1.3, '2': 1, '3': 0.7, '4': 0.5}
	,'4241': {'-4': 1.8, '-3': 2.2, '-2': 2.5, '-1': 2.5, '0': 2.2, '1': 1.8, '2': 1.3, '3': 0.9, '4': 0.6}
	,'4261': {'-4': 1.7, '-3': 2, '-2': 2.2, '-1': 2.3, '0': 2.1, '1': 1.7, '2': 1.3, '3': 0.9, '4': 0.6}
	,'4271': {'-4': 1.6, '-3': 1.8, '-2': 2, '-1': 1.9, '0': 1.7, '1': 1.4, '2': 1, '3': 0.7, '4': 0.5}
	,'4281': {'-4': 2.7, '-3': 3.2, '-2': 3.3, '-1': 3.2, '0': 2.8, '1': 2.2, '2': 1.6, '3': 1.1, '4': 0.7}
	,'4212': {'-4': 0.3, '-3': 0.4, '-2': 0.5, '-1': 0.6, '0': 0.5, '1': 0.4, '2': 0.3, '3': 0.2, '4': 0.2}
	,'4222': {'-4': 0.7, '-3': 0.8, '-2': 1, '-1': 1.1, '0': 1, '1': 0.8, '2': 0.6, '3': 0.4, '4': 0.3}
	,'4232': {'-4': 0.9, '-3': 1.1, '-2': 1.4, '-1': 1.5, '0': 1.4, '1': 1.1, '2': 0.9, '3': 0.6, '4': 0.4}
	,'4252': {'-4': 1, '-3': 1.3, '-2': 1.6, '-1': 1.7, '0': 1.6, '1': 1.3, '2': 1, '3': 0.7, '4': 0.5}
	,'4242': {'-4': 1.4, '-3': 1.8, '-2': 2.1, '-1': 2.2, '0': 2, '1': 1.6, '2': 1.2, '3': 0.8, '4': 0.6}
	,'4262': {'-4': 1.5, '-3': 1.9, '-2': 2.2, '-1': 2.3, '0': 2.1, '1': 1.7, '2': 1.3, '3': 0.9, '4': 0.6}
	,'4272': {'-4': 1.7, '-3': 2.1, '-2': 2.5, '-1': 2.6, '0': 2.3, '1': 1.9, '2': 1.4, '3': 1, '4': 0.7}
	,'4282': {'-4': 2.6, '-3': 3.2, '-2': 3.6, '-1': 3.7, '0': 3.3, '1': 2.6, '2': 1.9, '3': 1.4, '4': 0.9}
	,'5110': {'-4': 0.4, '-3': 0.5, '-2': 0.7, '-1': 1, '0': 1.2, '1': 1.3, '2': 1.1, '3': 0.9, '4': 0.7}
	,'5120': {'-4': 0.5, '-3': 0.8, '-2': 1.1, '-1': 1.5, '0': 1.9, '1': 2, '2': 1.9, '3': 1.6, '4': 1.2}
	,'5130': {'-4': 0.4, '-3': 0.6, '-2': 0.9, '-1': 1.2, '0': 1.5, '1': 1.7, '2': 1.6, '3': 1.4, '4': 1.1}
	,'5150': {'-4': 0.3, '-3': 0.5, '-2': 0.7, '-1': 1, '0': 1.3, '1': 1.5, '2': 1.5, '3': 1.3, '4': 1.1}
	,'5140': {'-4': 0.6, '-3': 0.9, '-2': 1.3, '-1': 1.8, '0': 2.3, '1': 2.6, '2': 2.5, '3': 2.3, '4': 1.8}
	,'5160': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.4, '0': 1.8, '1': 2.2, '2': 2.3, '3': 2.1, '4': 1.7}
	,'5170': {'-4': 0.4, '-3': 0.7, '-2': 1, '-1': 1.3, '0': 1.7, '1': 1.9, '2': 2, '3': 1.9, '4': 1.6}
	,'5180': {'-4': 0.6, '-3': 0.9, '-2': 1.3, '-1': 1.8, '0': 2.4, '1': 2.8, '2': 3, '3': 2.9, '4': 2.5}
	,'5111': {'-4': 0.3, '-3': 0.4, '-2': 0.6, '-1': 0.7, '0': 0.9, '1': 0.9, '2': 0.8, '3': 0.6, '4': 0.5}
	,'5121': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.3, '0': 1.6, '1': 1.7, '2': 1.5, '3': 1.2, '4': 0.9}
	,'5131': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.3, '0': 1.6, '1': 1.7, '2': 1.5, '3': 1.2, '4': 0.9}
	,'5151': {'-4': 0.6, '-3': 0.8, '-2': 1.1, '-1': 1.5, '0': 1.8, '1': 1.8, '2': 1.5, '3': 1.2, '4': 0.9}
	,'5141': {'-4': 0.7, '-3': 1.1, '-2': 1.5, '-1': 2, '0': 2.5, '1': 2.7, '2': 2.5, '3': 2.1, '4': 1.6}
	,'5161': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.9, '0': 2.3, '1': 2.4, '2': 2.3, '3': 1.9, '4': 1.5}
	,'5171': {'-4': 0.6, '-3': 0.8, '-2': 1.2, '-1': 1.5, '0': 1.9, '1': 2.1, '2': 2.1, '3': 1.8, '4': 1.4}
	,'5181': {'-4': 0.9, '-3': 1.3, '-2': 1.9, '-1': 2.5, '0': 3.1, '1': 3.5, '2': 3.5, '3': 3.1, '4': 2.5}
	,'5112': {'-4': 0.2, '-3': 0.3, '-2': 0.4, '-1': 0.5, '0': 0.6, '1': 0.6, '2': 0.5, '3': 0.4, '4': 0.3}
	,'5122': {'-4': 0.3, '-3': 0.5, '-2': 0.7, '-1': 0.9, '0': 1.1, '1': 1.1, '2': 1, '3': 0.8, '4': 0.6}
	,'5132': {'-4': 0.5, '-3': 0.7, '-2': 1, '-1': 1.3, '0': 1.6, '1': 1.6, '2': 1.3, '3': 1, '4': 0.7}
	,'5152': {'-4': 0.6, '-3': 0.8, '-2': 1.2, '-1': 1.5, '0': 1.8, '1': 1.8, '2': 1.5, '3': 1.1, '4': 0.8}
	,'5142': {'-4': 0.7, '-3': 1, '-2': 1.4, '-1': 1.8, '0': 2.2, '1': 2.3, '2': 2.1, '3': 1.6, '4': 1.2}
	,'5162': {'-4': 0.7, '-3': 1.1, '-2': 1.5, '-1': 1.9, '0': 2.4, '1': 2.5, '2': 2.2, '3': 1.7, '4': 1.3}
	,'5172': {'-4': 0.8, '-3': 1.1, '-2': 1.6, '-1': 2.1, '0': 2.6, '1': 2.8, '2': 2.5, '3': 2, '4': 1.4}
	,'5182': {'-4': 1.1, '-3': 1.6, '-2': 2.2, '-1': 3, '0': 3.7, '1': 4, '2': 3.7, '3': 3, '4': 2.3}
	,'5210': {'-4': 0.8, '-3': 1.1, '-2': 1.3, '-1': 1.3, '0': 1.2, '1': 0.9, '2': 0.6, '3': 0.4, '4': 0.3}
	,'5220': {'-4': 1.4, '-3': 1.8, '-2': 2.1, '-1': 2.1, '0': 1.8, '1': 1.3, '2': 0.9, '3': 0.6, '4': 0.4}
	,'5230': {'-4': 1.2, '-3': 1.6, '-2': 1.8, '-1': 1.8, '0': 1.5, '1': 1.1, '2': 0.7, '3': 0.5, '4': 0.3}
	,'5250': {'-4': 1.2, '-3': 1.5, '-2': 1.6, '-1': 1.5, '0': 1.2, '1': 0.9, '2': 0.6, '3': 0.4, '4': 0.2}
	,'5240': {'-4': 2.1, '-3': 2.5, '-2': 2.7, '-1': 2.6, '0': 2.2, '1': 1.6, '2': 1.1, '3': 0.7, '4': 0.4}
	,'5260': {'-4': 1.9, '-3': 2.3, '-2': 2.4, '-1': 2.2, '0': 1.7, '1': 1.2, '2': 0.8, '3': 0.5, '4': 0.3}
	,'5270': {'-4': 1.8, '-3': 2.1, '-2': 2.1, '-1': 2, '0': 1.6, '1': 1.1, '2': 0.8, '3': 0.5, '4': 0.3}
	,'5280': {'-4': 2.8, '-3': 3.1, '-2': 3.1, '-1': 2.8, '0': 2.2, '1': 1.5, '2': 1, '3': 0.7, '4': 0.4}
	,'5211': {'-4': 0.5, '-3': 0.7, '-2': 0.9, '-1': 1, '0': 0.9, '1': 0.7, '2': 0.5, '3': 0.3, '4': 0.2}
	,'5221': {'-4': 1.1, '-3': 1.4, '-2': 1.7, '-1': 1.8, '0': 1.5, '1': 1.2, '2': 0.8, '3': 0.5, '4': 0.3}
	,'5231': {'-4': 1.1, '-3': 1.4, '-2': 1.7, '-1': 1.8, '0': 1.6, '1': 1.2, '2': 0.8, '3': 0.5, '4': 0.3}
	,'5251': {'-4': 1, '-3': 1.4, '-2': 1.7, '-1': 2, '0': 1.8, '1': 1.4, '2': 1, '3': 0.6, '4': 0.4}
	,'5241': {'-4': 1.9, '-3': 2.4, '-2': 2.8, '-1': 2.8, '0': 2.4, '1': 1.8, '2': 1.2, '3': 0.8, '4': 0.5}
	,'5261': {'-4': 1.7, '-3': 2.2, '-2': 2.5, '-1': 2.6, '0': 2.3, '1': 1.7, '2': 1.2, '3': 0.8, '4': 0.5}
	,'5271': {'-4': 1.6, '-3': 2, '-2': 2.2, '-1': 2.1, '0': 1.9, '1': 1.4, '2': 1, '3': 0.6, '4': 0.4}
	,'5281': {'-4': 2.9, '-3': 3.4, '-2': 3.7, '-1': 3.5, '0': 3, '1': 2.2, '2': 1.5, '3': 1, '4': 0.6}
	,'5212': {'-4': 0.3, '-3': 0.4, '-2': 0.6, '-1': 0.6, '0': 0.6, '1': 0.4, '2': 0.3, '3': 0.2, '4': 0.1}
	,'5222': {'-4': 0.6, '-3': 0.9, '-2': 1.1, '-1': 1.2, '0': 1.1, '1': 0.8, '2': 0.6, '3': 0.4, '4': 0.2}
	,'5232': {'-4': 0.8, '-3': 1.2, '-2': 1.5, '-1': 1.7, '0': 1.6, '1': 1.2, '2': 0.8, '3': 0.6, '4': 0.4}
	,'5252': {'-4': 0.9, '-3': 1.3, '-2': 1.7, '-1': 2, '0': 1.9, '1': 1.4, '2': 1, '3': 0.7, '4': 0.4}
	,'5242': {'-4': 1.4, '-3': 1.9, '-2': 2.3, '-1': 2.5, '0': 2.2, '1': 1.6, '2': 1.1, '3': 0.8, '4': 0.5}
	,'5262': {'-4': 1.5, '-3': 2, '-2': 2.4, '-1': 2.6, '0': 2.4, '1': 1.8, '2': 1.2, '3': 0.8, '4': 0.5}
	,'5272': {'-4': 1.7, '-3': 2.3, '-2': 2.8, '-1': 3, '0': 2.6, '1': 1.9, '2': 1.3, '3': 0.9, '4': 0.6}
	,'5282': {'-4': 2.7, '-3': 3.4, '-2': 4, '-1': 4.2, '0': 3.6, '1': 2.7, '2': 1.8, '3': 1.2, '4': 0.8}
	,'6110': {'-4': 0.3, '-3': 0.5, '-2': 0.7, '-1': 1, '0': 1.3, '1': 1.4, '2': 1.3, '3': 1, '4': 0.7}
	,'6120': {'-4': 0.5, '-3': 0.7, '-2': 1.1, '-1': 1.6, '0': 2.1, '1': 2.3, '2': 2.1, '3': 1.7, '4': 1.2}
	,'6130': {'-4': 0.4, '-3': 0.6, '-2': 0.9, '-1': 1.3, '0': 1.7, '1': 1.9, '2': 1.8, '3': 1.5, '4': 1.1}
	,'6150': {'-4': 0.3, '-3': 0.5, '-2': 0.7, '-1': 1, '0': 1.4, '1': 1.7, '2': 1.7, '3': 1.4, '4': 1.1}
	,'6140': {'-4': 0.5, '-3': 0.8, '-2': 1.3, '-1': 1.8, '0': 2.5, '1': 2.9, '2': 2.8, '3': 2.4, '4': 1.9}
	,'6160': {'-4': 0.4, '-3': 0.6, '-2': 0.9, '-1': 1.4, '0': 1.9, '1': 2.4, '2': 2.6, '3': 2.3, '4': 1.8}
	,'6170': {'-4': 0.4, '-3': 0.6, '-2': 0.9, '-1': 1.3, '0': 1.8, '1': 2.2, '2': 2.3, '3': 2.1, '4': 1.7}
	,'6180': {'-4': 0.5, '-3': 0.8, '-2': 1.2, '-1': 1.8, '0': 2.5, '1': 3.1, '2': 3.4, '3': 3.2, '4': 2.7}
	,'6111': {'-4': 0.2, '-3': 0.4, '-2': 0.5, '-1': 0.8, '0': 1, '1': 1.1, '2': 0.9, '3': 0.7, '4': 0.4}
	,'6121': {'-4': 0.4, '-3': 0.6, '-2': 0.9, '-1': 1.3, '0': 1.8, '1': 1.9, '2': 1.7, '3': 1.3, '4': 0.9}
	,'6131': {'-4': 0.4, '-3': 0.6, '-2': 1, '-1': 1.4, '0': 1.8, '1': 2, '2': 1.7, '3': 1.3, '4': 0.9}
	,'6151': {'-4': 0.5, '-3': 0.8, '-2': 1.1, '-1': 1.6, '0': 2, '1': 2.1, '2': 1.6, '3': 1.3, '4': 0.9}
	,'6141': {'-4': 0.6, '-3': 1, '-2': 1.5, '-1': 2.1, '0': 2.8, '1': 3.1, '2': 2.8, '3': 2.2, '4': 1.6}
	,'6161': {'-4': 0.6, '-3': 0.9, '-2': 1.4, '-1': 2, '0': 2.6, '1': 2.8, '2': 2.5, '3': 2.1, '4': 1.5}
	,'6171': {'-4': 0.5, '-3': 0.8, '-2': 1.1, '-1': 1.6, '0': 2.1, '1': 2.3, '2': 2.3, '3': 1.9, '4': 1.5}
	,'6181': {'-4': 0.8, '-3': 1.2, '-2': 1.8, '-1': 2.6, '0': 3.4, '1': 3.9, '2': 3.9, '3': 3.3, '4': 2.6}
	,'6112': {'-4': 0.2, '-3': 0.3, '-2': 0.4, '-1': 0.5, '0': 0.7, '1': 0.7, '2': 0.5, '3': 0.4, '4': 0.2}
	,'6122': {'-4': 0.3, '-3': 0.5, '-2': 0.7, '-1': 1, '0': 1.3, '1': 1.3, '2': 1.1, '3': 0.8, '4': 0.5}
	,'6132': {'-4': 0.4, '-3': 0.7, '-2': 1, '-1': 1.4, '0': 1.8, '1': 1.8, '2': 1.4, '3': 1, '4': 0.7}
	,'6152': {'-4': 0.5, '-3': 0.8, '-2': 1.2, '-1': 1.6, '0': 2.1, '1': 2.1, '2': 1.6, '3': 1.1, '4': 0.7}
	,'6142': {'-4': 0.6, '-3': 0.9, '-2': 1.3, '-1': 1.9, '0': 2.5, '1': 2.6, '2': 2.3, '3': 1.7, '4': 1.1}
	,'6162': {'-4': 0.6, '-3': 1, '-2': 1.4, '-1': 2, '0': 2.7, '1': 2.8, '2': 2.4, '3': 1.8, '4': 1.2}
	,'6172': {'-4': 0.7, '-3': 1, '-2': 1.6, '-1': 2.2, '0': 2.9, '1': 3.2, '2': 2.7, '3': 2, '4': 1.4}
	,'6182': {'-4': 0.9, '-3': 1.4, '-2': 2.2, '-1': 3.1, '0': 4.1, '1': 4.5, '2': 4.1, '3': 3.2, '4': 2.3}
	,'6210': {'-4': 0.8, '-3': 1.1, '-2': 1.4, '-1': 1.6, '0': 1.3, '1': 0.9, '2': 0.6, '3': 0.3, '4': 0.2}
	,'6220': {'-4': 1.4, '-3': 1.9, '-2': 2.3, '-1': 2.4, '0': 2, '1': 1.3, '2': 0.8, '3': 0.5, '4': 0.3}
	,'6230': {'-4': 1.3, '-3': 1.7, '-2': 2, '-1': 2, '0': 1.6, '1': 1.1, '2': 0.7, '3': 0.4, '4': 0.2}
	,'6250': {'-4': 1.2, '-3': 1.6, '-2': 1.8, '-1': 1.7, '0': 1.3, '1': 0.8, '2': 0.5, '3': 0.3, '4': 0.2}
	,'6240': {'-4': 2.2, '-3': 2.7, '-2': 3.1, '-1': 3, '0': 2.3, '1': 1.5, '2': 0.9, '3': 0.6, '4': 0.3}
	,'6260': {'-4': 2, '-3': 2.5, '-2': 2.7, '-1': 2.4, '0': 1.7, '1': 1.1, '2': 0.7, '3': 0.4, '4': 0.2}
	,'6270': {'-4': 1.9, '-3': 2.3, '-2': 2.4, '-1': 2.2, '0': 1.7, '1': 1.1, '2': 0.7, '3': 0.4, '4': 0.2}
	,'6280': {'-4': 3, '-3': 3.5, '-2': 3.6, '-1': 3.1, '0': 2.2, '1': 1.4, '2': 0.9, '3': 0.5, '4': 0.3}
	,'6211': {'-4': 0.5, '-3': 0.8, '-2': 1, '-1': 1.2, '0': 1, '1': 0.7, '2': 0.4, '3': 0.3, '4': 0.2}
	,'6221': {'-4': 1, '-3': 1.5, '-2': 1.9, '-1': 2.1, '0': 1.7, '1': 1.1, '2': 0.7, '3': 0.4, '4': 0.3}
	,'6231': {'-4': 1.1, '-3': 1.5, '-2': 1.9, '-1': 2.1, '0': 1.8, '1': 1.2, '2': 0.8, '3': 0.5, '4': 0.3}
	,'6251': {'-4': 1, '-3': 1.4, '-2': 1.9, '-1': 2.3, '0': 2.1, '1': 1.4, '2': 0.9, '3': 0.5, '4': 0.3}
	,'6241': {'-4': 1.9, '-3': 2.6, '-2': 3.1, '-1': 3.3, '0': 2.7, '1': 1.8, '2': 1.1, '3': 0.7, '4': 0.4}
	,'6261': {'-4': 1.8, '-3': 2.4, '-2': 2.8, '-1': 3, '0': 2.6, '1': 1.7, '2': 1.1, '3': 0.7, '4': 0.4}
	,'6271': {'-4': 1.7, '-3': 2.2, '-2': 2.5, '-1': 2.5, '0': 2.1, '1': 1.4, '2': 0.9, '3': 0.5, '4': 0.3}
	,'6281': {'-4': 3, '-3': 3.8, '-2': 4.2, '-1': 4, '0': 3.3, '1': 2.2, '2': 1.4, '3': 0.8, '4': 0.5}
	,'6212': {'-4': 0.3, '-3': 0.4, '-2': 0.6, '-1': 0.8, '0': 0.7, '1': 0.5, '2': 0.3, '3': 0.2, '4': 0.1}
	,'6222': {'-4': 0.6, '-3': 0.9, '-2': 1.2, '-1': 1.5, '0': 1.3, '1': 0.8, '2': 0.5, '3': 0.3, '4': 0.2}
	,'6232': {'-4': 0.8, '-3': 1.2, '-2': 1.6, '-1': 2, '0': 1.8, '1': 1.2, '2': 0.8, '3': 0.5, '4': 0.3}
	,'6252': {'-4': 0.9, '-3': 1.3, '-2': 1.9, '-1': 2.4, '0': 2.2, '1': 1.4, '2': 0.9, '3': 0.6, '4': 0.3}
	,'6242': {'-4': 1.4, '-3': 2, '-2': 2.6, '-1': 2.9, '0': 2.5, '1': 1.6, '2': 1, '3': 0.6, '4': 0.4}
	,'6262': {'-4': 1.4, '-3': 2.1, '-2': 2.7, '-1': 3.1, '0': 2.7, '1': 1.8, '2': 1.1, '3': 0.7, '4': 0.4}
	,'6272': {'-4': 1.6, '-3': 2.4, '-2': 3.1, '-1': 3.5, '0': 2.9, '1': 1.9, '2': 1.2, '3': 0.7, '4': 0.4}
	,'6282': {'-4': 2.7, '-3': 3.7, '-2': 4.5, '-1': 4.9, '0': 4, '1': 2.6, '2': 1.7, '3': 1, '4': 0.6}
	,'7110': {'-4': 0.2, '-3': 0.4, '-2': 0.7, '-1': 1, '0': 1.5, '1': 1.7, '2': 1.4, '3': 1, '4': 0.6}
	,'7120': {'-4': 0.4, '-3': 0.6, '-2': 1, '-1': 1.6, '0': 2.4, '1': 2.7, '2': 2.3, '3': 1.7, '4': 1.2}
	,'7130': {'-4': 0.3, '-3': 0.5, '-2': 0.8, '-1': 1.2, '0': 1.9, '1': 2.3, '2': 2, '3': 1.5, '4': 1.1}
	,'7150': {'-4': 0.2, '-3': 0.4, '-2': 0.6, '-1': 1, '0': 1.5, '1': 2, '2': 1.9, '3': 1.5, '4': 1.1}
	,'7140': {'-4': 0.4, '-3': 0.7, '-2': 1.1, '-1': 1.8, '0': 2.7, '1': 3.4, '2': 3.2, '3': 2.6, '4': 1.9}
	,'7160': {'-4': 0.3, '-3': 0.5, '-2': 0.8, '-1': 1.3, '0': 2, '1': 2.8, '2': 3, '3': 2.5, '4': 1.8}
	,'7170': {'-4': 0.3, '-3': 0.5, '-2': 0.8, '-1': 1.3, '0': 2, '1': 2.5, '2': 2.6, '3': 2.3, '4': 1.7}
	,'7180': {'-4': 0.4, '-3': 0.6, '-2': 1.1, '-1': 1.7, '0': 2.6, '1': 3.5, '2': 3.9, '3': 3.6, '4': 2.8}
	,'7111': {'-4': 0.2, '-3': 0.3, '-2': 0.5, '-1': 0.8, '0': 1.2, '1': 1.3, '2': 1, '3': 0.6, '4': 0.4}
	,'7121': {'-4': 0.3, '-3': 0.5, '-2': 0.9, '-1': 1.4, '0': 2, '1': 2.3, '2': 1.8, '3': 1.3, '4': 0.8}
	,'7131': {'-4': 0.3, '-3': 0.6, '-2': 0.9, '-1': 1.4, '0': 2.1, '1': 2.3, '2': 1.8, '3': 1.3, '4': 0.8}
	,'7151': {'-4': 0.4, '-3': 0.7, '-2': 1.1, '-1': 1.6, '0': 2.4, '1': 2.5, '2': 1.8, '3': 1.3, '4': 0.8}
	,'7141': {'-4': 0.5, '-3': 0.8, '-2': 1.3, '-1': 2.1, '0': 3.2, '1': 3.6, '2': 3.1, '3': 2.3, '4': 1.6}
	,'7161': {'-4': 0.5, '-3': 0.8, '-2': 1.3, '-1': 2, '0': 3.1, '1': 3.3, '2': 2.9, '3': 2.2, '4': 1.5}
	,'7171': {'-4': 0.4, '-3': 0.7, '-2': 1.1, '-1': 1.6, '0': 2.5, '1': 2.8, '2': 2.7, '3': 2.1, '4': 1.4}
	,'7181': {'-4': 0.6, '-3': 1, '-2': 1.6, '-1': 2.6, '0': 3.9, '1': 4.5, '2': 4.4, '3': 3.6, '4': 2.6}
	,'7112': {'-4': 0.1, '-3': 0.2, '-2': 0.4, '-1': 0.5, '0': 0.8, '1': 0.8, '2': 0.6, '3': 0.3, '4': 0.2}
	,'7122': {'-4': 0.2, '-3': 0.4, '-2': 0.6, '-1': 1, '0': 1.5, '1': 1.6, '2': 1.2, '3': 0.7, '4': 0.4}
	,'7132': {'-4': 0.3, '-3': 0.6, '-2': 0.9, '-1': 1.4, '0': 2.1, '1': 2.2, '2': 1.5, '3': 0.9, '4': 0.6}
	,'7152': {'-4': 0.4, '-3': 0.7, '-2': 1.1, '-1': 1.7, '0': 2.5, '1': 2.5, '2': 1.7, '3': 1.1, '4': 0.6}
	,'7142': {'-4': 0.4, '-3': 0.8, '-2': 1.2, '-1': 1.9, '0': 2.9, '1': 3.2, '2': 2.5, '3': 1.7, '4': 1}
	,'7162': {'-4': 0.5, '-3': 0.8, '-2': 1.4, '-1': 2.1, '0': 3.1, '1': 3.4, '2': 2.6, '3': 1.7, '4': 1.1}
	,'7172': {'-4': 0.5, '-3': 0.9, '-2': 1.5, '-1': 2.3, '0': 3.4, '1': 3.9, '2': 3.1, '3': 2, '4': 1.2}
	,'7182': {'-4': 0.7, '-3': 1.2, '-2': 2, '-1': 3.1, '0': 4.7, '1': 5.4, '2': 4.5, '3': 3.3, '4': 2.1}
	,'7210': {'-4': 0.8, '-3': 1.2, '-2': 1.6, '-1': 1.9, '0': 1.5, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'7220': {'-4': 1.4, '-3': 2, '-2': 2.6, '-1': 3, '0': 2.3, '1': 1.2, '2': 0.7, '3': 0.4, '4': 0.2}
	,'7230': {'-4': 1.3, '-3': 1.8, '-2': 2.3, '-1': 2.4, '0': 1.8, '1': 0.9, '2': 0.5, '3': 0.3, '4': 0.1}
	,'7250': {'-4': 1.3, '-3': 1.8, '-2': 2.2, '-1': 2, '0': 1.5, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'7240': {'-4': 2.2, '-3': 3, '-2': 3.6, '-1': 3.5, '0': 2.5, '1': 1.3, '2': 0.8, '3': 0.4, '4': 0.2}
	,'7260': {'-4': 2.1, '-3': 2.8, '-2': 3.3, '-1': 2.8, '0': 1.7, '1': 0.9, '2': 0.5, '3': 0.3, '4': 0.1}
	,'7270': {'-4': 2, '-3': 2.6, '-2': 2.9, '-1': 2.6, '0': 1.7, '1': 0.9, '2': 0.5, '3': 0.3, '4': 0.1}
	,'7280': {'-4': 3.3, '-3': 4, '-2': 4.1, '-1': 3.5, '0': 2.3, '1': 1.2, '2': 0.7, '3': 0.4, '4': 0.2}
	,'7211': {'-4': 0.5, '-3': 0.8, '-2': 1.1, '-1': 1.4, '0': 1.2, '1': 0.6, '2': 0.3, '3': 0.2, '4': 0.1}
	,'7221': {'-4': 1, '-3': 1.5, '-2': 2.1, '-1': 2.5, '0': 2, '1': 1.1, '2': 0.6, '3': 0.3, '4': 0.2}
	,'7231': {'-4': 1, '-3': 1.5, '-2': 2.1, '-1': 2.6, '0': 2.1, '1': 1.1, '2': 0.6, '3': 0.3, '4': 0.2}
	,'7251': {'-4': 1, '-3': 1.5, '-2': 2.1, '-1': 2.9, '0': 2.5, '1': 1.3, '2': 0.7, '3': 0.4, '4': 0.2}
	,'7241': {'-4': 1.9, '-3': 2.7, '-2': 3.6, '-1': 4, '0': 3, '1': 1.6, '2': 0.9, '3': 0.5, '4': 0.3}
	,'7261': {'-4': 1.8, '-3': 2.6, '-2': 3.2, '-1': 3.7, '0': 3.1, '1': 1.6, '2': 0.9, '3': 0.5, '4': 0.3}
	,'7271': {'-4': 1.7, '-3': 2.4, '-2': 3, '-1': 3, '0': 2.5, '1': 1.3, '2': 0.7, '3': 0.4, '4': 0.2}
	,'7281': {'-4': 3.1, '-3': 4.2, '-2': 4.9, '-1': 4.8, '0': 3.7, '1': 2, '2': 1.1, '3': 0.6, '4': 0.3}
	,'7212': {'-4': 0.2, '-3': 0.4, '-2': 0.7, '-1': 1, '0': 0.8, '1': 0.4, '2': 0.2, '3': 0.1, '4': 0.1}
	,'7222': {'-4': 0.5, '-3': 0.9, '-2': 1.4, '-1': 1.8, '0': 1.5, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'7232': {'-4': 0.7, '-3': 1.1, '-2': 1.8, '-1': 2.6, '0': 2.2, '1': 1.1, '2': 0.7, '3': 0.4, '4': 0.2}
	,'7252': {'-4': 0.8, '-3': 1.3, '-2': 2, '-1': 3, '0': 2.6, '1': 1.4, '2': 0.8, '3': 0.4, '4': 0.2}
	,'7242': {'-4': 1.2, '-3': 2, '-2': 2.9, '-1': 3.6, '0': 2.9, '1': 1.5, '2': 0.8, '3': 0.5, '4': 0.2}
	,'7262': {'-4': 1.3, '-3': 2.1, '-2': 3, '-1': 3.8, '0': 3.1, '1': 1.6, '2': 0.9, '3': 0.5, '4': 0.3}
	,'7272': {'-4': 1.5, '-3': 2.4, '-2': 3.6, '-1': 4.3, '0': 3.3, '1': 1.7, '2': 1, '3': 0.5, '4': 0.3}
	,'7282': {'-4': 2.6, '-3': 3.9, '-2': 5.2, '-1': 5.9, '0': 4.5, '1': 2.4, '2': 1.3, '3': 0.7, '4': 0.4}
	,'8110': {'-4': 0.2, '-3': 0.3, '-2': 0.6, '-1': 1, '0': 1.9, '1': 2.2, '2': 1.5, '3': 0.9, '4': 0.6}
	,'8120': {'-4': 0.2, '-3': 0.4, '-2': 0.8, '-1': 1.5, '0': 2.8, '1': 3.4, '2': 2.6, '3': 1.7, '4': 1}
	,'8130': {'-4': 0.2, '-3': 0.4, '-2': 0.6, '-1': 1.1, '0': 2.2, '1': 2.8, '2': 2.3, '3': 1.6, '4': 1}
	,'8150': {'-4': 0.2, '-3': 0.3, '-2': 0.5, '-1': 0.9, '0': 1.8, '1': 2.4, '2': 2.3, '3': 1.6, '4': 1}
	,'8140': {'-4': 0.3, '-3': 0.5, '-2': 0.9, '-1': 1.6, '0': 3.1, '1': 4.1, '2': 3.7, '3': 2.8, '4': 1.8}
	,'8160': {'-4': 0.2, '-3': 0.3, '-2': 0.6, '-1': 1.1, '0': 2.1, '1': 3.3, '2': 3.6, '3': 2.8, '4': 1.8}
	,'8170': {'-4': 0.2, '-3': 0.3, '-2': 0.6, '-1': 1.1, '0': 2.1, '1': 3.1, '2': 3.2, '3': 2.6, '4': 1.7}
	,'8180': {'-4': 0.2, '-3': 0.4, '-2': 0.8, '-1': 1.5, '0': 2.8, '1': 4.1, '2': 4.6, '3': 4, '4': 3}
	,'8111': {'-4': 0.1, '-3': 0.2, '-2': 0.4, '-1': 0.7, '0': 1.4, '1': 1.6, '2': 1, '3': 0.6, '4': 0.3}
	,'8121': {'-4': 0.2, '-3': 0.4, '-2': 0.7, '-1': 1.3, '0': 2.4, '1': 2.9, '2': 2, '3': 1.2, '4': 0.7}
	,'8131': {'-4': 0.2, '-3': 0.4, '-2': 0.8, '-1': 1.3, '0': 2.5, '1': 2.9, '2': 2, '3': 1.2, '4': 0.7}
	,'8151': {'-4': 0.3, '-3': 0.5, '-2': 0.9, '-1': 1.6, '0': 3.1, '1': 3.2, '2': 2, '3': 1.3, '4': 0.7}
	,'8141': {'-4': 0.3, '-3': 0.6, '-2': 1.1, '-1': 2, '0': 3.7, '1': 4.5, '2': 3.5, '3': 2.4, '4': 1.4}
	,'8161': {'-4': 0.3, '-3': 0.6, '-2': 1.1, '-1': 2, '0': 3.8, '1': 4.2, '2': 3.3, '3': 2.3, '4': 1.4}
	,'8171': {'-4': 0.3, '-3': 0.5, '-2': 0.9, '-1': 1.6, '0': 3, '1': 3.4, '2': 3.2, '3': 2.2, '4': 1.3}
	,'8181': {'-4': 0.4, '-3': 0.7, '-2': 1.4, '-1': 2.4, '0': 4.6, '1': 5.6, '2': 5.2, '3': 3.9, '4': 2.6}
	,'8112': {'-4': 0.1, '-3': 0.2, '-2': 0.3, '-1': 0.5, '0': 1, '1': 1.1, '2': 0.6, '3': 0.3, '4': 0.1}
	,'8122': {'-4': 0.2, '-3': 0.3, '-2': 0.5, '-1': 1, '0': 1.8, '1': 2.1, '2': 1.3, '3': 0.7, '4': 0.3}
	,'8132': {'-4': 0.2, '-3': 0.4, '-2': 0.8, '-1': 1.4, '0': 2.7, '1': 2.8, '2': 1.5, '3': 0.8, '4': 0.4}
	,'8152': {'-4': 0.3, '-3': 0.5, '-2': 0.9, '-1': 1.6, '0': 3.2, '1': 3.3, '2': 1.7, '3': 0.9, '4': 0.4}
	,'8142': {'-4': 0.3, '-3': 0.6, '-2': 1, '-1': 1.8, '0': 3.5, '1': 4, '2': 2.7, '3': 1.6, '4': 0.8}
	,'8162': {'-4': 0.3, '-3': 0.6, '-2': 1.1, '-1': 2, '0': 3.8, '1': 4.3, '2': 2.8, '3': 1.6, '4': 0.8}
	,'8172': {'-4': 0.3, '-3': 0.7, '-2': 1.2, '-1': 2.1, '0': 4, '1': 4.9, '2': 3.5, '3': 1.9, '4': 1}
	,'8182': {'-4': 0.5, '-3': 0.9, '-2': 1.6, '-1': 2.9, '0': 5.6, '1': 6.8, '2': 5.1, '3': 3.3, '4': 1.9}
	,'8210': {'-4': 0.7, '-3': 1.1, '-2': 1.8, '-1': 2.5, '0': 1.8, '1': 0.6, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8220': {'-4': 1.3, '-3': 2.1, '-2': 3.1, '-1': 3.8, '0': 2.6, '1': 0.9, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8230': {'-4': 1.2, '-3': 1.9, '-2': 2.7, '-1': 3.1, '0': 2, '1': 0.7, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8250': {'-4': 1.2, '-3': 1.9, '-2': 2.7, '-1': 2.5, '0': 1.7, '1': 0.6, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8240': {'-4': 2.2, '-3': 3.3, '-2': 4.2, '-1': 4.4, '0': 2.8, '1': 0.9, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8260': {'-4': 2.2, '-3': 3.2, '-2': 4.1, '-1': 3.3, '0': 1.8, '1': 0.6, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8270': {'-4': 2.1, '-3': 3, '-2': 3.6, '-1': 3.3, '0': 1.8, '1': 0.6, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8280': {'-4': 3.5, '-3': 4.6, '-2': 5, '-1': 4.2, '0': 2.2, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8211': {'-4': 0.4, '-3': 0.7, '-2': 1.2, '-1': 1.9, '0': 1.4, '1': 0.5, '2': 0.2, '3': 0.1, '4': 0}
	,'8221': {'-4': 0.8, '-3': 1.5, '-2': 2.4, '-1': 3.4, '0': 2.4, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8231': {'-4': 0.8, '-3': 1.5, '-2': 2.4, '-1': 3.4, '0': 2.5, '1': 0.8, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8251': {'-4': 0.9, '-3': 1.5, '-2': 2.4, '-1': 3.9, '0': 3.2, '1': 1, '2': 0.5, '3': 0.2, '4': 0.1}
	,'8241': {'-4': 1.7, '-3': 2.9, '-2': 4.1, '-1': 5.1, '0': 3.5, '1': 1.1, '2': 0.5, '3': 0.3, '4': 0.1}
	,'8261': {'-4': 1.7, '-3': 2.7, '-2': 3.9, '-1': 4.9, '0': 3.8, '1': 1.2, '2': 0.6, '3': 0.3, '4': 0.1}
	,'8271': {'-4': 1.6, '-3': 2.6, '-2': 3.7, '-1': 4, '0': 3, '1': 0.9, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8281': {'-4': 3.1, '-3': 4.6, '-2': 5.9, '-1': 6.2, '0': 4.4, '1': 1.4, '2': 0.7, '3': 0.3, '4': 0.1}
	,'8212': {'-4': 0.2, '-3': 0.4, '-2': 0.7, '-1': 1.3, '0': 1.1, '1': 0.3, '2': 0.2, '3': 0.1, '4': 0}
	,'8222': {'-4': 0.4, '-3': 0.8, '-2': 1.6, '-1': 2.5, '0': 1.8, '1': 0.6, '2': 0.3, '3': 0.1, '4': 0.1}
	,'8232': {'-4': 0.5, '-3': 1, '-2': 1.9, '-1': 3.5, '0': 2.8, '1': 0.9, '2': 0.4, '3': 0.2, '4': 0.1}
	,'8252': {'-4': 0.5, '-3': 1.1, '-2': 2.1, '-1': 4.1, '0': 3.4, '1': 1, '2': 0.5, '3': 0.2, '4': 0.1}
	,'8242': {'-4': 1, '-3': 2, '-2': 3.3, '-1': 4.7, '0': 3.5, '1': 1.1, '2': 0.5, '3': 0.2, '4': 0.1}
	,'8262': {'-4': 1, '-3': 2, '-2': 3.4, '-1': 5.1, '0': 3.8, '1': 1.2, '2': 0.6, '3': 0.3, '4': 0.1}
	,'8272': {'-4': 1.2, '-3': 2.3, '-2': 4.2, '-1': 5.8, '0': 3.9, '1': 1.3, '2': 0.6, '3': 0.3, '4': 0.1}
	,'8282': {'-4': 2.3, '-3': 4, '-2': 6.1, '-1': 7.7, '0': 5.3, '1': 1.7, '2': 0.8, '3': 0.4, '4': 0.2}
	,'9110': {'-4': 0.1, '-3': 0.2, '-2': 0.3, '-1': 0.7, '0': 2.4, '1': 2.9, '2': 1.6, '3': 0.8, '4': 0.4}
	,'9120': {'-4': 0.1, '-3': 0.2, '-2': 0.5, '-1': 1.1, '0': 3.4, '1': 4.6, '2': 2.9, '3': 1.6, '4': 0.8}
	,'9130': {'-4': 0.1, '-3': 0.2, '-2': 0.4, '-1': 0.8, '0': 2.6, '1': 3.7, '2': 2.7, '3': 1.5, '4': 0.8}
	,'9150': {'-4': 0.1, '-3': 0.2, '-2': 0.3, '-1': 0.7, '0': 2.3, '1': 3.1, '2': 2.9, '3': 1.6, '4': 0.8}
	,'9140': {'-4': 0.1, '-3': 0.3, '-2': 0.6, '-1': 1.2, '0': 3.6, '1': 5.3, '2': 4.4, '3': 2.9, '4': 1.6}
	,'9160': {'-4': 0.1, '-3': 0.2, '-2': 0.4, '-1': 0.8, '0': 2.3, '1': 4.2, '2': 4.6, '3': 3, '4': 1.7}
	,'9170': {'-4': 0.1, '-3': 0.2, '-2': 0.4, '-1': 0.8, '0': 2.4, '1': 4, '2': 4, '3': 2.9, '4': 1.6}
	,'9180': {'-4': 0.1, '-3': 0.2, '-2': 0.5, '-1': 1, '0': 2.9, '1': 5.2, '2': 5.7, '3': 4.6, '4': 3.1}
	,'9111': {'-4': 0.1, '-3': 0.1, '-2': 0.3, '-1': 0.6, '0': 1.9, '1': 2.2, '2': 1, '3': 0.5, '4': 0.2}
	,'9121': {'-4': 0.1, '-3': 0.2, '-2': 0.5, '-1': 1, '0': 3.1, '1': 3.9, '2': 2.2, '3': 1, '4': 0.4}
	,'9131': {'-4': 0.1, '-3': 0.2, '-2': 0.5, '-1': 1, '0': 3.3, '1': 4, '2': 2.2, '3': 1, '4': 0.5}
	,'9151': {'-4': 0.1, '-3': 0.3, '-2': 0.6, '-1': 1.2, '0': 4.3, '1': 4.4, '2': 2.3, '3': 1.1, '4': 0.5}
	,'9141': {'-4': 0.1, '-3': 0.3, '-2': 0.7, '-1': 1.4, '0': 4.6, '1': 6.1, '2': 4, '3': 2.3, '4': 1.1}
	,'9161': {'-4': 0.1, '-3': 0.3, '-2': 0.7, '-1': 1.5, '0': 5, '1': 5.7, '2': 4, '3': 2.3, '4': 1.1}
	,'9171': {'-4': 0.1, '-3': 0.3, '-2': 0.6, '-1': 1.2, '0': 3.9, '1': 4.6, '2': 3.9, '3': 2.3, '4': 1.1}
	,'9181': {'-4': 0.2, '-3': 0.4, '-2': 0.8, '-1': 1.8, '0': 5.7, '1': 7.3, '2': 6.2, '3': 4.2, '4': 2.4}
	,'9112': {'-4': 0, '-3': 0.1, '-2': 0.2, '-1': 0.4, '0': 1.4, '1': 1.5, '2': 0.5, '3': 0.2, '4': 0.1}
	,'9122': {'-4': 0.1, '-3': 0.2, '-2': 0.3, '-1': 0.7, '0': 2.4, '1': 2.9, '2': 1.3, '3': 0.4, '4': 0.1}
	,'9132': {'-4': 0.1, '-3': 0.2, '-2': 0.5, '-1': 1.1, '0': 3.7, '1': 4, '2': 1.4, '3': 0.5, '4': 0.2}
	,'9152': {'-4': 0.1, '-3': 0.3, '-2': 0.6, '-1': 1.3, '0': 4.4, '1': 4.6, '2': 1.4, '3': 0.5, '4': 0.2}
	,'9142': {'-4': 0.1, '-3': 0.3, '-2': 0.7, '-1': 1.4, '0': 4.5, '1': 5.5, '2': 2.9, '3': 1.3, '4': 0.4}
	,'9162': {'-4': 0.2, '-3': 0.3, '-2': 0.7, '-1': 1.5, '0': 5, '1': 5.9, '2': 2.9, '3': 1.3, '4': 0.4}
	,'9172': {'-4': 0.2, '-3': 0.3, '-2': 0.7, '-1': 1.6, '0': 5.1, '1': 6.9, '2': 3.9, '3': 1.4, '4': 0.5}
	,'9182': {'-4': 0.2, '-3': 0.5, '-2': 1, '-1': 2.1, '0': 6.9, '1': 9.1, '2': 5.7, '3': 3.1, '4': 1.4}
	,'9210': {'-4': 0.5, '-3': 1, '-2': 2, '-1': 3.6, '0': 2.3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9220': {'-4': 1, '-3': 2, '-2': 3.6, '-1': 5.4, '0': 3.1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9230': {'-4': 1, '-3': 1.9, '-2': 3.3, '-1': 4.3, '0': 2.5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9250': {'-4': 1.1, '-3': 2, '-2': 3.6, '-1': 3.5, '0': 2.1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9240': {'-4': 2, '-3': 3.6, '-2': 5.2, '-1': 6, '0': 3.2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9260': {'-4': 2.1, '-3': 3.7, '-2': 5.5, '-1': 4.3, '0': 2.1, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9270': {'-4': 2, '-3': 3.5, '-2': 4.7, '-1': 4.3, '0': 2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9280': {'-4': 3.7, '-3': 5.4, '-2': 6.4, '-1': 5.3, '0': 2.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9211': {'-4': 0.2, '-3': 0.6, '-2': 1.3, '-1': 2.8, '0': 1.9, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9221': {'-4': 0.6, '-3': 1.3, '-2': 2.8, '-1': 4.8, '0': 3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9231': {'-4': 0.6, '-3': 1.3, '-2': 2.7, '-1': 5, '0': 3.2, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9251': {'-4': 0.6, '-3': 1.4, '-2': 2.9, '-1': 5.8, '0': 4.5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9241': {'-4': 1.3, '-3': 2.9, '-2': 4.8, '-1': 7.2, '0': 4.3, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9261': {'-4': 1.4, '-3': 2.9, '-2': 4.9, '-1': 7.1, '0': 5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9271': {'-4': 1.4, '-3': 2.8, '-2': 4.8, '-1': 5.7, '0': 4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9281': {'-4': 3, '-3': 5.1, '-2': 7.3, '-1': 8.6, '0': 5.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9212': {'-4': 0.1, '-3': 0.2, '-2': 0.6, '-1': 1.9, '0': 1.5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9222': {'-4': 0.2, '-3': 0.5, '-2': 1.7, '-1': 3.7, '0': 2.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9232': {'-4': 0.2, '-3': 0.6, '-2': 1.8, '-1': 5.2, '0': 3.9, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9252': {'-4': 0.2, '-3': 0.7, '-2': 1.8, '-1': 6.1, '0': 4.7, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9242': {'-4': 0.6, '-3': 1.7, '-2': 3.7, '-1': 6.8, '0': 4.4, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9262': {'-4': 0.6, '-3': 1.7, '-2': 3.7, '-1': 7.4, '0': 5, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9272': {'-4': 0.7, '-3': 1.8, '-2': 5.1, '-1': 8.4, '0': 4.7, '1': 0, '2': 0, '3': 0, '4': 0}
	,'9282': {'-4': 1.8, '-3': 3.9, '-2': 7, '-1': 10.9, '0': 6.4, '1': 0, '2': 0, '3': 0, '4': 0}
	};
	
	return LI[InnBaseOut][RunDiff];
}

function getBaseSit(runner_on_base_status){
	if(runner_on_base_status == "0"){
		var BaseSit = 1;
	}else if(runner_on_base_status == "1"){
		var BaseSit = 2;
	}else if(runner_on_base_status == "2"){
		var BaseSit = 3;
	}else if(runner_on_base_status == "4"){
		var BaseSit = 4;
	}else if(runner_on_base_status == "3"){
		var BaseSit = 5;
	}else if(runner_on_base_status == "5"){
		var BaseSit = 6;
	}else if(runner_on_base_status == "6"){
		var BaseSit = 7;
	}else{
		var BaseSit = 8;
	}
	
	return BaseSit;
}

function getRunDiff(away_team_runs, home_team_runs){
	var diff = home_team_runs - away_team_runs;
	var RunDiff = Math.min(Math.max(diff,-4),4);
	
	return RunDiff;
}

// Sets arrays
var finish_ab = <?php echo json_encode($finish_ab);?>;
var on_deck = <?php echo json_encode($on_deck);?>;
var include_CLI = <?php echo json_encode($include_CLI);?>;
var vid_player = <?php echo json_encode($vid_player);?>;
var delay_setting = <?php echo json_encode($delay * 1000);?>;
var pref_order = <?php echo json_encode($priority);?>;
var ignore = <?php echo json_encode($ignore);?>;
var posPlayers = <?php echo json_encode($posPlayers);?>;
var games_CLI = <?php echo json_encode($games_CLI); ?>;



(function (){
  var textFile = null,
  makeTextFile = function (text) {
    var data = new Blob([text], {
      type: 'text/plain'
    });

    // If we are replacing a previously generated file we need to
    // manually revoke the object URL to avoid memory leaks.
    if (textFile !== null) {
      window.URL.revokeObjectURL(textFile);
    }

    textFile = window.URL.createObjectURL(data);

    return textFile;
  };
  
  var create_export = document.getElementById('create_export');

  create_export.addEventListener('click', function () {
    var link = document.getElementById('downloadlink');
    link.href = makeTextFile(JSON.stringify(<?php echo json_encode($settings); ?>));
    link.style.display = 'block';
}, false);
  
})();

$(document).ready(
  function(){
      $('input:file').change(
          function(){
              if ($(this).val()) {
                  $('#file_upload').css('display','block');
              } 
          }
          );
  });



// Sets variables
var cur_game = "";
var cur_game_vid = "";
var cur_batter = "";
var cur_game_high_LI_flag = "N";
var cur_game_high_LI = -3;
var vid_counter = 0;
var vid_launched = 'N';
var delay = 100; 

// Delay
$('#delay').change(function (){
	delay_setting = $(this).val() * 1000;
});

// Ignore
$("input:checkbox[id^=x]").change(function() {
	if($(this).is(":checked")) {
		ignore.push($(this).val());
	}else{
		// Find and remove item from an array
		var i_index = ignore.indexOf($(this).val());
		if(i_index != -1) {
			ignore.splice(i_index, 1);
		}
	}
});

// Priority List
$('select[id^="data_"]').add('select[id^="priority_"]').change(function() {
	var temp_priority = [];
	
	for(i = 1; i <= 55; i++){
		if($('select[id="type_' + i + '"]').val() != '' && $('select[id="priority_' + i + '"]').val() > 0){
			type = $('select[id="type_' + i + '"]').val();
			data = $('select[id="data_' + i + '"]').val();
			priority = $('select[id="priority_' + i + '"]').val();
			temp_priority.push({'type' : type, 'data' : data, 'priority' : priority});
		}
	}
	temp_priority.sort(function(a, b){
		return a.priority-b.priority
	})
	pref_order = temp_priority;
});

// Finish AB
$('input[type=radio][name=finish_ab]').change(function() {
	finish_ab = this.value;
});

// On Deck
$('input[type=radio][name=on_deck]').change(function() {
	on_deck = this.value;
});

// Include CLI
$('input[type=radio][name=include_CLI]').change(function() {
	include_CLI = this.value;
});

// Video Player
$('input[type=radio][name=vid_player]').change(function() {
	vid_player = this.value;
});


// Video launcher
$('#launch').click(function (){
	cur_game = "";
	cur_game_vid = "";
	cur_game_text = "";
	cur_batter = "";
	cur_game_high_LI_flag = "N";
	cur_game_high_LI = -3;
	vid_counter = 0;
	vid_launched = 'N';
	delay = 100;
	stay_on_game = 'N';
	gameWindow = window.open('', 'mlb.tv');
	$(this).hide();
	var timer = setInterval(checkChild, 500);
	vid_launched = 'Y';
});

// Video close
function checkChild(){
	if(gameWindow.closed){
		$('#launch').show();
		cur_game = "";
		cur_game_vid = "";
		cur_batter = "";
		cur_game_high_LI_flag = "N";
		cur_game_high_LI = -3;
		vid_counter = 0;
		vid_launched = 'N';
		delay = 100;
		$("#current_game").html("");
	}
}

// Game updater
function updateGame(game_url, delay) {
	setTimeout(function() {
		gameWindow.location.href = game_url;
	}, delay);
}

function updateGameText(cur_game_text, delay) {
	setTimeout(function() {
		$("#current_game").html("");
		$("#current_game").append('<h2>Currently Showing</h2><br>' + cur_game_text);
	}, delay);
}



// Games
(function update(){
	var games = [];
	var game_vid = "";
	var stay_on_game = "N";
	var user_date = new Date();
	var user_time = user_date.getTime();
	var user_offset = user_date.getTimezoneOffset() * 60000;
	var utc = user_time + user_offset;
	var western_time = utc + (3600000 * -8);
	var date = new Date(western_time);
	
  var day = ("0" + date.getDate()).slice(-2);
	var month = ("0" + (date.getMonth() + 1)).slice(-2);
	var year = date.getFullYear();
	$.ajax({
		type: "GET",
		url: "//gd2.mlb.com/components/game/mlb/year_" + year + "/month_" + month + "/day_" + day + "/playertracker.xml",
		dataType: "xml",
		cache: false,
		success: function(xml) {
			
				$("#active").html("");
				$("#commercial").html("");
				$("#post").html("");
				$("#pre").html("");
				// Goes through each game
				$(xml).find('game').each(function(){
					console.clear();
					// set variables
					var BaseSit = 0;
					var inning = 0;
					var half = 0;
					var half_name = '';
					var outs = 0;
					var run1 = '';
					var run1_name = '';
					var run2 = '';
					var run2_name = '';
					var run3 = '';
					var run3_name = '';
					var skip_game = 'N';
					console.log('vid_counter: ' + vid_counter);
					
					// Adds games that are in progress and not in middle of inning
					if($(this).attr('ind') == 'I' || $(this).attr('ind') == 'MC' || $(this).attr('ind') == 'MA' || $(this).attr('ind') == 'MI'){
						BaseSit = getBaseSit($(this).attr('runner_on_base_status'));
						inning = Math.min($(this).attr('inning'),9);
						if($(this).attr('top_inning') == 'Y'){
							half = 1;
						}else{
							half = 2;
						}
						outs = $(this).attr('outs');
						if(outs == 3){
							if($(this).attr('top_inning') == 'Y'){
								half_name = 'Middle';
							}else{
								half_name = 'End';
							}
							var LI = '';
							var div_status = "#commercial";
						}else{
							if($(this).attr('top_inning') == 'Y'){
								half_name = 'Top';
							}else{
								half_name = 'Bottom';
							}
							var RunDiff = getRunDiff($(this).attr('away_team_runs'), $(this).attr('home_team_runs'));
							var InnBaseOut = inning.toString() + half.toString() + BaseSit.toString() + outs.toString();
							var home_team = $(this).attr('home_name_abbrev');
							
              if(include_CLI === 'Y' && home_team in games_CLI && games_CLI[home_team] !== 'undefined' && games_CLI[home_team] !== ''){
                var CLI = games_CLI[home_team];
              }else{
                var CLI = 1;
              }
              
              console.log($(this).attr('home_name_abbrev') + ' CLI: ' + CLI);
							var LI = (getLI(InnBaseOut,RunDiff) * CLI).toFixed(2);
							var div_status = "#active";
						}
						
						// Adds game to html
						$(div_status).append('Leverage Index: ' + LI + '<table class="main_table"><thead><tr><th width="100">' + half_name + ' ' + $(this).attr('inning') + '</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + $(this).attr('away_team_name') + '</td><td><div align="center">' + $(this).attr('away_team_runs') + '</div></td><td><div align="center">' + $(this).attr('away_team_hits') + '</div></td><td><div align="center">' + $(this).attr('away_team_errors') + '</div></td></tr><tr><td>' + $(this).attr('home_team_name') + '</td><td><div align="center">' + $(this).attr('home_team_runs') + '</div></td><td><div align="center">' + $(this).attr('home_team_hits') + '</div></td><td><div align="center">' + $(this).attr('home_team_errors') + '</div></td></tr><tr><td>Outs: ' + outs + '</td><td colspan="3"><div align="center"><img src="images/Bases_' + BaseSit + '.png" width="34" height="11" align="absmiddle" /></div></td></tr></tbody></table>Pitching: '  + $(this).find('current_pitcher').attr('first_name') + ' ' + $(this).find('current_pitcher').attr('last_name') + '<br>At Bat: '  + $(this).find('current_batter').attr('first_name') + ' ' + $(this).find('current_batter').attr('last_name') + '<br>On Deck: '  + $(this).find('current_ondeck').attr('first_name') + ' ' + $(this).find('current_ondeck').attr('last_name') + '<br><br>');
						
						
						// Baserunners
						if($(this).attr('runner_on_1b')){
							run1 = $(this).attr('runner_on_1b');
							run1_name = $(this).find('runner_on_1b').attr('first_name') + ' ' + $(this).find('runner_on_1b').attr('last_name');
						}
						if($(this).attr('runner_on_2b')){
							run2 = $(this).attr('runner_on_2b');
							run2_name = $(this).find('runner_on_2b').attr('first_name') + ' ' + $(this).find('runner_on_2b').attr('last_name');
						}
						if($(this).attr('runner_on_3b')){
							run3 = $(this).attr('runner_on_3b');
							run3_name = $(this).find('runner_on_3b').attr('first_name') + ' ' + $(this).find('runner_on_3b').attr('last_name');
						}
						
						// Flag to stay on current game if same batter is still at bat
						if(finish_ab == 'Y' && $(this).attr('id') == cur_game && $(this).find('current_batter').attr('id') == cur_batter && outs < 3){
							stay_on_game = 'Y';
						}
												
						// If the current game showing is the highest leverage game and this is that game
						if(cur_game_high_LI_flag == 'Y' && cur_game_vid == $(this).attr('calendar_event_id')){
							cur_game_high_LI = LI; // Grabs that games LI to compare to the new high lev game
							cur_batter = $(this).find('current_batter').attr('id');
						}
						
						// Goes through ignore list to see if game should be skipped
						if(typeof ignore != "undefined" && ignore != null && ignore.length > 0){
							for (i = 0; i < ignore.length; i++){
								if(ignore[i] == $(this).attr('away_name_abbrev') || ignore[i] == $(this).attr('home_name_abbrev')){
									skip_game = 'Y';
								}
							}
						}
						
						// Places data into array
						if(outs < 3 && skip_game == 'N'){
							games.push({
								'id': $(this).attr('id'),
								'LI': LI,
                'outs': outs,
								'vid': $(this).attr('calendar_event_id'),
								'away':  $(this).attr('away_name_abbrev'),
								'home':  $(this).attr('home_name_abbrev'),
								'batter':  $(this).find('current_batter').attr('id'),
								'batter_name': $(this).find('current_batter').attr('first_name') + ' ' + $(this).find('current_batter').attr('last_name'),
								'pitcher':  $(this).find('current_pitcher').attr('id'),
								'pitcher_name': $(this).find('current_pitcher').attr('first_name') + ' ' + $(this).find('current_pitcher').attr('last_name'),
								'ondeck': $(this).find('current_ondeck').attr('id'),
                'ondeck_name': $(this).find('current_ondeck').attr('first_name') + ' ' + $(this).find('current_ondeck').attr('last_name'),
                'run1':  run1,
								'run1_name': run1_name,
								'run2':  run2,
								'run2_name': run2_name,
								'run3':  run3,
								'run3_name': run3_name,
								'inning': $(this).attr('inning'),
								'half': half,
								'away_hits': $(this).attr('away_team_hits'),
								'home_hits': $(this).attr('home_team_hits'),
								'away_runs': $(this).attr('away_team_runs'),
								'home_runs': $(this).attr('home_team_runs'),
								'ind': $(this).attr('ind')
							});
						}
					}else if($(this).attr('ind') == 'F' || $(this).attr('ind') == 'O'){
						// Adds game to html
						$("#post").append('<table class="main_table"><thead><tr><th width="100">Final</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + $(this).attr('away_team_name') + '</td><td><div align="center">' + $(this).attr('away_team_runs') + '</div></td><td><div align="center">' + $(this).attr('away_team_hits') + '</div></td><td><div align="center">' + $(this).attr('away_team_errors') + '</div></td></tr><tr><td>' + $(this).attr('home_team_name') + '</td><td><div align="center">' + $(this).attr('home_team_runs') + '</div></td><td><div align="center">' + $(this).attr('home_team_hits') + '</div></td><td><div align="center">' + $(this).attr('home_team_errors') + '</div></td></tr></tbody></table>W: '  + $(this).find('winning_pitcher').attr('first_name') + ' ' + $(this).find('winning_pitcher').attr('last_name') + '<br>L: '  + $(this).find('losing_pitcher').attr('first_name') + ' ' + $(this).find('losing_pitcher').attr('last_name') + '<br><br>');
					}else if($(this).attr('ind') == 'P' || $(this).attr('ind') == 'PW' || $(this).attr('ind') == 'S'){
						// Adds game to html
						$("#pre").append('<table class="main_table"><thead><tr><th width="100">' + $(this).attr('time') + ' ' + $(this).attr('time_zone') + '</th><th width="60">W - L</th></tr></thead><tbody><tr><td>' + $(this).attr('away_team_name') + '</td><td><div align="center">' + $(this).attr('away_win') + '-' + $(this).attr('away_loss') + '</div></td></tr><tr><td>' + $(this).attr('home_team_name') + '</td><td><div align="center">' + $(this).attr('home_win') + '-' + $(this).attr('home_loss') + '</div></td></tr></tbody></table><br><br>');
					}
				});
				
				console.log('cur_batter: ' + cur_batter);
				console.log('cur_game: ' + cur_game);
				console.log('stay_on_game: ' + stay_on_game);
				console.log('finish_ab: ' + finish_ab);
				
				if(stay_on_game == 'Y'){
					game_vid = cur_game_vid;
				}else if(games.length > 0 && vid_launched == 'Y'){
					// Sort games by Leverage Index (descending)
					games.sort(function(a, b){
						return b.LI-a.LI
					})
					
					// Checks if any games match priority list
					if(pref_order && pref_order.length > 0){
						var game_found = 'N';
						for (p = 0; p < pref_order.length; p++){
							priority_number = p + 1;
							for (g = 0; g < games.length; g++){
								if(game_found == 'N' && pref_order[p].type == 'bat' && (pref_order[p].data == games[g].batter || (pref_order[p].data == games[g].ondeck && on_deck == 'Y' && games[g].outs < 2))){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
                  if(pref_order[p].data == games[g].batter){
                    cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].batter_name + ' at bat<br>Priority #' + priority_number;
                  }else{
                    cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].ondeck_name + ' on deck<br>Priority #' + priority_number;
                  }
								}else if(game_found == 'N' && pref_order[p].type == 'pit' && pref_order[p].data == games[g].pitcher){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].pitcher_name + ' pitching<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'run' && ((pref_order[p].data == games[g].run1 && games[g].run2 == '') || (pref_order[p].data == games[g].run2 && games[g].run3 == ''))){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									if(pref_order[p].data == games[g].run1 && games[g].run2 == ''){
										runner_name = run1_name;
									}else if(pref_order[p].data == games[g].run2 && games[g].run3 == ''){
										runner_name = run2_name;
									}
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + runner_name + ' on base<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'team' && (pref_order[p].data == games[g].away || pref_order[p].data == games[g].home)){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' playing<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'team_bat' && ((pref_order[p].data == games[g].away && games[g].half == 1) || (pref_order[p].data == games[g].home && games[g].half == 2))){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' batting<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'team_pit' && ((pref_order[p].data == games[g].away && games[g].half == 2) || (pref_order[p].data == games[g].home && games[g].half == 1))){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' pitching<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'LI' && pref_order[p].data <= games[g].LI){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: leverage index >= ' + pref_order[p].data + '<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'NoNo' && games[g].inning > pref_order[p].data && ((games[g].half == 1 && games[g].away_hits == 0) || (games[g].half == 2 && games[g].home_hits == 0))){
									game_vid = games[g].vid;
									game_found = 'Y';
									cur_batter = games[g].batter;
									cur_game = games[g].id;
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: No-Hitter through ' + pref_order[p].data + ' innings<br>Priority #' + priority_number;
								}else if(game_found == 'N' && pref_order[p].type == 'GameSit'){
									if(pref_order[p].data == 'through5_tie' && games[g].inning > 5 && games[g].away_runs == games[g].home_runs){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 5 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through6_tie' && games[g].inning > 6 && games[g].away_runs == games[g].home_runs){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 6 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through7_tie' && games[g].inning > 7 && games[g].away_runs == games[g].home_runs){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 7 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through8_tie' && games[g].inning > 8 && games[g].away_runs == games[g].home_runs){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 8 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through5_1run' && games[g].inning > 5 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 5 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through6_1run' && games[g].inning > 6 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 6 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through7_1run' && games[g].inning > 7 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 7 innings<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'through8_1run' && games[g].inning > 8 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 8 innings<br>Priority #' + priority_number;
									}
								}else if(game_found == 'N' && pref_order[p].type == 'Misc'){
									if(pref_order[p].data == 'PosP_pit' && posPlayers[games[g].pitcher] !== undefined && posPlayers[games[g].pitcher] == 'PosP'){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Position player pitching (' + games[g].pitcher_name + ')<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'extra' && games[g].inning > 9){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Extra-inning game<br>Priority #' + priority_number;
									}else if(pref_order[p].data == 'replay' && (games[g].ind == 'MC' || games[g].ind == 'MA' || games[g].ind == 'MI')){
										game_vid = games[g].vid;
										game_found = 'Y';
										cur_batter = games[g].batter;
										cur_game = games[g].id;
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Replay challenge<br>Priority #' + priority_number;
									}
								}
							}
						}
					}
					
					console.log('cur_game_high_LI_flag: ' + cur_game_high_LI_flag);
					console.log('cur_game_high_LI: ' + cur_game_high_LI);
					console.log('game_vid: ' + game_vid);
					console.log(pref_order);
					
					// If no preference items were met
					if(game_vid.length == 0){
						cur_game_high_LI++;
						if(cur_game_high_LI.length == 0){
							cur_game_high_LI = -3;
						}
						if((cur_game_high_LI_flag == 'Y' && games[0].LI > cur_game_high_LI) || cur_game_high_LI_flag == 'N'){// To replace another high leverage game, the new game must be at least 1.0 higher than the current one
							game_vid = games[0].vid;
							cur_batter = games[0].batter;
							cur_game = games[0].id;
							cur_game_text = games[0].away + ' & ' + games[0].home + '<br>Reason: Highest leverage game<br>No priority items were met';
						}else{
							game_vid = cur_game_vid;
							// cur_batter and cur_game were set earlier while looping through games
						}
						
						cur_game_high_LI_flag = 'Y'; // For next time through
						cur_game_high_LI = -3; // Sets to -3, just in case this game ends
						console.log('cur_game_high_LI_flag: ' + cur_game_high_LI_flag);
						console.log('cur_game_high_LI: ' + cur_game_high_LI);
					}else{
						cur_game_high_LI_flag = 'N';// Game was set by priority list
					}
					
					if(vid_player == 'old'){
						game_url = 'http://mlb.mlb.com/shared/flash/mediaplayer/v4.5/R8/MP4.jsp?calendar_event_id=' + game_vid + '&media_id=&view_key=&media_type=video&source=MLB&sponsor=MLB&clickOrigin=Media+Grid&affiliateId=Media+Grid&team=mlb';
					}else{
						game_url = 'http://m.mlb.com/tv/e' + game_vid + '/v601078983/?&media_type=video';
					}
					
					console.log('game_vid: ' + game_vid);
					console.log('cur_game_vid: ' + cur_game_vid);
					
					// Only update if this is a different game
					console.log('vid_counter: ' + vid_counter);
					if(game_vid != cur_game_vid && vid_launched == 'Y'){
						if(vid_counter > 0){
							delay = delay_setting;
						}
						cur_game_vid = game_vid;
						vid_counter++;
						updateGame(game_url, delay);
						updateGameText(cur_game_text, delay)
					}else if(game_vid == cur_game_vid && vid_launched == 'Y'){
						if(vid_counter > 0){
							delay = delay_setting;
						}
						cur_game_vid = game_vid;
						updateGameText(cur_game_text, delay)
					}
				}
		},
		error: function() {
			
		}
	}).complete(function(){
		setTimeout(function(){update();}, 6000);
	})
})();

// Sets priority to zero if duplicate
$(function() {
	$('select[name^="priority"]').change(function(){ // This binds listeners to the change event on all the select elements
		var sId = $(this).attr('id')
		var vId = $(this).val();
		$('select[name^="priority"]').each(function(){ // this loops across the same set of elements
			if($(this).attr('id') != sId && $(this).val() == vId) { // If it is not the triggering element and the value is the same, do something
				$(this).val(0);
			}
		});
	});
});

// Change all links on page to open in another window
$(function() {
	$('a[href]').attr('target', '_blank');
	$('a[href="#FAQ"]').attr('target', '_self');
});


</script>