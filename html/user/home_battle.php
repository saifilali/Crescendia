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
