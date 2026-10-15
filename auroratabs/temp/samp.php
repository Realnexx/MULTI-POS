<?php
//start

//college enrol ids array
$college_id = [];
$sql = "SELECT id FROM enrolment 
        WHERE studID='{$stud_id}' 
        AND status='confirm' 
        AND (course LIKE 'bachelor%' OR course LIKE '%cpe%') 
        ORDER BY id ASC";
$qry = $remote_conn->query($sql);
while($rs = $qry->fetch_assoc()){
    $college_id[] = $rs['id'];
}

$sql = "SELECT * FROM enrolment 
        WHERE studID='{$stud_id}' 
        AND status='confirm' 
        ORDER BY id ASC";
    $qry = $remote_conn->query($sql);
    while($rs = $qry->fetch_assoc()){
        $current_enrolid = $rs['id'];
        $addon_mult_arr = array();
        $dscount_reason = "";
        $less_reason = "";
        $less_amt = 0;
        $dscount_amt = 0;
        $total_addons = 0;
        $total_units = 0;
        $total_lesspay = 0;
        //Tuition Information
        $reg_fee = $rs['enrol_fee'];
        $misc_fee = $rs['misc_fee'];
        $lib_fee = $rs['library_fee'];
        $t_fee = $rs['tuition'];
        //Course Major and Year Level
        $course = $rs['course'];
        $major = $rs['major'];
        $yr_lvl = $rs['level'];
        $sem = $rs['semester'];
        $true_sem = $sem;
        $sc_yr = $rs['syear'];
        if ($sem == '1st' || $sem == '2nd') {
           $sem = $sem.' Semester of '.$sc_yr.'~'.intval($sc_yr+1);
        }else{
           $sem = $sem.' of '.$sc_yr.'~'.intval($sc_yr+1);
        }
        if($major != ''){
           $cHeader = 'Course: '. $course.' | '.$major;
        }else{
           $cHeader = 'Course: '.$course;
        }
        //Enrolled subjects units
        $usql = "SELECT units FROM enroll_subjects 
                 WHERE enrol_id='{$current_enrolid}' 
                 AND status=''";
        $uqry = $remote_conn->query($usql);
        while($urs = $uqry->fetch_assoc()){
            $total_units += unit_format($urs['units']);
        }
       
        //Enroll Add-Ons
        $adsql = "SELECT item,amount,pieces 
                  FROM enroladdons 
                  WHERE enrol_ID='{$current_enrolid}' 
                  AND status='' 
                  ORDER BY id ASC";
        $adqry = $remote_conn->query($adsql);
        while($adrs = $adqry->fetch_assoc()){
            $item = $adrs['item'];
            $amount = $adrs['amount'];
            $pieces = $adrs['pieces'];
            if($pieces == 0 || $pieces == ''){
                $pieces = 0;
            }
            $tot_addon = floatval($amount*$pieces);
            $zinsert = array($item, $tot_addon);
            $addon_mult_arr[] = $zinsert;
            $total_addons += $tot_addon;
        }
        //Calculate Total Tuition
        $t_fee_units = $t_fee*$total_units;
        
        //check for grade level units
        if ($t_fee_units > 100000 || intval($total_units) >= 50) {
           $t_fee_units = $t_fee;
        }
        
        //Set Fixed Tuition for Previous Grade Enrolments
        $wholeyearFee = array("Grade 11","Grade 12");
        if(in_array($course, $wholeyearFee) && $true_sem == '2nd' && $sc_yr >=2021){
          $reg_fee = 0;
          $misc_fee = 0;
          $lib_fee = 0;
          $t_fee_units = 0;
        }
        $total_tuition = $reg_fee + $misc_fee + $lib_fee + $t_fee_units;
        //Get Discount          
        $discount = $rs['discount'];
        if($discount != 0 || $discount != ''){
            $discount_arr = explode("@@@", $discount);
            $discount_perc = $discount_arr[0];
            $discount_desc = $discount_arr[1];
        }
        if(intval($discount_perc) > 0){
            $dscount_amt = $t_fee_units*($discount_perc/100);
            $dscount_reason = $discount_desc.' @ '.$dscount_amt.'%';
        }else{
            $discount_perc = 0;
        }
        
        //Less Amount
        $less_amount = $rs['less_amount'];
        if($less_amount != 0){
            $less_amt = $less_amount;
            $less_reason = $rs['less_reason'];
        }else{
            $less_amt = 0;
        }
        
        //Less Payments
        $pay_sql = "SELECT amount FROM payments 
                    WHERE enrol_ID='{$current_enrolid}' 
                    AND deleted='' 
                    AND status=''";
        $pay_qry = $remote_conn->query($pay_sql);
        while($pay_rs = $pay_qry->fetch_assoc()){
            $total_lesspay += $pay_rs['amount'];
        }
        <?php
if(in_array($current_enrolid, $college_id)){

    $opt_soa_all .='<tr style="height:12px;"></tr><tr style="background-color: whitesmoke;font-weight:600;">'
                        .'<td colspan="4" class="find_course">'.
                            $cHeader
                        .'</td>'.
                        '<td class="bill-payments" title="View Payments" billinfo="'.$current_enrolid.'">'.
                        '<i class="fa-solid fa-file-invoice"></i>'.
                        '</td>'.
                      '</tr>'.
                      '<tr style="background-color: whitesmoke;">'
                        .'<td colspan="5" class="find_semyr">'.
                            'Sem/S.Y.: '.$sem.' | Year Level: '.$yr_lvl
                        .'</td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;width: 20%;">'.
                            'Registration Fee'.
                         '</td>'.
                         '<td style="width: 10%;text-align: right;">'.
                            number_format($reg_fee,2).
                         '</td>'.
                         '<td colspan="3"></td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;width: 20%;">'.
                            'Miscellaneous Fee'.
                         '</td>'.
                         '<td style="width: 10%;text-align: right;">'.
                            number_format($misc_fee,2).
                         '</td>'.
                         '<td colspan="3"></td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;width: 20%;">'.
                            'Library Fee'.
                         '</td>'.
                         '<td style="width: 10%;text-align: right;">'.
                            number_format($lib_fee,2).
                         '</td>'.
                         '<td colspan="3"></td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;">'.
                            'Tuition ('.$total_units.' units @ '.number_format($t_fee,2).')'.
                         '</td>'.
                         '<td style="text-align: right;">'.
                            number_format($t_fee_units,2).
                         '</td>'.
                         '<td style="width: 12%;"></td>'.
                         '<td style="width: 10%;text-align: right;">'.
                            number_format($total_tuition,2).
                         '</td>'.
                         '<td style="width: 12%"></td>';
}
//Check if addon is not empty
if (!empty($addon_mult_arr)) {
   $taddons = 0;
   if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<tr>'.
                            '<td style="padding-left: 15px;" colspan="5">'.
                               '<i>Add: Add-ons</i>'.
                            '</td>'.
                         '</tr>';
   }
   foreach($addon_mult_arr AS $addVal){
      $taddons += $addVal[1];
      if ($taddons == $total_addons) {
        if(in_array($current_enrolid, $college_id)){
             $opt_soa_all .='<tr>'.
                                  '<td style="padding-left: 30px;">'.
                                     $addVal[0].//Addon Name
                                  '</td>'.
                                  '<td style="text-align: right;">'.
                                     number_format($addVal[1],2).//Addon Value
                                  '</td>'.                                                
                                  '<td></td>'.
                                  '<td style="text-align:right;">'.
                                     number_format($total_addons,2).
                                  '</td>'.
                                  '<td></td>'.
                                '</tr>';
        }         
      }else{
        if(in_array($current_enrolid, $college_id)){
         $opt_soa_all .='<tr>'.
                              '<td style="padding-left: 30px;">'.
                                 $addVal[0].//Addon Name
                              '</td>'.
                              '<td style="text-align: right;">'.
                                 number_format($addVal[1],2).//Addon Value
                              '</td>'.
                              '<td colspan="3"></td>'.
                            '</tr>';
        }
      }
   }
}
//Amount Payables
if(in_array($current_enrolid, $college_id)){
    $opt_soa_all .='<tr class="soa-vline"></tr>'.
                      '<tr>'.
                         '<td>'.
                            'Total Amount Payables'.
                         '</td>'.
                         '<td colspan="2"></td>';
}

