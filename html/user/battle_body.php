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
$authcode = $_SESSION["authcode"];
$user_id = $_SESSION['user'];

 ?>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="./css/font-awesome.min.css">
        <div id = "battle_body" class="row center-block" style="max-width:1200px">
          <?php
          $battle_data = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/get_battle_info?auth=".$authcode."&user_id=".$user_id));
          if($user_id == $battle_data->user_1_id)
          {
            $top_user_id = $battle_data->user_1_id;
            $top_squad_id = $battle_data->user_1_squad;
            $bottom_user_id = $battle_data->user_2_id;
            $bottom_squad_id = $battle_data->user_2_squad;
          }
          else
          {
            $top_user_id = $battle_data->user_2_id;
            $top_squad_id = $battle_data->user_2_squad;
            $bottom_user_id = $battle_data->user_1_id;
            $bottom_squad_id = $battle_data->user_1_squad;
          }
          $top_songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_units?auth=".$authcode."&user_id=".$user_id."&user_query_id=".$top_user_id));
          $bottom_songs_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_units?auth=".$authcode."&user_id=".$user_id."&user_query_id=".$bottom_user_id));
          $top_squad = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_squad?auth=".$authcode."&user_id=".$user_id."&squad_id=".$top_squad_id));
          $bottom_squad = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_squad?auth=".$authcode."&user_id=".$user_id."&squad_id=".$bottom_squad_id));

            if(count($top_squad) > 0)
            {
              foreach($top_squad AS $squad)
              {
                  #http://crescendiagame.com:8080/user/display_unit_moves?squad_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
                  #$actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves_all?auth=".$authcode."&user_id=".$user_id."&squad_id=".$squad->id));
                  ?>
                  <div class="row panel panel-primary">
                    <div class="panel-heading"><h3>
                      <div align="left"><div class="row">
                        <div class="col-xs-6">
                       <h3><?php
                        $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$top_user_id));
                        echo $friend_info[0]->username;
                        ?> - <?php echo $squad->name;?></br></h3>
                     </div>
                     <div class="col-xs-2"></br>
                    Average Level: <?php echo $squad->average_level;?></br>
                      </div>
                      <div class="col-xs-2"></br>
                         <?php echo $squad->harmony_index_string;?></br>
                       </div>
                       <div class="col-xs-2"></br>
                          Rating: <?php echo $squad->strength_index;?></br>
                        </div>
                     </div></div>
                    </h3></div>
                    <div class="panel-body">

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
                      foreach($top_songs_list AS $song)
                      {
                        if ($song->id == $song_id_lookup)
                        {
                          $display_title = $song->song_title;
                          $display_class = $song->class_name;
                          $display_key = $song->song_key;
                          $display_tempo = $song->tempo;
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
                      <img  class="thumb_small src="http://crescendiagame.com/images/chars/char_<?php echo $song->id;?>.png"></br>
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
                      <img  class="thumb_small src="http://crescendiagame.com/images/chars/char_<?php echo $song->id;?>.png"></br>
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
                    </div>
                     <?php

            }
            }
            ?>

                <?php
                  if($battle_data->status == "finished" AND $battle_data->winner == $user_id)
                  {
                ?>
                <div class="row panel panel-success">
                  <div align="center" class="panel-heading"><h1>
                  The Battle Is Won!!</h1></div>
                </div>
                <?php }
                else if($battle_data->status == "finished" AND $battle_data->winner != $user_id)
                {
              ?>
              <div class="row panel panel-danger">
                <div align="center" class="panel-heading"><h1>
                The Battle Is Lost!!</h1></div>
              </div>
              <?php }
                else if($battle_data->status == "accepted"){
                  ?>
                  <div class="row panel panel-warning">
                    <div align="center" class="panel-heading"><h1>
                    Battle will start soon!</h1></div>
                  </div>
                  <?php
                } ?>

            <?php
            if(count($bottom_squad) > 0)
            {
              foreach($bottom_squad AS $squad)
              {
                  #http://crescendiagame.com:8080/user/display_unit_moves?squad_id=23&user_id=16&auth=2526459703ee2c4da8744dae5789d5ab
                  #$actions = json_decode(file_get_contents("http://crescendiagame.com:8080/user/display_unit_moves_all?auth=".$authcode."&user_id=".$user_id."&squad_id=".$squad->id));
                  ?>
                  <div class="row panel panel-primary">
                    <div class="panel-heading"><h3>
                       <div align="left"><div class="row">
                         <div class="col-xs-6">
                        <h3><?php
                         $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$bottom_user_id));
                         echo $friend_info[0]->username;
                         ?> - <?php echo $squad->name;?></br></h3>
                        </div>

                      <div class="col-xs-2"></br>
                     Average Level: <?php echo $squad->average_level;?></br>
                       </div>
                       <div class="col-xs-2"></br>
                          <?php echo $squad->harmony_index_string;?></br>
                        </div>

                        <div class="col-xs-2"></br>
                           Rating: <?php echo $squad->strength_index;?></br>
                         </div>
                      </div></div>
                    </h3></div>
                    <div class="panel-body">

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
                      foreach($bottom_songs_list AS $song)
                      {
                        if ($song->id == $song_id_lookup)
                        {
                          $display_title = $song->song_title;
                          $display_class = $song->class_name;
                          $display_key = $song->song_key;
                          $display_tempo = $song->tempo;
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
                      <img class="thumb_small flip" src="http://crescendiagame.com/images/chars/char_<?php echo $song->id;?>.png"></br>
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
                      <img  class="thumb_small flip" src="http://crescendiagame.com/images/chars/char_<?php echo $song->id;?>.png"></br>
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
                    </div>
                     <?php

            }
            }
           ?>
