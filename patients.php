<?php


use ClickSend\Model\SmsMessage;
require_once './vendor/autoload.php';
include './config/connection.php';
include './common_service/common_functions.php';
// Configure HTTP basic authorization: BasicAuth
// username: hiruzen2497
// password: 9DA29B8C-B496-760D-BF13-B5E2B7825BAD
$config = ClickSend\Configuration::getDefaultConfiguration()
    ->setUsername('jerremygab@gmail.com')
    ->setPassword('C9F1DD50-723E-E495-11EC-0DC52E4C312F');
$apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);


$message = '';
try {
    $q = $con->prepare("DESCRIBE past_medical_history;");
    $q->execute();
    $past_medical_fields = array_values($q->fetchAll(PDO::FETCH_COLUMN));
    array_shift($past_medical_fields);
    $past_medical_len = (int) count($past_medical_fields);


    $q = $con->prepare("DESCRIBE family_history;");
    $q->execute();
    $family_history_fields = array_values($q->fetchAll(PDO::FETCH_COLUMN));
    array_shift($family_history_fields);
    $family_history_len = (int) count($family_history_fields);

    $q = $con->prepare("DESCRIBE immunization;");
    $q->execute();
    $immunization_fields = array_values($q->fetchAll(PDO::FETCH_COLUMN));
    array_shift($immunization_fields);
    $immunization_len = (int) count($immunization_fields);


} catch(PDOException $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
    exit;
}


