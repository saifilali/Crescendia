<!DOCTYPE html>
<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);
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
$authcode = $_SESSION["authcode"];
$user_id = $_SESSION['user'];
$result = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_unit?auth=".$authcode."&user_id=".$user_id."&song_id=".$_GET['song_id']));
?>

<html lang="en">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css">

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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js"></script>
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
      <li class="active"><a href="#">Unit Configuration</a></li>
    </ul>
  </div>
</nav>
      <div class="container-fluid center-block" style="width:100%">
        <div class="row center-block" style="max-width:1200px">
        </br>
        <?php
        if($_GET['comment']=="not_enough_actions")
        {?>
        <div class="alert alert-danger">
          <strong><center>Not enough actions selected!!!</center></strong>
        </div>
        <?php
          }
        if($_GET['comment']=="not_enough_platinum")
        {?>
        <div class="alert alert-danger">
          <strong><center>You don\'t have enough platinum to instantly level up!</center></strong>
        </div>
        <?php
        }
        if($_GET['comment']=="leveled_up")
        {?>
        <div class="alert alert-success">
          <strong><center>You have successfully instantly leveled up your unit!</center></strong>
        </div>
        <?php
        }

        foreach($result AS $song)
        {
            #http://crescendiagame.com:8080/user/display_unit_moves?song_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
            $actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves_all?auth=".$authcode."&user_id=".$user_id."&song_id=".$song->id));
            echo
            '
            <div class="row panel panel-primary">
              <div class="panel-heading"><h3>'.$song->song_artist.' - '.$song->song_title.'</h3></div>
              <div class="panel-body">
                <div class="col-sm-3">
                  <img class="thumb_large" src="http://crescendiagame.com/images/chars/char_'.$song->id.'.png">
                  </br><strong>
                   Class: '.$song->class_name.'</br>
                   Level: '.$song->level.'</br>
                   Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/'.$song->song_key.'.png"></br>
                   Tempo: '.$song->tempo.' BPM</br></br></strong>
                   Health: '.$song->health.'</br>
                   Defense: '.$song->defense.'</br>
                   Power: '.$song->power.'</br>
                   Energy: '.$song->energy.'</br>
                   Speed: '.$song->speed.'</br>
                 </div>
                 <div class="col-sm-9">
                 <audio controls="controls" height="32" width="100%" tabindex="0" preload="none">
                  <source type="audio/mpeg" src="http://crescendiagame.com/songs/'.$song->filename.'"></source>
                  Your browser does not support the audio element.
                  </audio></br>
                  ';
                  foreach($actions AS $action)
                  {
                    if(($action->cost))
                    {
                      if(($action->id == $song->action_A) or($action->id == $song->action_B))
                      {
                        echo '<b>Action (Selected): '.$action->name.'(Cost: '.$action->cost.') : </br>'.$action->description.'</b></br></br>';
                      }
                    }
                    else
                    {
                    echo '<b>Passive: '.$action->name.': </br>'.$action->description.'</b></br></br>';
                    }
                  }

                  foreach($actions AS $action)
                  {
                    if(($action->cost))
                    {
                      if(($action->id == $song->action_A) or($action->id == $song->action_B))
                      {

                      }
                      else {
                        echo 'Action (Unselected): '.$action->name.' (Cost: '.$action->cost.') : </br>'.$action->description.'</br></br>';
                      }
                    }
                  }
                  if($_GET["configure"] == "true")
                  {
              echo '



                    <form action="http://crescendiagame.com:8080/user/set_actions_unit" method="get">
                      <input class="hidden" name="user_id" value="'.$user_id.'">
                      <input class="hidden" name="auth" value="'.$authcode.'">
                      <input class="hidden" name="song_id" value="'.$_GET["song_id"].'">
                      <input class="hidden" name="go_home" value="true"">
                      <select class="selectpicker" multiple data-max-options="2" data-width="100%" id="actions" name="actions_selected">';
                      foreach($actions AS $action)
                      {
                        if(($action->cost))
                        {
                        echo '<option value="'.$action->id.'">'.$action->name.'</option>';
                        }
                      }
                      echo'
                              </select>
                        <input type="submit" class="btn btn-success btn-block form-control"  value="Select TWO Actions & Create (Pick 2 or else it breaks!)">
                    </form>

                    ';
                      echo '<a class="btn btn-danger btn-block" href="http://crescendiagame.com:8080/user/delete_unit?song_id='.$song->id.'&user_id='.$user_id.'&auth='.$authcode.'">Cancel</a>
                      ';
                      if($song->level < 5)
                      {
                        echo'
                      <a class="btn btn-info btn-block" href="http://crescendiagame.com:8080/user/level_unit?song_id='.$song->id.'&user_id='.$user_id.'&auth='.$authcode.'">Instant Max Level Up! (Costs 10 Platinum)</a>';
                    }
                    }
                    else {
                      echo '
                            <form action="http://crescendiagame.com:8080/user/set_actions_unit" method="get">
                              <input class="hidden" name="user_id" value="'.$user_id.'">
                              <input class="hidden" name="auth" value="'.$authcode.'">
                              <input class="hidden" name="song_id" value="'.$_GET["song_id"].'">
                              <input class="hidden" name="go_home" value="false">
                              <select class="selectpicker" multiple data-max-options="2" data-width="100%" id="actions" name="actions_selected">';
                              foreach($actions AS $action)
                              {
                                if(($action->cost))
                                {
                                echo '<option value="'.$action->id.'">'.$action->name.'</option>';
                                }
                              }
                              echo'
                                      </select>
                                <input type="submit" class="btn btn-success btn-block form-control"  value="Select Actions (Pick 2 or else it breaks!)">
                            </form>

                            ';
                      echo '<a class="btn btn-danger btn-block" href="http://crescendiagame.com:8080/user/delete_unit?song_id='.$song->id.'&user_id='.$user_id.'&auth='.$authcode.'">Remove</a></br>';
                      if($song->level < 5)
                        {
                          echo'<a class="btn btn-info btn-block" href="http://crescendiagame.com:8080/user/level_unit?song_id='.$song->id.'&user_id='.$user_id.'&auth='.$authcode.'">Instant Max Level Up! (Costs 10 Platinum)</a>';
                        }
                      }
              echo '
                 </div>
               </div>
               </div>

             </div>
             </br>'
             ;
        }
        ?>
</div>
</div>
</body>
</html>
