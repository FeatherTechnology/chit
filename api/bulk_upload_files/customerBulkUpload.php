<?php
require '../../ajaxconfig.php';
include 'customerUploadClass.php';
require_once('../../vendor/csvreader/php-excel-reader/excel_reader2.php');
require_once('../../vendor/csvreader/SpreadsheetReader_XLSX.php');


$obj = new customerUploadClass();

$allowedFileType = ['application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'text/csv', 'text/xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
if (in_array($_FILES["excelFile"]["type"], $allowedFileType)) {

    $excelfolder = $obj->uploadFiletoFolder();

    $Reader = new SpreadsheetReader_XLSX($excelfolder);
    $sheetCount = count($Reader->sheets());

    for ($i = 0; $i < $sheetCount; $i++) {

        $Reader->ChangeSheet($i);
        $rowChange = 0;
        foreach ($Reader as $Row) {
            if ($rowChange != 0) { // omitted 0,1 to avoid headers

                $data = $obj->fetchAllRowData($Row);
                $data['loan_id'] = isset($data['loan_id']) ? $data['loan_id'] : '';
                if (isset($data['loan_id'])) {
                    $data['loan_id'] = $obj->getLoanCode($pdo, $data['loan_id']);
                }

                $loan_cat_id = $obj->getLoanCategoryId($pdo, $data['loan_category']);
                $data['loan_category_id'] = $loan_cat_id;
                $agent_id = $obj->checkAgent($pdo, $data['agent_name']);
                $data['agent_id'] = $agent_id;
                 

                $area_id = $obj->getAreaId($pdo, $data['area']);
                $data['area_id'] = $area_id;

                $areaLine = $obj->getAreaLine($pdo, $data['area_id']);
                $data['line_id'] = $areaLine;
                
                $cus_data_response = $obj->checkCustomerData($pdo, $data['cus_id'], $data['cus_profile_id']);
                $data['cus_data'] = $cus_data_response['cus_data'];
                $data['id'] = $cus_data_response['id'];
                
                // Set customer status based on the returned value
                if ($cus_data_response['cus_data'] == 'Existing') {
                    $data['cus_status'] = $cus_data_response['cus_status']; // 'Additional' or 'Renewal'
                } else {
                    $data['cus_status'] = ''; // For new customers, status is empty
                }
                $data['scheme_id'] = $obj->getSchemeId($pdo, $data['scheme_name']);

                $err_columns = $obj->handleError($data);
                if (empty($err_columns)) {
                    // Call LoanEntryTables function
                    $obj->FamilyTable($pdo, $data);
                    $gur_id = $obj->guarantorName($pdo, $data['cus_id']);
                    $data['gur_id'] = $gur_id;   
                    $obj->LoanEntryTables($pdo, $data);
                } else {
                    $errtxt = "Please Check the input given in Serial No: " . ($rowChange) . " on below. <br><br>";
                    $errtxt .= "<ul>";
                    foreach ($err_columns as $columns) {
                        $errtxt .= "<li>$columns</li>";
                    }
                    $errtxt .= "</ul><br>";
                    $errtxt .= "Insertion completed till Serial No: " . ($rowChange - 1);
                    echo $errtxt;
                    exit();
                }
            }

            $rowChange++;
        }
    }
    $message = 'Bulk Upload Completed.';
} else {
    $message = 'File is not in Excel Format.';
}

echo $message;
