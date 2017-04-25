<?php
session_start();
ob_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

if( !isset($_SESSION['user']) ) {
 header("Location: ./index.php");
 exit;
}
if( !isset($_SESSION['authcode']) ) {
 header("Location: ./index.php");
 exit;
}
$authcode = $_SESSION["authcode"];
$user_id = $_SESSION['user'];
if(isset($_GET['comment']))
{
  if($_GET['comment'] == "processing")
  {
    $warnMSG = "That song is now processing! Check the search again in a couple minutes, the song should be there";
  }
}
$user_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info?auth=".$authcode."&user_id=".$user_id));
$songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_units?auth=".$authcode."&user_id=".$user_id));
$squads_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_squads?auth=".$authcode."&user_id=".$user_id));
$friends_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_friends?auth=".$authcode."&user_id=".$user_id));

foreach($friends_list as $friend)
{
  if ($friend->user_2_id ==$user_id AND $friend->request_status == "pending")
  {
    $warnMSG = "You have pending friend requests!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <style type="text/css">
    html,body {
    background: url("http://crescendiagame.com/bg.png") center center fixed;
    -webkit-background-size: cover; /* For WebKit*/
    -moz-background-size: cover;    /* Mozilla*/
    -o-background-size: cover;      /* Opera*/
    background-size: cover;         /* Generic*/
    }
    </style>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js"></script>
        <link rel="stylesheet" href="/css/font-awesome.min.css">
    </head>
    <body>
      <nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Crescendia Prototype</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="http://crescendiagame.com/user/home.php">User Home</a></li>
      <li><a href="http://crescendiagame.com/user/logout.php?logout">Log Out!</a></li>
    </ul>
  </div>
</nav>

      <div class="container-fluid center-block"  style="width:100%">
        <div class="row center-block" style="max-width:1200px">
        <?php
        if ( isset($succMSG) ) {
         ?>
         <div class="form-group">
                  <div class="alert alert-success">
         <span class="glyphicon glyphicon-info-sign"></span> <?php echo $succMSG; ?>
                     </div>
                  </div>
                     <?php
        }?>
        <?php
        if ( isset($warnMSG) ) {

         ?>
         <div class="form-group">
                  <div class="alert alert-warning">
         <span class="glyphicon glyphicon-info-sign"></span> <?php echo $warnMSG; ?>
                     </div>
                  </div>
                     <?php
        }?>
        <?php
        if ( isset($errMSG) ) {

         ?>
         <div class="form-group">
                  <div class="alert alert-danger">
         <span class="glyphicon glyphicon-info-sign"></span> <?php echo $errMSG; ?>
                     </div>
                  </div>
                     <?php
        }?>


        </br>
        <div class="row">
        <div class="col-sm-12">
          <div class="row">
                    <div class="col-xs-5">
                <div class="row">
                    <div class="col-xs-4">
                    <a class="btn btn-success btn-block btn-lg" href="http://crescendiagame.com/user/home_main.php">Home</a>
                    </div>
                    <div class="col-xs-4">
                    <a class="btn btn-primary btn-block btn-lg" href="http://crescendiagame.com/user/home_units.php">Songs</a>
                    </div>
                    <div class="col-xs-4">
                    <a class="btn btn-primary btn-block btn-lg" href="http://crescendiagame.com/user/home_squad.php">Squads</a>
                    </div>
              </div>
          </div>
          <div class="col-xs-2">
          <div class="row">
          <div class="col-xs-12">
                      <a class="btn btn-danger btn-block btn-lg" href="http://crescendiagame.com/user/home_battle.php">Battle</a>
          </div>
          </div>
          </div>

          <div class="col-xs-5">
                <div class="row">
                    <div class="col-xs-4">
                                <a class="btn btn-primary btn-block btn-lg" href="http://crescendiagame.com/user/home_guild.php">Label</a>
                    </div>
                    <div class="col-xs-4">
                                <a class="btn btn-primary btn-block btn-lg" href="http://crescendiagame.com/user/home_friends.php">Friends</a>
                    </div>
                    <div class="col-xs-4">
                                <a class="btn btn-info btn-block btn-lg" href="http://crescendiagame.com/user/home_settings.php">Settings</a>
                    </div>
              </div>
          </div>
          </div>
        <div class="row panel panel-default">
        <div class="panel-heading">
        <h1>Your Squads(<?php echo count($squads_list);?>/<?php echo $user_info[0]->squads_max;?>)</h1> </br>
        </div>
        <div class="panel-body">
        <?php

        if(count($squads_list) > 0)
        {
          foreach($squads_list AS $squad)
          {
              #http://crescendiagame.com:8080/user/display_unit_moves?squad_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
              #$actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves_all?auth=".$authcode."&user_id=".$user_id."&squad_id=".$squad->id));
              ?>
              <div class="row panel panel-primary">
                <div class="panel-heading">
                  <div class="row">
                    <div class="col-xs-6">
                   <h3><?php echo $squad->name;?></br></h3>
                 </div>
                 <div class="col-xs-3"></br>
                Average Level: <?php echo $squad->average_level;?></br>
                  </div>
                  <div class="col-xs-3"></br>
                     <?php echo $squad->harmony_index_string;?></br>
                   </div>
                 </div>
                   <a class="btn btn-info btn-block" href="http://crescendiagame.com/user/squad_config.php?squad_id=<?php echo $squad->squad_id;?>">Configure Squad</a>
                   <a class="btn btn-danger btn-block" href="http://crescendiagame.com:8080/user/delete_squad?squad_id=<?php echo $squad->squad_id;?>&user_id=<?php echo $user_id;?>&auth=<?php echo $authcode;?>">Remove Squad</a>
                </div>
                <div class="panel-body">
                </div>
                <div class="row">
                  <?php
                for ($i = 0; $i <= 3; $i++) {
                  if($i == 0){
                    $song_id_lookup = $squad->song_0_id;
                  }
                  if($i == 1){
                    $song_id_lookup = $squad->song_1_id;
                  }
                  if($i == 2){
                    $song_id_lookup = $squad->song_2_id;
                  }
                  if($i == 3){
                    $song_id_lookup = $squad->song_3_id;
                  }
                  $display_title = "Title";
                  foreach($songs_list AS $song)
                  {
                    if ($song->id == $song_id_lookup)
                    {
                      $display_title = $song->song_title;
                      $display_class = $song->class_name;
                      $display_key = $song->song_key;
                      $display_tempo = $song->tempo;
                      $display_id = $song->id;
                    }
                  }
                  if(is_null($song_id_lookup))
                  {
                    $display_title = "Not Selected";
                    $display_class = " ";
                    $display_key = " ";
                    $display_tempo = " ";
                  }
                ?>
                <div class="col-sm-3">
                <?php
                if($squad->headliner == $i)
                {
                  ?><div class="panel panel-primary">
                  <div class="panel-heading"><?php echo $display_title;?></div>
                  <div class="panel-body">
                  <img  class="thumb_medium" src="http://crescendiagame.com/images/chars/char_<?php echo $display_id;?>.png"></br>
                  Class: <?php echo $display_class;?></br>
                  Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $display_key;?>.png"></br>
                  Tempo: <?php echo $display_tempo;?></br>
                  </div><?php
                }
                else
                {
                  ?><div class="panel panel-def">
                  <div class="panel-heading"><?php echo $display_title;?></div>
                  <div class="panel-body">
                  <img  class="thumb_medium" src="http://crescendiagame.com/images/chars/char_<?php echo $display_id;?>.png"></br>
                  Class: <?php echo $display_class;?></br>
                  Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $display_key;?>.png"></br>
                  Tempo: <?php echo $display_tempo;?></br>
                  </div><?php
                }
                ?>
                  </div>


                </div><?php
              }
                ?>
                </div>
                </div>
                 <?php

        }
        }
        else {
          if(count($songs_list) < 4)
          {
            ?><center>Hey, it looks like you are new here. You need songs before making a squad so get 4 or more songs first!</center><?php
          }
          else {
            ?><center><a class="btn btn-warning btn-block" href="http://crescendiagame.com:8080/user/add_squad?user_id=<?php echo $user_id;?>&auth=<?php echo $authcode;?>">Make a squad</a></center><?php
          }
        }
        if(0 < count($squads_list) and count($squads_list) < 3)
        {
          ?><a class="btn btn-warning btn-block" href="http://crescendiagame.com:8080/user/add_squad?user_id=<?php echo $user_id;?>&auth=<?php echo $authcode;?>">Make a squad</a><?php
        }
        ?>
        </div>
        </div>

</div>
</div>
</div>
</body>
</html>
