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
			<div id="game-changer-vue-container"></div>
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

window.players = <?php echo json_encode($players); ?>;
window.posPlayers = <?php echo json_encode($posPlayers);?>;
window.games_CLI = <?php echo json_encode($games_CLI); ?>;
window.LI = <?php echo json_encode($LeverageIndex); ?>;
window.leverage_index_thresholds = [
	{ display: ">= 10.0", id: 10 },
	{ display: ">= 9.0", id: 9 },
	{ display: ">= 8.0", id: 8 },
	{ display: ">= 7.0", id: 7 },
	{ display: ">= 6.0", id: 6 },
	{ display: ">= 5.0", id: 5 },
	{ display: ">= 4.5", id: 4.5 },
	{ display: ">= 4.0", id: 4.0 },
	{ display: ">= 3.5", id: 3.5 },
	{ display: ">= 3.0", id: 3.0 },
	{ display: ">= 2.5", id: 2.5 },
	{ display: ">= 2.0", id: 2.0 },
	{ display: ">= 1.5", id: 1.5 },
	{ display: ">= 1.0", id: 1.0 },
	{ display: ">= 0.5", id: 0.5 },
	{ display: ">= 0.0", id: 0 }
];
window.GameSit = [
	{ display: "Tie game through 5 innings", id: "through5_tie" },
	{ display: "Tie game through 6 innings", id: "through6_tie" },
	{ display: "Tie game through 7 innings", id: "through7_tie" },
	{ display: "Tie game through 8 innings", id: "through8_tie" },
	{ display: "1-run game through 5 innings", id: "through5_1run" },
	{ display: "1-run game through 6 innings", id: "through6_1run" },
	{ display: "1-run game through 7 innings", id: "through7_1run" },
	{ display: "1-run game through 8 innings", id: "through8_1run" }
];
window.Misc = [
	{ display: "Position Player Pitching", id: "PosP_pit" },
	{ display: "Extra Innings", id: "extra" },
	{ display: "Replay Challenge/Review", id: "replay" }
];
window.NoNo = [
	{ display: "Through 8 innings", id: 8 },
	{ display: "Through 7 innings", id: 7 },
	{ display: "Through 6 innings", id: 6 },
	{ display: "Through 5 innings", id: 5 },
	{ display: "Through 4 innings", id: 4 },
	{ display: "Through 3 innings", id: 3 },
	{ display: "Through 2 innings", id: 2 },
	{ display: "Through 1 innings", id: 1 }
];
window.teams = [
		{
			location: "Arizona",
			name: "Diamondbacks",
			display: "Arizona Diamondbacks",
			id: 109,
			league: "NL",
			tbg_abbr: "ARI"
		},
		{
			location: "Atlanta",
			name: "Braves",
			display: "Atlanta Braves",
			id: 144,
			league: "NL",
			tbg_abbr: "ATL"
		},
		{
			location: "Chicago",
			name: "Cubs",
			display: "Chicago Cubs",
			id: 112,
			league: "NL",
			tbg_abbr: "CHC"
		},
		{
			location: "Cincinnati",
			name: "Reds",
			display: "Cincinnati Reds",
			id: 113,
			league: "NL",
			tbg_abbr: "CIN"
		},
		{
			location: "Colorado",
			name: "Rockies",
			display: "Colorado Rockies",
			id: 115,
			league: "NL",
			tbg_abbr: "COL"
		},
		{
			location: "Los Angeles",
			name: "Dodgers",
			display: "Los Angeles Dodgers",
			id: 119,
			league: "NL",
			tbg_abbr: "LAD"
		},
		{
			location: "Miami",
			name: "Marlins",
			display: "Miami Marlins",
			id: 146,
			league: "NL",
			tbg_abbr: "MIA"
		},
		{
			location: "Milwaukee",
			name: "Brewers",
			display: "Milwaukee Brewers",
			id: 158,
			league: "NL",
			tbg_abbr: "MIL"
		},
		{
			location: "New York",
			name: "Mets",
			display: "New York Mets",
			id: 121,
			league: "NL",
			tbg_abbr: "NYM"
		},
		{
			location: "Philadelphia",
			name: "Phillies",
			display: "Philadelphia Phillies",
			id: 143,
			league: "NL",
			tbg_abbr: "PHI"
		},
		{
			location: "Pittsburgh",
			name: "Pirates",
			display: "Pittsburgh Pirates",
			id: 134,
			league: "NL",
			tbg_abbr: "PIT"
		},
		{
			location: "San Diego",
			name: "Padres",
			display: "San Diego Padres",
			id: 135,
			league: "NL",
			tbg_abbr: "SD"
		},
		{
			location: "San Francisco",
			name: "Giants",
			display: "San Francisco Giants",
			id: 137,
			league: "NL",
			tbg_abbr: "SF"
		},
		{
			location: "St. Louis",
			name: "Cardinals",
			display: "St. Louis Cardinals",
			id: 138,
			league: "NL",
			tbg_abbr: "STL"
		},
		{
			location: "Washington",
			name: "Nationals",
			display: "Washington Nationals",
			id: 120,
			league: "NL",
			tbg_abbr: "WSH"
		},
		{
			location: "Baltimore",
			name: "Orioles",
			display: "Baltimore Orioles",
			id: 110,
			league: "AL",
			tbg_abbr: "BAL"
		},
		{
			location: "Boston",
			name: "Red Sox",
			display: "Boston Red Sox",
			id: 111,
			league: "AL",
			tbg_abbr: "BOS"
		},
		{
			location: "Chicago",
			name: "White Sox",
			display: "Chicago White Sox",
			id: 145,
			league: "AL",
			tbg_abbr: "CWS"
		},
		{
			location: "Cleveland",
			name: "Indians",
			display: "Cleveland Indians",
			id: 114,
			league: "AL",
			tbg_abbr: "CLE"
		},
		{
			location: "Detroit",
			name: "Tigers",
			display: "Detroit Tigers",
			id: 116,
			league: "AL",
			tbg_abbr: "DET"
		},
		{
			location: "Houston",
			name: "Astros",
			display: "Houston Astros",
			id: 117,
			league: "AL",
			tbg_abbr: "HOU"
		},
		{
			location: "Kansas City",
			name: "Royals",
			display: "Kansas City Royals",
			id: 118,
			league: "AL",
			tbg_abbr: "KC"
		},
		{
			location: "Los Angeles",
			name: "Angels",
			display: "Los Angeles Angels of Anaheim",
			id: 108,
			league: "AL",
			tbg_abbr: "LAA"
		},
		{
			location: "Minnesota",
			name: "Twins",
			display: "Minnesota Twins",
			id: 142,
			league: "AL",
			tbg_abbr: "MIN"
		},
		{
			location: "New York",
			name: "Yankees",
			display: "New York Yankees",
			id: 147,
			league: "AL",
			tbg_abbr: "NYY"
		},
		{
			location: "Oakland",
			name: "Athletics",
			display: "Oakland Athletics",
			id: 133,
			league: "AL",
			tbg_abbr: "OAK"
		},
		{
			location: "Seattle",
			name: "Mariners",
			display: "Seattle Mariners",
			id: 136,
			league: "AL",
			tbg_abbr: "SEA"
		},
		{
			location: "Tampa Bay",
			name: "Rays",
			display: "Tampa Bay Rays",
			id: 139,
			league: "AL",
			tbg_abbr: "TB"
		},
		{
			location: "Texas",
			name: "Rangers",
			display: "Texas Rangers",
			id: 140,
			league: "AL",
			tbg_abbr: "TEX"
		},
		{
			location: "Toronto",
			name: "Blue Jays",
			display: "Toronto Blue Jays",
			id: 141,
			league: "AL",
			tbg_abbr: "TOR"
		}
	];