if(isset($_POST['sendMessageBtn'])) {

    $patientNumber = $_POST['messagePhoneNumber'];
    $patientname = $_POST['messagePatientName'];


    $patientLastname = trim($patientname);

    $name = trim($patientname);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $name ) );



    try {
        $msg = new SmsMessage();
        $msg->setBody("Hello Mr/Mrs '".$last_name."',  '".$patientname."' is currently admitted to University of Batangas Clinic");
        $msg->setTo($patientNumber);
        $msg->setSource("sdk");
// \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
        $sms_messages = new \ClickSend\Model\SmsMessageCollection();
        $sms_messages->setMessages([$msg]);
        $result = $apiInstance->smsSendPost($sms_messages);

        $message = 'Emergency message has been sent.';
    } catch(PDOException $e) {
        echo $e->getMessage();

    }

    header("Location:congratulation.php?goto_page=patients.php&message=$message");

}
if (isset($_POST['save_Patient'])) {
    print_r($_POST);
    $student_number = trim($_POST['student_number']);
    $patientName = trim($_POST['patient_name']);
    $address = trim($_POST['address']);
    $course = trim($_POST['course']);
    /* sa sa sa*/
  $bp = $_POST['bp'];
  $temp = $_POST['temp'];
  $pr = $_POST['pr'];
  $weight = $_POST['weight'];
  $height = $_POST['height'];
  $iden = $_POST['iden'];
  $disease = $_POST['disease'];
  $allergy = $_POST['allergy'];

    $dateBirth = trim($_POST['date_of_birth']);
    $todays_time = $_POST['todays_time'];
    $dateArr = explode("/", $dateBirth);

    $dateBirth = $dateArr[2].'-'.$dateArr[0].'-'.$dateArr[1];

    $phoneNumber = trim($_POST['phone_number']);

    $patientName = ucwords(strtolower($patientName));
    $address = ucwords(strtolower($address));
    
    $past_medical_values = [];
    
    

    foreach($past_medical_fields as $past_medical_field){
        array_push($past_medical_values, $_POST[$past_medical_field . '_pm']);
    }
    $past_medical_values_str = implode("', '" , $past_medical_values);

    $family_history_values = [];
    foreach($family_history_fields as $family_history_field){
        array_push($family_history_values, $_POST[$family_history_field]);
    }
    $family_history_values_str = implode("', '" , $family_history_values);

    $family_history_rel_values = [];
    foreach($family_history_fields as $family_history_field){
        array_push($family_history_rel_values, $_POST[$family_history_field . '_relation']);
    }
    $family_history_rel_str = implode("', '" , $family_history_rel_values);

    $immunization_values = [];
    foreach($immunization_fields as $immunization_field){
        array_push($immunization_values, formatDateInsert($_POST[$immunization_field] ?? '00/00/0000'));
    }
    $immunization_str = implode("', '" , $immunization_values);
    
    




    $gender = $_POST['gender'];
    $complaint = trim($_POST['complaint']);
    
    /*med*/
    $medicineCapsulesLeft = $_POST['medicineCapsulesLeft'];
  $medicineCapsulesQty = $_POST['medicineCapsulesQty'];
  //Medicine IDs to be updated
  $medicineIdsToUpdate = array_keys($medicineCapsulesQty); 
  
  foreach ($medicineIdsToUpdate as $medId) {
      $totalLeft = $medicineCapsulesLeft[$medId];
      $capsuleQuantity = $medicineCapsulesQty[$medId];

      $queryMedicationHistory = "INSERT INTO `patient_medication_history`(
      `patient_visit_id`, `medicine_detail_id`, `quantity`)
      VALUES($lastInsertId, $medId, $capsuleQuantity);";

      $stmtDetails = $con->prepare($queryMedicationHistory);
      $stmtDetails->execute();

      $updateMedicineQuery = "UPDATE `medicine_details` SET `total_capsules` = $totalLeft
      WHERE `medicine_details`.`id` = $medId";

      $stmtMedicine = $con->prepare($updateMedicineQuery);
      $stmtMedicine->execute();
    }
  
    if ($student_number != '' && $patientName != '' && $address != '' &&
        $course != '' && $dateBirth != '' && $phoneNumber != '' && $gender != '' && $complaint != '') {
        $query = "INSERT INTO `patients`(`student_number`, `bp`, `temp`, `pr`, `weight`, `height`, `iden`, `disease`, `allergy`, `patient_name`, `address`, `course`, `date_of_birth`, todays_time, `phone_number`, `gender`, `complaint`)
                    VALUES('$student_number', '$bp', '$temp', '$pr', '$weight', '$height', '$iden', '$disease', '$allergy', '$patientName', '$address', '$course', '$dateBirth', '$todays_time', '$phoneNumber', '$gender', '$complaint');";
        try {

            $con->beginTransaction();
            $stmtPatient = $con->prepare($query);
            $stmtPatient->execute();

            $latestPatientId = $con->lastInsertId();

            $past_medical_query = "INSERT INTO `past_medical_history`(" . implode(',', $past_medical_fields) . ") 
                                VALUES('$past_medical_values_str')" ;

            $pastMedicalRecordStmt = $con->prepare($past_medical_query);
            $pastMedicalRecordStmt->execute();

            $latestPastMedicalID = $con->lastInsertId();

            $family_history_query = "INSERT INTO `family_history`(" . implode(',', $family_history_fields) . ") 
                                VALUES('$family_history_values_str')" ;

            $familyHistoryStmt = $con->prepare($family_history_query);
            $familyHistoryStmt->execute();

            $latestFamilyHistoryId = $con->lastInsertId();

            $family_history_relation_query = "INSERT INTO `family_history_relation`( id, " . implode(',', $family_history_fields) . ") 
                                        VALUES($latestFamilyHistoryId, '$family_history_rel_str')" ;
            $familyHistoryRelStmt = $con->prepare($family_history_relation_query);
            $familyHistoryRelStmt->execute();

            $latestFamilyHistoryRelId = $con->lastInsertId();

            $immunization_query = "INSERT INTO `immunization`(" . implode(',', $immunization_fields) . ") 
                                        VALUES('$immunization_str')" ;
            $immunizationStmt = $con->prepare($immunization_query);
            $immunizationStmt->execute();
            $latestImmunizationId = $con->lastInsertId();

            $health_record_query = "INSERT INTO `health_record`(`patient_id`, `past_medical_history_id`, `family_history_id`, `immunization_id`)
                                    VALUES($latestPatientId, $latestPastMedicalID, $latestFamilyHistoryId, $latestImmunizationId)";
            $healthRecordStmt = $con->prepare($health_record_query);
            $healthRecordStmt->execute();
                
            $con->commit();

            $message = 'patient added successfully.';
        } catch(PDOException $ex) {
            $con->rollback();

            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            exit;
        }
    }
    header("Location:congratulation.php?goto_page=patients.php&message=$message");
    exit;
}