//Check Student Old Balance
if ($sprev_bal > 0) {
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<td style="text-align: right;">'.
                            number_format($total_tuition + $total_addons,2).
                          '</td>'.
                          '<td></td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;">'.
                            '<i>Add:</i> Old Account(s)'.
                         '</td>'.
                         '<td style="text-align: right;">'.
                            number_format($sprev_bal,2).
                         '</td><td></td>'.
                         '<td style="text-align: right;">'.
                             number_format($sprev_bal,2).
                         '</td>'.
                         '<td style="text-align: right;font-weight:600; color: darkred;">'.
                             number_format($total_tuition + $total_addons + $sprev_bal,2).
                         '</td>'.
                      '</tr>';
    }          
}elseif (intval($old_account) > 0) { //Student Old Accounts / Previous Balance
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<td style="text-align: right;">'.
                            number_format($total_tuition + $total_addons,2).
                          '</td>'.
                          '<td></td>'.
                      '</tr>'.
                      '<tr>'.
                         '<td style="padding-left: 15px;">'.
                            '<i>Add:</i> Old Account(s)'.
                         '</td>'.
                         '<td style="text-align: right;">'.
                            number_format($old_account,2).
                         '</td><td></td>'.
                         '<td style="text-align: right;">'.
                             number_format($old_account,2).
                         '</td>'.
                         '<td style="text-align: right;font-weight:600; color: darkred;">'.
                             number_format($total_tuition + $total_addons + $old_account,2).
                         '</td>'.
                      '</tr>';
    }
                  
}else{
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<td colspan="2" style="text-align: right; font-weight:600; color: darkred;">'.
                            number_format($total_tuition + $total_addons,2).
                         '</td>'.
                      '</tr>';
    }
}
//Less Payments
//Check Excess Payment from previous
if ($excess_payment > 0) {
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<tr>'.
                            '<td style="padding-left: 15px;">'.
                               '<i>Less:</i> Overpayment'.
                            '</td><td></td>'.
                            '<td style="text-align: right;">'.
                               number_format($excess_payment,2).
                            '</td>'.
                            '<td colspan="2"></td>'.
                      '</tr>';
    }
}
//Check Discount
if ($dscount_amt > 0) {
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<tr>'.
                            '<td style="padding-left: 15px;">'.
                               '<i>Less: </i>'.$dscount_reason.
                            '</td><td></td>'.
                            '<td style="text-align: right;">'.
                               number_format($dscount_amt,2).
                            '</td>'.
                            '<td colspan="2"></td>'.
                      '</tr>';
    }
}
//Check Less %
if ($less_amt > 0) {
    if(in_array($current_enrolid, $college_id)){
       $opt_soa_all .='<tr>'.
                            '<td style="padding-left: 15px;">'.
                               '<i>Less: </i>'.$less_reason.
                            '</td><td></td>'.
                            '<td style="text-align: right;">'.
                               number_format($less_amt,2).
                            '</td>'.
                            '<td colspan="2"></td>'.
                      '</tr>';
    }
}
//Check Payments
if ($total_lesspay > 0) {
    if(in_array($current_enrolid, $college_id)){
        $opt_soa_all .='<tr>'.
                            '<td style="padding-left: 15px;">'.
                               '<i>Less:</i> Total Payments'.
                            '</td><td></td>'.
                            '<td style="text-align: right;">'.
                               number_format($total_lesspay,2).
                            '</td>'.
                            '<td colspan="2" style="text-align: right; font-weight: 600; color:darkgreen;">'.
                               number_format($excess_payment + $dscount_amt + $less_amt + $total_lesspay,2).
                            '</td>'.
                      '</tr>';
    }
}
if(in_array($current_enrolid, $college_id)){
 $opt_soa_all .='<tr class="soa-vline"></tr>';
}
 //Final Balance
 $total_balance = $total_tuition + $total_addons + $sprev_bal + $old_account;
 $total_less = $dscount_amt + $less_amt + $total_lesspay + $excess_payment;
 $final_balance = $total_balance - $total_less;
 $sprev_bal = 0;
 if (round($final_balance,1) > 0 && round($final_balance,1) != 0) {
   //Set old account
   $old_account = $final_balance;
   $excess_payment = 0;
   //Red if not fully paid.
if(in_array($current_enrolid, $college_id)){
   $opt_soa_all .='<tr style="font-weight: 600;color: red;background-color: lightyellow;">'.
                        '<td colspan="4">'.
                           'Balance'.
                        '</td>'.
                        '<td style="text-align:right;">'.
                           number_format($final_balance,2).
                        '</td>'.
                     '</tr>';
}
 }elseif (round($final_balance,1) < 0 && round($final_balance,1) != 0) {
   //Excess Balance
   $excess_payment = abs($final_balance);
   $excess_payment = roundFloat($excess_payment, 2, 5);
   $old_account = 0;
    if(in_array($current_enrolid, $college_id)){
       //Green if Excess.
       $opt_soa_all .='<tr style="font-weight: 600;color: green;background-color: lightyellow;">'.
                         '<td colspan="4">'.
                            'Excess Payment'.
                         '</td>'.
                         '<td style="text-align:right;">'.
                            number_format($excess_payment,2).
                         '</td>'.
                      '</tr>';
    }
 }else{
   //Exact 0 Balance
   $old_account = 0;
   $excess_payment = 0;  
   $final_balance = 0.00;
   if(in_array($current_enrolid, $college_id)){
       //Navy if 0 fully paid.
       $opt_soa_all .='<tr style="font-weight: 600;color: navy;background-color: lightyellow;">'.
                      '<td colspan="4">'.
                         'Balance'.
                      '</td>'.
                      '<td style="text-align:right;">'.
                         number_format($final_balance,2).
                      '</td>'.
                   '</tr>';
   }
 }
