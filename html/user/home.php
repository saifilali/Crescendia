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
header( 'Location: http://crescendiagame.com/user/home_main.php' ) ;
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
        <div class="col-sm-3">
        <div class="row panel panel-primary">
        <div class="panel-heading"><h3>
        Your account</br>
        </h3></div>
        <div class="panel-body">
        Username: <?php echo $user_info[0]->username;?></br>
        Email Address: <?php echo $user_info[0]->email;?></br>
        </br>
        Battles Played: <?php echo $user_info[0]->battles_total;?></br>
        Battles Won: <?php echo $user_info[0]->battles_won;?></br>
        </br>
        Gold: <?php echo $user_info[0]->money_earned;?></br>
        Platinum: <?php echo $user_info[0]->money_bought;?></br>
        </br>
        User ID Number: <?php echo $user_info[0]->user_id;?></br>
        Date Joined: <?php echo $user_info[0]->created_time;?></br>
        Last Login: <?php echo $user_info[0]->last_login_time;?></br>
        Total Logins: <?php echo $user_info[0]->login_number;?></br>
        </br>
        Maximum Unit Capacity: <?php echo $user_info[0]->units_max;?></br>
        Maximum Squad Capacity: <?php echo $user_info[0]->squads_max;?></br>
        </br>
        Waifu Unit: <?php echo $user_info[0]->waifu;?></br>
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
          <input type="submit" class="btn btn-info form-control"  value="View All">
          </form><?php
        }
        else{
          ?>You have too many units! Remove some before adding more, or purchase an expansion.<?php
        }
        ?>
          </div>
        </div>

        <div class="row panel panel-primary">
          <div class="panel-heading">
          <h3>Friends</br></h3>
          </div>
          <div class="panel-body">
          <h3> Pending Requests </h3>
          <?php
          foreach($friends_list as $friend)
          {
            if ($friend->user_2_id ==$user_id AND $friend->request_status == "pending")
            {

              $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$friend->user_1_id));
              ?>
              <div class="row">
                <div class="col-xs-7">

              <a href="#" class="btn btn-link" role="button"><?php echo $friend_info[0]->username;?></a>
              </div>
              <div class="col-xs-5">
                <a href="http://crescendiagame.com:8080/user/friend_action?user_id=<?php echo $friend_info[0]->user_id.'&friend_id='.$user_id.'&auth='.$authcode.'&action=accept'?>" class="btn btn-link" role="button"><i class="fa fa-check" aria-hidden="true"></i></a>
                <a href="http://crescendiagame.com:8080/user/friend_action?user_id=<?php echo $friend_info[0]->user_id.'&friend_id='.$user_id.'&auth='.$authcode.'&action=reject'?>" class="btn btn-link" role="button"><i class="fa fa-ban" aria-hidden="true"></i></a>
              </div>
            </div>
            <?php
            }
          }
          ?>
          <h3> Friends List </h3>
          <?php
          foreach($friends_list as $friend)
          {
            if ($friend->request_status == "accept")
            {
              $query_user_id = $friend->user_1_id;
              if($friend->user_1_id == $user_id)
              {
                $query_user_id = $friend->user_2_id;
              }
              $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$query_user_id));
              ?>
              <div class="row">
                <div class="col-sm-7">
              <a href="#" class="btn btn-link" role="button"><?php echo $friend_info[0]->username;?></a>
              </div>
              <div class = "col-sm-5">
                <a href="#" class="btn btn-link hidden" role="button"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Battle!</a>
              </div>
            </div>
              <?php
            }
          }
          ?>
          <h3> Search Friend </h3>
          <form action="http://crescendiagame.com/user/friendsearch.php" method="get">
          <input type="text" class="form-control" name="query">
          <input type="submit" class="btn btn-success form-control"  value="Search">
          </form>
          </div>
        </div>

        <div class="row panel panel-primary">
          <div class="panel-heading">
          <h3>Record Label</br></h3>
          </div>
          <div class="panel-body">
          <h3> My Record Label </h3>
          <h3> Search Record Labels </h3>
          <form action="http://crescendiagame.com/user/guildsearch.php" method="get">
          <input type="text" class="form-control" name="query">
          <input type="submit" class="btn btn-success form-control"  value="Search">
          </form>
          </div>
        </div>

        <div class="row panel panel-primary">
          <div class="panel-heading">
          <h3>Debug Things</br></h3>
          </div>
          <div class="panel-body">
          <form action="http://crescendiagame.com:8080/user/add_gold?" method="get">
          <input type="text" class="form-control" name="gold">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input type="submit" class="btn btn-warning form-control"  value="Add Gold">
          </form>
          <form action="http://crescendiagame.com:8080/user/add_platinum?" method="get">
          <input type="text" class="form-control" name="platinum">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input type="submit" class="btn btn-info form-control"  value="Add Platinum">
          </form>
          <form action="http://crescendiagame.com:8080/user/buy_slot?" method="get">
          <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
          <input class="hidden" name="auth" value="<?php echo $authcode;?>">
          <input type="submit" class="btn btn-warning form-control"  value="Buy 5 Slots!">
          </form>
          </div>
        </div>

        </div>
        <div class="col-sm-1"></div>
        <div class="col-sm-8">

          <div class="row panel panel-default">
          <div class="panel-heading">
          <h1>Battle!</h1> </br>
          </div>
          <div class="panel-body">
          <div id="battle_header">
          REFRESH IN HERE
          </div>
          </br>
          <h3> Start a Battle Request</h3>
          <?php
          $battle_request = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/request_check?status=open&auth=".$authcode."&user_id=".$user_id));
          if(count($battle_request) == 0)
          {
          ?>



            <form action="http://crescendiagame.com:8080/battle/request_add" method="get">
              <input class="hidden" name="auth" value="<?php echo $authcode;?>">
              <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
              1) Select Squad
              <select class="selectpicker" data-width="100%" id="something" name="squad_id">
                <?php
              foreach($squads_list AS $squad)
              {
                echo '<option value="'.$squad->squad_id.'">'.$squad->name.'</option>';
              }
              ?>
              </select>

              2) Select who to battle

              <input type="button" class="btn btn-warning btn-block" data-toggle="collapse" data-target="#bots" value="Bots">
              <div id="bots" class="collapse text-center">
              <?php echo $companyRow[0]?>
              <input type="submit" name="bots" class="btn btn-danger btn-block form-control" value="Fight Bots!">
              </div>
              </br>
              <input type="button" class="btn btn-warning btn-block" data-toggle="collapse" data-target="#friends" value="Friends">
              <div id="friends" class="collapse text-center">
                <select class="selectpicker" data-width="100%" id="something" name="friend_id">
                  <?php

                  foreach($friends_list as $friend)
                  {
                    if ($friend->request_status == "accept")
                    {
                      $query_user_id = $friend->user_1_id;
                      if($friend->user_1_id == $user_id)
                      {
                        $query_user_id = $friend->user_2_id;
                      }
                      $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$query_user_id));
                      echo '<option value="'.$query_user_id.'">'.$friend_info[0]->username.'</option>';
                    }
                  }
                ?>
                </select>
              <input type="submit" name="friend" class="btn btn-danger btn-block form-control" value="Challenge Friend!">
              </div>
              </br>
              <input type="button" class="btn btn-warning btn-block" data-toggle="collapse" data-target="#random" value="Random">
              <div id="random" class="collapse text-center">
              <?php echo $companyRow[0]?>
              <input type="submit" name="random" class="btn btn-danger btn-block form-control" value="Find A Battle!">
              </div>


            </form>


        <?php
          }
          else {
            ?>
            You already have a battle request started!
            <?php
          }
           ?>
           </div>
           </div>
      </br>
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
<script language="javascript" type="text/javascript">
function autoRefresh_div() {
    $("#battle_header").load("battle_header.php", function() {
        setTimeout(autoRefresh_div, 50000);
    });
}

autoRefresh_div();
</script>

</html>