try {

    $query = "SELECT  patients.*, date_format(`date_of_birth`, '%d %b %Y') as `date_of_birth`, 
`phone_number`, `gender`, `complaint`
FROM `patients` order by `student_number` asc;"; 

    $stmtPatient1 = $con->prepare($query);
    $stmtPatient1->execute();

} catch(PDOException $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
    exit;
}

$patients = getPatients($con);
$employee = getEmployee($con);
$medicines = getMedicines($con);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './config/site_css_links.php';?>

    <?php include './config/data_tables_css.php';?>

    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <title></title>
    <link rel="icon" href="./images/ubicon.png" sizes="32x32" type="image/png">

</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div class="wrapper">
    <!-- Navbar -->
    <?php include './config/header.php';
    include './config/sidebar.php';?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Student Patients</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="card card-outline card-primary rounded-0 shadow">
                <div class="card-header">
                    <h3 class="card-title">Add Patients</h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>

                    </div>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Student Number</label>
                                <input type="number" name="student_number" id="student_number" required="required"
                                       class="form-control form-control-sm rounded-0"/>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Patient Name</label>
                                <input type="text" id="patient_name" name="patient_name" required="required"
                                       class="form-control form-control-sm rounded-0"/>
                            </div>
                            <br>
                            <br>
                            <br>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Address</label>
                                <input type="text" id="address" name="address" required="required"
                                       class="form-control form-control-sm rounded-0"/>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Course</label>
                                <select class="form-control form-control-sm rounded-0" id="course" name="course" required="required"> 
                                <option value="">--- Select Course ---</option>
                                <option value="Bachelor of Science in Accountancy">Bachelor of Science in Accountancy</option>
                                <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                                <option value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</option>
                                <option value="Bachelor of Science in Criminology">Bachelor of Science in Criminology</option>
                                <option value="Bachelor of Science in Education">Bachelor of Science in Education</option>
                                <option value="Bachelor of Science in Industrial Engineering">Bachelor of Science in Industrial Engineering</option>
                                <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                <option value="International Hospitality Management">International Hospitality Management</option>
                                <option value="Bachelor of Science in Legal Management">Bachelor of Science in Legal Management</option>
                                <option value="Bachelor of Science in Psychology">Bachelor of Science in Psychology</option>
                                <option value="Bachelor of Science in Tourism Management">Bachelor of Science in Tourism Management</option>
                            
                            </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <div class="form-group">
                                    <label>Date</label>
                                    <div class="input-group date"
                                         id="date_of_birth"
                                         data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm rounded-0 datetimepicker-input" data-target="#date_of_birth" name="date_of_birth"
                                               data-toggle="datetimepicker" autocomplete="off" />
                                        <div class="input-group-append"
                                             data-target="#date_of_birth"
                                             data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <div class="form-group">
                                    <label>Time</label>
                                    <input type="time" class="form-control form-control-sm rounded-0" data-target="#todays_time" name="todays_time" autocomplete="off" />
                                </div>
                            </div>


                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Phone Number&nbsp<text style="font-size:.8rem">(eg. 639XXXXXXXXX)</text></label>
                                
                                <input type="text" id="phone_number" name="phone_number" required="required" placeholder="(+63)" pattern="\d{12}" maxlength="12"
                                       class="form-control form-control-sm rounded-0"/>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Gender</label>
                                <select class="form-control form-control-sm rounded-0" id="gender"
                                        name="gender">
                                    <?php echo getGender();?>
                                </select>

                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <label>Complaints</label>
                                <input type="text" id="complaint" name="complaint" required="required"
                                       class="form-control form-control-sm rounded-0"/>
                            </div>
                            <div class="col-md-12"><hr></div>
                            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
        <label>Blood Pressure</label>
        <input id="bp" class="form-control form-control-sm rounded-0" name="bp" required="required" />
      </div>
      <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
        <label>Temperature</label>
        <input id="temp" class="form-control form-control-sm rounded-0" name="temp" required="required" />
      </div>
      <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
        <label>Pulse rate</label>
        <input id="pr" class="form-control form-control-sm rounded-0" name="pr" required="required" />
      </div>
      <div class="col-md-12"><hr></div>


      <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
		<label for="weight">Weight (kg):</label>
		<input type="number" id="weight" name="weight" required><br>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
		<label for="height">Height (cm):</label>
		<input type="number" id="height" name="height" required><br>
    </div>

    <div class="col-lg-1 col-md-1 col-sm-6 col-xs-12">
    <label>&nbsp;</label>
		<input type="button" class="btn btn-primary btn-sm btn-flat btn-block" value="Calculate" onclick="calculateBMI()" required="required" />
    </div>	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;	&nbsp;

    

    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
		<label for="bmi">Body Mass Index:</label>
		<input type="text" id="bmi" name="bmi" readonly>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
		<label for="identification">Identification:</label>
		<input type="text" id="iden" name="iden" readonly>
