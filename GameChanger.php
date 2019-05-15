<?php
include_once('includes/config.php');

include_once('Connections/fg.php');

include_once('includes/functions_headers.php');
include_once('includes/functions.php');
sec_session_start();
include_once('includes/metric_variables.php');
include_once('includes/query_variables.php');

include_once('background/LI_calc.php');
$LeverageIndex = $LI;
unset($LI);


// Sorts multidimensional array by key
function sortByOrder($a, $b) {
	return $a['priority'] - $b['priority'];
}


$max_priorities = 50;


$player = '';
$type = '';

$year = date("Y");

$query_plyrs = "
SELECT
	nameFirst,
	nameLast,
	mlbam.ID,
	IF(SUM(a.G_p) > SUM(a.G_all - a.G_p),'P','PosP') AS Pos,
	IFNULL(cur.teamID,'') AS teamID
FROM metrics
LEFT JOIN apps AS a USING (playerID, yearID, teamID)
INNER JOIN mlbam USING (playerID)
LEFT JOIN People USING (playerID)
LEFT JOIN (
  SELECT
		apps.playerID,
		apps.teamID
  FROM apps
  INNER JOIN (
    SELECT
			playerID,
			MAX(stint) AS stint
    FROM apps
    WHERE yearID = $year
    GROUP BY playerID
  ) AS c ON (apps.playerID = c.playerID AND apps.stint = c.stint)
  WHERE apps.yearID = $year
) AS cur USING (playerID)
WHERE metrics.yearID >= $year - 1
GROUP BY metrics.playerID
ORDER BY nameLast, nameFirst
";
$stmt_plyrs = $db_fg->prepare($query_plyrs);
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

	// New player
	// if($row_plyrs['ID'] == '627500'){// ID before player on alphabetical list
	// 	$players[] = array('display' => 'Tucker, Kyle (HOU)', 'id' => '663656');
	// 	$posPlayers['663656'] = 'PosP';
	// }
}

// Set Ohtani to pitcher (so he doesn't come up as position player pitching)
$posPlayers[660271] = 'P';


$query_CLI = "
SELECT
	s.home AS home_teamID,
	((((a.pennant_win - a.pennant_lose) / 8 + (a.wildcard_win - a.wildcard_lose) / 16) / 0.0058688) + (((h.pennant_win - h.pennant_lose) / 8 + (h.wildcard_win - h.wildcard_lose) / 16) / 0.0058688)) / 2 AS aCLI
