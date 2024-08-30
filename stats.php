<?php
require_once('login_connection.php');
global $conn;
require_once('header.php');
?>
<style>
    .table tbody tr:nth-child(odd) { background-color: lightgray; }
</style> 
<?php
$SQLQueryName = "SELECT * FROM player WHERE player_id=:player_id";
$stmtName = $conn->prepare($SQLQueryName);
$stmtName->bindValue(':player_id', $_POST['player_id']);
$stmtName->execute();
$rowName = $stmtName->fetch();

$SQLQueryHistory = "SELECT team_name, MIN(plays.season) as 'start', MAX(plays.season) as 'end', GROUP_CONCAT(DISTINCT jersey_number SEPARATOR ', ') as 'jersey', GROUP_CONCAT(DISTINCT stadium_name SEPARATOR ', ') as 'stadiums' FROM plays JOIN team ON team.abbr = plays.team_name and team.season = plays.season GROUP BY player_id, team_name HAVING player_id = :player_id ORDER BY start";
$stmtHistory = $conn->prepare($SQLQueryHistory);
$stmtHistory->bindValue(':player_id', $_POST['player_id']);
$stmtHistory->execute();

$qbflag = false;
if($rowName['position'] == "QB"){
    $qbflag = true;
    $SQLQueryOffense = "SELECT season, completions, attempts, passing_yards, passing_touchdowns, interceptions, sack_fumbles_lost,
        games, carries, rush_yards, rushing_touchdowns, rushing_fumbles_lost, receiving_touchdowns, team_name FROM quarterback 
        INNER JOIN offensive USING (player_id, season) JOIN plays USING (player_id, season) WHERE player_id=:player_id";

    $SQLOffenseTotal = "SELECT player_id, SUM(completions) AS total_completions, SUM(attempts) AS total_attempts, 
        SUM(passing_yards) AS total_passing_yards, SUM(passing_touchdowns) AS total_passing_touchdowns, 
        SUM(interceptions) AS total_interceptions, SUM(sack_fumbles_lost) AS total_sack_fumbles_lost, 
        SUM(games) AS total_games, SUM(carries) AS total_carries, SUM(rush_yards) AS total_rush_yards, 
        SUM(rushing_touchdowns) AS total_rushing_touchdowns, SUM(rushing_fumbles_lost) AS total_rushing_fumbles_lost, 
        SUM(receiving_touchdowns) AS total_receiving_touchdowns FROM quarterback INNER JOIN offensive 
        USING (player_id, season) GROUP BY player_id HAVING player_id = :player_id";
} else {
    $SQLQueryOffense = "SELECT * FROM offensive JOIN plays USING (player_id, season) WHERE player_id=:player_id";

    $SQLOffenseTotal = "SELECT SUM(games) AS total_games, SUM(carries) AS total_carries, SUM(rush_yards) AS total_rush_yards, 
        SUM(targets) AS total_targets, SUM(receptions) AS total_receptions, SUM(receiving_yards) AS total_receiving_yards, 
        SUM(yards_after_catch) AS total_yards_after_catch, SUM(rushing_fumbles_lost) AS total_rushing_fumbles_lost, 
        SUM(receiving_fumbles_lost) AS total_receiving_fumbles_lost, SUM(rushing_touchdowns) AS total_rushing_touchdowns, 
        SUM(receiving_touchdowns) AS total_receiving_touchdowns FROM offensive GROUP BY player_id HAVING player_id = :player_id";
}
$stmtOffense = $conn->prepare($SQLQueryOffense);
$stmtOffense->bindValue(':player_id', $_POST['player_id']);
$stmtOffense->execute();

$stmtTotOffense = $conn->prepare($SQLOffenseTotal);
$stmtTotOffense->bindValue(':player_id', $_POST['player_id']);
$stmtTotOffense->execute();
$TotalOffenseRow = $stmtTotOffense->fetch();

$SQLQueryDefensive = "SELECT * FROM defensive JOIN plays USING (player_id, season) WHERE player_id=:player_id";
$stmtDefense = $conn->prepare($SQLQueryDefensive);
$stmtDefense->bindValue(':player_id', $_POST['player_id']);
$stmtDefense->execute();

$SQLDefenseTotal = "SELECT SUM(games) AS total_games, SUM(total_tackles) AS total_total_tackles, 
    SUM(solo_tackles) AS total_solo_tackles, SUM(assist_tackles) AS total_assist_tackles, 
    SUM(tackles_for_loss) AS total_tackles_for_loss, SUM(forced_fumbles) AS total_forced_fumbles, SUM(fumbles_recovered) AS total_fumbles_recovered,
    SUM(sacks) AS total_sacks, SUM(interceptions) AS total_interceptions, SUM(passes_defended) AS total_passes_defended, 
    SUM(safeties) AS total_safeties, SUM(touchdowns) AS total_touchdowns FROM defensive GROUP BY player_id HAVING player_id = :player_id";

