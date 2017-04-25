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

$songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_units?auth=".$authcode."&user_id=".$user_id));
$squads_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_squad?auth=".$authcode."&user_id=".$user_id."&squad_id=".$_GET['squad_id']));
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
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
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
    <li><a href="http://crescendiagame.com/user/home.php">User Home</a></li>
      <li class="active"><a href="#">Unit Configuration</a></li>
    </ul>
  </div>
</nav>
      <div class="container-fluid center-block" style="width:100%">
        <div class="row center-block" style="max-width:1200px">
          <?php
        if($_GET['comment']=="duplicate_unit")
        {
          ?>
        <div class="alert alert-danger">
          <strong><center>Duplicate unit!</center></strong>
        </div>
        <?php
      }


        foreach($squads_list AS $squad)
        {
            #http://crescendiagame.com:8080/user/display_unit_moves?squad_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
            #$actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves_all?auth=".$authcode."&user_id=".$user_id."&squad_id=".$squad->id));
            ?>
            <div class="row panel panel-primary">
              <div class="panel-heading"><h3>
                <div class="row">
                  <div class="col-xs-6">
                 <h3><?php echo $squad->name;?></br></h3>
                 <form action="http://crescendiagame.com:8080/user/squad_set_name" method="get">
                    <input type="text" class="form-control" name="squad_name">
                    <input class="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input class="hidden" name="auth" value="<?php echo $authcode; ?>">
                    <input class="hidden" name="squad_id" value="<?php echo $_GET["squad_id"]; ?>">
                    <input class="hidden" name="go_home" value="false">
                    <input type="submit" class="btn btn-info form-control"  value="change name">
                 </form>
               </div>
               <div class="col-xs-3"></br>
              Average Level: <?php echo $squad->average_level;?></br>
                </div>
                <div class="col-xs-3"></br>
                   <?php echo $squad->harmony_index_string;?></br>
                 </div>
               </div>

              </h3></div>
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
                    $actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves?auth=".$authcode."&user_id=".$user_id."&song_id=".$song->id));
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
                ?>
                <div class="panel panel-primary">
                <div class="panel-heading"><h3><?php echo $display_title; ?></h3></div>
                <div class="panel-body">
                <img class="thumb_large" src="http://crescendiagame.com/images/chars/char_<?php echo $display_id; ?>.png"></br>
                Class: <?php echo $display_class; ?></br>
                Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $display_key;?>.png"></br>
                Tempo: <?php echo $display_tempo; ?></br></br>
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
            <a class="btn btn-info btn-block" href="http://crescendiagame.com/user/unit_config.php?song_id=<?php echo $song->id;?>">Configure Unit</a>
                <a class="btn btn-primary disabled btn-block" href="#">Already Headliner</a></br>
                <form action="http://crescendiagame.com:8080/user/squad_set_unit" method="get">
                  <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
                  <input class="hidden" name="auth" value="<?php echo $authcode;?>">
                  <input class="hidden" name="squad_id" value="<?php echo $_GET["squad_id"];?>">
                  <input class="hidden" name="go_home" value="false">
                  <input class="hidden" name="slot" value="<?php echo $i;?>">
                  <select class="selectpicker" data-width="100%" id="something" name="song_id">
                    <?php
                  foreach($songs_list AS $song)
                  {

                    echo '<option value="'.$song->song_id.'">'.$song->song_key." ".$song->tempo."BPM: ".$song->song_title.'</option>';

                  }
                  ?>
                  </select>
                    <input type="submit" class="btn btn-info btn-block form-control"  value="Select Song">
                </form>

                </div>
                <?php
              }
              else
              {
                ?>
                <div class="panel panel-def">
                <div class="panel-heading"><h3><?php echo $display_title; ?></h3></div>
                <div class="panel-body">
                  <img class="thumb_large" src="http://crescendiagame.com/images/chars/char_<?php echo $display_id; ?>.png"></br>
                  Class: <?php echo $display_class; ?></br>
                  Key: <img width="30px" height="30px" src="http://crescendiagame.com/images/keys/<?php echo $display_key;?>.png"></br>
                  Tempo: <?php echo $display_tempo; ?></br></br>
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
              <a class="btn btn-info btn-block" href="http://crescendiagame.com/user/unit_config.php?song_id=<?php echo $song->id;?>">Configure Unit</a>
                <a class="btn btn-primary btn-block" href="http://crescendiagame.com:8080/user/squad_choose_headliner?headliner=<?php echo $i.'&squad_id='.$_GET["squad_id"].'&user_id='.$user_id.'&auth='.$authcode ?>">Make Headliner</a></br>

                <form action="http://crescendiagame.com:8080/user/squad_set_unit" method="get">
                  <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
                  <input class="hidden" name="auth" value="<?php echo $authcode;?>">
                  <input class="hidden" name="squad_id" value="<?php echo $_GET["squad_id"];?>">
                  <input class="hidden" name="go_home" value="false">
                  <input class="hidden" name="slot" value="<?php echo $i;?>">
                  <select class="selectpicker" data-width="100%" id="something" name="song_id">
                    <?php
                  foreach($songs_list AS $song)
                  {
                    echo '<option value="'.$song->song_id.'">'.$song->song_key." ".$song->tempo."BPM: ".$song->song_title.'</option>';
                  }
                  ?>
                  </select>
                    <input type="submit" class="btn btn-info btn-block form-control"  value="Select Song">
                </form>

                </div>

              <?php }?>
                </div>
              </div>'
            <?php }?>

              </div>
              </div>
               </div>
             <?php }?>
</div>
</div>
</body>
</html>
