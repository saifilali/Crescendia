<!DOCTYPE html>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
session_start();
ob_start();

if( !isset($_SESSION['user']) ) {
 header("Location: index.php");
 exit;
}
if( !isset($_SESSION['authcode']) ) {
 header("Location: index.php");
 exit;
}
$refreshtime = 9999;
$authcode = $_SESSION["authcode"];
$user_id = $_SESSION['user'];
$active_battle_data = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/active_get_info?auth=".$authcode."&user_id=".$user_id));
$battle_id = $active_battle_data->battle_id;
$battle_info = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_battle_info?auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id));
$battle_effects = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_effect_info?auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id));
$battle_units = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_unit_info?auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id));
#$battle_actions_queue = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_queue_info?auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id));
$battle_actions_queue = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_queue_info?unprocessed&auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id));
$battle_actions_reciept = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_queue_info?receipt&auth=".$authcode."&user_id=".$user_id."&battle_id=".$battle_id."&turn=".$active_battle_data->current_turn));
if($battle_info[0]->user_1_id == $user_id)
{
  $myteam = 1;
}
else
{
  $myteam = 2;
}
$myqueue_count = 0;
foreach($battle_actions_queue AS $action_queue)
{
  if ($action_queue->team == $myteam)
  {
    $myqueue_count = $myqueue_count +1;
  }
}

if ($myqueue_count == 4)
{
  $refreshtime = 10;
}
if($active_battle_data->status == "accepted")
{
  $refreshtime = 10;
}
 ?>

<html lang="en">
    <head>
    <style type="text/css">
    html,body {
    background: url("http://crescendiagame.com/bg.png") repeat center center fixed;
    -webkit-background-size: cover; /* For WebKit*/
    -moz-background-size: cover;    /* Mozilla*/
    -o-background-size: cover;      /* Opera*/
    background-size: cover;         /* Generic*/
    }
    </style>
        <meta charset="utf-8">
        <meta http-equiv="refresh" content="<?php echo $refreshtime;?>; URL=http://crescendiagame.com/user/battle.php">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="./css/font-awesome.min.css">
    </head>
    <body>
      <nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Crescendia Prototype</a>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="http://crescendiagame.com/user/home.php">User Home</a></li>
      <li class="active"><a href="#">Battle Mode</a></li>
    </ul>
  </div>
