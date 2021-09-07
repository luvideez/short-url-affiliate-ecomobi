<?php
error_reporting(E_ERROR | E_PARSE);
$shopee1 = file_get_contents("http://giau.link/quanly/shopee1.txt");
$shopee2 = file_get_contents("http://giau.link/quanly/shopee2.txt");
$shopee3 = file_get_contents("http://giau.link/quanly/shopee3.txt");
$shopeeid = $_POST['shopeeid'];
$ten = $_POST['ten'];
$email = $_POST['email'];


    $myfile = fopen("url$shopeeid.php", "x") or die("Link đã tồn tại - truy cập vào link sau: http://vtv.asia/user/url$shopeeid.php"); 
    
    $txt = $shopee1;
    fwrite($myfile, $txt); 
    $txt = '$id="'.$shopeeid.'"'.';';
    fwrite($myfile, $txt);
    $txt = "$shopee2";
    fwrite($myfile, $txt);
    $txt = "$ten - $email - $shopeeid ";
    fwrite($myfile, $txt);
    $txt = "$shopee3";
    fwrite($myfile, $txt);
    
    fclose($myfile);
    
    alert("Đã tạo thành công cho user id: $shopeeid - chúng tôi sẽ tự chuyển hướng trang web sau 3 giây");

function alert($msg) {
    echo "<script type='text/javascript'>alert('$msg');</script>";
}
header("Refresh:3; url=url$shopeeid.php");

?>