$stmtTotDefense = $conn->prepare($SQLDefenseTotal);
$stmtTotDefense->bindValue(':player_id', $_POST['player_id']);
$stmtTotDefense->execute();
$TotalDefenseRow = $stmtTotDefense->fetch();

$SQLQueryP = "SELECT * FROM punter JOIN plays USING (player_id, season) WHERE player_id=:player_id";
$SQLQueryPR = "SELECT * FROM punt_returner JOIN plays USING (player_id, season) WHERE player_id=:player_id";
$SQLQueryK = "SELECT * FROM kicker JOIN plays USING (player_id, season) WHERE player_id=:player_id";
$SQLQueryKR = "SELECT * FROM kick_return JOIN plays USING (player_id, season) WHERE player_id=:player_id";

$stmtP = $conn->prepare($SQLQueryP);
$stmtPR = $conn->prepare($SQLQueryPR);
$stmtK = $conn->prepare($SQLQueryK);
$stmtKR = $conn->prepare($SQLQueryKR);

$stmtP->bindValue(':player_id', $_POST['player_id']);
$stmtPR->bindValue(':player_id', $_POST['player_id']);
$stmtK->bindValue(':player_id', $_POST['player_id']);
$stmtKR->bindValue(':player_id', $_POST['player_id']);

$stmtP->execute();
$stmtPR->execute();
$stmtK->execute();
$stmtKR->execute();

$numberRowsOffense = $stmtOffense->rowCount();
$numberRowsTotOffense = $stmtTotOffense->rowCount();
$numberRowsDefense = $stmtDefense->rowCount();
$numberRowsTotDefense = $stmtTotDefense->rowCount();
$numberRowsP = $stmtP->rowCount();
$numberRowsPR = $stmtPR->rowCount();
$numberRowsK = $stmtK->rowCount();
$numberRowsKR = $stmtKR->rowCount();

$SQLPTotal = "SELECT SUM(games) as total_games, SUM(punt_attempts) as total_punt_attempts, MAX(longest_punt) as total_longest_punt,
    SUM(punting_yards) as total_punting_yards, ROUND(AVG(average_punt_distance), 2) as total_average_punt_distance, SUM(blocked_punts) as total_blocked_punts 
    FROM punter GROUP BY player_id HAVING player_id=:player_id";

$stmtPTot = $conn->prepare($SQLPTotal);
$stmtPTot->bindValue(':player_id', $_POST['player_id']);
$stmtPTot->execute();
$TotalPRow = $stmtPTot->fetch();

$SQLKTotal = "SELECT SUM(fg_att) as total_fg_att, SUM(fg_made) AS total_fg_made, SUM(field_goal_blocked) AS total_field_goal_blocked, 
    MAX(fg_long) AS total_fg_long, SUM(fg_made_0_19) AS total_fg_made_0_19, SUM(fg_made_20_29) AS total_fg_made_20_29, SUM(fg_made_30_39) AS total_fg_made_30_39,
    SUM(fg_made_40_49) AS total_fg_made_40_49, SUM(fg_made_50_59) AS total_fg_made_50_59, SUM(fg_made_60_) AS total_fg_made_60_,
    ROUND(AVG(percent_field_goal), 2) AS total_percent_field_goal, SUM(pat_blocked) AS total_pat_blocked, SUM(pat_att) AS total_pat_att,
    SUM(pat_made) AS total_pat_made, ROUND(AVG(pat_pct), 2) AS total_pat_pct FROM kicker GROUP BY player_id HAVING player_id = :player_id";

$stmtKTot = $conn->prepare($SQLKTotal);
$stmtKTot->bindValue(':player_id', $_POST['player_id']);
$stmtKTot->execute();
$TotalKRow = $stmtKTot->fetch();

$SQLPRTotal = "SELECT SUM(games) as total_games, SUM(return_attempts) AS total_return_attempts, SUM(return_yards) AS total_return_yards, 
    ROUND(AVG(yards_per_return), 2) AS total_yards_per_return, MAX(longest_return) AS total_longest_return, SUM(touchdowns) AS total_touchdowns, 
    SUM(fumbles) AS total_fumbles FROM punt_returner GROUP BY player_id HAVING player_id = :player_id";

$stmtPRTot = $conn->prepare($SQLPRTotal);
$stmtPRTot->bindValue(':player_id', $_POST['player_id']);
$stmtPRTot->execute();
$TotalPRRow = $stmtPRTot->fetch();