FROM current_season_sched AS s
INNER JOIN CLI_today AS a ON (s.GAME_DT = a.GAME_DT AND s.away = a.teamID)
INNER JOIN CLI_today AS h ON (s.GAME_DT = h.GAME_DT AND s.home = h.teamID)
";
$stmt_CLI = $db_fg->prepare($query_CLI);
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
	array('display' => '>= 1.0', 'id' => 1.0),
	array('display' => '>= 0.5', 'id' => 0.5),
	array('display' => '>= 0.0', 'id' => 0)
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
<!DOCTYPE html>
<html>
  <head>
    <title>
      MLB.tv Game Changer - The Baseball Gauge</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
  </head>

  <body>
    <?php include('header.php');?>

		<br>
		<div>
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- Manager -->
			<ins class="adsbygoogle"
				style="display:block"
				data-ad-client="ca-pub-5313776129180174"
				data-ad-slot="2587310839"
				data-ad-format="auto">
			</ins>
			<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>

    <main>
      <h2 class="page__title">MLB.tv Game Changer</h2>
      <br>
      <div class="GC-save-settings">
        <button id="launch">Launch Video</button>
      </div>
      <br>
      <div class="GC-notes">
        <a href="#FAQ">Requirements / Instructions</a>
      </div>
      <br>
      <div id="current_game"></div>

      <div class="GC-settings">
        <h3 class="GC-settings-title">Games</h3>
        <div class="GC-games" id="active"></div>
      </div>
      <br>
      <form id="GC-form" method="post" name="priority">

      <div class="GC-container">
        <div class="GC-column">
          <div class="GC-settings">
            <h3 class="GC-settings-title">If Batter is On Deck with < 2 Outs</h3>
						<div class="team-ignore">
							<input type="radio" class="form__radio" id="on_deck_Y" name="on_deck" value="Y" />
	            <label for="on_deck_Y"><span></span> Switch to game immediately</label>
						</div>
						<div class="team-ignore">
	            <input type="radio" class="form__radio" id="on_deck_N" name="on_deck" value="N" />
	            <label for="on_deck_N"><span></span> Wait until player is at bat</label>
						</div>
          </div>

          <div class="GC-settings">
            <h3 class="GC-settings-title">Teams to Ignore</h3>
            <div class="league-ignore-container">
              <div class="league-ignore">
                <div class="team-ignore">
									<input class="form__checkbox" name="xARI" type="checkbox" value="ARI" id="xARI" />
	                <label for="xARI"><span></span> ARI</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xATL" type="checkbox" value="ATL" id="xATL" />
	                <label for="xATL"><span></span> ATL</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xCHC" type="checkbox" value="CHC" id="xCHC" />
	                <label for="xCHC"><span></span> CHC</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xCIN" type="checkbox" value="CIN" id="xCIN" />
									<label for="xCIN"><span></span> CIN</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xCOL" type="checkbox" value="COL" id="xCOL" />
									<label for="xCOL"><span></span> COL</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xLAD" type="checkbox" value="LAD" id="xLAD" />
									<label for="xLAD"><span></span> LAD</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xMIA" type="checkbox" value="MIA" id="xMIA" />
									<label for="xMIA"><span></span> MIA</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xMIL" type="checkbox" value="MIL" id="xMIL" />
									<label for="xMIL"><span></span> MIL</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xNYM" type="checkbox" value="NYM" id="xNYM" />
									<label for="xNYM"><span></span> NYM</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xPHI" type="checkbox" value="PHI" id="xPHI" />
									<label for="xPHI"><span></span> PHI</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xPIT" type="checkbox" value="PIT" id="xPIT" />
									<label for="xPIT"><span></span> PIT</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xSD" type="checkbox" value="SD" id="xSD" />
									<label for="xSD"><span></span> SD</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xSF" type="checkbox" value="SF" id="xSF" />
									<label for="xSF"><span></span> SF</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xSTL" type="checkbox" value="STL" id="xSTL" />
	                <label for="xSTL"><span></span> STL</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xWSH" type="checkbox" value="WSH" id="xWSH" />
									<label for="xWSH"><span></span> WSH</label>
								</div>
              </div>

              <div class="league-ignore">
								<div class="team-ignore">
									<input class="form__checkbox" name="xBAL" type="checkbox" value="BAL" id="xBAL" />
	                <label for="xBAL"><span></span> BAL</label>
								</div>
								<div class="team-ignore">
									<input class="form__checkbox" name="xBOS" type="checkbox" value="BOS" id="xBOS" />
	                <label for="xBOS"><span></span> BOS</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xCWS" type="checkbox" value="CWS" id="xCWS" />
									<label for="xCWS"><span></span> CWS</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xCLE" type="checkbox" value="CLE" id="xCLE" />
	                <label for="xCLE"><span></span> CLE</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xDET" type="checkbox" value="DET" id="xDET" />
									<label for="xDET"><span></span> DET</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xHOU" type="checkbox" value="HOU" id="xHOU" />
									<label for="xHOU"><span></span> HOU</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xKC" type="checkbox" value="KC" id="xKC" />
									<label for="xKC"><span></span> KC</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xLAA" type="checkbox" value="LAA" id="xLAA" />
									<label for="xLAA"><span></span> LAA</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xMIN" type="checkbox" value="MIN" id="xMIN" />
									<label for="xMIN"><span></span> MIN</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xNYY" type="checkbox" value="NYY" id="xNYY" />
									<label for="xNYY"><span></span> NYY</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xOAK" type="checkbox" value="OAK" id="xOAK" />
									<label for="xOAK"><span></span> OAK</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xSEA" type="checkbox" value="SEA" id="xSEA" />
									<label for="xSEA"><span></span> SEA</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xTB" type="checkbox" value="TB" id="xTB" />
									<label for="xTB"><span></span> TB</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xTEX" type="checkbox" value="TEX" id="xTEX" />
									<label for="xTEX"><span></span> TEX</label>
								</div>
                <div class="team-ignore">
									<input class="form__checkbox" name="xTOR" type="checkbox" value="TOR" id="xTOR" />
									<label for="xTOR"><span></span> TOR</label>
								</div>
              </div>
            </div>
          </div>

          <div class="GC-settings">
            <h3 class="GC-settings-title">Championship Leverage Index</h3>
						<div class="team-ignore">
							<input type="radio" class="form__radio" id="include_CLI_Y" name="include_CLI" value="Y" />
	            <label for="include_CLI_Y"><span></span> Include in Leverage Index</label>
						</div>
						<div class="team-ignore">
	            <input type="radio" class="form__radio" id="include_CLI_N" name="include_CLI" value="N" />
	            <label for="include_CLI_N"><span></span> Do not include in Leverage Index</label>
						</div>
          </div>

          <div class="GC-settings">
            <h3 class="GC-settings-title">Delay</h3>
            <select class="form__dropdown-select" id="delay" name="delay">
            <?php for($i = 0; $i <= 75; $i++){?>
              <option value="<?php echo $i;?>"><?php echo $i;?> seconds</option>
            <?php }?>
            </select>
            <br>* Time between MLB's gameday feed<br> and MLB.tv broadcast
          </div>

          <div class="GC-settings">
            <h3 class="GC-settings-title">Video Player</h3>
						<div class="team-ignore">
	            <input type="radio" class="form__radio" id="vid_player_reg" name="vid_player" value="reg" />
	            <label for="vid_player_reg"><span></span> Regular MLB.tv video player</label>
						</div>
						<div class="team-ignore">
	            <input type="radio" class="form__radio" id="vid_player_old" name="vid_player" value="old" />
	            <label for="vid_player_old"><span></span> Old MLB.tv video player</label>
						</div>
					</div>
        </div>
        <div class="GC-column">
          <div class="GC-settings">
            <h3 class="GC-settings-title">Priority List</h3>
						<div class="GC-title-container">
							<span class="GC-priorityNumber">#</span>
							<div class="GC-title">
								<div class="GC-drag__element-title">
									<div class="GC-priority__type-container"><h4>Type</h4></div>
									<div class="GC-priority__data-container"><h4>Player/Team/Data</h4></div>
									<div class="GC-priority__immediate-container"><h4>Switch<br>Immediately?</h4></div>
								</div>
							</div>
						</div>
						<?php
            for($i = 1; $i <= $max_priorities; $i++){
              $type_name = 'type_' . $i;
              $data_name = 'data_' . $i;
              $immediate_name = 'immediate_' . $i;
              ?>
							<div class="drag__drop" id="drag__drop-<?=$i;?>" data-priority="<?=$i;?>">
								<div class="drag__drop-line"></div>
							</div>
							<div class="GC-priorityContainer">
					      <span class="GC-priorityNumber"><?=$i;?></span>
								<div class="drag__container" id="drag__container-<?=$i;?>" data-priority="<?=$i;?>">
					        <div draggable="true" class="drag__element" id="drag__element-<?=$i;?>" data-priority="<?=$i;?>">
										<div class="GC-priority__type-container">
											<select class="form__dropdown-select" id="<?php echo $type_name;?>" name="<?php echo $type_name;?>" data-linked="data_<?=$i;?>">
	                      <option value=""></option>
	                      <option value="bat">Batter</option>
	                      <option value="pit">Pitcher</option>
	                      <option value="run">Runner</option>
	                      <option value="LI">Leverage Index</option>
	                      <option value="team">Team</option>
	                      <option value="NoNo">No-Hitter</option>
	                      <option value="GameSit">Game Situation</option>
	                      <option value="team_bat">Team Batting</option>
	                      <option value="team_pit">Team Pitching</option>
	                      <option value="Misc">Miscellaneous</option>
	                    </select>
										</div>
										<div class="GC-priority__data-container">
											<select class="form__dropdown-select" id="<?php echo $data_name;?>" name="<?php echo $data_name;?>">
											</select>
										</div>

										<div class="GC-priority__immediate-container">
					            <input class="form__checkbox" type="checkbox" name="<?=$immediate_name;?>" id="<?=$immediate_name;?>" value="Y">
											<label for="<?=$immediate_name;?>" id="immediate_label_<?=$i;?>"><span></span></label>
					          </div>
										<svg class="drag__delete">
					            <use xlink:href="images/sprite.svg#icon-bin"></use>
					          </svg>
										<svg class="drag__handle">
					            <use xlink:href="images/sprite.svg#icon-menu2"></use>
					          </svg>
					        </div>
								</div>
					    </div>
						<?php }?>
					</div>
        </div>
      </div>
      </form>
      <br>

      <div class="GC-settings">
        <h3 class="GC-settings-title">Import/Export Settings</h3>
        <br>
        <button id="create_export" class="form__submit">Create file to export settings:</button><br>
        <a download="GameChanger.txt" id="downloadlink" style="display: none" >Download Settings</a>
        <br>
        Choose file to import:<br>
				*Settings will automatically import upon file selection<br>
        <input type="file" name="file" id="import" accept=".txt, text/plain" />
				<label for="import">Import Settings</label>
      </div>

      <br><br>
			<div class="GC-FAQ-container">
	      <div class="GC-requirements">
					<h3 id="FAQ" class="GC-settings-title">Requirements</h3>
					&#149; Video can take up to 5 seconds to load game after the tab is launched.
	        <br><br>
					&#149; Unfortunately, Full-Screen mode in MLB.tv cannot be maintained after a new game is loaded.
	        <br><br>
	        &#149; Must have a subscription to <a href="http://mlb.mlb.com/mlb/subscriptions/index.jsp?c_id=mlb&affiliateId=mlbMENU" target="_blank">MLB.tv</a>. This will probably work with a Radio-only subscription, however, you may need to adjust the delay setting to sync your audio feed.
	        <br><br>
	        &#149; Settings are automatically saved, using your browser's local storage. If you'd like to transfer settings to a friend, another browser or device, you can generate a .txt file, which will contain your settings for import.
	        <br><br>
	        &#149; Pop-ups must be enabled on this page. The Game Changer opens games in a second browser tab/window. This will not work if pop-ups are blocked.
	        <br><br>
	        &#149; This will only work using a web browser, and not on a Roku/Amazon Fire TV/Chromecast type device.
	        <br><br>
	        &#149; This window needs to remain open in your browser for this to work. If you close this window, the game currently showing will no longer change based on your priority list.
	      </div>

				<div class="GC-FAQ">
					<h3 id="FAQ" class="GC-settings-title">Instructions</h3>
	      	&#149; <b>If Batter is On Deck with < 2 Outs: </b>This setting is for batters in your priority list. If that batter is on deck with fewer than 2 outs, the application can switch to that game to help ensure that you don't miss that at bat. If the option "Wait until player is at bat" is selected, there is a chance that this batter's at bat ends before the application can switch to their game.
	        <br><br>
	        &#149; <b>Teams to ignore: </b>Any teams checked will be ignored. This is if you are blacked out from viewing certain teams, or if you have a certain game on a different device, or if you just can't stand watching a certain team. For example, I usually keep KC and TB checked since I am blacked out from Royals games and I usually have Rays games on a tablet, since I want to avoid having the same game on two screens.
	        <br><br>
	        &#149; <b>Delay: </b>There is a delay in MLB.tv feeds, from when the mlb.com's gameday data updates to when MLB.tv shows the game. I have made it adjustable for the user just in case there is a variation in the delay for certain people or certain times. If games are switching before at bats finish, you can increase the delay time.
	        <br><br>
					&#149; <b>Switch Immediately?: </b>If this option is checked, the application will immedately switch to the game matching this preference. Otherwise, it will wait until the current at bat being shown is finished before switching. This option is left unchecked by default to prevent excessive switching of games in the middle of at bats.
	        <br><br>
	        &#149; <b>Priority List: </b>This application constantly checks to see if any of the current games match the user's priority criteria. If there are any matches, it will switch to the game with the highest priority. If there are no games that match any of the criteria on the priority list, the application will choose the game with the highest current leverage index (LI) and will switch between games with the highest LI until there are any matches on the priority list.<br><br>
	        If you want to see a player like Shohei Ohtani pitch AND hit, you need to include them on the priority list in both "Batter" and "Pitcher" categories.<br><br>
	        The Runner category is for when that runner is on 1st or 2nd base with the next base open. This is for stolen base threats like Billy Hamilton.
	        <br><br>
	        When an inning ends, the application will switch games. However, there is currently no way to detect a pitching change. So if you have Bryce Harper as your highest priority, and let's say when he comes to bat, the opposing team decides to bring in a LHP. Since MLB's gameday data shows him "at bat" during the entire pitching change, the game will stay on during the commercial break.
	        <br><br>
	        &#149; <b>Championship Leverage Index: </b>CLI is the measurement of a game's importance to a team's probability of winning the World Series, with 1 equaling the importance of a game on opening day. By choosing to include CLI, the in-game leverage index is multiplied by the championship leverage index.
	      </div>
			</div>
    </main>
    <footer><?php include('footer.php');?></footer>
		<script src="js/scripts.js?v=<?php echo time(); ?>"></script>
    <script src="js/nav.js?v=<?php echo time(); ?>"></script>
    <script src="js/tooltip.js?v=<?php echo time(); ?>"></script>
    <script src="js/autosuggest.js?v=<?php echo time(); ?>"></script>
  </body>
