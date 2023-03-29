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
    $employee_number = trim($_POST['employee_number']);
    $patientName = trim($_POST['patient_name']);
    $address = trim($_POST['address']);
    $department = trim($_POST['department']);

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
    if ($employee_number != '' && $patientName != '' && $address != '' &&
        $department != '' && $dateBirth != '' && $phoneNumber != '' && $gender != '' && $complaint != '') {
        $query = "INSERT INTO `employee_record`(`employee_number`, `patient_name`, `address`, `department`, `date_of_birth`, todays_time, `phone_number`, `gender`, `complaint`)
                    VALUES('$employee_number', '$patientName', '$address', '$department', '$dateBirth', '$todays_time', '$phoneNumber', '$gender', '$complaint');";
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

    $query = "SELECT  employee_record.*, date_format(`date_of_birth`, '%d %b %Y') as `date_of_birth`, 
`phone_number`, `gender`, `complaint`
FROM `employee_record` order by `employee_number` asc;"; 

    $stmtPatient1 = $con->prepare($query);
    $stmtPatient1->execute();

} catch(PDOException $ex) {
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
    exit;
}


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
                        <h1>Employee Patients</h1>
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
                                <label>Employee Number</label>
                                <input type="number" name="employee_number" id="employee_number" required="required"
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
                            <label>Department</label>
                                <select class="form-control form-control-sm rounded-0" id="department" name="department" required="required"> 
                                <option value="">--- Select Department ---</option>
                                <option value="COLLEGE OF ALIED AND MEDICAL SCIENCES">COLLEGE OF ALIED AND MEDICAL SCIENCES</option>
                                <option value="COLLEGE OF ARTS AND SCIENCES">COLLEGE OF ARTS AND SCIENCES</option>
                                <option value="COLLEGE OF CRIMINAL JUSTICE">COLLEGE OF CRIMINAL JUSTICE</option>
                                <option value="COLLEGE OF EDUCATION">COLLEGE OF EDUCATION</option>
                                <option value="ETEEAP">ETEEAP</option>
                                <option value="COLLEGE OF BUSINESS ACCOUNTANCY">COLLEGE OF BUSINESS ACCOUNTANCY</option>
                                <option value="COLLEGE OF ENGINEERING">COLLEGE OF ENGINEERING </option>
                                <option value="COLLEGE OF INFORMATION AND COMMUNICATIONS AND TECHNOLOG">COLLEGE OF INFORMATION AND COMMUNICATIONS AND TECHNOLOGY</option>
                                <option value="COLLEGE OF LAW">COLLEGE OF LAW</option>
                                <option value="COLLEGE OF NURSING AND MIDWIFERY">COLLEGE OF NURSING AND MIDWIFERY </option>
                                <option value="COLLEGE OF TOURISM AND HOSPITALITY MANAGEMENT">COLLEGE OF TOURISM AND HOSPITALITY MANAGEMENT </option>
                                <option value="SENIOR HIGH SCHOOL">SENIOR HIGH SCHOOL</option>
                                <option value="JUNIOR HIGH SCHOOL">JUNIOR HIGH SCHOOL</option>
                                <option value="COLLEGE OF INFORMATION TECHNOLOGY, ENTERTAINMENT AND COMMUNICATION">COLLEGE OF INFORMATION TECHNOLOGY, ENTERTAINMENT AND COMMUNICATION </option>
                                <option value="COLLEGE OF ENTREPRENEURSHIP, TOURISM, HOSPITALITY AND REAL ESTATE MANAGEMENT">COLLEGE OF ENTREPRENEURSHIP, TOURISM, HOSPITALITY AND REAL ESTATE MANAGEMENT</option>
                                <option value="COLLEGE OF ENGINEERING AND ARCHITECTURE">COLLEGE OF ENGINEERING AND ARCHITECTURE</option>
                                <option value="COLLEGE OF EDUCATION, ARTS AND SCIENCES">COLLEGE OF EDUCATION, ARTS AND SCIENCES</option>
                                <option value="COLLEGE OF CRIMINAL JUSTICE EDUCATION">COLLEGE OF CRIMINAL JUSTICE EDUCATION</option>
                                <option value="COLLEGE OF BUSINESS, ACCOUNTANCY AND AUDITING">COLLEGE OF BUSINESS, ACCOUNTANCY AND AUDITING</option>
                                <option value="REGISTRAR OFFICE">REGISTRAR OFFICE </option>
                                <option value="ACCOUNTING OFFICE">ACCOUNTING OFFICE</option>
                                <option value="CASHIER OFFICE">CASHIER OFFICE</option>
                                <option value="ADMISSION OFFICE">ADMISSION OFFICE </option>
                            
                            </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                                <div class="form-group">
                                    <label>Today's Date</label>
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
                                    <label>Today's Time</label>
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
                        </div>
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
        showMenuSelected("#mnu_patients", "#mi_employee_patients");

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
</body>
</html>