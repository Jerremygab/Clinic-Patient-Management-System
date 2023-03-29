<?php
include './config/connection.php';

 $message = '';
if(isset($_POST['form_remove'])) {
    $form_id = $_POST['form_id'];
    $form_medicinename = $_POST['form_medicinename'];
    $form_totalcapsules = $_POST['form_totalcapsules'];
    $form_expiredate = $_POST['form_expiredate'];
    if($form_id !== '') {
        try{

            $del_query = "DELETE FROM `medicine_details` WHERE `id`= $form_id;";
            
            $con->exec($del_query);

            $message = "Record deleted sucessfully.";

            if(file_exists($form_medicinename)){
                
                unlink($form_medicinename);
            }
            
            if(file_exists($form_totalcapsules)){
                
                unlink($form_totalcapsules);
            }
            if(file_exists($form_expiredate)){
                
                unlink($form_expiredate);
            }

        }catch(PDOException $ex){
            $con->rollback();
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            exit;
        }

}
header("Location:congratulation.php?goto_page=medicine_details.php&message=$message");
exit;
}

?>