</html>
<script>

var max_priorities = <?=json_encode($max_priorities);?>;

function moveDragElement(o, n){
	document.getElementById('drag__element-' + o).id = 'drag__element-' + n;
	var dragElement = document.getElementById('drag__element-' + n);
	dragElement.setAttribute('data-priority', n);
	dragElement.querySelector('[id^="type_"]').id = 'type_' + n;
	dragElement.querySelector('[id^="type_"]').setAttribute('name', 'type_' + n);
	dragElement.querySelector('[id^="type_"]').setAttribute('data-linked', 'data_' + n);
	dragElement.querySelector('[id^="data_"]').id = 'data_' + n;
	dragElement.querySelector('[id^="data_"]').setAttribute('name', 'data_' + n);
	dragElement.querySelector('[id^="immediate_"]').id = 'immediate_' + n;
	dragElement.querySelector('[id^="immediate_"]').setAttribute('name', 'immediate_' + n);
	dragElement.querySelector('[id^="immediate_label_"]').id = 'immediate_label_' + n;
	dragElement.querySelector('[id^="immediate_label_"]').setAttribute('for', 'immediate_' + n);
	// Move element down to next priority
	if(n !== 'temp'){
		document.getElementById('drag__container-' + n).appendChild(dragElement);
	}
}

