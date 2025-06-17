<?php 
if(is_user_logged_in()){
  return;
}
?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<div class="buttonDiv">
</div>
<?php 
// if(!session_id()) {
//   session_start();
// }
// No need for the button is the user is already logged
// $html="";
// if(is_user_logged_in())
//   return;
// if(!isset($_SESSION['tth_google_url']))
//   $_SESSION['tth_google_url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
// if (get_option( 'users_can_register' )) {
//   $button_label = TravelHelper::getNewIcon('g+', '', '100%');
// } else {
//   $button_label = TravelHelper::getNewIcon('g+', '', '100%');
// }
// if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

// } else {
//    $html .= '<a href="'.$getLoginUrl.'"  id="tth-googleplus-button">'.$button_label.'</a>';
// }
// echo $html;
?>