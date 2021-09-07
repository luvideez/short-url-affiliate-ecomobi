<?php
error_reporting(E_ERROR | E_PARSE);
$eco1 = file_get_contents("http://giau.link/quanly/eco1.txt");
$eco2 = file_get_contents("http://giau.link/quanly/eco2.txt");
$eco3 = file_get_contents("http://giau.link/quanly/eco3.txt");
$token = $_POST['token'];
$ten = $_POST['ten'];
$email = $_POST['email'];


    $myfile = fopen("url$token.php", "x") or die("Link đã tồn tại - truy cập vào link sau: http://vtv.asia/user/url$token.php"); 
    
    $txt = $eco1;
    fwrite($myfile, $txt); 
    $txt = "$token";
    fwrite($myfile, $txt);
    $txt = "$eco2";
    fwrite($myfile, $txt);
    $txt = "$ten - $email - Ecomobi service ";
    fwrite($myfile, $txt);
    $txt = "$eco3";
    fwrite($myfile, $txt);
    
    fclose($myfile);
    
    alert("Đã tạo thành công cho user eco có tokenid: $token - chúng tôi sẽ tự chuyển hướng trang web sau 3 giây");

function alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}
header("Refresh:3; url=url$token.php");

?>