var dragDrops = document.getElementsByClassName('drag__drop');
for(var i = 0; i < dragDrops.length; i++){
	dragDrops[i].addEventListener('dragover', function(e){
		e.preventDefault();
		this.setAttribute("style", "padding: 1.2rem 0;");
		this.querySelector('.drag__drop-line').setAttribute("style", "background-color: #ccc;");
	});
	dragDrops[i].addEventListener('dragleave', function(e){
		e.preventDefault();
		this.setAttribute("style", "padding: .5rem 0;");
		this.querySelector('.drag__drop-line').setAttribute("style", "background-color: transparent;");
	})
	dragDrops[i].addEventListener('drop', function(e){
		e.preventDefault();
		this.setAttribute("style", "padding: .5rem 0;");
		this.querySelector('.drag__drop-line').setAttribute("style", "background-color: transparent;");
		// Old
		var dragDivID = e.dataTransfer.getData("from_dragDivID");
		var old_priority = Number(e.dataTransfer.getData("old_priority"));
		// New
		var new_priority = Number(this.getAttribute('data-priority'));
		if(old_priority > new_priority){
			moveDragElement(old_priority, 'temp');
			for(j = old_priority - 1; j >= new_priority; j--){
				var k = j + 1;
				moveDragElement(j, k);
			}
			moveDragElement('temp', new_priority);
		}else if(old_priority < new_priority){
			moveDragElement(old_priority, 'temp');
			for(j = old_priority + 1; j < new_priority; j++){
				var k = j - 1;
				moveDragElement(j, k);
			}
			moveDragElement('temp', new_priority - 1);
		}
		updatePriorities();
	});
}

var dragDiv = document.getElementsByClassName('drag__element');
for (var i = 0; i < dragDiv.length; i++){
	dragDiv[i].addEventListener('dragstart', function(e){
		e.dataTransfer.setData("dragDivID", e.target.id);
		e.dataTransfer.setData("old_priority", e.target.getAttribute('data-priority'));
	});
}

var dragDeletes = document.getElementsByClassName('drag__delete');
for(var i = 0; i < dragDeletes.length; i++){
	dragDeletes[i].addEventListener('click', function(){
		var confirm = window.confirm("Delete Priority?");
		if(confirm){
			var dragDivID = this.closest('.drag__element').id;
			var old_priority = Number(document.getElementById(dragDivID).getAttribute('data-priority'));
			var new_priority = max_priorities;

			document.getElementById(dragDivID).querySelector('[id^="type_"]').value = '';
			document.getElementById(dragDivID).querySelector('[id^="data_"]').options.length = 0;
			document.getElementById(dragDivID).querySelector('[id^="immediate_"]').checked = false;
			moveDragElement(old_priority, 'temp');
			for(j = old_priority + 1; j <= new_priority; j++){
				var k = j - 1;
				moveDragElement(j, k);
			}
			moveDragElement('temp', new_priority);
		}
		updatePriorities();
	});
}

var dropDownType = document.querySelectorAll('[id^="type_"]');
for(var i = 0; i < dropDownType.length; i++){
	dropDownType[i].addEventListener('change', function(){
		var priorityNum = this.getAttribute('data-linked');
		configureDropDownLists(this,document.getElementById(priorityNum));
	});
}

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
	var LI = <?php echo json_encode($LeverageIndex); ?>;
	return LI[InnBaseOut][RunDiff];
}

function getBaseSit(runner_on_base_status){
	if(runner_on_base_status === "0"){
		var BaseSit = 1;
	}else if(runner_on_base_status === "1"){
		var BaseSit = 2;
	}else if(runner_on_base_status === "2"){
		var BaseSit = 3;
	}else if(runner_on_base_status === "4"){
		var BaseSit = 4;
	}else if(runner_on_base_status === "3"){
		var BaseSit = 5;
	}else if(runner_on_base_status === "5"){
		var BaseSit = 6;
	}else if(runner_on_base_status === "6"){
		var BaseSit = 7;
	}else{
		var BaseSit = 8;
	}
	return BaseSit;
}

function getRunDiff(away_team_runs, home_team_runs){
	var diff = home_team_runs - away_team_runs;
	var RunDiff = Math.min(Math.max(diff,-10),10);
	return RunDiff;
}

// Sets Radios
function setRadioByValue(array, value){
	for(var i = 0; i < array.length; i++){
		if(array[i].value == value){
			array[i].checked = true;
			return;
		}
	}
	return;
}

// Sets options on load
var onDeckOptions = document.querySelectorAll('[id^="on_deck_"]');
if(localStorage.getItem('GC-on_deck') !== null){
	var on_deck = localStorage.getItem('GC-on_deck');
}else{
	var on_deck = 'N';
}
setRadioByValue(onDeckOptions, on_deck);

var CLI_Options = document.querySelectorAll('[id^="include_CLI_"]');
if(localStorage.getItem('GC-CLI') !== null){
	var include_CLI = localStorage.getItem('GC-CLI');
}else{
	var include_CLI = 'N';
}
setRadioByValue(CLI_Options, include_CLI);

var vidOptions = document.querySelectorAll('[id^="vid_player_"]');
if(localStorage.getItem('GC-vid') !== null){
	var vid_player = localStorage.getItem('GC-vid');
}else{
	var vid_player = 'reg';
}
setRadioByValue(vidOptions, vid_player);

var delayOption = document.getElementById('delay');
if(localStorage.getItem('GC-delay') !== null){
	var delay_setting = localStorage.getItem('GC-delay');
}else{
	var delay_setting = 5000;
}
delayOption.value = delay_setting / 1000;

if(localStorage.getItem('GC-priority') !== null){
	var pref_order = JSON.parse(localStorage.getItem('GC-priority'));
	for(var j = 1; j <= pref_order.length; j++){
		var k = j - 1;
		var typeDropDown = document.getElementById('type_' + j);
		var dataDropDown = document.getElementById('data_' + j);
		var immediateCheckbox = document.getElementById('immediate_' + j);
		typeDropDown.value = pref_order[k].type;
		configureDropDownLists(typeDropDown,dataDropDown);
		dataDropDown.value = pref_order[k].data;
		if(pref_order[k].immediate === 'Y'){
			immediateCheckbox.checked = true;
		}
	}
}else{
	var pref_order = [];
}

if(localStorage.getItem('GC-ignore') !== null){
	var ignore = JSON.parse(localStorage.getItem('GC-ignore'));
	if(ignore.length > 0){
		for(var i = 0; i < ignore.length; i++){
			document.getElementById('x' + ignore[i]).checked = true;
		}
	}
}else{
	var ignore = [];
}


var posPlayers = <?php echo json_encode($posPlayers);?>;
var games_CLI = <?php echo json_encode($games_CLI); ?>;







// Sets variables
var cur_game = "";
var cur_game_vid = "";
var cur_batter = "";
var cur_game_high_LI_flag = "N";
var cur_game_high_LI = -3;
var vid_counter = 0;
var vid_launched = 'N';
var delay = 100;

// delay
document.getElementById('delay').addEventListener('change', function(){
	delay_setting = this.value * 1000;
	localStorage.setItem('GC-delay', delay_setting);
});

// Ignore
var ignoreCheckboxes = document.querySelectorAll('[id^="x"]');
for(var i = 0; i < ignoreCheckboxes.length; i++){
	ignoreCheckboxes[i].addEventListener('change', function(){
		if(this.checked){
			ignore.push(this.value);
		}else{
			var i_index = ignore.indexOf(this.value);
			if(i_index !== -1) {
				ignore.splice(i_index, 1);
			}
		}
		localStorage.setItem('GC-ignore', JSON.stringify(ignore));
	});
}

