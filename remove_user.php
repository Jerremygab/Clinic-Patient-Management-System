<?php
include './config/connection.php';

 $message = '';
if(isset($_POST['form_remove'])) {
    $form_id = $_POST['form_id'];
    $form_profile_picture = $_POST['form_profile_picture'];
    $form_user_name = $_POST['form_user_name'];
    if($form_id !== '') {
        try{

            $del_query = "DELETE FROM `users` WHERE `id`= $form_id;";
            
            $con->exec($del_query);

            $message = "User deleted sucessfully.";
            
            if(file_exists($form_filename)){
                
                unlink($form_filename);
            }

        }catch(PDOException $ex){
            $con->rollback();
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            exit;
        }

}
header("Location:congratulation.php?goto_page=users.php&message=$message");
exit;
}

?>


