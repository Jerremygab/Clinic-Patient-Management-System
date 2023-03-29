<?php 
include './config/connection.php';
include './common_service/common_functions.php';


define('CORRECT_PIN', '0751');
$entered_pin = $_POST['pin'];
if(isset($_POST['submit'])) { 
   if ($entered_pin === CORRECT_PIN) {
    $medicineName = $_POST['medicine_name'];
    $total_capsules = $_POST['total_capsules'];
    $expire_date = $_POST['expire_date'];
    

    $expireDateArr = explode("/", $expire_date);

    $cleanExpireDate = $expireDateArr[2] . '-' . $expireDateArr[0] . '-' . $expireDateArr[1];
    $query = "INSERT into `medicine_details` (`medicine_name`, `total_capsules`, `expire_date`) values ('$medicineName', '$total_capsules', '$cleanExpireDate');";
   

      $con->beginTransaction();
      
      $stmtDetails = $con->prepare($query);
      $stmtDetails->execute();

      $con->commit();
      echo "<script>alert('Medicine added successfully.');</script>";
  }else{
    $message = "Invalid Pin!, Please Try Again!";
  echo "<script type='text/javascript'>alert('$message');</script>";
  }
}


  

// $medicines = getMedicines($con);

$query = "select `md`.`medicine_name`, 
`md`.`id`, `md`.`total_capsules`, `md`.`expire_date`
from `medicine_details` as `md` 
order by `md`.`id` asc;";

  
    $stmtDetails = $con->prepare($query);
    $stmtDetails->execute();   



