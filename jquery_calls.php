
<?php

require_once("../../../wp-config.php");
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();
if(
    (isset($_POST['action'])&&$_POST['action']=="handle_picture_upload")
    ||
    (isset($_FILES['userfile']) && $_FILES['userfile']['error'] == 0)
  )
{
	$nonce=$_POST['nonce'];
    if ( ! wp_verify_nonce( $nonce, 'wpc-picture-upload-nonce' ) )
    {
        $busted=__("Cheating huh?", "wpc");
        die ($busted);
    }
	
    $upload_dir=  wp_upload_dir();
    $generation_path = $upload_dir["basedir"];
    $generation_url = $upload_dir["baseurl"];
    $file_name=  uniqid();
    $valid_formats=  get_option("wpc-upl-extensions");
    if(!$valid_formats)
        $valid_formats = array("jpg", "png", "gif", "bmp","jpeg");//wpc-upl-extensions
//    var_dump($valid_formats);
    $name = $_FILES['userfile']['name'];
    $size = $_FILES['userfile']['size'];
    
    if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST")
    {
    
    if(strlen($name))
    {
        list($txt, $ext) = explode(".", $name);
        $ext=  strtolower($ext);
        if(in_array($ext,$valid_formats))
        {
                $tmp = $_FILES['userfile']['tmp_name'];
                $success=0;
                $message="";
                if(move_uploaded_file($tmp, $generation_path."/".$file_name.".$ext"))
                {
                    $success=1;
                    $message="<span class='clipart-img'><img src='$generation_url/$file_name.$ext'></span>";
                }
                else
                {
                    $success=0;
                    $message=__( 'An error occured during the upload. Please try again later', 'wpc' );
                }
        }
        else
        {
            $success=0;
            $message=__( 'Incorrect file extension. Allowed extensions: ', 'wpc' ).  implode(", ", $valid_formats);
        }
        echo json_encode(
                            array(
                                    "success"=>$success,
                                    "message"=>$message,
                            )
                        );
    }
    }
}

?>