</nav>

      <div class="container-fluid center-block" style="width:100%">

        <?php
          if($active_battle_data->status == "finished" AND $active_battle_data->winner == $user_id)
          {
        ?>
        <div class="row panel panel-success">
          <div align="center" class="panel-heading"><h1>
          The Battle Is Won!!</h1></div>
        </div>
        <?php }
        else if($active_battle_data->status == "finished" AND $active_battle_data->winner != $user_id)
        {
      ?>
      <div class="row panel panel-danger">
        <div align="center" class="panel-heading"><h1>
        The Battle Is Lost!!</h1></div>
      </div>
      <?php }
        else if($active_battle_data->status == "accepted"){
          ?>
          <div class="row panel panel-warning">
            <div align="center" class="panel-heading"><h1>
            Battle is in progress (and will be determined by random chance)!</h1></div>
          </div>
          <?php
        } ?>
        <div id = "battle_display" class="row center-block">
          <div class="panel panel-primary">
          <div class="panel-body" style="background-image: url('http://crescendiagame.com/images/battle_bg_<?php echo ($battle_id%5);?>.jpg');background-repeat: no-repeat; background-size:cover; background-position: right top;">
            <div class = "col-xs-6"><div class = "row"><?php
          foreach($battle_units AS $unit)
          {
            if($unit->team == $myteam)
            {
              if($unit->slot == 0)
              {
                $myunit_0_name = $unit->title;
              }
              if($unit->slot == 1)
              {
                $myunit_1_name = $unit->title;
              }
              if($unit->slot == 2)
              {
                $myunit_2_name = $unit->title;
              }
              if($unit->slot == 3)
              {
                $myunit_3_name = $unit->title;
              }
            }
            else
            {
              if($unit->slot == 0)
              {
                $enemyunit_0_name = $unit->title;
                $enemyunit_0_health = $unit->health_current;
                if($unit->health_current < 1)
                {
                  $enemyunit_0_hidden = "disabled";
                }
              }
              if($unit->slot == 1)
              {
                $enemyunit_1_name = $unit->title;
                $enemyunit_1_health = $unit->health_current;
                if($unit->health_current < 1)
                {
                  $enemyunit_1_hidden = "disabled";
                }
              }
              if($unit->slot == 2)
              {
                $enemyunit_2_name = $unit->title;
                $enemyunit_2_health = $unit->health_current;
                if($unit->health_current < 1)
                {
                  $enemyunit_2_hidden = "disabled";
                }
              }
              if($unit->slot == 3)
              {
                $enemyunit_3_name = $unit->title;
                $enemyunit_3_health = $unit->health_current;
                if($unit->health_current < 1)
                {
                  $enemyunit_3_hidden = "disabled";
                }

              }
            }
          }
          foreach($battle_units AS $unit)
          {
            if($unit->team == $myteam)
            {
              $disabled = "";
              if($unit->health_current < 0)
              {
                $unit->health_current=0;
              }
            foreach($battle_actions_queue AS $battle_action)
            {
              if($battle_action->team == $myteam AND $battle_action->unit == $unit->slot)
              {
                $disabled = "disabled";
              }
            }
          ?>
          <div class = "col-xs-3">


          <img <?php if($unit->health_current < 1)
          {
            echo ' style="-webkit-transform: rotate(270deg);
  -moz-transform: rotate(270deg);
  -ms-transform: rotate(270deg);
  -o-transform: rotate(270deg);
  transform: rotate(270deg);
  transform-origin: 50% 80%;
  z-index: -1;
  -webkit-filter: grayscale(80%); /* Safari 6.0 - 9.0 */
    filter: grayscale(80%);" ';
          }
          ?> class="thumb_large" src="http://crescendiagame.com/images/chars/char_<?php echo $unit->song_id;?>.png"</br>
          <div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo 100*($unit->health_current/$unit->health_default);?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo 100*($unit->health_current/$unit->health_default);?>%">
          </div></div>
          <div class="progress"><div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="<?php echo 100*($unit->energy_current/$unit->energy_default);?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo 100*($unit->energy_current/$unit->energy_default);?>%">
          </div></div>
          <div class="well">
          <?php echo $unit->title;?></br>
          <?php echo $unit->artist;?></br>
          </br>
          Class: <?php echo $unit->class_name;?></br>
          Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $unit->song_key."A";?>.png"></br>
          Health: <?php echo $unit->health_current;?>/<?php echo $unit->health_default;?></br>
          Energy: <?php echo $unit->energy_current;?>/<?php echo $unit->energy_default;?></br>
          Power: <?php echo $unit->power_current;?>/<?php echo $unit->power_default;?></br>
          Defense: <?php echo $unit->defense_current;?>/<?php echo $unit->defense_default;?></br>
          Speed: <?php echo $unit->speed_current;?>/<?php echo $unit->speed_default;?></br>
          </br>
          <?php
          if($unit->health_current == 0)
          {?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="Z">
            <button type="submit" class="btn btn-block btn-danger <?php echo $disabled;?>">Dead!!! Very Sad!!!</button>
            </form>
          <?php
          }
          else {
            if($unit->headliner == 1)
            {?>
              <audio height="32" width="100%" tabindex="0" autoplay="true">
               <source type="audio/mpeg" src="http://crescendiagame.com/songs/<?php echo $unit->song_file;?>"></source>
               Your browser does not support the audio element.
               </audio>
               <?php
            }
            ?>
            <button class="btn btn-primary" data-toggle="collapse" data-target="#demo_<?php echo $unit->slot;?>">Do action!</button>
            <div id="demo_<?php echo $unit->slot;?>" class="collapse">
              <?php
            #begin the close

          echo $unit->action_A_name;?> (<?php echo $unit->action_A_cost;?>): <?php echo $unit->action_A_description;?></br>
          <?php
          if( $unit->action_A_target == "A_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (All Enemies)</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "A_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (All Allies)</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "1_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_0_hidden;?>"><?php echo $enemyunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_1_hidden;?>"><?php echo $enemyunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_2_hidden;?>"><?php echo $enemyunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_3_hidden;?>"><?php echo $enemyunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "1_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_0_hidden;?>"><?php echo $myunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_1_hidden;?>"><?php echo $myunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_2_hidden;?>"><?php echo $myunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_3_hidden;?>"><?php echo $myunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "U_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget Enemy)</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "U_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget Ally)</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "U_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="all">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget)</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "1_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_0_hidden;?>"><?php echo $enemyunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_1_hidden;?>"><?php echo $enemyunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_2_hidden;?>"><?php echo $enemyunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_3_hidden;?>"><?php echo $enemyunit_3_name;?></button>
            </form>

            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_0_hidden;?>"><?php echo $myunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_1_hidden;?>"><?php echo $myunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_2_hidden;?>"><?php echo $myunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_3_hidden;?>"><?php echo $myunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "A_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="all">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast on all units</button>
            </form>
            <?php
          }
          if( $unit->action_A_target == "1_self")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="action_slot" value="A">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast on self</button>
            </form>
            <?php
          }
          ?>
          </br>
          <?php echo $unit->action_B_name;?> (<?php echo $unit->action_B_cost;?>): <?php echo $unit->action_B_description;?></br>
          <?php
          if( $unit->action_B_target == "A_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (All Enemies)</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "A_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (All Allies)</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "1_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_0_hidden;?>"><?php echo $enemyunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_1_hidden;?>"><?php echo $enemyunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_2_hidden;?>"><?php echo $enemyunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_3_hidden;?>"><?php echo $enemyunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "1_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_0_hidden;?>"><?php echo $myunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_1_hidden;?>"><?php echo $myunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_2_hidden;?>"><?php echo $myunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_3_hidden;?>"><?php echo $myunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "U_enemy")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget Enemy)</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "U_ally")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget Ally)</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "U_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="all">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast (Autotarget)</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "1_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_0_hidden;?>"><?php echo $enemyunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_1_hidden;?>"><?php echo $enemyunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_2_hidden;?>"><?php echo $enemyunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$enemyunit_3_hidden;?>"><?php echo $enemyunit_3_name;?></button>
            </form>

            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="0">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_0_hidden;?>"><?php echo $myunit_0_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_1_hidden;?>"><?php echo $myunit_1_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="2">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_2_hidden;?>"><?php echo $myunit_2_name;?></button>
            </form>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="enemy">
            <input class="hidden" name="target_unit" value="3">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled." ".$myunit_3_hidden;?>"><?php echo $myunit_3_name;?></button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "A_all")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="all">
            <input class="hidden" name="target_unit" value="-1">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast on all units</button>
            </form>
            <?php
          }
          if( $unit->action_B_target == "1_self")
          {
            ?>
            <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
            <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
            <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
            <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
            <input class="hidden" name="team" value="<?php echo $myteam;?>">
            <input class="hidden" name="auth" value="<?php echo $authcode;?>">
            <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="target_team" value="ally">
            <input class="hidden" name="target_unit" value="<?php echo $unit->slot;?>">
            <input class="hidden" name="action_slot" value="B">
            <button type="submit" class="btn btn-block btn-info <?php echo $disabled;?>">Cast on self</button>
            </form>
            <?php
          }
          ?>
          </br>
          Basic Attack: Damage an enemy for <?php echo $unit->power_current;?> damage</br>
          <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
          <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
          <input class="hidden" name="team" value="<?php echo $myteam;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
          <input class="hidden" name="target_team" value="enemy">
          <input class="hidden" name="target_unit" value="0">
          <input class="hidden" name="action_slot" value="X">
          <button type="submit" class="btn btn-block btn-danger <?php echo $disabled." ".$enemyunit_0_hidden;?>"><?php echo $enemyunit_0_name;?></button>
          </form>
          <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
          <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
          <input class="hidden" name="team" value="<?php echo $myteam;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
          <input class="hidden" name="target_team" value="enemy">
          <input class="hidden" name="target_unit" value="1">
          <input class="hidden" name="action_slot" value="X">
          <button type="submit" class="btn btn-block btn-danger <?php echo $disabled." ".$enemyunit_1_hidden;?>"><?php echo $enemyunit_1_name;?></button>
          </form>
          <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
          <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
          <input class="hidden" name="team" value="<?php echo $myteam;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
          <input class="hidden" name="target_team" value="enemy">
          <input class="hidden" name="target_unit" value="2">
          <input class="hidden" name="action_slot" value="X">
          <button type="submit" class="btn btn-block btn-danger <?php echo $disabled." ".$enemyunit_2_hidden;?>"><?php echo $enemyunit_2_name;?></button>
          </form>
          <form action="http://crescendiagame.com:8080/battle/add_action_to_queue" method="get">
          <input class="hidden" name="battle_id" value="<?php echo $battle_id;?>">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="turn" value="<?php echo $active_battle_data->current_turn;?>">
          <input class="hidden" name="team" value="<?php echo $myteam;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input class="hidden" name="slot" value="<?php echo $unit->slot;?>">
          <input class="hidden" name="target_team" value="enemy">
          <input class="hidden" name="target_unit" value="3">
          <input class="hidden" name="action_slot" value="X">
          <button type="submit" class="btn btn-block btn-danger <?php echo $disabled." ".$enemyunit_3_hidden;?>"><?php echo $enemyunit_3_name;?></button>
          </form>
          </br>
          </div>
          <?php
        }
        #end the close
        ?></div>
        </div>
          <?php
            }
          }
          ?>
          </div></div>
          <div class = "col-xs-5 offset-xs-1"><div class = "row">
          <?php
          foreach($battle_units AS $unit)
          {
            if($unit->team != $myteam)
            {
              if($unit->health_current < 0)
              {
                $unit->health_current=0;
              }
          ?>
          <div class = "col-xs-3">

          <img <?php if($unit->health_current < 1)
          {
            echo ' style="-webkit-transform: rotate(270deg);
  -moz-transform: rotate(90deg);
  -ms-transform: rotate(90deg);
  -o-transform: rotate(90deg);
  transform: rotate(90deg);
  transform-origin: 50% 80%;
  z-index: -1;
  -webkit-filter: grayscale(80%); /* Safari 6.0 - 9.0 */
    filter: grayscale(80%);""';

          }
          ?> class="thumb_large flip" src="http://crescendiagame.com/images/chars/char_<?php echo $unit->song_id;?>.png"></br>
          <div class="progress"><div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo 100*($unit->health_current/$unit->health_default);?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo 100*($unit->health_current/$unit->health_default);?>%">
          </div></div>
        </br>
      </br>
          <div class="well">
          <?php echo $unit->title;?></br>
          <?php echo $unit->artist;?></br>
          </br>
          Class: <?php echo $unit->class_name;?></br>
          Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $unit->song_key."A";?>.png"></br>
          Health: <?php echo $unit->health_current;?>/<?php echo $unit->health_default;?></br>

          </div>
          </div>

          <?php
            }
          }


          ?></div></div><?php
           ?>
        </div>

        </div>
        <div class="row panel panel-warning">
          <div align="center" class="panel-heading">
            Receipt of actions that happened in the last turn:</br>
            <table class="table"><tbody>
          <?php
          foreach($battle_actions_reciept AS $action_receipt)
          {
            if($action_receipt->summary_code != "pass")
            {echo "<tr><td>".$action_receipt->summary_text."</td><td>".$action_receipt->summary_code."</tr>";}

          }
          ?>
        </tbody></table></div>
        </div>
        </div>
      </br>
</div>
</div>
</body>
</html>
