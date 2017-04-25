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
$user_songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_units?auth=".$authcode."&user_id=".$user_id));
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
      <li class="active"><a href="#">Song Search</a></li>
    </ul>
  </div>
</nav>

      <div class="container-fluid center-block" style="width:100%">
        <div class="row center-block" style="max-width:1200px">
          <div class="alert alert-warning">
        <h2>Search Results for: "<?php echo $_GET["query"]; ?>"</h2>
      </div>
      </br><?php

        if($_GET["all"] == "true")
        {
          $songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/units/show_all"));
        }
        else {
          $songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/units/search?query=".urlencode($_GET["query"])));
        }
        $user_already_owned = array();
        foreach($user_songs_list AS $user_song)
        {
          array_push($user_already_owned, $user_song->id);
        }
        foreach($songs_list AS $song)
        {
            ?>
            <div class="row panel panel-primary">
              <div class="panel-heading"><h3><?php echo $song->song_artist.' - '.$song->song_title;?></h3></div>
              <div class="panel-body">
                <div class="col-sm-3">
                  <img class="thumb_large" src="http://crescendiagame.com/images/chars/char_<?php echo $song->id;?>.png">
                 </div><div class="col-sm-9">
                 <div class="col-sm-3">
                   Class: <?php echo $song->class_name; ?></br>
                   Health: <?php echo $song->health; ?></br>
                   Defense: <?php echo $song->defense; ?></br>
                   Power: <?php echo $song->power; ?></br>
                   Energy: <?php echo $song->energy; ?></br>
                   Speed: <?php echo $song->speed; ?></br>

                 </div>
                 <div class="col-sm-7">
                    <h4>Key</h4></br>
                   <img width="80px" height="80px" src="http://crescendiagame.com/images/keys/<?php echo $song->song_key;?>.png"></br></br>
                   <h4><?php echo $song->tempo; ?> BPM</h4>
                   <audio controls="controls" height="32" width="100%" tabindex="0" preload="none">
                    <source type="audio/mpeg" src="http://crescendiagame.com/songs/<?php echo $song->filename;?>"></source>
                    Your browser does not support the audio element.
                    </audio></br>
                 </div>
                 <div class="col-sm-2"><?php
                 if(in_array($song->id,$user_already_owned)){
                 ?><a class="btn btn pull-right disabled btn-default" href="#">Already In Collection</a><?php
               }
               else {
                 ?><a class="btn btn pull-right btn-warning" href="http://crescendiagame.com:8080/user/add_unit?configure=true&song_id=<?php echo $song->id.'&user_id='.$user_id.'&auth='.$authcode;?>">Add to collection</a><?php
               }
                 ?>
                 </div>
               </div>
               </div>
             </div>
           </br><?php
        }
        ?>
        <a class="btn btn-block btn-info" href="http://crescendiagame.com/user/soundcloudsearch.php?query=<?php echo $_GET["query"];?>">Couldn't find your song? Try SoundCloud</a>
</div>
</div>
</body>
</html>
