<?php
require '../../ajaxconfig.php';
@session_start();

class customerUploadClass
{
    public function uploadFiletoFolder()
    {
        $excel = $_FILES['excelFile']['name'];
        $excel_temp = $_FILES['excelFile']['tmp_name'];
        $excelfolder = "../../uploads/excel_format/customer_format/" . $excel;

        $fileExtension = pathinfo($excelfolder, PATHINFO_EXTENSION); //get the file extension

        $excel = uniqid() . '.' . $fileExtension;
        while (file_exists("../../uploads/excel_format/customer_format/" . $excel)) {
            // this loop will continue until it generates a unique file name
            $excel = uniqid() . '.' . $fileExtension;
        }
        $excelfolder = "../../uploads/excel_format/customer_format/" . $excel;
        move_uploaded_file($excel_temp, $excelfolder);
        return $excelfolder;
    }

    public function fetchAllRowData($Row)
    {
        $dataArray = array(
            'first_name' => isset($Row[1]) ? $Row[1] : "",
            'last_name' => isset($Row[2]) ? $Row[2] : "",
            'aadhar_number' => isset($Row[3]) ? $Row[3] : "",
            'place' => isset($Row[4]) ? $Row[4] : "",
            'mobile1' => isset($Row[5]) ? $Row[5] : "",
            'address' => isset($Row[6]) ? $Row[6] : "",
            'occupation' => isset($Row[7]) ? $Row[7] : "",
            'occ_detail' => isset($Row[8]) ? $Row[8] : "",
            'income' => isset($Row[9]) ? $Row[9] : "",
            'chit_limit' => isset($Row[10]) ? $Row[10] : "",
            'reference' => isset($Row[11]) ? $Row[11] : "",
            'fam_name' => isset($Row[12]) ? $Row[12] : "",
            'fam_relationship' => isset($Row[13]) ? $Row[13] : "",
            'fam_aadhar' => isset($Row[14]) ? $Row[14] : "",
            'fam_mobile' => isset($Row[15]) ? $Row[15] : "",
            'guarantor_aadhar' => isset($Row[16]) ? $Row[16] : "",
            'grp_name' => isset($Row[17]) ? $Row[17] : "",
            'joining_month' => isset($Row[18]) ? $Row[18] : "",
        );

        $dataArray['guarantor_aadhar'] = strlen($dataArray['guarantor_aadhar']) == 12 ? $dataArray['guarantor_aadhar'] : 'Invalid';
       
        $dataArray['mobile1'] = strlen($dataArray['mobile1']) == 10 ? $dataArray['mobile1'] : 'Invalid';
        $dataArray['fam_aadhar'] = strlen($dataArray['fam_aadhar']) == 12 ? $dataArray['fam_aadhar'] : 'Invalid';
        $dataArray['aadhar_number'] = strlen($dataArray['aadhar_number']) == 12 ? $dataArray['aadhar_number'] : 'Invalid';

        $guarantor_relationshipArray = ['Father' => 'Father', 'Mother' => 'Mother', 'Spouse' => 'Spouse', 'Sister' => 'Sister', 'Brother' => 'Brother', 'Son' => 'Son', 'Daughter' => 'Daughter'];
        $dataArray['fam_relationship'] = $this->arrayItemChecker($guarantor_relationshipArray, $dataArray['fam_relationship']);
        $dataArray['fam_mobile'] = strlen($dataArray['fam_mobile']) == 10 ? $dataArray['fam_mobile'] : 'Invalid';
        $referred_typeArray = ['Yes' => '1', 'No' => '2'];
        $dataArray['reference'] = $this->arrayItemChecker($referred_typeArray, $dataArray['reference']);
        return $dataArray;
    }
    function dateFormatChecker($checkdate)
    {
        // Attempt to create a DateTime object from the provided date
        $dateTime = DateTime::createFromFormat('Y-m-d', $checkdate);

        // Check if the date is in the correct format
        if ($dateTime && $dateTime->format('Y-m-d') === $checkdate) {
            // Date is in the correct format, no need to change anything
            return $checkdate;
        }
        return 'Invalid Date';
    }
    function arrayItemChecker($arrayList, $arrayItem)
    {
        if (array_key_exists($arrayItem, $arrayList)) {
            $arrayItem = $arrayList[$arrayItem];
        } else {
            $arrayItem = 'Not Found';
        }
        return $arrayItem;
    }
   