</div>

<div class="col-md-12"><hr></div>



      <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
        <label>Disease</label>
        <input id="disease" required="required" name="disease" class="form-control form-control-sm rounded-0" />
      </div>
      <div class="col-lg-8 col-md-8 col-sm-6 col-xs-12">
      <label>Allergy&nbsp<text style="font-size:.8rem">(optional)</text></label>
        <input id="allergy" name="allergy" class="form-control form-control-sm rounded-0" />
      </div>


    </div>

    <div class="col-md-12"><hr /></div>
    <div class="clearfix">&nbsp;</div>

    <div class="row">
     <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
      <label>Select Medicine</label>
      <select id="medicine" class="form-control form-control-sm rounded-0">
        <?php echo $medicines;?>
      </select>
    </div>

    <div  class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
     <p id="packing" value="">No medicine selected</p>
    </div>

    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
      <label>Quantity</label>
      <input id="quantity" type="number" min="1" max="10" class="form-control form-control-sm rounded-0" />
    </div>

    <div class="col-lg-1 col-md-1 col-sm-6 col-xs-12">
      <label>&nbsp;</label>
      <button id="add_to_list" type="button" class="btn btn-primary btn-sm btn-flat btn-block">
        <i class="fa fa-plus"></i>
      </button>
    </div>

  </div>

  <div class="clearfix">&nbsp;</div>
  <div class="row table-responsive">
    <table id="medication_list" class="table table-striped table-bordered">
      <colgroup>
        <col width="10%">
        <col width="50%">
        <col width="10%">
        <col width="10%">
        <col width="15%">
        <col width="5%">
      </colgroup>
      <thead class="bg-primary">
        <tr>
          <th>No.</th>
          <th>Medicine Name</th>
          <th>QTY</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody id="current_medicines_list">

      </tbody>
    </table>
  </div>
  <div id="medicineInputs">
    <!-- Hidden inputs -->

    <div class="clearfix">&nbsp;</div>
    <div class="clearfix">&nbsp;</div>
    <div class="clearfix">&nbsp;</div>
    <div class="clearfix">&nbsp;</div>