$SQLKRTotal = "SELECT SUM(games) as total_games, SUM(return_attempts) AS total_return_attempts, SUM(return_yards) AS total_return_yards, 
    ROUND(AVG(yards_per_return), 2) AS total_yards_per_return, MAX(longest_return) AS total_longest_return, SUM(touchdowns) AS total_touchdowns, 
    SUM(fumbles) AS total_fumbles FROM kick_return GROUP BY player_id HAVING player_id = :player_id";

$stmtKRTot = $conn->prepare($SQLKRTotal);
$stmtKRTot->bindValue(':player_id', $_POST['player_id']);
$stmtKRTot->execute();
$TotalKRRow = $stmtKRTot->fetch();

$numberRowsTotP = $stmtPTot->rowCount();
$numberRowsTotPR = $stmtPRTot->rowCount();
$numberRowsTotK = $stmtKTot->rowCount();
$numberRowsTotKR = $stmtKRTot->rowCount();

$SQLQuery2023 = "SELECT MAX(season) as 'season' FROM `plays` GROUP BY player_id HAVING player_id = :player_id";
$stmt2023 = $conn->prepare($SQLQuery2023);
$stmt2023->bindValue(':player_id', $_POST['player_id']);
$stmt2023->execute();
$row2023 = $stmt2023->fetch();
$flag2023 = $row2023['season'] == '2023';
$offensePositions = array('QB', 'FB', 'RB', 'WR', 'TE', 'G', 'OL', 'C');
$specPositions = array('SPEC', 'K', 'KR', 'P', 'PR', 'LS');
if($flag2023){
    if($rowName['position'] == 'QB'){
        $SQL2023 = "SELECT completions, passing_yards, passing_touchdowns, rushing_touchdowns, receiving_touchdowns FROM quarterback 
        INNER JOIN offensive USING (player_id, season) WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'RB' or $rowName['position'] == 'FB'){
        $SQL2023 = "SELECT carries, rush_yards, rushing_touchdowns FROM offensive WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){
        $SQL2023 = "SELECT receptions, receiving_yards, receiving_touchdowns FROM offensive WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){
        $SQL2023 = "SELECT total_tackles, sacks, interceptions FROM defensive WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){
        $SQL2023 = "SELECT percent_field_goal, pat_pct, fg_long FROM kicker WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'P'){
        $SQL2023 = "SELECT punt_attempts, average_punt_distance, longest_punt FROM punter WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'KR'){
        $SQL2023 = "SELECT return_yards, yards_per_return, touchdowns FROM kick_return WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }

    else if($rowName['position'] == 'PR'){
        $SQL2023 = "SELECT return_yards, yards_per_return, touchdowns FROM punt_returner WHERE player_id=:player_id and season = :season";

        $stmt2023 = $conn->prepare($SQL2023);
        $stmt2023->bindValue(':player_id', $_POST['player_id']);
        $stmt2023->bindValue(':season', '2023');
        $stmt2023->execute();
        $row2023 = $stmt2023->fetch();
    }
    $numberRows2023 = $stmt2023->rowCount();
}
?>
<div class="bg-dark pb-2" style="background-color: #e3f2fd;">
	<a href="searchbar.php" class="btn btn-outline-light mt-2 ml-2">Search Another Player</a>
</div>
<div class="row g-0 d-flex align-items-end" style="background-color: gray">
    <div class="col">
        <h1 class="pl-2"><?php echo $rowName['name'];?></h1>
    </div>
</div>
<div class="row g-0 d-flex align-items-end" style="background-color: gray">
    <div class="col-3 p-0">
        <img class="img-fluid" src="<?php echo $rowName['headshot'];?>">
    </div>
    <div class="col-8 mb-1 p-0 d-flex flex-column justify-content-end">
        <div class="jumbotron mr-2 mb-0 pl-2 pt-0 pr-0 pb-0">
            <div class="row">
                <div class="col">
                    <div class="row">
                        <p class="display-6">Bio</p>
                    </div>
                    <p class="lead">Height: <?php echo round($rowName['height'] / 12, 2);?></p>
                    <p class="lead">Weight: <?php echo $rowName['weight'];?></p>
                    <p class="lead">Birthday: <?php echo $rowName['date_of_birth'];?></p>
                    <p class="lead">Position: <?php echo $rowName['position'];?></p>
                    <p class="lead">College: <?php echo $rowName['college'];?></p>
                    <p class="lead mb-0"><?php if($rowName['draft_number'] == -1){ ?>
                    Undrafted in <?php echo $rowName['draft_year'];?>
                    <?php } else { ?>
                    Drafted <?php echo $rowName['draft_number'];?> overall in <?php echo $rowName['draft_year']; 
                    } ?> </p>
                </div>
                <div class="col">
                    <div class="row">
                        <p class="display-6">Career</p>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <?php 
                                if($rowName['position'] == 'QB'){?>
                                <p class="lead">CMP</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_completions'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB' or $rowName['position'] == 'FB'){?>
                                <p class="lead">CAR</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_carries'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">REC</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_receptions'] : '--';?></p>
                                <?php }
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">TCK</p>
                                <p class="lead"><?php echo $numberRowsTotDefense > 0 ? $TotalDefenseRow['total_total_tackles'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">FG%</p>
                                <p class="lead"><?php echo $numberRowsTotK > 0 ? $TotalKRow['total_percent_field_goal'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">ATT</p>
                                <p class="lead"><?php echo $numberRowsTotP > 0 ? $TotalPRow['total_punt_attempts'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRowsTotKR > 0 ? $TotalKRRow['total_return_yards'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRowsTotPR > 0 ? $TotalPRRow['total_return_yards'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <?php if($rowName['position'] == 'QB'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_passing_yards'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB' or $rowName['position'] == 'FB'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_rush_yards'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_receiving_yards'] : '--';?></p>
                                <?php }
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">SCK</p>
                                <p class="lead"><?php echo $numberRowsTotDefense > 0 ? $TotalDefenseRow['total_sacks'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">PAT%</p>
                                <p class="lead"><?php echo $numberRowsTotK > 0 ? $TotalKRow['total_pat_pct'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRowsTotP > 0 ? $TotalPRow['total_average_punt_distance'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRowsTotKR > 0 ? $TotalKRRow['total_yards_per_return'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRowsTotPR > 0 ? $TotalPRRow['total_yards_per_return'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <?php if($rowName['position'] == 'QB'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_passing_touchdowns'] + $TotalOffenseRow['total_rushing_touchdowns'] + $TotalOffenseRow['total_receiving_touchdowns'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_rushing_touchdowns'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRowsTotOffense > 0 ? $TotalOffenseRow['total_receiving_touchdowns'] : '--';?></p>
                                <?php } 
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">INT</p>
                                <p class="lead"><?php echo $numberRowsTotDefense > 0 ? $TotalDefenseRow['total_interceptions'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">LNG</p>
                                <p class="lead"><?php echo $numberRowsTotK > 0 ? $TotalKRow['total_fg_long'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">LNG</p>
                                <p class="lead"><?php echo $numberRowsTotP > 0 ? $TotalPRow['total_longest_punt'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRowsTotKR > 0 ? $TotalKRRow['total_touchdowns'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRowsTotPR > 0 ? $TotalPRRow['total_touchdowns'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if($flag2023){ ?>
                    <div class="row">
                        <p class="display-6">2023</p>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <?php 
                                $offensePositions = array('QB', 'FB', 'RB', 'WR', 'TE', 'G', 'OL', 'C');
                                $specPositions = array('SPEC', 'K', 'KR', 'P', 'PR', 'LS');
                                if($rowName['position'] == 'QB'){?>
                                <p class="lead">CMP</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['completions'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB' or $rowName['position'] == 'FB'){?>
                                <p class="lead">CAR</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['carries'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">REC</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['receptions'] : '--';?></p>
                                <?php }
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">TCK</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['total_tackles'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">FG%</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['percent_field_goal'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">ATT</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['punt_attempts'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['return_yards'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['return_yards'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <?php if($rowName['position'] == 'QB'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['passing_yards'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB' or $rowName['position'] == 'FB'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['rush_yards'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">YDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['receiving_yards'] : '--';?></p>
                                <?php }
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">SCK</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['sacks'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">PAT%</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['pat_pct'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['average_punt_distance'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['yards_per_return'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">AVG</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['yards_per_return'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <?php if($rowName['position'] == 'QB'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['passing_touchdowns'] + $row2023['rushing_touchdowns'] + $row2023['receiving_touchdowns'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'RB'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['rushing_touchdowns'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'WR' or $rowName['position'] == 'TE'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['receiving_touchdowns'] : '--';?></p>
                                <?php } 
                                else if(!in_array($rowName['position'], $offensePositions) and !in_array($rowName['position'], $specPositions)){?>
                                <p class="lead">INT</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['interceptions'] : '--';?></p>
                                <?php }
                                else if($rowName['position'] == 'K' or $rowName['position'] == 'SPEC'){?>
                                <p class="lead">LNG</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['fg_long'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'P'){?>
                                <p class="lead">LNG</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['longest_punt'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'KR'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['touchdowns'] : '--';?></p>
                                <?php } 
                                else if($rowName['position'] == 'PR'){?>
                                <p class="lead">TDS</p>
                                <p class="lead"><?php echo $numberRows2023 > 0 ? $row2023['touchdowns'] : '--';?></p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="col">
                    <div class="row">
                        <p class="display-6">History</p>
                    </div>
                    <?php 
                    while ($sqlRow = $stmtHistory->fetch()){
                        ?>
                        <p class="lead"><?php echo $sqlRow['team_name'] . ': ' . $sqlRow['start'] . '-' . $sqlRow['end'] . ', #' . $sqlRow['jersey']; ?></p>
                        <P class="lead">Stadium: <?php echo $sqlRow['stadiums']; ?>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if($numberRowsOffense > 0){?>
    <h2 class="ml-4">Offensive Stats</h2>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 95%">
        <thead class="thead-dark">
            <tr>
                <?php if($qbflag){?>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th>
                <th>Pass Attempts</th>
                <th>Pass Completions</th>
                <th>Passing Yards</th>
                <th>Carries</th>
                <th>Rushing Yards</th>
                <th>Yards per Carry</th>
                <th>Fumbles</th>
                <th>Interceptions</th>
                <th>Passing Touchdowns</th>
                <th>Touchdowns</th>
                <?php } else {?>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th>
                <th>Carries</th>
                <th>Rushing Yards</th>
                <th>Yards per Carry</th>
                <th>Targets</th>
                <th>Receptions</th>
                <th>Receiving Yards</th>
                <th>Yards After Catch</th>
                <th>Turnovers</th>
                <th>Touchdowns</th>
                <?php }?>
            </tr>
        </thead>

        <tbody>
            <?php while ($sqlRow = $stmtOffense->fetch()){?>
                <tr>
                    <?php if($qbflag){?>
                    <th><?php echo $sqlRow['season'];?></th>
                    <th><?php echo $sqlRow['team_name'];?></th>
                    <th><?php echo $sqlRow['games'];?></th>
                    <th><?php echo $sqlRow['attempts'];?></th>
                    <th><?php echo $sqlRow['completions'];?></th>
                    <th><?php echo $sqlRow['passing_yards'];?></th>
                    <th><?php echo $sqlRow['carries'];?></th>
                    <th><?php echo $sqlRow['rush_yards'];?></th>
                    <th><?php 
                    if($sqlRow['carries'] == 0){
                        echo '0';
                    } else {
                        echo round($sqlRow['rush_yards'] / $sqlRow['carries'], 2);
                    }?></th>
                    <th><?php echo $sqlRow['rushing_fumbles_lost'] + $sqlRow['sack_fumbles_lost'];?></th>
                    <th><?php echo $sqlRow['interceptions'];?></th>
                    <th><?php echo $sqlRow['passing_touchdowns'];?></th>
                    <th><?php echo $sqlRow['rushing_touchdowns'] + $sqlRow['receiving_touchdowns'];?></th>
                    <?php } else {?>
                    <th><?php echo $sqlRow['season'];?></th>
                    <th><?php echo $sqlRow['team_name'];?></th>
                    <th><?php echo $sqlRow['games'];?></th>
                    <th><?php echo $sqlRow['carries'];?></th>
                    <th><?php echo $sqlRow['rush_yards'];?></th>
                    <th><?php 
                    if($sqlRow['carries'] == 0){
                        echo '0';
                    } else {
                        echo round($sqlRow['rush_yards'] / $sqlRow['carries'], 2);
                    }?></th>
                    <th><?php echo $sqlRow['targets'];?></th>
                    <th><?php echo $sqlRow['receptions'];?></th>
                    <th><?php echo $sqlRow['receiving_yards'];?></th>
                    <th><?php echo $sqlRow['yards_after_catch'];?></th>
                    <th><?php echo $sqlRow['rushing_fumbles_lost'] + $sqlRow['receiving_fumbles_lost'];?></th>
                    <th><?php echo $sqlRow['rushing_touchdowns'] + $sqlRow['receiving_touchdowns'];?></th>
                    <?php }?>
                </tr>
                <?php
            }   
            if($qbflag){
                ?>
                <tr class="thead-dark">
                <th colspan="2"><?php echo "Career Totals";?></th>
                <th><?php echo $TotalOffenseRow['total_games'];?></th>
                <th><?php echo $TotalOffenseRow['total_attempts'];?></th>
                <th><?php echo $TotalOffenseRow['total_completions'];?></th>
                <th><?php echo $TotalOffenseRow['total_passing_yards'];?></th>
                <th><?php echo $TotalOffenseRow['total_carries'];?></th>
                <th><?php echo $TotalOffenseRow['total_rush_yards'];?></th>
                <th><?php 
                if($TotalOffenseRow['total_carries'] == 0){
                    echo '0';
                } else {
                    echo round($TotalOffenseRow['total_rush_yards'] / $TotalOffenseRow['total_carries'], 2);
                }?></th>
                <th><?php echo $TotalOffenseRow['total_rushing_fumbles_lost'] + $TotalOffenseRow['total_sack_fumbles_lost'];?></th>
                <th><?php echo $TotalOffenseRow['total_interceptions'];?></th>
                <th><?php echo $TotalOffenseRow['total_passing_touchdowns'];?></th>
                <th><?php echo $TotalOffenseRow['total_rushing_touchdowns'] + $TotalOffenseRow['total_receiving_touchdowns'];?></th>
                <?php
            } else {
                ?>
                <tr class="thead-dark">
                <th colspan="2"><?php echo "Career Totals";?></th>
                <th><?php echo $TotalOffenseRow['total_games'];?></th>
                <th><?php echo $TotalOffenseRow['total_carries'];?></th>
                <th><?php echo $TotalOffenseRow['total_rush_yards'];?></th>
                <th><?php 
                if($TotalOffenseRow['total_carries'] == 0){
                    echo '0';
                } else {
                    echo round($TotalOffenseRow['total_rush_yards'] / $TotalOffenseRow['total_carries'], 2);
                }?></th>
                <th><?php echo $TotalOffenseRow['total_targets'];?></th>
                <th><?php echo $TotalOffenseRow['total_receptions'];?></th>
                <th><?php echo $TotalOffenseRow['total_receiving_yards'];?></th>
                <th><?php echo $TotalOffenseRow['total_yards_after_catch'];?></th>
                <th><?php echo $TotalOffenseRow['total_rushing_fumbles_lost'] + $TotalOffenseRow['total_receiving_fumbles_lost'];?></th>    
                <th><?php echo $TotalOffenseRow['total_rushing_touchdowns'] + $TotalOffenseRow['total_receiving_touchdowns'];?></th>                
                <?php
            }
            ?>
            </tr>
        </tbody>
    </table>    
<?php
}

if($numberRowsDefense > 0){?>
    <h2 class="ml-4">Defensive Stats</h2>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 97%">
        <thead class="thead-dark">
            <tr>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th> 
                <th>Total Tackles</th>
                <th>Solo Tackles</th>
                <th>Tackle Assists</th>
                <th>Tackles for Loss</th>
                <th>Fumbles Forced</th>
                <th>Fumbles Recovered</th>
                <th>Sacks</th>
                <th>Interceptions</th>
                <th>Passes Defended</th>
                <th>Safeties</th>
                <th>Touchdowns</th>
            </tr>
        </thead>

        <tbody>
            <?php

            while ($sqlRow = $stmtDefense->fetch()) {
                ?>
                <tr>
                    <th><?php echo $sqlRow['season'];?></th>
                    <th><?php echo $sqlRow['team_name'];?></th>
                    <th><?php echo $sqlRow['games'];?></th>
                    <th><?php echo $sqlRow['total_tackles'];?></th>
                    <th><?php echo $sqlRow['solo_tackles'];?></th>
                    <th><?php echo $sqlRow['assist_tackles'];?></th>
                    <th><?php echo $sqlRow['tackles_for_loss'];?></th>
                    <th><?php echo $sqlRow['forced_fumbles'];?></th>
                    <th><?php echo $sqlRow['fumbles_recovered'];?></th>
                    <th><?php echo $sqlRow['sacks'];?></th>
                    <th><?php echo $sqlRow['interceptions'];?></th>
                    <th><?php echo $sqlRow['passes_defended'];?></th>
                    <th><?php echo $sqlRow['safeties'];?></th>
                    <th><?php echo $sqlRow['touchdowns'];?></th>
                </tr>
                <?php
            }            ?>
            <tr class="thead-dark">
                <th colspan="2"><?php echo "Career Totals";?></th>
                <th><?php echo $TotalDefenseRow['total_games'];?></th>
                <th><?php echo $TotalDefenseRow['total_total_tackles'];?></th>
                <th><?php echo $TotalDefenseRow['total_solo_tackles'];?></th>
                <th><?php echo $TotalDefenseRow['total_assist_tackles'];?></th>
                <th><?php echo $TotalDefenseRow['total_tackles_for_loss'];?></th>
                <th><?php echo $TotalDefenseRow['total_forced_fumbles'];?></th>
                <th><?php echo $TotalDefenseRow['total_fumbles_recovered'];?></th>
                <th><?php echo $TotalDefenseRow['total_sacks'];?></th>
                <th><?php echo $TotalDefenseRow['total_interceptions'];?></th>
                <th><?php echo $TotalDefenseRow['total_passes_defended'];?></th>
                <th><?php echo $TotalDefenseRow['total_safeties'];?></th>
                <th><?php echo $TotalDefenseRow['total_touchdowns'];?></th>
            </tr>
        </tbody>
    </table>
<?php
}

$flagK = $numberRowsK > 0;   
$flagP = $numberRowsP > 0; 
$flagPR = $numberRowsPR > 0;
$flagKR = $numberRowsKR > 0;
if($flagK or $flagP or $flagPR or $flagKR){
    ?> <h2 class="ml-4">Special Teams Stats</h2> <?php
}
if($flagP){?>
    <h4 class="ml-4">Punting</h4>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 95%">
        <thead class="thead-dark">
            <tr>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th>
                <th>Punt Attempts</th>
                <th>Longest Punt</th>
                <th>Punting Yards</th>
                <th>Average Punt Distance</th>
                <th>Blocked Punts</th>
            </tr>
        </thead>

        <tbody>
            <?php 
                while ($sqlRow = $stmtP->fetch()) { ?>
                    <tr>
                        <th><?php echo $sqlRow['season'];?></th>
                        <th><?php echo $sqlRow['team_name'];?></th>
                        <th><?php echo $sqlRow['games'];?></th>
                        <th><?php echo $sqlRow['punt_attempts'];?></th>
                        <th><?php echo $sqlRow['longest_punt'];?></th>
                        <th><?php echo $sqlRow['punting_yards'];?></th>
                        <th><?php echo $sqlRow['average_punt_distance'];?></th>
                        <th><?php echo $sqlRow['blocked_punts'];?></th>
                    </tr>
            <?php } 
                ?>
                <tr class="thead-dark">
                    <th colspan="2"><?php echo "Career Totals";?></th>
                    <th><?php echo $TotalPRow['total_games'];?></th>
                    <th><?php echo $TotalPRow['total_punt_attempts'];?></th>
                    <th><?php echo $TotalPRow['total_longest_punt'];?></th>
                    <th><?php echo $TotalPRow['total_punting_yards'];?></th>
                    <th><?php echo $TotalPRow['total_average_punt_distance'];?></th>
                    <th><?php echo $TotalPRow['total_blocked_punts'];?></th>
                </tr>
        </tbody>
    </table>
<?php
}

if($flagK){ ?>
    <h4 class="ml-4">Kicking</h4>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 95%">
        <thead class="thead-dark">
            <tr>
                <th>Season</th>
                <th>Team</th>
                <th>FG Attempts</th>
                <th>FGs Made</th>
                <th>FGs Blocked</th>
                <th>Longest FG</th>
                <th>From 0-19</th>
                <th>From 20-29</th>
                <th>From 30-39</th>
                <th>From 40-49</th>
                <th>From 50-59</th>
                <th>From 60+</th>
                <th>Percent FG Made</th>
                <th>PAT Blocked</th>
                <th>PAT Attempts</th>
                <th>PAT Made</th>
                <th>Percent PAT Made</th> 
            </tr>
        </thead>

        <tbody> 
        <?php
            while ($sqlRow = $stmtK->fetch()) { ?>
                <tr>
                    <th><?php echo $sqlRow['season'];?></th>
                    <th><?php echo $sqlRow['team_name'];?></th>
                    <th><?php echo $sqlRow['fg_att'];?></th>
                    <th><?php echo $sqlRow['fg_made'];?></th>
                    <th><?php echo $sqlRow['field_goal_blocked'];?></th>
                    <th><?php echo $sqlRow['fg_long'];?></th>
                    <th><?php echo $sqlRow['fg_made_0_19'];?></th>
                    <th><?php echo $sqlRow['fg_made_20_29'];?></th>
                    <th><?php echo $sqlRow['fg_made_30_39'];?></th>
                    <th><?php echo $sqlRow['fg_made_40_49'];?></th>
                    <th><?php echo $sqlRow['fg_made_50_59'];?></th>
                    <th><?php echo $sqlRow['fg_made_60_'];?></th>
                    <th><?php echo $sqlRow['percent_field_goal'];?></th>
                    <th><?php echo $sqlRow['pat_blocked'];?></th>
                    <th><?php echo $sqlRow['pat_att'];?></th>
                    <th><?php echo $sqlRow['pat_made'];?></th>
                    <th><?php echo $sqlRow['pat_pct'];?></th>
                </tr>
            <?php }
                ?>
                <tr class="thead-dark">
                    <th colspan="2"><?php echo "Career Totals";?></th>
                    <th><?php echo $TotalKRow['total_fg_att'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made'];?></th>
                    <th><?php echo $TotalKRow['total_field_goal_blocked'];?></th>
                    <th><?php echo $TotalKRow['total_fg_long'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_0_19'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_20_29'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_30_39'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_40_49'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_50_59'];?></th>
                    <th><?php echo $TotalKRow['total_fg_made_60_'];?></th>
                    <th><?php echo $TotalKRow['total_percent_field_goal'];?></th>
                    <th><?php echo $TotalKRow['total_pat_blocked'];?></th>
                    <th><?php echo $TotalKRow['total_pat_att'];?></th>
                    <th><?php echo $TotalKRow['total_pat_made'];?></th>
                    <th><?php echo $TotalKRow['total_pat_pct'];?></th>
                </tr>
        </tbody>
    </table>
<?php
}

if($flagPR){?>
    <h4 class="ml-4">Punt Return</h4>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 95%">
        <thead class="thead-dark">
            <tr>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th>
                <th>Returns</th>
                <th>Return Yards</th>
                <th>Yards per Return</th>
                <th>Longest Return</th>
                <th>Touchdowns</th>
                <th>Fumbles</th>
            </tr>
        </thead>

        <tbody>
            <?php 
                while ($sqlRow = $stmtPR->fetch()) { ?>
                    <tr>
                        <th><?php echo $sqlRow['season'];?></th>
                        <th><?php echo $sqlRow['team_name'];?></th>
                        <th><?php echo $sqlRow['games'];?></th>
                        <th><?php echo $sqlRow['return_attempts'];?></th>
                        <th><?php echo $sqlRow['return_yards'];?></th>
                        <th><?php echo $sqlRow['yards_per_return'];?></th>
                        <th><?php echo $sqlRow['longest_return'];?></th>
                        <th><?php echo $sqlRow['touchdowns'];?></th>
                        <th><?php echo $sqlRow['fumbles'];?></th>
                    </tr>
                <?php } 
                ?>
                <tr class="thead-dark">
                    <th colspan="2"><?php echo "Career Totals";?></th>
                    <th><?php echo $TotalPRRow['total_games'];?></th>
                    <th><?php echo $TotalPRRow['total_return_attempts'];?></th>
                    <th><?php echo $TotalPRRow['total_return_yards'];?></th>
                    <th><?php echo $TotalPRRow['total_yards_per_return'];?></th>
                    <th><?php echo $TotalPRRow['total_longest_return'];?></th>
                    <th><?php echo $TotalPRRow['total_touchdowns'];?></th>
                    <th><?php echo $TotalPRRow['total_fumbles'];?></th>
                </tr>
        </tbody>
    </table>
<?php
}

if($flagKR){?>
    <h4 class="ml-4">Kick Return</h4>
    <table class="ml-4 table table-bordered table-sm" style="max-width: 95%">
        <thead class="thead-dark">
            <tr>
                <th>Season</th>
                <th>Team</th>
                <th>Games</th>
                <th>Returns</th>
                <th>Return Yards</th>
                <th>Yards per Return</th>
                <th>Longest Return</th>
                <th>Touchdowns</th>
                <th>Fumbles</th>
            </tr>
        </thead>

        <tbody>
            <?php 
                while ($sqlRow = $stmtKR->fetch()) { ?>
                    <tr>
                        <th><?php echo $sqlRow['season'];?></th>
                        <th><?php echo $sqlRow['team_name'];?></th>
                        <th><?php echo $sqlRow['games'];?></th>
                        <th><?php echo $sqlRow['return_attempts'];?></th>
                        <th><?php echo $sqlRow['return_yards'];?></th>
                        <th><?php echo $sqlRow['yards_per_return'];?></th>
                        <th><?php echo $sqlRow['longest_return'];?></th>
                        <th><?php echo $sqlRow['touchdowns'];?></th>
                        <th><?php echo $sqlRow['fumbles'];?></th>
                    </tr>
                <?php } 
                ?>
                <tr class="thead-dark">
                    <th colspan="2"><?php echo "Career Totals";?></th>
                    <th><?php echo $TotalKRRow['total_games'];?></th>
                    <th><?php echo $TotalKRRow['total_return_attempts'];?></th>
                    <th><?php echo $TotalKRRow['total_return_yards'];?></th>
                    <th><?php echo $TotalKRRow['total_yards_per_return'];?></th>
                    <th><?php echo $TotalKRRow['total_longest_return'];?></th>
                    <th><?php echo $TotalKRRow['total_touchdowns'];?></th>
                    <th><?php echo $TotalKRRow['total_fumbles'];?></th>
                </tr>
        </tbody>
    </table>
<?php
}

if($numberRowsDefense == 0 and $numberRowsOffense == 0 and !$flagK and !$flagKR and !$flagP and !$flagPR){
    echo "No data for " . $rowName['name'];
}
?>    