// Priority List
function updatePriorities(){
	var temp_priority = [];

	for(var k = 1; k <= max_priorities; k++){
		if(document.getElementById('type_' + k).value !== ''){
			type = document.getElementById('type_' + k).value;
			data = document.getElementById('data_' + k).value;
			immediate = (document.getElementById('immediate_' + k).checked) ? 'Y' : '';
			temp_priority.push({'type': type, 'data': data, 'immediate': immediate, 'priority': k});
		}
	}
	temp_priority.sort(function(a, b){
		return a.priority - b.priority;
	});
	pref_order = temp_priority;
	localStorage.setItem('GC-priority', JSON.stringify(pref_order));
}

var priorityItems = document.querySelectorAll('[id^="data_"], [id^="type_"], [id^="immediate_"]');
for(var i = 0; i < priorityItems.length; i++){
	priorityItems[i].addEventListener('change', updatePriorities);
}

// On Deck
var onDeckOptions = document.querySelectorAll('[id^="on_deck_"]');
for(var i = 0; i < onDeckOptions.length; i++){
	onDeckOptions[i].addEventListener('click', function(){
		on_deck = this.value;
		localStorage.setItem('GC-on_deck', on_deck);
	});
}

// Include CLI
var CLI_Options = document.querySelectorAll('[id^="include_CLI_"]');
for(var i = 0; i < CLI_Options.length; i++){
	CLI_Options[i].addEventListener('click', function(){
		include_CLI = this.value;
		localStorage.setItem('GC-CLI', include_CLI);
	});
}

// Video Player
var vidOptions = document.querySelectorAll('[id^="vid_player_"]');
for(var i = 0; i < vidOptions.length; i++){
	vidOptions[i].addEventListener('click', function(){
		vid_player = this.value;
		localStorage.setItem('GC-vid', vid_player);
	});
}


// Video launcher
document.getElementById('launch').addEventListener('click', function(){
	this.style.display = 'none';
	cur_game = "";
	cur_game_vid = "";
	cur_game_text = "";
	cur_batter = "";
	cur_game_high_LI_flag = "N";
	cur_game_high_LI = -3;
	vid_counter = 0;
	delay = 100;
	same_batter = 'N';
	gameWindow = window.open('', 'mlb.tv');
	var timer = setInterval(checkChild, 500);
	vid_launched = 'Y';
})

// Video close
function checkChild(){
	if(gameWindow.closed){
		document.getElementById('launch').style.display = 'inline-block';
		cur_game = "";
		cur_game_vid = "";
		cur_batter = "";
		cur_game_high_LI_flag = "N";
		cur_game_high_LI = -3;
		vid_counter = 0;
		vid_launched = 'N';
		delay = 100;
		document.getElementById('current_game').innerHTML = '';
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
		var currentGame = document.getElementById('current_game');
		currentGame.innerHTML = '<div class="GC-current-game"><h3 class="GC-settings-title">Currently Showing</h3>' + cur_game_text + '</div>';
	}, delay);
}

function updateGameHighlight(old_game, new_game, delay) {
  setTimeout(function() {
		if(old_game !== '' && old_game !== new_game){
			document.getElementById(old_game).classList.remove('GC-game-highlight');
    }
    console.log('new_game: ' + new_game);
		document.getElementById(new_game).classList.add('GC-game-highlight');
	}, delay);
}

(function (){
  var textFile = null,
  makeTextFile = function (text){
    var data = new Blob([text], {
      type: 'text/plain'
    });

    // If we are replacing a previously generated file we need to
    // manually revoke the object URL to avoid memory leaks.
    if(textFile !== null) {
      window.URL.revokeObjectURL(textFile);
    }
    textFile = window.URL.createObjectURL(data);
    return textFile;
  };

  var create_export = document.getElementById('create_export');

  create_export.addEventListener('click', function (){
    var link = document.getElementById('downloadlink');
		var settings = {
			'on_deck' : on_deck,
			'include_CLI' : include_CLI,
			'vid_player' : vid_player,
			'delay' : delay,
			'ignore' : ignore,
			'priority' : pref_order
		};
    link.href = makeTextFile(JSON.stringify(settings));
    link.style.display = 'block';
	});
})();


function importSettings(text){
	var settings = JSON.parse(text);

	if(settings.on_deck && (['Y', 'N'].indexOf(settings.on_deck) >= 0)){
		on_deck = settings.on_deck;
		localStorage.setItem('GC-on_deck', on_deck);
		setRadioByValue(onDeckOptions, on_deck);
	}
	if(settings.include_CLI && (['Y', 'N'].indexOf(settings.include_CLI) >= 0)){
		include_CLI = settings.include_CLI;
		localStorage.setItem('GC-CLI', include_CLI);
		setRadioByValue(CLI_Options, include_CLI);
	}
	if(settings.vid_player && (['reg', 'old'].indexOf(settings.vid_player) >= 0)){
		vid_player = settings.vid_player;
		localStorage.setItem('GC-vid', vid_player);
		setRadioByValue(vidOptions, vid_player);
	}
	if(settings.delay && settings.delay >= 0 && settings.delay <= 100){
		delay_setting = settings.delay * 1000;
		localStorage.setItem('GC-delay', delay_setting);
		delayOption.value = delay_setting / 1000; // element set earlier
	}
	if(settings.ignore){
		var ignoreCheckboxes = document.querySelectorAll('[id^="x"]');
		for(var i = 0; i < ignoreCheckboxes; i++){
			ignoreCheckboxes[i].checked = false;
		}
		var ignore = settings.ignore;
		if(ignore.length > 0){
			for(var i = 0; i < ignore.length; i++){
				document.getElementById('x' + ignore[i]).checked = true;
			}
		}
		localStorage.setItem('GC-ignore', JSON.stringify(ignore));
	}
	if(settings.priority){
		var pref_order = settings.priority;
		for(var j = 1; j <= pref_order.length; j++){
			var k = j - 1;
			var typeDropDown = document.getElementById('type_' + j);
			var dataDropDown = document.getElementById('data_' + j);
			var immediateCheckbox = document.getElementById('immediate_' + j);
			typeDropDown.value = pref_order[k].type;
			configureDropDownLists(typeDropDown,dataDropDown);
			dataDropDown.value = pref_order[k].data;
			if(pref_order[k].immediate === 'Y'){
				immediateCheckbox.checked = true;
			}
		}
		localStorage.setItem('GC-priority', JSON.stringify(pref_order));
	}
	document.getElementById('import').value = '';
	alert('Settings Imported!');
}