if(in_array($current_enrolid, $college_id)){
    $opt_soa_all .= '<tr class="soa-separator"></tr>';
}

//Endline
?>
    }//end while of enrolment

    if($final_balance > 0){
        $opt_soa_all .='<tr style="color: indianred;background-color: lightyellow;">'.
                        '<td colspan="5" style="padding: 8px 0px 8px 0px;">'.
                           '<b>PAYABLE BY TERM(S):</b> Subject to adjustment upon payment of current balance.'.
                        '</td>'.
                     '</tr>';
        if(strtolower($true_sem) == 'summer'){
            $opt_soa_all .='<tr style="font-weight: 600;color: indianred;text-align: center;background-color: lightyellow;">'.
                            '<td>'.
                               'PRELIM'.
                            '</td>'.
                            '<td>'.
                               'MIDTERM'.
                            '</td>'.
                            '<td>'.
                               'SEMI-FINALS'.
                            '</td>'.
                            '<td colspan="2">'.
                               'FINALS'.
                            '</td>'.
                          '</tr>';
            $pay_midterm = '₱ '.number_format($final_balance/3,2);
            $pay_finals = '₱ '.number_format($final_balance/1,2);
            $opt_soa_all .= '<tr style="background-color: lightyellow;">
                                    <td class="text-center">0.00</td>
                                    <td class="text-center">'.$pay_midterm.'</td>
                                    <td class="text-center">0.00</td>
                                    <td colspan="2" class="text-center">'.$pay_finals.'</td>
                                </tr>';
        }else{
            $opt_soa_all .='<tr style="font-weight: 600;color: indianred;text-align: center;background-color: lightyellow;">'.
                            '<td>'.
                               'PRELIM'.
                            '</td>'.
                            '<td>'.
                               'MIDTERM'.
                            '</td>'.
                            '<td>'.
                               'SEMI-FINALS'.
                            '</td>'.
                            '<td colspan="2">'.
                               'FINALS'.
                            '</td>'.
                          '</tr>';
            $pay_prelim = '₱ '.number_format($final_balance/4,2);
            $pay_midterm = '₱ '.number_format($final_balance/3,2);
            $pay_semifinals = '₱ '.number_format($final_balance/2,2);
            $pay_finals = '₱ '.number_format($final_balance/1,2);
            $opt_soa_all .= '<tr style="background-color: lightyellow;">
                                    <td class="text-center">'.$pay_prelim.'</td>
                                    <td class="text-center">'.$pay_midterm.'</td>
                                    <td class="text-center">'.$pay_semifinals.'</td>
                                    <td colspan="2" class="text-center">'.$pay_finals.'</td>
                                </tr>';
        }
    }
    
    
    
    
    
echo $opt_soa_all;

?>