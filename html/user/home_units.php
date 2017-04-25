<?php
session_start();
ob_start();
#ini_set('display_errors', 'On');
#error_reporting(E_ALL | E_STRICT);

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
        <div class="row panel panel-primary">
          <div class="panel-heading">
          <h3>Add A Song</br></h3>
          </div>
          <div class="panel-body">
          <?php
          if (count($songs_list)<$user_info[0]->units_max)
          {
          ?><form action="http://crescendiagame.com/user/search.php" method="get">
          <input type="text" class="form-control" name="query">
          <input type="submit" class="btn btn-success form-control"  value="Search">
          </form>
          <form action="http://crescendiagame.com/user/search.php" method="get">
          <input class="hidden" name="all" value="true">
          <input type="submit" class="btn btn-info form-control hidden"  value="View All">
          </form><?php
        }
        else{
          ?>You have too many units! Remove some before adding more, or purchase an expansion.<?php
        }
        ?>
          </div>
          </div>


        <div class="row panel panel-default">
        <div class="panel-heading">
        <h1>Your Songs(<?php echo count($songs_list);?>/<?php echo $user_info[0]->units_max;?>)</h1> </br>
        </div><?php
        if(count($songs_list) > 0)
        {
          foreach($songs_list AS $song)
          {
              #http://crescendiagame.com:8080/user/display_unit_moves?song_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
              $actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves?auth=".$authcode."&user_id=".$user_id."&song_id=".$song->id));
              ?>
              <div class="row panel panel-primary">
                <div class="panel-heading"><h3><?php echo $song->song_artist;?> - <?php echo $song->song_title;?></h3></div>
                <div class="panel-body">
                  <div class="col-sm-3">
                    <img  class="thumb_medium" src="http://crescendiagame.com/images/chars/char_<?php echo $song->song_id?>.png">
                   </div><div class="col-sm-9">
                   <div class="col-sm-4"><strong>
                     Class: <?php echo $song->class_name;?></br>
                     Level: <?php echo $song->level;?></br>
                     Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $song->song_key;?>.png"></br>
                     Tempo: <?php echo $song->tempo;?> BPM</br></br></strong>
                     Health: <?php echo $song->health;?></br>
                     Defense: <?php echo $song->defense;?></br>
                     Power: <?php echo $song->power;?></br>
                     Energy: <?php echo $song->energy;?></br>
                     Speed: <?php echo $song->speed;?></br>
                   </div>
                   <div class="col-sm-8">
                   <audio controls="controls" height="32" width="100%" tabindex="0" preload="none">
                    <source type="audio/mpeg" src="http://crescendiagame.com/songs/<?php echo $song->filename;?>"></source>
                    Your browser does not support the audio element.
                    </audio></br>
                    <?php
                    foreach($actions AS $action)
                    {
                      if(($action->cost))
                      {
                      ?>Action: <?php echo $action->name;?>(Cost: <?php echo $action->cost;?>) : </br><?php echo $action->description;?></br></br><?php
                      }
                      else
                      {
                      ?>Passive: <?php echo $action->name;?>: </br><?php echo $action->description;?></br></br><?php
                      }
                    }
                ?>
                   </div>
                 </div>
                 </div>
                 <div class="panel-footer">
                 <a class="btn btn-info" href="http://crescendiagame.com/user/unit_config.php?song_id=<?php echo $song->id;?>">Configure Unit</a>
                 <a class="btn btn-danger" href="http://crescendiagame.com:8080/user/delete_unit?song_id=<?php echo $song->id;?>&user_id=<?php echo $user_id;?>&auth=<?php echo $authcode;?>">Remove</a>
                 </div>
               </div>
               </br><?php
          }
        }
        else {
          # code...
        }
        ?>
</div>
</div>
</div>
</div>
</body>
</html>