</form>


                        <div class="clearfix">&nbsp;</div>
                        <strong>Past Medical History: Has the child suffered from any of the following</strong>
                        <div class="row">
                            <div class="col">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Disease</th> 
                                            <th scope="col">Yes</th>
                                            <th scope="col" class="border-right">No</th>
                                            <th scope="col" >Disease</th>
                                            <th scope="col">Yes</th>
                                            <th scope="col">No</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i = 0; $i < $past_medical_len; $i+=2): ?>
                                            <tr class="border-bottom">
                                                <td><?= makeTitle($past_medical_fields[$i], '_') ?></td>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="<?= $past_medical_fields[$i] . '_pm' ?>" id="<?= $past_medical_fields[$i] . '_pm' ?>" value="yes" required>
                                                    </div>
                                                </td>
                                                <td class="border-right">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="<?= $past_medical_fields[$i] . '_pm' ?>" id="<?=$past_medical_fields[$i] . '_pm' ?>" value="no" required>
                                                    </div>
                                                </td>
                                                <?php if($i + 1 < $past_medical_len): ?>
                                                    <td><?= makeTitle($past_medical_fields[$i + 1], '_') ?></td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="<?= $past_medical_fields[$i + 1] . '_pm' ?>" id="<?= $past_medical_fields[$i + 1] . '_pm' ?>" value="yes" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="<?= $past_medical_fields[$i + 1] . '_pm' ?>" id="<?= $past_medical_fields[$i + 1] . '_pm' ?>" value="no" required>
                                                            
                                                        </div>
                                                    </td>
                                                <?php endif; ?>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="clearfix">&nbsp;</div>
                        <strong>Family History</strong>
                        <div class="row">
                            <div class="col">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Disease</th>
                                            <th scope="col">Yes</th>
                                            <th scope="col">No</th>
                                            <th scope="col" class="border-right">Relation</th>
                                            <th scope="col">Disease</th>
                                            <th scope="col">Yes</th>
                                            <th scope="col">No</th>
                                            <th scope="col">Relation</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i = 0 ; $i < $family_history_len ; $i+=2): ?>
                                            <tr class="border-bottom">
                                                <td><?= makeTitle($family_history_fields[$i], '_') ?></td>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="<?= $family_history_fields[$i] ?>" id="<?= $family_history_fields[$i] ?>" value="yes" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="<?= $family_history_fields[$i] ?>" id="<?= $family_history_fields[$i] ?>" value="no" required>
                                                    </div>
                                                </td>
                                                <td class="border-right">
                                                    <select class="form-control form-control-sm rounded-0" id="<?= $family_history_fields[$i] . '_relation'?>" name="<?= $family_history_fields[$i] . '_relation'?>" required="required"> 
                                                        <option value="">--- Select Relation ---</option>
                                                        <option value="None">None</option>
                                                        <option value="Grandparents">Grandparents</option>
                                                        <option value="Parents">Parents</option>
                                                        <option value="Aunts/Uncles">Aunts/Uncles</option>
                                                        <option value="Brother/Sister">Brother/Sister</option>
                                                        <option value="Nieces/Nephews">Nieces/Nephews</option>
                                                        
                                                </td>
                                                <?php if($i + 1 < $family_history_len): ?>
                                                    <td><?= makeTitle($family_history_fields[$i + 1], '_') ?></td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="<?= $family_history_fields[$i + 1] ?>" id="<?= $family_history_fields[$i + 1] ?>" value="yes" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="<?= $family_history_fields[$i + 1] ?>" id="<?= $family_history_fields[$i + 1] ?>" value="no" required>
                                                        </div>
                                                    </td>
                                                    <td>
                                                    <select class="form-control form-control-sm rounded-0" id="<?= $family_history_fields[$i] . '_relation'?>" name="<?= $family_history_fields[$i] . '_relation'?>" required="required"> 
                                                        <option value="">--- Select Relation ---</option>
                                                        <option value="None">None</option>
                                                        <option value="Grandparents">Grandparents</option>
                                                        <option value="Parents">Parents</option>
                                                        <option value="Aunts/Uncles">Aunts/Uncles</option>
                                                        <option value="Brother/Sister">Brother/Sister</option>
                                                        <option value="Nieces/Nephews">Nieces/Nephews</option>
                                                    </td>
                                                <?php endif ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="clearfix">&nbsp;</div>
                        <strong>Immunization</strong>
                        <div class="row">
                            <div class="col">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Immunization</th>
                                            <th scope="col" class="border-right">Dates</th>
                                            <th scope="col">Immunization</th>
                                            <th scope="col">Dates</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for($i = 0 ; $i < $immunization_len; $i+=2): ?>
                                            <tr class="border-bottom">
                                                <td><?= makeTitle($immunization_fields[$i], '_') ?></td>
                                                <td class="border-right">
                                                    <div class="input-group date"
                                                        id="<?= $immunization_fields[$i] ?>"
                                                        data-target-input="nearest">
                                                        <input type="text" class="form-control form-control-sm rounded-0 datetimepicker-input" data-target="#<?= $immunization_fields[$i] ?>" name="<?= $immunization_fields[$i] ?>"
                                                            data-toggle="datetimepicker" autocomplete="off" />
                                                        <div class="input-group-append"
                                                            data-target="#<?= $immunization_fields[$i] ?>"
                                                            data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <?php if($i + 1 < $immunization_len): ?>
                                                    <td><?= makeTitle($immunization_fields[$i + 1], '_') ?></td>
                                                    <td>
                                                        <div class="input-group date"
                                                            id="<?= $immunization_fields[$i+1] ?>"
                                                            data-target-input="nearest">
                                                            <input type="text" class="form-control form-control-sm rounded-0 datetimepicker-input" data-target="#<?= $immunization_fields[$i+1] ?>" name="<?= $immunization_fields[$i + 1] ?>"
                                                                data-toggle="datetimepicker" autocomplete="off" />
                                                            <div class="input-group-append"
                                                                data-target="#<?= $immunization_fields[$i+1] ?>"
                                                                data-toggle="datetimepicker">
                                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php endif ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="clearfix">&nbsp;</div>

                        <div class="row">
                            <div class="col-lg-11 col-md-10 col-sm-10 xs-hidden">&nbsp;</div>

                            <div class="col-lg-1 col-md-2 col-sm-2 col-xs-12">
                                <button type="submit" id="save_Patient"
                                        name="save_Patient" class="btn btn-primary btn-sm btn-flat btn-block">Save</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </section>

        <br/>
        <br/>
        <br/>

       
    </div>
    <!-- /.content -->

    <!-- /.content-wrapper -->
    <?php
    include './config/footer.php';

    $message = '';
    if(isset($_GET['message'])) {
        $message = $_GET['message'];
    }
    ?>
    <!-- /.control-sidebar -->


    <?php include './config/site_js_links.php'; ?>
    <?php include './config/data_tables_js.php'; ?>


    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    

    <script>
        showMenuSelected("#mnu_patients", "#mi_patients");

        var message = '<?php echo $message;?>';

        if(message !== '') {
            showCustomMessage(message);
        }

        $('#date_of_birth').datetimepicker({
            format: 'L'
        });

        $('#dpt_opv_i').datetimepicker({
            format: 'L'
        });
        $('#dpt_opv_ii').datetimepicker({
            format: 'L'
        });
        $('#dpt_opv_iii').datetimepicker({
            format: 'L'
        });
        $('#dpt_opv_booster_i').datetimepicker({
            format: 'L'
        });
        $('#dpt_opv_booster_ii').datetimepicker({
            format: 'L'
        });
        $('#hib_i').datetimepicker({
            format: 'L'
        });
        $('#hib_ii').datetimepicker({
            format: 'L'
        });
        $('#hib_iii').datetimepicker({
            format: 'L'
        });
        $('#anti_measios').datetimepicker({
            format: 'L'
        });
        $('#anti_hepit_b_i').datetimepicker({
            format: 'L'
        });
        $('#anti_hepit_b_ii').datetimepicker({
            format: 'L'
        });
        $('#anti_hepit_b_iii').datetimepicker({
            format: 'L'
        });
        $('#mmr').datetimepicker({
            format: 'L'
        });
        $('#anti_chicken_pox').datetimepicker({
            format: 'L'
        });
        $('#anti_hepepititis_a_i').datetimepicker({
            format: 'L'
        });
        $('#anti_hepepititis_a_ii').datetimepicker({
            format: 'L'
        });
        $('#anti_hepepititis_a_iii').datetimepicker({
            format: 'L'
        });
        $('#anti_typhoid_fever').datetimepicker({
            format: 'L'
        });
        $('#others').datetimepicker({
            format: 'L'
        });




        $(function () {
            $("#all_patients").DataTable({
                "responsive": true, "lengthChange": false, "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            }).buttons().container().appendTo('#all_patients_wrapper .col-md-6:eq(0)');

        });


    </script>
    <script>
  var serial = 1;

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }

  var medicineCountCache = {
    decrementCount: function(medID, newVal) {
      let {totalQuantity, totalCapsules} = this[medID];
      newVal = parseInt(newVal);

      if(isNaN(newVal) || newVal < 0){
        showCustomMessage("Please enter a number and should a positve value");
        return false;
      } 
      
      if(newVal > totalCapsules){

        showCustomMessage("Not enough stocks");
        return false;
      }
      
      this[medID].totalQuantity = totalQuantity + newVal;
      this[medID].totalCapsules = totalCapsules - newVal;
      console.log(`decrementCount ${this[medID].totalQuantity} ${this[medID].totalCapsules}`);
      return true;
    },
    incrementCount: function(medID, newVal) {
      let {totalQuantity, totalCapsules} = this[medID];
      newVal = parseInt(newVal);
      this[medID].totalQuantity = totalQuantity - newVal;
      this[medID].totalCapsules = totalCapsules + newVal;
      console.log(`incrementCount ${this[medID].totalQuantity} ${this[medID].totalCapsules}`);

    }
  };

  $(document).ready(function() {
    
    $('#medication_list').find('td').addClass("px-2 py-1 align-middle")
    $('#medication_list').find('th').addClass("p-1 align-middle")
    $('#visit_date').datetimepicker({
      format: 'L'
    });


    $("#medicine").change(function() {

      // var medicineId = $("#medicine").val();
      let medicineId = $(this).val();
      const htmlText = `Total left: `;
      const packing = $("#packing");
      if(medicineId !== '') {
        if(medicineId in medicineCountCache){
            packing.attr('value', medicineCountCache[medicineId].totalCapsules);
            packing.html(htmlText + medicineCountCache[medicineId].totalCapsules);
        }
        else{
            $.ajax({
              url: "ajax/get_packings.php",
              type: 'GET', 
              data: {
                'medicine_id': medicineId
              },
              cache:false,
              async:false,
              success: function (data, status, xhr) {
                packing.attr('value', data);
                packing.html(htmlText + data);
                medicineCountCache[medicineId] = {totalQuantity: 0, totalCapsules: parseInt(data)};
              },
              error: function (jqXhr, textStatus, errorMessage) {
                showCustomMessage(errorMessage);
              }
            });
        }
        
      }
      else{
        packing.attr("value", "");
        packing.html("No medicine selected");
      }
    });


    $("#add_to_list").click(function() {
      let medicineId = $("#medicine").val();
     
      if(medicineId === ''){
        showCustomMessage('Please select a medicine');
        return;
      }
      var medicineName = $("#medicine option:selected").text();
      
      var quantity = $("#quantity").val().trim();

      if(medicineCountCache.decrementCount(medicineId, quantity) === false){
        return;
      }

      var oldData = $("#current_medicines_list").html();

      if(medicineName !== '' && packing !== '' && quantity !== '') {
        
        
        // inputs = inputs + '<input type="hidden" name="quantities[]" value="'+quantity+'" />';
        // if($("input[]"))
        const medCapsulesLeftQuery = $(`[name="medicineCapsulesLeft[${medicineId}]"]`);
        const medCapsulesQtyQuery = $(`[name="medicineCapsulesQty[${medicineId}]"]`);
        if(medCapsulesLeftQuery.length > 0 && medCapsulesQtyQuery.length > 0){
          medCapsulesLeftQuery.val(medicineCountCache[medicineId].totalCapsules);
          medCapsulesQtyQuery.val(medicineCountCache[medicineId].totalQuantity);
        }else{
          const input1 = `<input type="hidden" name="medicineCapsulesLeft[${medicineId}]" value="${medicineCountCache[medicineId].totalCapsules}" /> `;
          const input2 = `<input type="hidden" name="medicineCapsulesQty[${medicineId}]" value="${medicineCountCache[medicineId].totalQuantity}" />`;
          $("#medicineInputs").append(input1 + input2);
        }

        var tr = `<tr medid=${medicineId} quantity=${quantity}>`;
        tr = tr + '<td class="px-2 py-1 align-middle">'+serial+'</td>';
        tr = tr + '<td class="px-2 py-1 align-middle">'+medicineName+'</td>';
        tr = tr + '<td class="px-2 py-1 align-middle">'+quantity+'</td>';

        tr = tr + '<td class="px-2 py-1 align-middle text-center"><button type="button" class="btn btn-outline-danger btn-sm rounded-0" onclick="deleteCurrentRow(this);"><i class="fa fa-times"></i></button></td>';
        tr = tr + '</tr>';
        oldData = oldData + tr;
        serial++;

        $("#current_medicines_list").html(oldData);

        $("#medicine").val('');

        $("#packing").attr('value', '');
        $("#packing").html('No medicine selected');

        $("#quantity").val('');

      } else {
        showCustomMessage('Please fill all fields.');
      }

    });

  });

  function deleteCurrentRow(obj) {
    const parent = obj.parentNode.parentNode;
    const medId = parent.getAttribute('medid');
    const quantity = parent.getAttribute('quantity');
    var rowIndex = obj.parentNode.parentNode.rowIndex;
    medicineCountCache.incrementCount(medId, quantity);
    document.getElementById("medication_list").deleteRow(rowIndex);

    const medCapsulesLeftQuery = $(`[name="medicineCapsulesLeft[${medId}]"]`);
    const medCapsulesQtyQuery = $(`[name="medicineCapsulesQty[${medId}]"]`);
    if(medicineCountCache[medId].totalQuantity === 0){
      medCapsulesLeftQuery.remove();
      medCapsulesQtyQuery.remove();
    }
    else{
      medCapsulesLeftQuery.val(medicineCountCache[medId].totalCapsules);
      medCapsulesQtyQuery.val(medicineCountCache[medId].totalQuantity);
    }
  }
</script>

<script>
		function calculateBMI() {
			var weight = document.getElementById("weight").value;
			var height = document.getElementById("height").value;
			var bmi = weight / ((height/100) ** 2);
			document.getElementById("bmi").value = bmi.toFixed(2);

			if (bmi < 18.5) {
				document.getElementById("iden").value = "Underweight";
			} else if (bmi >= 18.5 && bmi < 25) {
				document.getElementById("iden").value = "Normal weight";
			} else if (bmi >= 25 && bmi < 30) {
				document.getElementById("iden").value = "Overweight";
			} else {
				document.getElementById("iden").value = "Obese";
			}
		}
	</script>
</body>
</html>