?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>
 <?php include './config/data_tables_css.php';?>
 <link rel="stylesheet" type='' href="plugins/admincss/admin.css" />
 <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
 <title>University of Batangas in Lipa</title>
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
              <h1>Medicine Details</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>

      <!-- Main content -->
      <section class="content">

        <!-- Default box -->
        <div class="card card-outline card-primary rounded-0 shadow">
          <div class="card-header">
            <h3 class="card-title">Add Medicine Details</h3>

            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
              </button>
              
            </div>
          </div>
          <div class="card-body">
            <form method="post">
              <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                  <!-- <label>Select Medicine</label> -->
                  <!-- <select id="medicine" name="medicine" class="form-control form-control-sm rounded-0" required="required">
                    <?php echo $medicines;?>
                  </select> -->
                  <label>Medicine Name</label>
                  <input type="text" id="medicine_name" name="medicine_name" required="required"
                  class="form-control form-control-sm rounded-0" />
                </div>

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
                  <label>Total Drugs</label>
                  <input id="total_capsules" name="total_capsules" class="form-control form-control-sm rounded-0"  required="required" />
                </div>

                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-10">
                  <div class="form-group">
                    <label>Expiration Date</label>
                    <div class="input-group date" 
                      id="expire_date" 
                      data-target-input="nearest">
                      <input type="text" class="form-control form-control-sm rounded-0 datetimepicker-input" data-target="#expire_date" name="expire_date" required="required" data-toggle="datetimepicker" autocomplete="off"/>
                      <div class="input-group-append" 
                        data-target="#expire_date" 
                        data-toggle="datetimepicker">
                        <div class="input-group-text">
                          <i class="fa fa-calendar"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-1 col-md-2 col-sm-4 col-xs-12">
                  <label>&nbsp;</label>
                  <button type="#" id="#" name="#" data-toggle="modal" data-target="#exampleModal"
                  class="btn btn-primary btn-sm btn-flat btn-block">Submit</button>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Enter Pin</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group">
            <input input type="password" id="pin" name="pin" class="form-control form-control-sm rounded-0"  required="required" maxlength="4"/>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" id="saveButton" name="submit" 
                  class="btn btn-primary" onclick="checkPin()">Save</button>
      </div>
    </div>
  </div>
                </div>
              </div>
            </form>
          </div>
          <!-- /.card-body -->
          
        </div>
        <!-- /.card -->

      </section>

      <div class="clearfix">&nbsp;</div>
      <div class="clearfix">&nbsp;</div>
      
  <section class="content">
      <!-- Default box -->
      <div class="card card-outline card-primary rounded-0 shadow">
        <div class="card-header">
          <h3 class="card-title">Medicine Details</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
            
          </div>
        </div>

        <div class="card-body">
            <div class="row table-responsive">
              <table id="medicine_details" 
              class="table table-striped dataTable table-bordered dtr-inline" 
               role="grid" aria-describedby="medicine_details_info">
                <colgroup>
                  <col width="5%">
                  <col width="35%">
                  <col width="20%">
                  <col width="10%">
                  <col width="15%">
                  <col width="5%">
                </colgroup>
                <thead>
                  <tr>
                    <th>S.No</th>
                    <th>Medicine Name</th>
                    <th>Total Drugs</th>
                    <th>Expiration Date</th>
                    <th style="text-align:center;">Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                
                  <?php 
                  $serial = 0;
                  while($row =$stmtDetails->fetch(PDO::FETCH_ASSOC)){
                    $serial++;
                  ?>
                  <tr>
                    <td class="text-center"><?php echo $serial; ?></td>
                    <td><?php echo $row['medicine_name'];?></td>
                    <td><?php echo $row['total_capsules'];?></td>
                    <td><?php echo $row['expire_date'];?></td>
                    <td style="text-align:center;"><?php 
                    $date_now = date("Y-m-d"); 
                    $targetTimestamp = strtotime($date_now);
                    $currentTimestamp = time();
                    $secondsUntilTarget = $targetTimestamp - $currentTimestamp;
                    
                    
                    $threshold = 90; // days

                    $today = new DateTime();
                    $expiration = new DateTime ($row['expire_date']);
                    $diff = $today->diff($expiration);

                    if ($date_now > $row['expire_date']) {
                      echo '<b style="Color:white; border:1px solid #CD0404; border-radius:5px; background-color:#CD0404; padding:5px 10px;">Expired</b>';
                    }
                    elseif ($diff->days <= $threshold) {
                      echo '<b style="Color:white; border:1px solid #FFBF00; border-radius:5px; background-color:#FFBF00;  padding:5px 10px;">Near Expiration</b>';
                    }
                    else {
                      echo '<b style="Color:white; border:1px solid #1F8A70; border-radius:5px; background-color: #1F8A70;  padding:5px 10px; ">Good Condition</b>';
                    } ?></td>
                    <td class="text-center">
                      <a href="update_medicine_details.php?medicine_id=<?php echo $row['id'];?>&medicine_detail_id=<?php echo $row['id'];?>&total_capsules=<?php echo $row['total_capsules'];?>&expire_date=<?= $row['expire_date'] ?>" 
                      class = "btn btn-primary btn-sm btn-flat">
                      <i class="fa fa-edit"></i>
                      </a>
                      <button type="button" class="btn btn-danger btn-sm btn-flat" data-toggle="modal" data-target="#form_delete<?php echo $row['id']?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                    </td>
          
                  </tr>
                  <!-- Modal -->
                  <div class="modal fade" id="form_delete<?php echo $row['id']?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Medicine</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete <?php echo $row['medicine_name']?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <form method="POST" action="remove_medicineform.php">
                                                    <input type="hidden" name="form_id" value="<?php echo $row['id']?>"/>
                                                    <input type="hidden" name="form_medicinename" value="<?php echo $row['medicine_name']?>"/>
                                                    <input type="hidden" name="form_totalcapsules" value="<?php echo $row['total_capsules']?>"/>
                                                    <input type="hidden" name="form_expiredate" value="<?php echo $row['expire_date']?>"/>
                                                    <button type="submit" class="btn btn-danger" name="form_remove">Continue</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                <?php
                }
                ?>
                </tbody>
              </table>
            </div>
        </div>
      </div>

      
    </section>
  <!-- /.content-wrapper -->
 </div>

  <?php include './config/footer.php';

$message = '';
if(isset($_GET['message'])) {
  $message = $_GET['message'];
}
  ?>  
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<?php include './config/site_js_links.php'; ?>
<?php include './config/data_tables_js.php'; ?>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script>
  showMenuSelected("#mnu_medicines", "#mi_medicine_details");

    var message = '<?php echo $message;?>';
    $('#expire_date').datetimepicker({
      format:"L"
    })

  if(message !== '') {
    showCustomMessage(message);
  }
  $(function () {
    $("#medicine_details").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#medicine_details_wrapper .col-md-6:eq(0)');
    
  });

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>