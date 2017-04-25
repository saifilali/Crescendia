<?php
session_start();
ob_start();
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

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
$squads_list = json_decode(file_get_contents("http://crescendiagame.com:8080/user/show_all_squads?auth=".$authcode."&user_id=".$user_id));
//$battle_random_challenge = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/request_challenge_random?auth=".$authcode."&user_id=".$user_id));
$battle_friend_challenge = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/request_challenge_friend?auth=".$authcode."&user_id=".$user_id));

$battle_request = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/request_check?status=accepted&auth=".$authcode."&user_id=".$user_id));
$battle_request_active = json_decode(file_get_contents("http://crescendiagame.com:8080/battle/request_check?status=active&auth=".$authcode."&user_id=".$user_id));

if(count($battle_request) > 0)
{
    echo '<a class="btn btn-danger btn-block" href="http://crescendiagame.com/user/battle.php">Battle Accepted! Start the fight!</a>';
}

if(count($battle_request_active) > 0)
{
    echo '<a class="btn btn-danger btn-block" href="http://crescendiagame.com/user/battle.php">Battle Already in progress! Get back in!</a>';
}?>


<h3> Available Battle Challenges</h3>
<?php

foreach($battle_friend_challenge AS $friend_challenge)
{
  $friend_info = json_decode(file_get_contents("http://crescendiagame.com:8080/user/get_info_public?auth=".$authcode."&user_id=".$user_id."&query_user_id=".$friend_challenge->user_1_id));
  echo "Your friend ".$friend_info[0]->username." has challenged you to a battle! Deny or Accept!";
  ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js"></script>
  <link rel="stylesheet" href="/css/font-awesome.min.css">
  <form action="http://crescendiagame.com:8080/battle/request_accept" method="get">
    <input class="hidden" name="auth" value="<?php echo $authcode;?>">
    <input class="hidden" name="user_id" value="<?php echo $user_id;?>">
    <input class="hidden" name="battle_id" value="<?php echo $friend_challenge->battle_id;?>">
    1) Select Squad
    <select class="selectpicker" data-width="100%" id="something2" name="squad_id">
      <?php
    foreach($squads_list AS $squad)
    {
      echo '<option value="'.$squad->squad_id.'">'.$squad->name.'</option>';
    }
    ?>
    </select>
  </br></br>
    <input type="submit" name="accept" class="btn btn-success btn-block form-control" value="Start Battle!">
    </br>
    <input type="submit" name="deny" class="btn btn-danger btn-block form-control" value="Deny">


  </form>

  <?php
}
?>