document.getElementById('import').addEventListener('change', function(e){
	var file = e.target.files;
	var reader = new FileReader();
	reader.onload = function(e) {
	  importSettings(reader.result);
	}
	reader.readAsText(file[0]);
});

// Change all links on page to open in another window
var pageLinks = document.querySelectorAll('a[href]');
for(var i = 0; i < pageLinks.length; i++){
	pageLinks[i].setAttribute('target', '_blank');
}
document.querySelector('a[href="#FAQ"]').setAttribute('target', '_self');

var game_vid,
		game_pk,
		game_found;

function setCurrentGame(vid, pk, batter, id){
	game_vid = vid;
	game_pk = pk;
	game_found = 'Y';
	cur_batter = batter;
	cur_game = id;
}

function get_calendar_event_id(game){

	var media = game.game_media.media;

	var calendar_event_id = '';
	if(Array.isArray(media)){
		for(var i = 0; i < media.length; i++){
			if(media[i].calendar_event_id){
				calendar_event_id = media[i].calendar_event_id;
				break;
			}
		}
	}else{
		calendar_event_id = media.calendar_event_id;
	}

	return calendar_event_id;
}

// Games
function update(){
	var games = [];
	game_vid = "";
	game_pk = "";
	var same_batter = "N";
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
		url: "//gd2.mlb.com/components/game/mlb/year_" + year + "/month_" + month + "/day_" + day + "/master_scoreboard.json?v=" + new Date().getTime(),
		dataType: "json",
		cache: false,
		success: function(response) {

			var data = response;
			var gamesList = data.data.games.game;

			var active = document.getElementById('active');
			active.innerHTML = '';
			// document.getElementById('commercial').innerHTML = '';
			// document.getElementById('post').innerHTML = '';
			// document.getElementById('pre').innerHTML = '';

			//Goes through each game
			for(var z = 0; z < gamesList.length; z++){
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
				var ind = gamesList[z].status.ind;
				var status = gamesList[z].status.status;
				var inning_state = gamesList[z].status.inning_state;
				var calendar_event_id = get_calendar_event_id(gamesList[z]);
				console.log('inning_state: ' + inning_state);
				console.log('vid_counter: ' + vid_counter);

				// Adds games that are in progress and not in middle of inning
				if((ind === 'I' || ind === 'MC' || ind === 'MA' || ind === 'MI') && (inning_state === 'Top' || inning_state === 'Bottom') && status !== 'Delayed'){
					BaseSit = getBaseSit(gamesList[z].runners_on_base.status);
					inning = Math.min(gamesList[z].status.inning,9);
					if(gamesList[z].status.top_inning === 'Y'){
						half = 1;
					}else{
						half = 2;
					}
					outs = gamesList[z].status.o;
					if(outs === '3'){
						if(gamesList[z].status.top_inning === 'Y'){
							half_name = 'Middle';
						}else{
							half_name = 'End';
						}
						var LI = '';
						var div_status = active; // Was #commercial
						var flex_order = 1;
					}else{
						if(gamesList[z].status.top_inning === 'Y'){
							half_name = 'Top';
						}else{
							half_name = 'Bottom';
						}
						var RunDiff = getRunDiff(gamesList[z].linescore.r.away, gamesList[z].linescore.r.home);
						var InnBaseOut = inning.toString() + half.toString() + BaseSit.toString() + outs.toString();
						var home_team = gamesList[z].home_name_abbrev;

						if(include_CLI === 'Y' && home_team in games_CLI && games_CLI[home_team] !== 'undefined' && games_CLI[home_team] !== ''){
							var CLI = games_CLI[home_team];
						}else{
							var CLI = 1;
						}

						console.log(gamesList[z].home_name_abbrev + ' CLI: ' + CLI);
						var LI = (getLI(InnBaseOut,RunDiff) * CLI).toFixed(2);
						var div_status = active;
						var flex_order = 0 - LI * 1000;
					}

					if(cur_game_vid === calendar_event_id){
						var class_highlight = "GC-game-highlight";
					}else{
						var class_highlight = "";
					}

					// Adds game to html
					div_status.innerHTML += '<div class="GC-linescore ' + class_highlight + '" id="' + calendar_event_id + '" style="order: ' + flex_order + '"><table class="table"><thead><tr><th width="100">' + half_name + ' ' + gamesList[z].status.inning + '</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].linescore.r.away + '</td><td class="center">' + gamesList[z].linescore.h.away + '</td><td class="center">' + gamesList[z].linescore.e.away + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].linescore.r.home + '</td><td class="center">' + gamesList[z].linescore.h.home + '</td><td class="center">' + gamesList[z].linescore.e.home + '</td></tr><tr><td>Outs: ' + outs + '</td><td colspan="3" class="center"><img src="images/Bases_' + BaseSit + '.png" width="34" height="11" align="absmiddle" /></td></tr></tbody></table><div class="GC-game-info">Leverage Index: ' + LI + '<br>Pitching: '  + gamesList[z].pitcher.first + ' ' + gamesList[z].pitcher.last + '<br>At Bat: '  + gamesList[z].batter.first + ' ' + gamesList[z].batter.last + '<br>On Deck: '  + gamesList[z].ondeck.first + ' ' + gamesList[z].ondeck.last + '</div></div>';


					// Baserunners
					if(gamesList[z].runners_on_base.status > 0){
						if(gamesList[z].runners_on_base.runner_on_1b){
							run1 = gamesList[z].runners_on_base.runner_on_1b.id;
							run1_name = gamesList[z].runners_on_base.runner_on_1b.first + ' ' + gamesList[z].runners_on_base.runner_on_1b.last;
						}
						if(gamesList[z].runners_on_base.runner_on_2b){
							run2 = gamesList[z].runners_on_base.runner_on_2b.id;
							run2_name = gamesList[z].runners_on_base.runner_on_2b.first + ' ' + gamesList[z].runners_on_base.runner_on_2b.last;
						}
						if(gamesList[z].runners_on_base.runner_on_3b){
							run3 = gamesList[z].runners_on_base.runner_on_3b.id;
							run3_name = gamesList[z].runners_on_base.runner_on_3b.first + ' ' + gamesList[z].runners_on_base.runner_on_3b.last;
						}
					}

					// Flag to stay on current game if same batter is still at bat
					if(gamesList[z].gameday === cur_game && gamesList[z].batter.id === cur_batter && outs < 3){
						same_batter = 'Y';
					}

					// If the current game showing is the highest leverage game and this is that game
					if(cur_game_high_LI_flag === 'Y' && cur_game_vid === calendar_event_id){
						cur_game_high_LI = LI; // Grabs that games LI to compare to the new high lev game
						cur_batter = gamesList[z].batter.id;
					}

					// Goes through ignore list to see if game should be skipped
					if(typeof ignore !== "undefined" && ignore !== null && ignore.length > 0){
						for (i = 0; i < ignore.length; i++){
							if(ignore[i] === gamesList[z].away_name_abbrev || ignore[i] === gamesList[z].home_name_abbrev){
								skip_game = 'Y';
							}
						}
					}

					// Places data into array
					if(outs < 3 && skip_game === 'N'){
						games.push({
							'id': gamesList[z].gameday,
							'LI': LI,
							'outs': outs,
							'vid': calendar_event_id,
							'game_pk': gamesList[z].game_pk,
							'away':  gamesList[z].away_name_abbrev,
							'home':  gamesList[z].home_name_abbrev,
							'batter':  gamesList[z].batter.id,
							'batter_name': gamesList[z].batter.first + ' ' + gamesList[z].batter.last,
							'pitcher':  gamesList[z].pitcher.id,
							'pitcher_name': gamesList[z].pitcher.first + ' ' + gamesList[z].pitcher.last,
							'ondeck': gamesList[z].ondeck.id,
							'ondeck_name': gamesList[z].ondeck.first + ' ' + gamesList[z].ondeck.last,
							'run1':  run1,
							'run1_name': run1_name,
							'run2':  run2,
							'run2_name': run2_name,
							'run3':  run3,
							'run3_name': run3_name,
							'inning': gamesList[z].status.inning,
							'half': half,
							'away_hits': gamesList[z].linescore.h.away,
							'home_hits': gamesList[z].linescore.h.home,
							'away_runs': gamesList[z].linescore.r.away,
							'home_runs': gamesList[z].linescore.r.home,
							'ind': ind
						});
					}
				}else if(ind === 'F' || ind === 'O'){
					// Adds game to html
					active.innerHTML += '<div class="GC-linescore" id="' + calendar_event_id + '" style="order: 3"><table class="table"><thead><tr><th width="100">Final</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].linescore.r.away + '</td><td class="center">' + gamesList[z].linescore.h.away + '</td><td class="center">' + gamesList[z].linescore.e.away + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].linescore.r.home + '</td><td class="center">' + gamesList[z].linescore.h.home + '</td><td class="center">' + gamesList[z].linescore.e.home + '</td></tr></tbody></table><div class="GC-game-info">W: '  + gamesList[z].winning_pitcher.first + ' ' + gamesList[z].winning_pitcher.last + '<br>L: '  + gamesList[z].losing_pitcher.first + ' ' + gamesList[z].losing_pitcher.last + '</div></div>';// changed from #post to #active
				}else if(ind === 'P' || ind === 'PW' || ind === 'S'){
					// Adds game to html
					active.innerHTML += '<div class="GC-linescore" id="' + calendar_event_id + '" style="order: 4"><table class="table"><thead><tr><th width="100">' + gamesList[z].time + ' ' + gamesList[z].time_zone + '</th><th width="60">W - L</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].away_win + '-' + gamesList[z].away_loss + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].home_win + '-' + gamesList[z].home_loss + '</td></tr></tbody></table></div>';// changed from #pre to #active
				}else if(ind === 'DR'){
					// Adds game to html
					active.innerHTML += '<div class="GC-linescore" id="' + calendar_event_id + '" style="order: 5"><table class="table"><thead><tr><th width="100">' + gamesList[z].time + ' ' + gamesList[z].time_zone + '</th><th width="60">W - L</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].away_win + '-' + gamesList[z].away_loss + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].home_win + '-' + gamesList[z].home_loss + '</td></tr></tbody></table><div class="GC-game-info">Game Postponed</div></div>';// changed from #pre to #active
				}else if(status === 'Delayed'){
					// Adds game to html
					active.innerHTML += '<div class="GC-linescore ' + class_highlight + '" id="' + calendar_event_id + '" style="order: ' + flex_order + '"><table class="table"><thead><tr><th width="100">Delayed (' + gamesList[z].status.inning + ')</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].linescore.r.away + '</td><td class="center">' + gamesList[z].linescore.h.away + '</td><td class="center">' + gamesList[z].linescore.e.away + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].linescore.r.home + '</td><td class="center">' + gamesList[z].linescore.h.home + '</td><td class="center">' + gamesList[z].linescore.e.home + '</td></tr><tr><td>Outs: ' + outs + '</td><td colspan="3" class="center"><img src="images/Bases_' + BaseSit + '.png" width="34" height="11" align="absmiddle" /></td></tr></tbody></table><div class="GC-game-info">Leverage Index: ' + LI + '<br>Pitching: '  + gamesList[z].pitcher.first + ' ' + gamesList[z].pitcher.last + '<br>At Bat: '  + gamesList[z].batter.first + ' ' + gamesList[z].batter.last + '<br>On Deck: '  + gamesList[z].ondeck.first + ' ' + gamesList[z].ondeck.last + '</div></div>';
				}else if(ind === 'IR'){
					// Adds game to html
					if(gamesList[z].status.o === '3'){
						if(gamesList[z].status.top_inning === 'Y'){
							half_name = 'Middle';
						}else{
							half_name = 'End';
						}
					}else{
						if(gamesList[z].status.top_inning === 'Y'){
							half_name = 'Top';
						}else{
							half_name = 'Bottom';
						}
					}
					active.innerHTML += '<div class="GC-linescore" id="' + calendar_event_id + '" style="order: 2"><table class="table"><thead><tr><th width="100">' + half_name + ' ' + gamesList[z].status.inning + '</th><th width="20">R</th><th width="20">H</th><th width="20">E</th></tr></thead><tbody><tr><td>' + gamesList[z].away_team_name + '</td><td class="center">' + gamesList[z].linescore.r.away + '</td><td class="center">' + gamesList[z].linescore.h.away + '</td><td class="center">' + gamesList[z].linescore.e.away + '</td></tr><tr><td>' + gamesList[z].home_team_name + '</td><td class="center">' + gamesList[z].linescore.r.home + '</td><td class="center">' + gamesList[z].linescore.h.home + '</td><td class="center">' + gamesList[z].linescore.e.home + '</td></tr></tbody></table><div class="GC-game-info">Game Delayed</div></div>';
				}
			}

			console.log('cur_batter: ' + cur_batter);
			console.log('cur_game: ' + cur_game);
			console.log('same_batter: ' + same_batter);
			console.log('games length: ' + games.length);
			if(games.length > 0 && vid_launched === 'Y'){
				// Sort games by Leverage Index (descending)
				games.sort(function(a, b){
					return b.LI-a.LI;
				});
				// Checks if any games match priority list
				if(pref_order && pref_order.length > 0){
					game_found = 'N';// set to 'Y' in setCurrentGame function
					priority_loop:
					for (p = 0; p < pref_order.length; p++){
						priority_number = p + 1;
						if(same_batter === 'N' || pref_order[p].immediate === 'Y'){
							for (g = 0; g < games.length; g++){
								if(game_found === 'N' && pref_order[p].type === 'bat' && (pref_order[p].data === games[g].batter || (pref_order[p].data === games[g].ondeck && on_deck === 'Y' && games[g].outs < 2))){
									if(pref_order[p].data === games[g].batter){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].batter_name + ' at bat<br>Priority #' + priority_number;
									}else{
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].ondeck_name + ' on deck<br>Priority #' + priority_number;
									}
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'pit' && pref_order[p].data === games[g].pitcher){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + games[g].pitcher_name + ' pitching<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'run' && ((pref_order[p].data === games[g].run1 && games[g].run2 === '') || (pref_order[p].data === games[g].run2 && games[g].run3 === ''))){
									if(pref_order[p].data === games[g].run1 && games[g].run2 === ''){
										runner_name = games[g].run1_name;
									}else if(pref_order[p].data === games[g].run2 && games[g].run3 === ''){
										runner_name = games[g].run2_name;
									}
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + runner_name + ' on base<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'team' && (pref_order[p].data === games[g].away || pref_order[p].data === games[g].home)){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' playing<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'team_bat' && ((pref_order[p].data === games[g].away && games[g].half === 1) || (pref_order[p].data === games[g].home && games[g].half === 2))){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' batting<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'team_pit' && ((pref_order[p].data === games[g].away && games[g].half === 2) || (pref_order[p].data === games[g].home && games[g].half === 1))){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: ' + pref_order[p].data + ' pitching<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'LI' && pref_order[p].data <= games[g].LI){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: leverage index >= ' + pref_order[p].data + '<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'NoNo' && games[g].inning > pref_order[p].data && ((games[g].half == 1 && games[g].away_hits == 0) || (games[g].half == 2 && games[g].home_hits == 0))){
									cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: No-Hitter through ' + pref_order[p].data + ' innings<br>Priority #' + priority_number;
									setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
									break priority_loop;
								}else if(game_found === 'N' && pref_order[p].type === 'GameSit'){
									if(pref_order[p].data === 'through5_tie' && games[g].inning > 5 && games[g].away_runs === games[g].home_runs){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 5 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through6_tie' && games[g].inning > 6 && games[g].away_runs === games[g].home_runs){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 6 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through7_tie' && games[g].inning > 7 && games[g].away_runs === games[g].home_runs){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 7 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through8_tie' && games[g].inning > 8 && games[g].away_runs === games[g].home_runs){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Tie game through 8 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through5_1run' && games[g].inning > 5 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 5 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through6_1run' && games[g].inning > 6 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 6 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through7_1run' && games[g].inning > 7 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 7 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'through8_1run' && games[g].inning > 8 && games[g].away_runs - games[g].home_runs >= -1 && games[g].away_runs - games[g].home_runs <= 1){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: One-run game through 8 innings<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}
								}else if(game_found === 'N' && pref_order[p].type === 'Misc'){
									if(pref_order[p].data === 'PosP_pit' && posPlayers[games[g].pitcher] !== undefined && posPlayers[games[g].pitcher] === 'PosP'){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Position player pitching (' + games[g].pitcher_name + ')<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'extra' && games[g].inning > 9){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Extra-inning game<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}else if(pref_order[p].data === 'replay' && (games[g].ind === 'MC' || games[g].ind === 'MA' || games[g].ind === 'MI')){
										cur_game_text = games[g].away + ' & ' + games[g].home + '<br>Reason: Replay challenge<br>Priority #' + priority_number;
										setCurrentGame(games[g].vid, games[g].game_pk, games[g].batter, games[g].id);
										break priority_loop;
									}
								}
							}
						}
					}
				}

				console.log('cur_game_high_LI_flag: ' + cur_game_high_LI_flag);
				console.log('cur_game_high_LI: ' + cur_game_high_LI);
				console.log('game_vid: ' + game_vid);

				// If no preference items were met
				if(game_vid.length === 0){
					// if same batter is still at plate
					if(same_batter === 'Y'){
						game_vid = cur_game_vid;
						updateGameHighlight(cur_game_vid, game_vid, delay);
					}else{
						cur_game_high_LI = cur_game_high_LI + 0.5;// New game's LI must be 0.5 higher than current game to switch
						// Ignore if current game is not default high LI game
						if(cur_game_high_LI.length === 0){
							cur_game_high_LI = -3;
						}
						if((cur_game_high_LI_flag === 'Y' && games[0].LI > cur_game_high_LI) || cur_game_high_LI_flag === 'N'){// To replace another high leverage game, the new game must be at least 0.5 higher than the current one
							game_vid = games[0].vid;
							game_pk = games[0].game_pk;
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
					}
				}else{
					cur_game_high_LI_flag = 'N';// Game was set by priority list
				}

				if(vid_player === 'old'){
					game_url = 'http://mlb.mlb.com/shared/flash/mediaplayer/v4.5/R8/MP4.jsp?calendar_event_id=' + game_vid + '&media_id=&view_key=&media_type=video&source=MLB&sponsor=MLB&clickOrigin=Media+Grid&affiliateId=Media+Grid&team=mlb';
				}else{
					game_url = 'https://www.mlb.com/tv/g' + game_pk;
				}

				console.log('game_vid: ' + game_vid);
				console.log('cur_game_vid: ' + cur_game_vid);

				// Only update if this is a different game
				console.log('vid_counter: ' + vid_counter);
				if(game_vid !== cur_game_vid && vid_launched === 'Y'){
					if(vid_counter > 0){
						delay = delay_setting;
					}
					vid_counter++;
					updateGame(game_url, delay);
					updateGameText(cur_game_text, delay);
					updateGameHighlight(cur_game_vid, game_vid, delay);
					cur_game_vid = game_vid;
				}else if(game_vid === cur_game_vid && vid_launched === 'Y'){
					if(vid_counter > 0){
						delay = delay_setting;
					}
					cur_game_vid = game_vid;
					updateGameText(cur_game_text, delay);
				}
			}
		},
		error: function() {

		},
    complete: function(){
			// Re-check every 6 seconds
      setTimeout(function(){update();}, 6000);
    }
  });
}
update();
</script>