    function getcusId($pdo, $id)
    {
        if (!isset($id) || $id == '') {
            $qry = $pdo->query("SELECT cus_id FROM customer_creation WHERE cus_id != '' ORDER BY id DESC LIMIT 1");

            if ($qry->rowCount() > 0) {
                $qry_info = $qry->fetch();
                $l_no = ltrim(strstr($qry_info['cus_id'], '-'), '-');
                $l_no = $l_no + 1;
                $cus_ID_final = "C-" . "$l_no";
            } else {
                $cus_ID_final = "C-101";
            }
        } else {
            $stmt = $pdo->prepare("SELECT cus_id FROM customer_creation WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() > 0) {
                $qry_info = $stmt->fetch();
                $cus_ID_final = $qry_info['cus_id'];
            } else {
                $cus_ID_final = "C-101"; // Default value if not found
            }
        }

        return $cus_ID_final;
    }

   
    function guarantorName($pdo,$cus_id)
    {
        $stmt = $pdo->query("SELECT id, fam_name FROM  family_info WHERE cus_id = '$cus_id'");
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $gur_id = $row["id"];
        }
        return $gur_id;
    }

    function placeName($pdo,$place)
    {
        $stmt = $pdo->query("SELECT id, place FROM  place WHERE LOWER(REPLACE(TRIM(place),' ' ,'')) = LOWER(REPLACE(TRIM('$place'),' ' ,''))");
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pl_id = $row["id"];
        }
        return $pl_id;
    }

    function getLoanCategoryId($pdo, $loan_category)
    {
        $stmt = $pdo->query("SELECT lcc.id FROM loan_category_creation lcc LEFT JOIN loan_category lc ON lcc.loan_category = lc.id WHERE LOWER(REPLACE(TRIM(lc.loan_category),' ' ,'')) = LOWER(REPLACE(TRIM('$loan_category'),' ' ,'')) ");
        //  $stmt->execute(['loan_category' => $loan_category]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $loan_cat_id = $row["id"];
        } else {
            $loan_cat_id = 'Not Found';
        }

        return $loan_cat_id;
    }
    function getAreaLine($pdo, $areaId)
    {
        $defaultLinename = 'Invalid';
        $defaultLineId = null;
        $query = "SELECT ac.line_id, lnc.linename 
            FROM `area_creation` ac 
            LEFT JOIN line_name_creation lnc ON ac.line_id = lnc.id
            WHERE FIND_IN_SET(:areaId, ac.area_id)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([':areaId' => $areaId]);

        if ($stmt) {
            if ($stmt->rowCount() > 0) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $lineId = $result['line_id'];
            } else {
                $lineId = $defaultLineId; // If no matching line_id found, set to default
            }
        } else {
            $lineId = $defaultLineId;
        }

        return $lineId;
    }

    function checkAgent($pdo, $agent_name)
    {
        if ($agent_name != '') { // because it's not mandatory
            $stmt = $pdo->query("SELECT id FROM `agent_creation` WHERE LOWER(REPLACE(TRIM(agent_name),' ' ,'')) = LOWER(REPLACE(TRIM('$agent_name'),' ' ,'')) ");
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $agentCheck = $row["id"];
            } else {
                $agentCheck = 'Not Found';
            }
        } else {
            $agentCheck = '';
        }
        return $agentCheck;
    }

    function getSchemeId($pdo, $scheme_name)
    {
        $stmt = $pdo->query("SELECT s.id
        FROM `loan_category_creation` lcc 
        JOIN scheme s ON FIND_IN_SET(s.id, lcc.scheme_name)
        WHERE LOWER(REPLACE(TRIM(s.scheme_name),' ' ,'')) = LOWER(REPLACE(TRIM('$scheme_name'),' ' ,'')) ");
        if ($stmt->rowCount() > 0) {
            $scheme_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        } else {
            $scheme_id = '';
        }
        return $scheme_id;
    }
    function FamilyTable($pdo, $data)
    {
        $user_id = $_SESSION['user_id'];
        $check_query = "SELECT id FROM family_info WHERE cus_id = '" . $data['cus_id'] . "' AND fam_aadhar = '" . $data['fam_aadhar'] . "'";
        $result = $pdo->query($check_query);
        if ($result->rowCount() == 0) {
            $insert_query = "INSERT INTO family_info (cus_id, fam_name, fam_relationship,fam_aadhar, fam_mobile, insert_login_id, created_on) 
                VALUES (
                    '" . $data['cus_id'] . "',
                    '" . $data['fam_name'] . "',
                    '" . $data['fam_relationship'] . "',
                    '" . $data['fam_aadhar'] . "',
                    '" . $data['fam_mobile'] . "',
                    '" . $user_id . "',
                   NOW(),
                )
            ";

            $pdo->query($insert_query);
        }
    }
    function PlaceTable($pdo, $data)
    {
        $user_id = $_SESSION['user_id'];
    
        // Check if the place already exists (case-insensitive and ignoring spaces)
        $check_querys= "SELECT id FROM place 
        WHERE LOWER(REPLACE(TRIM(place), ' ', '')) = LOWER(REPLACE(TRIM('" . $data['place'] . "'), ' ', ''))";
        // Execute the query
        $result1 = $pdo->query($check_querys);
    
        // If the place does not exist, insert it
        if ($result1->rowCount() == 0) {
            $insert_query = "INSERT INTO place (place, insert_login_id, created_on) 
                             VALUES ('" . strip_tags($data['place']) . "',, '$user_id', NOW())";
    
            // Execute the insert query
            $pdo->query($insert_query);
        }
    }
    function sourceTable($pdo, $data)
    {
        $user_id = $_SESSION['user_id'];
    
        // Check if the place already exists (case-insensitive and ignoring spaces)
        $check_queryss = "SELECT id FROM family_info WHERE cus_id = '" . $data['cus_id'] . "' '";
        
        // Execute the query
        $result1 = $pdo->query($check_queryss);
    
        // If the place does not exist, insert it
        if ($result1->rowCount() == 0) {
            $insert_query = "INSERT INTO place (place, insert_login_id, created_on) 
                             VALUES ('$', '$user_id', NOW())";
    
            // Execute the insert query
            $pdo->query($insert_query);
        }
    }
    function CustomerEntryTables($pdo, $data)
    {
        // Print or log $data to see what values are being passed
        $user_id = $_SESSION['user_id'];
        $che_query = "SELECT id FROM customer_creation WHERE cus_id = '" . $data['cus_id'] . "' AND aadhar_number = '" . $data['aadhar_number'] . "'";
        $result2 = $pdo->query($che_query);
        if ($result2->rowCount() == 0) {
        $insertQuery = "INSERT INTO `customer_creation` (
             `cus_id`,
            `first_name`, 
            `last_name`, 
            `aadhar_number`, 
            `place`, 
            `mobile1`, 
            `address`, 
            `chit_limit`, 
            `reference`, 
            `insert_login_id`, `created_on`
        ) VALUES (
        '" . strip_tags($data['cus_id']) . "',
            '" . strip_tags($data['first_name']) . "',
            '" . strip_tags($data['last_name']) . "',
            '" . strip_tags($data['aadhar_number']) . "',
            '" . strip_tags($data['pl_id']) . "',
            '" . strip_tags($data['mobile1']) . "',
            '" . strip_tags($data['address']) . "',
            '" . strip_tags($data['occupation']) . "',
            '" . strip_tags($data['occ_detail']) . "',
            '" . strip_tags($data['income']) . "',
            '" . strip_tags($data['chit_limit']) . "',
            '" . strip_tags($data['reference']) . "',
               '" . $user_id . "', 
            NOW()
        )";
        
        }
        $pdo->query($insertQuery);

        // Get the last inserted ID
        $cus_profile_id = $pdo->lastInsertId();
        $cus_sts_insert_query = "INSERT INTO `customer_status` (`cus_profile_id`, `status`, `update_login_id`, `updated_on`, `cus_id`)  VALUES (:cus_profile_id, 1, :user_id, NOW(), :cus_id)";
        $stmt = $pdo->prepare($cus_sts_insert_query);
        $stmt->execute([
            ':cus_profile_id' => $cus_profile_id,
            ':user_id' => $user_id,
            ':cus_id' => strip_tags($data['cus_id'])
        ]);

        // Insert into loan_entry_loan_calculation table
        $due_method = strip_tags($data['due_method']);
        if ($data['profit_type'] == 1) {
            $due_method = '';
        }

        $insert_vlc = "INSERT INTO loan_entry_loan_calculation (
            cus_profile_id, cus_id, loan_id, loan_category, loan_amount, profit_type, due_method, due_type, profit_method, scheme_due_method, scheme_day, scheme_name, interest_rate, due_period, doc_charge, processing_fees,
            loan_amnt, principal_amnt, interest_amnt, total_amnt, due_amnt, doc_charge_calculate, processing_fees_calculate, net_cash, loan_date, due_startdate, maturity_date, referred, agent_id, agent_name, insert_login_id, created_on, updated_on
        ) VALUES (
            '" . strip_tags($cus_profile_id) . "', '" . strip_tags($data['cus_id']) . "','" . strip_tags($data['loan_id']) . "', '" . strip_tags($data['loan_category_id']) . "','" . strip_tags($data['loan_amount']) . "', '" . strip_tags($data['profit_type']) . "', '" . $due_method . "', '" . strip_tags($data['due_type']) . "',
            '" . strip_tags($data['profit_method']) . "','" . strip_tags($data['due_method_scheme']) . "','" . strip_tags($data['scheme_day']) . "','" . strip_tags($data['scheme_id']) . "',
            '" . strip_tags($data['interest_rate']) . "','" . strip_tags($data['due_period']) . "','" . strip_tags($data['doc_charge']) . "','" . strip_tags($data['processing_fees']) . "','" . strip_tags($data['loan_amount']) . "','" . strip_tags($data['principal_amnt']) . "',
            '" . strip_tags($data['interest_amnt']) . "', '" . strip_tags($data['total_amnt']) . "', '" . strip_tags($data['due_amnt']) . "', '" . strip_tags($data['doc_charge_calculate']) . "', '" . strip_tags($data['processing_fees_calculate']) . "',
            '" . strip_tags($data['net_cash']) . "','" . strip_tags($data['loan_date']) . "','" . strip_tags($data['dueStart_date']) . "','" . strip_tags($data['maturity_date']) . "',
            '" . strip_tags($data['referred']) . "','" . strip_tags($data['agent_id']) . "','" . strip_tags($data['agent_name']) . "','" . $user_id . "','" . strip_tags($data['loan_date']) . "','" . strip_tags($data['loan_date']) . "'
        )";

        $pdo->query($insert_vlc);

        // Get the last inserted Id
        $loan_calculation_id = $pdo->lastInsertId();


        $cus_sts_update_query = "UPDATE `customer_status` SET `loan_calculation_id` = :loan_calculation_id, `status` = 2, `update_login_id` = :user_id, `updated_on` = NOW() WHERE `cus_profile_id` = :cus_profile_id";
        $stmt = $pdo->prepare($cus_sts_update_query);
        $stmt->execute([
            ':loan_calculation_id' => $loan_calculation_id,
            ':user_id' => $user_id,
            ':cus_profile_id' => $cus_profile_id
        ]);

        $insert_li_query = "INSERT INTO `loan_issue` 
        (`cus_id`, `cus_profile_id`, `loan_amnt`, `net_cash`, `payment_mode`, `issue_amnt`, `transaction_id`, `cheque_no`, `issue_date`, `issue_person`, `relationship`, `insert_login_id`, `created_on`) 
        VALUES ('" . strip_tags($data['cus_id']) . "','" . strip_tags($cus_profile_id) . "','" . strip_tags($data['loan_amount']) . "','" . strip_tags($data['net_cash']) .  "', '" . strip_tags($data['payment_mode']) . "', 
         '" . strip_tags($data['net_cash']) .  "', '" . strip_tags($data['transaction_id']) . "','" . strip_tags($data['cheque_no']) . "', '" . strip_tags($data['issue_date']) . "', '" . strip_tags($data['issue_person']) . "', '" . strip_tags($data['relationship']) . "', 
         '" .  $user_id . "', '"  . strip_tags($data['loan_date']) . "')";


        $pdo->query($insert_li_query);


        $cus_sts_update_query2 = "UPDATE `customer_status` 
        SET `status` = 7, `update_login_id` = :user_id, `updated_on` = NOW() 
        WHERE `cus_profile_id` = :cus_profile_id";
        $stmt = $pdo->prepare($cus_sts_update_query2);
        $stmt->execute([
            ':user_id' => $user_id,
            ':cus_profile_id' => $cus_profile_id
        ]);
    }

    function handleError($data)
    {
        $errcolumns = array();

        if ($data['cus_id'] == 'Invalid') {
            $errcolumns[] = 'Customer ID';
        }

        if ($data['cus_data'] == 'Not Found') {
            $errcolumns[] = 'Customer Data';
        }
        if ($data['cus_name'] == '') {
            $errcolumns[] = 'Customer Name';
        }
        if ($data['cus_status'] == 'Existing' && (!preg_match('/^[A-Za-z]+$/', $data['cus_status']) || $data['cus_status'] == '')) {
            $errcolumns[] = 'Customer Existence Type';
        }

        if ($data['cus_name'] == '') {
            $errcolumns[] = 'Customer Name';
        }
        if ($data['dob'] == 'Invalid Date') {
            $errcolumns[] = 'Date Of Birth';
        }
       
        if ($data['mobile'] == 'Invalid') {
            $errcolumns[] = 'Mobile Number';
        }

        if ($data['guarantor_name'] == '') {
            $errcolumns[] = 'Guarantor Name';
        }

        if ($data['guarantor_aadhar_no'] == 'Invalid') {
            $errcolumns[] = 'Guarantor Aadhar';
        }

        if (!preg_match('/^[0-9]+$/', $data['guarantor_age'])) {
            $errcolumns[] = 'Guarantor Age';
        }

        if ($data['guarantor_mobile_no'] == 'Invalid') {
            $errcolumns[] = 'Guarantor Mobile Number';
        }

        if (!preg_match('/^[A-Za-z0-9]+$/', $data['guarantor_occupation'])) {
            $errcolumns[] = 'Guarantor Occupation';
        }

        if ($data['loan_category_id'] == 'Not Found') {
            $errcolumns[] = 'Loan Category ID';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['loan_amount'])) {
            $errcolumns[] = 'Loan Amount';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['principal_amnt'])) {
            $errcolumns[] = 'Principal Amount Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['interest_amnt'])) {
            $errcolumns[] = 'Interest Amount Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['total_amnt'])) {
            $errcolumns[] = 'Total Amount Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['due_amnt'])) {
            $errcolumns[] = 'Due Amount Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['doc_charge_calculate'])) {
            $errcolumns[] = 'Document Charge Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['processing_fees_calculate'])) {
            $errcolumns[] = 'Processing Fee Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['net_cash'])) {
            $errcolumns[] = 'Net Cash Calculation';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['cus_limit'])) {
            $errcolumns[] = 'Customer Limit';
        }

        // Condition 1
        if ($data['area_confirm'] != 'Not Found') {
            // Subcondition 1.1
            if ($data['area_confirm'] == '1') {
                if (
                    $data['residential_type'] == ''
                    || $data['resident_detail'] == ''
                    || $data['res_address'] == ''
                    || $data['native_address'] == ''
                ) {
                    $errcolumns[] = 'Residential Type or Details or Address';
                }
            }
        } else {
            $errcolumns[] = 'Area Confirm Type';
        }

        if ($data['occupation'] == 'Not Found') {
            $errcolumns[] = 'Occupation Type';
        }

        // Condition 6
        if ($data['loan_date'] == 'Invalid Date') {
            $errcolumns[] = 'Loan Date';
        }

        // Condition 7
        if ($data['profit_type'] != 'Not Found') {
            // Subcondition 7.1
            if ($data['profit_type'] == '0') {
                if (
                    $data['due_method'] == 'Not Found'
                    || $data['due_type'] == 'Not Found'
                    || $data['profit_method'] == 'Not Found'
                ) {
                    $errcolumns[] = 'Due Method Calc or Due Type or Profit Method';
                }
            }

            // Subcondition 7.2
            if ($data['profit_type'] == '1') {
                if ($data['due_method_scheme'] == '' || $data['scheme_id'] == '') {
                    $errcolumns[] = 'Due Method Scheme or Scheme Name';
                }
            }
        } else {
            $errcolumns[] = 'Profit Type';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['interest_rate'])) {
            $errcolumns[] = 'Interest Rate';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['due_period'])) {
            $errcolumns[] = 'Due Period';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['doc_charge'])) {
            $errcolumns[] = 'Document Charge';
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $data['processing_fees'])) {
            $errcolumns[] = 'Processing Fee';
        }


        if ($data['dueStart_date'] == 'Invalid Date') {
            $errcolumns[] = 'Due Start From';
        }

        if ($data['maturity_date'] == 'Invalid Date') {
            $errcolumns[] = 'Maturity Date';
        }

        if ($data['issue_date'] == 'Invalid Date') {
            $errcolumns[] = 'Issued Date';
        }

        if ($data['agent_id'] == 'Not Found') {
            $errcolumns[] = 'Agent ID';
        }

        if ($data['issue_person'] == 'Not Found') {
            $errcolumns[] = 'Issue Person';
        }

        if ($data['payment_mode'] == 'Not Found') {
            $errcolumns[] = 'Payment Mode';
        }
        if ($data['area_id'] == 'Not Found') {
            $errcolumns[] = 'Area ID';
        }
        return $errcolumns;
    }
}
