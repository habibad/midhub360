<?php
class Upload 
{
    public static function UpdateBrand()
    {

        $weblink = Input::get('weblink');          
        $appname = Input::get('appname');          
        $fileToUpload = Input::get('fileToUpload');          
        $us_id = Session::get('us_id');	

        
           /* Function for prevent of sql injection */
           
           global $connect;
           $appname  = stripcslashes($appname);          
          
           $appname= mysqli_real_escape_string($connect, $appname);           
                 
          
       /* End of Function for prevent of sql injection */

              

        /* Upload Image Start*/
  
        $target_dir = "../uploads/logo/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $imagename = htmlspecialchars( basename( $_FILES["fileToUpload"]["name"]));
        
        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) 
        {
          $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);

          $width = $check[0];
          $height = $check[1];

          if($check !== false) 
          {                  
                if (file_exists($target_file))  // Check if file already exists
                {          
                Session::Flush('error', 'Sorry, this image (' .$imagename. ') already exists. Rename the image.');
                Redirect::to('branding.php'); 
                //$uploadOk = 0;
                }                       
                elseif ($_FILES["fileToUpload"]["size"] > 500000)  // Check file size
                {
                    Session::Flush('error', 'Sorry,  your image is too large. Maximum size 500KB');
                    Redirect::to('branding.php'); 
                    //$uploadOk = 0;
                }                      
                elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")   // Allow certain file formats 
                {          
                Session::Flush('error', 'Sorry, only JPG, JPEG and PNG files are allowed.');
                Redirect::to('branding.php'); 
                //$uploadOk = 0;
                }
                elseif ($width > 600 || $height > 600)
                {
                //echo '<p>Invalid image size. Maximum width is 600px and Maximum Height 600px';
                Session::Flush('error', 'Invalid image size. Maximum width is 600px and Maximum Height 600px');
                Redirect::to('branding.php'); 
                }
                else
                {
                    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
                    {
                      //echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
                      //Session::Flush('success', 'The image ' . $imagename. ' has been uploaded.');
                      //Redirect::to('branding.php');
                                                            
                        $query = "UPDATE branding SET web_link='$weblink',apps_name='$appname', logo_url='$imagename' WHERE id ='1'";
                        global $connect; 
                        $result = mysqli_query($connect, $query);    
                        if(!$result)
                        {                      
                            die("Query Failed!" . mysqli_error($connect));
                        }
                        Session::Flush('success', 'Successfully Updated!');
                        Redirect::to('branding.php'); 


                    } 
                    else 
                    {
                      Session::Flush('error', 'Sorry, there was an error uploading your file.');
                      Redirect::to('branding.php');
                    }
                }
            
          } 
          else 
          {
            Session::Flush('error', 'File is not an image');
            Redirect::to('branding.php');             
          }
        }
        else
        {
            echo "Invalid!";
        }
        
        /* Upload Image End*/

    }


        




}

?>