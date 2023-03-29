<?php 
include './config/connection.php';

$message = '';
if(isset($_POST['save_medicine'])) {
  $message = '';
  $type = trim($_POST['type']);
  $description = trim($_POST['description']);
  $medicineName = trim($_POST['medicine_name']);
  $medicineName = ucwords(strtolower($medicineName));
  if($medicineName != '') {
   $query = "INSERT INTO `medicines`(`medicine_name`, `type`, `description`)
   VALUES('$medicineName','$type', '$description');";
   
   try {

    $con->beginTransaction();

    $stmtMedicine = $con->prepare($query);
    $stmtMedicine->execute();

    $con->commit();

    $message = 'Medicine added successfully.';
  }catch(PDOException $ex) {
   $con->rollback();

   echo $ex->getMessage();
   echo $ex->getTraceAsString();
   exit;
 }

} else {
 $message = 'Empty form can not be submitted.';
}
header("Location:congratulation.php?goto_page=medicines.php&message=$message");
exit;
}

try {
  $query = "select `id`, `medicine_name`, `type`,`description` from `medicines` 
  order by `medicine_name` asc;";
  $stmt = $con->prepare($query);
  $stmt->execute();

} catch(PDOException $ex) {
  echo $ex->getMessage();
  echo $e->getTraceAsString();
  exit;  
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <?php include './config/site_css_links.php';?>

 
 <?php include './config/data_tables_css.php';?>
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
              <h1>Medicines</h1>
            </div>
          </div>
        </div><!-- /.container-fluid -->
      </section>
      <!-- Main content -->
      <section class="content">
        <!-- Default box -->
        <div class="card card-outline card-primary rounded-0 shadow">
          <div class="card-header">
            <h3 class="card-title">Add Medicine</h3>
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
                <label>Medicine Name</label>
                <input type="text" id="medicine_name" name="medicine_name" required="required"
                class="form-control form-control-sm rounded-0" />
              </div>
              <div class="col-lg-3 col-md-1 col-sm-1 col-xs-10">
                <label>Medicine Type</label>
                <input type="text" id="type" name="type" required="required"
                class="form-control form-control-sm rounded-0" />
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-10">
                <label>Description</label>
                <input type="text" id="description" name="description" required="required"
                class="form-control form-control-sm rounded-0" />
              </div>
              <div class="col-lg-1 col-md-2 col-sm-2 col-xs-2">
                <label>&nbsp;</label>
                <button type="submit" id="save_medicine" 
                name="save_medicine" class="btn btn-primary btn-sm btn-flat btn-block">Save</button>
              </div>
            </div>
          </form>
        </div>

      </div>
      <!-- /.card -->
    </section>
    <section class="content">
      <!-- Default box -->
      <div class="card card-outline card-primary rounded-0 shadow">
        <div class="card-header">
          <h3 class="card-title">All Medicines</h3>

          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
         <div class="row table-responsive">

          <table id="all_medicines" 
          class="table table-striped dataTable table-bordered dtr-inline" 
          role="grid" aria-describedby="all_medicines_info">
          <colgroup>
            <col width="10%">
            <col width="20%">
            <col width="20%">
            <col width="60%">
            <col width="10%">
          </colgroup>

          <thead>
            <tr>
             <th class="text-center">S.No</th>
             <th>Medicine Name</th>
             <th>Medicine Type</th>
             <th>Description</th>
             <th class="text-center">Action</th>
           </tr>
         </thead>

         <tbody>
          <?php 
          $serial = 0;
          while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
           $serial++;
           ?>
           <tr>
             <td class="text-center"><?php echo $serial;?></td>
             <td><?php echo $row['medicine_name'];?></td>
             <td><?php echo $row['type'];?></td>
             <td><?php echo $row['description'];?></td>
             <td class="text-center">
              <a href="update_medicine.php?id=<?php echo $row['id'];?>" 
               class="btn btn-primary btn-sm btn-flat">
               <i class="fa fa-edit"></i>
             </a>
           </td>
         </tr>
       <?php } ?>
     </tbody>
   </table>
 </div>
</div>

<!-- /.card-footer-->
</div>
<!-- /.card -->

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php 
include './config/footer.php';

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


<script>
  showMenuSelected("#mnu_medicines", "#mi_medicines");

  var message = '<?php echo $message;?>';

  if(message !== '') {
    showCustomMessage(message);
  }

  $(function () {
    $("#all_medicines").DataTable({
      "responsive": true, "lengthChange": false, "autoWidth": false,
      "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#all_medicines_wrapper .col-md-6:eq(0)');
    
  });

  $(document).ready(function() {

    $("#medicine_name").blur(function() {
      var medicineName = $(this).val().trim();
      $(this).val(medicineName);

      if(medicineName !== '') {
        $.ajax({
          url: "ajax/check_medicine_name.php",
          type: 'GET', 
          data: {
            'medicine_name': medicineName
          },
          cache:false,
          async:false,
          success: function (count, status, xhr) {
            if(count > 0) {
              showCustomMessage("This medicine name has already been stored. Please choose another name");
              $("#save_medicine").attr("disabled", "disabled");
            } else {
              $("#save_medicine").removeAttr("disabled");
            }
          },
          error: function (jqXhr, textStatus, errorMessage) {
            showCustomMessage(errorMessage);
          }
        });
      }

    });    
  });
</script>
</body>
</html>