window.game_status_inds = {
  I: {
    description: "In Progress",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: false
  },
  MC: {
    description: "Manager Challenge",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: true
  },
  MA: {
    description: "Manager Challenge",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: true
  },
  MI: {
    description: "Manager Challenge",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: true
  },
  MF: {
    description: "Manager Challenge",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: true
  },
  M: {
    description: "Manager Challenge",
    sort_score: 100,
    main_display: "linescore",
    bottom_display: "current",
    challenge: true
  },
  F: {
    description: "Final",
    sort_score: 80,
    main_display: "final",
    bottom_display: "result",
    challenge: false
  },
  O: {
    description: "Game Over",
    sort_score: 80,
    main_display: "final",
    bottom_display: "result",
    challenge: false
  },
  P: {
    description: "Pregame",
    sort_score: 70,
    main_display: "preview",
    bottom_display: "probables",
    challenge: false
  },
  PW: {
    description: "Postponed because of weather",
    sort_score: 70,
    main_display: "preview",
    bottom_display: "description",
    challenge: false
  },
  S: {
    description: "Scheduled",
    sort_score: 65,
    main_display: "preview",
    bottom_display: "probables",
    challenge: false
  },
  D: {
    description: "Postponed",
    sort_score: 60,
    main_display: "preview",
    bottom_display: "description",
    challenge: false
  },
  DR: {
    description: "Game Postponed",
    sort_score: 60,
    main_display: "preview",
    bottom_display: "description",
    challenge: false
  },
  Delayed: {
    description: "Delayed",
    sort_score: 95,
    main_display: "linescore",
    bottom_display: "description",
    challenge: false
  },
  IR: {
    description: "Injury Delay",
    sort_score: 90,
    main_display: "linescore",
    bottom_display: "description",
    challenge: false
  }
};
</script>
<script src="js/gamechanger.js?v=<?php echo time(); ?>"></script>
