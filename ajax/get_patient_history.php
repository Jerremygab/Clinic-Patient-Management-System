<?php 
	include '../config/connection.php';

  	$patientId = $_GET['patient_id'];

    $data = '';
    /**
    medicines (medicine_name)
    medicine_details (packing)
    patient_visits (visit_date, disease, bp, weight)
    patient_medication_history (quantity, )

    */
    $query = "SELECT `md`.`medicine_name`,
    `pv`.`visit_date`, `pv`.`disease`, `pv`.`bp`, `pv`.`temp`, `pv`.`pr`, `pv`.`weight`, `pv`.`height`, `pv`.`iden`,`pv`.`allergy`, `pmh`.`quantity` 
    from `medicine_details` as `md`, 
    `patient_visits` as `pv`, `patient_medication_history` as `pmh` 
    where
    `pv`.`patient_id` = $patientId and 
    `pv`.`id` = `pmh`.`patient_visit_id` and 
    `md`.`id` = `pmh`.`medicine_detail_id` 
    order by `pv`.`id` asc, `pmh`.`id` asc;";

    
      $stmt = $con->prepare($query);
      $stmt->execute();

      $i = 0;
      while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $i++;
        $data = $data.'<tr>';
        
        $data = $data.'<td class="px-2 py-1 align-middle text-center">'.$i.'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.date("M d, Y", strtotime($r['visit_date'])).'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['bp'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['temp'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['pr'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['weight'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['height'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['iden'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['disease'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['allergy'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle">'.$r['medicine_name'].'</td>';
        $data = $data.'<td class="px-2 py-1 align-middle text-right">'.$r['quantity'].'</td>';

        $data = $data.'</tr>';
      }

    
    

  	echo $data;
?>