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

        $dataArray['cus_id'] = strlen($dataArray['cus_id']) == 12 ? $dataArray['cus_id'] : 'Invalid';
        $cus_dataArray = ['New' => 'New', 'Existing' => 'Existing'];
        $dataArray['cus_data'] = $this->arrayItemChecker($cus_dataArray, $dataArray['cus_data']);

        $cus_exist_typeArray = ['Additional' => 'Additional', 'Renewal' => 'Renewal'];
        $cus_status = $this->arrayItemChecker($cus_exist_typeArray, $dataArray['cus_status']);
        $dataArray['cus_status'] = ($cus_status == 'Not Found') ? '' : $cus_status; //cause cus_exist_type may not be available
        $dataArray['mobile'] = strlen($dataArray['mobile']) == 10 ? $dataArray['mobile'] : 'Invalid';

        $dataArray['dob'] = $this->dateFormatChecker($dataArray['dob']);

        $genderArray = ['Male' => '1', 'Female' => '2', 'Others' => '3'];
        $dataArray['gender'] = $this->arrayItemChecker($genderArray, $dataArray['gender']);

        $dataArray['guarantor_aadhar_no'] = strlen($dataArray['guarantor_aadhar_no']) == 12 ? $dataArray['guarantor_aadhar_no'] : 'Invalid';

        $guarantor_relationshipArray = ['Father' => 'Father', 'Mother' => 'Mother', 'Spouse' => 'Spouse', 'Sister' => 'Sister', 'Brother' => 'Brother', 'Son' => 'Son', 'Daughter' => 'Daughter'];
        $dataArray['guarantor_relationship'] = $this->arrayItemChecker($guarantor_relationshipArray, $dataArray['guarantor_relationship']);
        $dataArray['guarantor_mobile_no'] = strlen($dataArray['guarantor_mobile_no']) == 10 ? $dataArray['guarantor_mobile_no'] : 'Invalid';

        $liveArray = ['Live' => '1', 'Deceased' => '2'];
        $dataArray['guarantor_live'] = $this->arrayItemChecker($liveArray, $dataArray['guarantor_live']);

        $residential_typeArray = ['Own' => '1', 'Rental' => '2', 'Lease' => '3', 'Quarters' => '4'];
        $residential_type = $this->arrayItemChecker($residential_typeArray, $dataArray['residential_type']);
        $dataArray['residential_type'] = ($residential_type == 'Not Found') ? '' : $residential_type; //cause residential_type may not be available

        $area_confirm_typeArray = ['Resident' => '1', 'Occupation' => '2'];
        $dataArray['area_confirm'] = $this->arrayItemChecker($area_confirm_typeArray, $dataArray['area_confirm']);

        $profit_typeArray = ['Calculation' => '0', 'Scheme' => '1'];
        $dataArray['profit_type'] = $this->arrayItemChecker($profit_typeArray, $dataArray['profit_type']);

        $due_method_calcArray = ['Monthly' => 'Monthly', 'Weekly' => 'Weekly', 'Daily' => 'Daily'];
        $dataArray['due_method'] = $this->arrayItemChecker($due_method_calcArray, $dataArray['due_method']);

        $due_method_schemeArray = ['Monthly' => '1', 'Weekly' => '2', 'Daily' => '3'];
        $due_method_scheme = $this->arrayItemChecker($due_method_schemeArray, $dataArray['due_method_scheme']);
        $dataArray['due_method_scheme'] = ($due_method_scheme == 'Not Found') ? '' : $due_method_scheme; //cause due_method_scheme may not be available

        $schemeday_typeArray = ['Monday' => '1','Tuesday' => '2','Wednesday'=>'3','Thursday'=>'4','Friday'=>'5','Saturday'=>'6','Sunday'=>'7'];
        $dataArray['scheme_day'] = $this->arrayItemChecker($schemeday_typeArray, $dataArray['scheme_day']);
        $dataArray['loan_date'] = $this->dateFormatChecker($dataArray['loan_date']);

        $dataArray['dueStart_date'] = $this->dateFormatChecker($dataArray['dueStart_date']);

        $dataArray['maturity_date'] = $this->dateFormatChecker($dataArray['maturity_date']);
        $dataArray['issue_date'] = $this->dateFormatChecker($dataArray['issue_date']);

        $referred_typeArray = ['Yes' => '0', 'No' => '1'];
        $dataArray['referred'] = $this->arrayItemChecker($referred_typeArray, $dataArray['referred']);

        $payment_typeArray = ['Cash' => '1', 'Bank Transfer' => '2', 'Cheque' => '3'];
        $dataArray['payment_mode'] = $this->arrayItemChecker($payment_typeArray, $dataArray['payment_mode']);

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
   
    function getLoanCode($pdo, $id)
    {
        if (!isset($id) || $id == '') {
            $qry = $pdo->query("SELECT loan_id FROM loan_entry_loan_calculation WHERE loan_id != '' ORDER BY id DESC LIMIT 1");

            if ($qry->rowCount() > 0) {
                $qry_info = $qry->fetch();
                $l_no = ltrim(strstr($qry_info['loan_id'], '-'), '-');
                $l_no = $l_no + 1;
                $loan_ID_final = "LID-" . "$l_no";
            } else {
                $loan_ID_final = "LID-101";
            }
        } else {
            $stmt = $pdo->prepare("SELECT loan_id FROM loan_entry_loan_calculation WHERE id = :id");
            $stmt->execute(['id' => $id]);

            if ($stmt->rowCount() > 0) {
                $qry_info = $stmt->fetch();
                $loan_ID_final = $qry_info['loan_id'];
            } else {
                $loan_ID_final = "LID-101"; // Default value if not found
            }
        }

        return $loan_ID_final;
    }

    function checkCustomerData($pdo, $cus_id, $cus_profile_id)
    {
        $cus_id = strip_tags($cus_id); // Sanitize input
    
        // Query to check customer profile and status
        $qry = $pdo->query("SELECT cp.*, cs.status 
                            FROM customer_profile cp
                            INNER JOIN customer_status cs ON cp.cus_id = cs.cus_id
                            WHERE cp.cus_id = '$cus_id' 
                            AND cp.id != '$cus_profile_id'");
    
        if ($qry && $qry->rowCount() > 0) {
            $result = $qry->fetch(PDO::FETCH_ASSOC);
            $status = $result['status'];  // Fetch the customer status
    
            // Determine cus_status based on status value
            if ($status >= 1 && $status <= 6) {
                $cus_status = '';  // For status between 1 and 6, cus_status is empty
            } elseif ($status == 7 || $status == 8) {
                $cus_status = 'Additional';  // For status 7 or 8, cus_status is 'Additional'
            } elseif ($status >= 9) {
                $cus_status = 'Renewal';  // For status 9 or above, cus_status is 'Renewal'
            }
    
            $response['cus_data'] = 'Existing';  // Customer is 'Existing'
            $response['id'] = $result['id'];     // Return the customer ID
            $response['cus_status'] = $cus_status;  // Include the determined cus_status
    
        } else {
            // If no result is found, it's a new customer
            $response['cus_data'] = 'New';   // Customer is 'New'
            $response['id'] = '';            // No ID for new customers
            $response['cus_status'] = '';    // cus_status is empty for new customers
        }
    
        return $response;
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

    function getAreaId($pdo, $areaname)
    {
        $stmt = $pdo->query("SELECT anc.id, anc.areaname  FROM area_creation ac JOIN area_name_creation anc ON FIND_IN_SET(anc.id, ac.area_id)
        WHERE  LOWER(REPLACE(TRIM(anc.areaname),' ','')) = LOWER(REPLACE(TRIM('$areaname'),' ',''))");
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $area_id = $row["id"];
        } else {
            $area_id = 'Not Found';
        }

        return $area_id;
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
        $check_query = "SELECT id FROM family_info WHERE cus_id = '" . $data['cus_id'] . "' AND fam_aadhar = '" . $data['guarantor_aadhar_no'] . "'";
        $result = $pdo->query($check_query);
        if ($result->rowCount() == 0) {
            $insert_query = "INSERT INTO family_info (cus_id, fam_name, fam_relationship, fam_age, fam_live, fam_occupation,fam_aadhar, fam_mobile, insert_login_id, created_on, updated_on) 
                VALUES (
                    '" . $data['cus_id'] . "',
                    '" . $data['guarantor_name'] . "',
                    '" . $data['guarantor_relationship'] . "',
                    '" . $data['guarantor_age'] . "',
                    '" . $data['guarantor_live'] . "',
                    '" . $data['guarantor_occupation'] . "',
                    '" . $data['guarantor_aadhar_no'] . "',
                    '" . $data['guarantor_mobile_no'] . "',
                    '" . $user_id . "',
                    '" . strip_tags($data['loan_date']) . "',
                    '" . strip_tags($data['loan_date']) . "'
                )
            ";

            $pdo->query($insert_query);
        }
    }

    function LoanEntryTables($pdo, $data)
    {
        // Print or log $data to see what values are being passed
        $user_id = $_SESSION['user_id'];
        $insert_cp_query = "INSERT INTO customer_profile (
            cus_id, cus_name, gender, dob, age, mobile1,pic, guarantor_name, gu_pic, cus_data, cus_status, res_type, res_detail, res_address, native_address, occupation, occ_detail, occ_income, occ_address, area_confirm, area, line, cus_limit, about_cus, insert_login_id, created_on, updated_on
        ) VALUES (
            '" . strip_tags($data['cus_id']) . "', '" . strip_tags($data['cus_name']) . "', '" . strip_tags($data['gender']) . "', '" . strip_tags($data['dob']) . "', '" . strip_tags($data['age']) . "', 
            '" . strip_tags($data['mobile']) . "', '', '" . strip_tags($data['gur_id']) . "', '', '" . strip_tags($data['cus_data']) . "', 
            '" . strip_tags($data['cus_status']) . "', '" . strip_tags($data['residential_type']) . "', '" . strip_tags($data['resident_detail']) . "', '" . strip_tags($data['res_address']) . "', 
            '" . strip_tags($data['native_address']) . "', '" . strip_tags($data['occupation']) . "', '" . strip_tags($data['occ_detail']) . "', '" . strip_tags($data['occ_income']) . "', 
            '" . strip_tags($data['occ_address']) . "', '" . strip_tags($data['area_confirm']) . "', '" . strip_tags($data['area_id']) . "', '" . strip_tags($data['line_id']) . "', 
             '" . strip_tags($data['cus_limit']) . "',  '" . strip_tags($data['about_cus']) . "', '" . $user_id . "', '" . strip_tags($data['loan_date']) . "', '" . strip_tags($data['loan_date']) . "'
        )";

        $pdo->query($insert_cp_query);

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
