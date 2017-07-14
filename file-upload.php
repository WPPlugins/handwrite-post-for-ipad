<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$postarray =  HashArray($_POST);
$base64str = "";
//    $fileName = $_POST['name'];
    $fileName = $postarray[0];
//    $absPath = $_POST['abspath'];
    $absPath = $postarray[1];
//   require_once($absPath.'wp-includes/functions.php');
   require_once($absPath.'wp-load.php');

//   $fileURL = $_POST['file'];

   for($i=2;$i<count($postarray);$i++){
       $base64str .= $postarray[$i];
   }

   $time = current_time('mysql');
   $uploads = wp_upload_dir($time);

   $fp = fopen($uploads['path'] . '/' . $fileName . '.png',w);
   fwrite($fp,base64_decode(str_replace(' ', '+', $base64str)));
   fclose($fp);

   $uploadpath = preg_replace('/.+wp-content/', WP_CONTENT_URL, $uploads['path']);

   echo $uploadpath . "/" . $fileName . ".png";

   function HashArray($arraydatas){

       $returnarray;

       foreach($arraydatas as $key => $value){
           $returnarray[] = $value;
       }

       return $returnarray;

   }

?>
