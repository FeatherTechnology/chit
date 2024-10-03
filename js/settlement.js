$(document).ready(function () {
    $(document).on('click', '.back_btn', function () {
        $('.settlement_table_content').show();
        $('#settlement_content,.back_btn').hide();
        $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        $('#payment_type').val('');
        $('#settle_type').val('');
        resetValidation()
    });

    // Initial Hide of all optional fields
    $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();

    // Payment Type Change
    $('#payment_type').change(function () {
        let paymentType = $(this).val();
        fetchSettlementData($('#groupid').val()); // Fetch data based on group ID
        updateSettleAmount();
        $('#settle_type').val('');
        resetValidation()
        if (paymentType == "1") { // Split Payment
            $('#settle_type_container').show();
            getBankName()
            $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
            // Calculate balance amount whenever the cash, cheque, or transaction values are entered
            $('#settle_cash, #cheque_val, #transaction_val').on('input', function () {
                calculateBalance();
            });
        } else if (paymentType == "2") { // Single Payment
            $('#settle_type_container').show();
            $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        } else {
            $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        }
    });

    $('#settle_type').change(function () {
        let settleType = $(this).val();
        updateSettleAmount();
        let paymentType = $('#payment_type').val();

        if (paymentType == '2') {  // Handling for Payment Type 2
            if (settleType == "1") { // Cash
                resetValidation();
                $('#cash_container').show();
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
                $('#settle_cash').prop('readonly', true);
                $('#transaction_val').val('');
                $('#cheque_val').val('');
            } else if (settleType == "2") { // Cheque
                resetValidation();
                getBankName();
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').show();
                $('#cash_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
                $('#cheque_val').prop('readonly', true);
                $('#settle_cash').val('');
                $('#transaction_val').val('');
            } else if (settleType == "3") { // Bank Transfer
                resetValidation();
                getBankName();
                $('#bank_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').show();
                $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').hide();
                $('#transaction_val').prop('readonly', true);
                $('#cheque_val').val('');
                $('#settle_cash').val('');
            } else {
                $('#cash_container, #bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            }
        } else if (paymentType == '1') {  // Handling for Payment Type 1 (assuming it's another value, modify if needed)
            if (settleType == "1") { // Cash
                resetValidation();
                $('#cash_container').show();
                $('#balance_remark_container').show();
                $('#settle_cash').prop('readonly', false);
                $('#balance_amount').val('');
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            } else if (settleType == "2") { // Cheque
                resetValidation();
                getBankName();
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').show();
                $('#balance_remark_container').show();
                $('#cheque_val').prop('readonly', false);
                $('#cash_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
                $('#balance_amount').val('');
            } else if (settleType == "3") { // Bank Transfer
                resetValidation();
                getBankName();
                $('#bank_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').show();
                $('#balance_remark_container').show();
                $('#transaction_val').prop('readonly', false);
                $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').hide();
                $('#balance_amount').val('');
            } else {
                $('#cash_container, #bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            }
        }
        let openingHandCash = 0, openingBankCash = 0, closingHandCash = 0, closingBankCash = 0;

        // Fetch opening balance
        $.ajax({
            url: 'api/accounts_files/accounts/opening_balance.php',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response && response.length > 0) {
                    openingHandCash = response[0].hand_cash;
                    openingBankCash = response[0].bank_cash;
                    // Now fetch the closing balance
                    fetchClosingBalance();
                }
            },
        });

        // Fetch closing balance
        function fetchClosingBalance() {
            $.ajax({
                url: 'api/accounts_files/accounts/closing_balance.php',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response && response.length > 0) {
                        closingHandCash = response[0].hand_cash;
                        closingBankCash = response[0].bank_cash;
                        // Now perform the combined calculation and validation
                        performValidation();
                    }
                },
            });
        }

        // Perform validation using combined opening and closing balances
        // Perform validation using combined opening and closing balances
        function performValidation() {
            // Add opening and closing balances
            let totalHandCash = openingHandCash + closingHandCash;
            let totalBankCash = openingBankCash + closingBankCash;
            let cheque_val = parseFloat($('#cheque_val').val().replace(/,/g, '')) || 0;
            let transaction_val = parseFloat($('#transaction_val').val().replace(/,/g, '')) || 0;
            let totalBankCashAmount = cheque_val + transaction_val;

            // Validate closing balance for Split Payment
            let submitButtonDisabled = false;

            if (paymentType === '1') {
                let settle_cash = parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0;
                if (settle_cash > totalHandCash) {
                    swalError('Warning', `Hand cash (₹${moneyFormatIndia(settle_cash)}) exceeds the available total balance (₹${moneyFormatIndia(totalHandCash)}).`);
                    submitButtonDisabled = true;

                }                

                if (totalBankCashAmount > totalBankCash) {
                    swalError('Warning', `Bank cash (₹${totalBankCashAmount}) exceeds the available total balance (₹${totalBankCash}).`);
                    submitButtonDisabled = true;
                }
            } else if (paymentType === '2') { // Single Payment
                if (settleType === '1') { // Settle with Cash
                    let settle_cash = parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0;
                    if (settle_cash > totalHandCash) {
                        swalError('Warning', `Hand cash (₹${moneyFormatIndia(settle_cash)}) exceeds the available total balance (₹${moneyFormatIndia(totalHandCash)}).`);
                        submitButtonDisabled = true;
                    }
                } else if (settleType >= '2') { // Settle with Bank
                    if (totalBankCashAmount > totalBankCash) {
                        swalError('Warning', `Bank cash (₹${moneyFormatIndia(totalBankCashAmount)}) exceeds the available total balance (₹${moneyFormatIndia(totalBankCash)}).`);
                        submitButtonDisabled = true;
                    }
                }
            }

            $('#submit_settle_info').attr('disabled', submitButtonDisabled);
        }
    });

    $(document).on('click', '.settleListBtn', function (event) {
        event.preventDefault();
        $('.settlement_table_content').hide();
        $('#settlement_content,.back_btn').show();
        let id = $(this).attr('value');
        $('#groupid').val(id);
        editGroupCreation(id)
        editCustomerCreation(id)
        getGuarantorRelationship(id)
        fetchSettlementData(id)
        $('#groupid').val(id);
        checkBalance()
        setTimeout(function() { 
            getDocInfoTable(); 
            getCashAck();  
        }, 1000);
      
        $('#submit_settle_info').attr('disabled', false);

    })

    // $('#gua_name').on('change', function () {
    //     const guarantorId = $(this).val();
    //     if (guarantorId === '-1') {
    //         // If "Customer" is selected
    //         $('#gua_relationship').val('Customer');
    //     } else if (guarantorId) {
    //         // Fetch the guarantor relationship if a valid ID is selected
    //         getGrelationshipName(guarantorId);
    //     } else {
    //         // Clear the relationship field if no valid ID is selected
    //         $('#gua_relationship').val('');
    //     }
    // });
    $('#gua_name').on('change', function () {
        const guarantorId = $(this).val();
        if (guarantorId && guarantorId !== 'null') {
            // Fetch the guarantor relationship if a valid ID is selected
            getGrelationshipName(guarantorId);
        } else {
            // Set default relationship as 'Customer' if no valid ID is selected
            $('#gua_relationship').val('Customer');
        }
    });
    $('#doc_holder_name').on('change', function () {
        const guarantorId = $(this).val();
        if (guarantorId && guarantorId !== 'null') {
            // Fetch the guarantor relationship if a valid ID is selected
            getDocrelationshipName(guarantorId);
        } else {
            // Set default relationship as 'Customer' if no valid ID is selected
            $('#doc_relationship').val('Customer');
        }
    });

    $('#settle_cash, #cheque_val, #transaction_val').on('input', function () {
        // Remove commas first, then parse to float
        let settle_balance = parseFloat($('#settle_balance').val().replace(/,/g, '')) || 0; // Convert to float, default to 0 if empty
        let payment_type = $('#payment_type').val();
        let settle_type = $('#settle_type').val();
        let settle_cash = parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0; // Convert to float, default to 0 if empty
        let cheque_val = parseFloat($('#cheque_val').val().replace(/,/g, '')) || 0; // Convert to float, default to 0 if empty
        let transaction_val = parseFloat($('#transaction_val').val().replace(/,/g, '')) || 0; // Convert to float, default to 0 if empty

        if (payment_type == '1') { // Split Payment
            var totalAmount = settle_cash + cheque_val + transaction_val;

            // Compare totalAmount with settle_balance
            if (totalAmount > settle_balance) {
                swalError('Warning', 'The entered amount exceeds the settlement balance.');
                $('#balance_amount').val(0);
                $('#settle_cash').val('');
                $('#cheque_val').val('');
                $('#transaction_val').val('');
            }
        }

        // Variables for balances
        let openingHandCash = 0, openingBankCash = 0, closingHandCash = 0, closingBankCash = 0;

        // Fetch opening balance
        $.ajax({
            url: 'api/accounts_files/accounts/opening_balance.php',
            method: 'POST',
            dataType: 'json',
            success: function (response) {
                if (response && response.length > 0) {
                    openingHandCash = response[0].hand_cash;
                    openingBankCash = response[0].bank_cash;
                    // Now fetch the closing balance
                    fetchClosingBalance();
                }
            },
        });

        // Fetch closing balance
        function fetchClosingBalance() {
            $.ajax({
                url: 'api/accounts_files/accounts/closing_balance.php',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    if (response && response.length > 0) {
                        closingHandCash = response[0].hand_cash;
                        closingBankCash = response[0].bank_cash;
                        // Now perform the combined calculation and validation
                        performValidation();
                    }
                },
            });
        }

        // Perform validation using combined opening and closing balances
        // Perform validation using combined opening and closing balances
        function performValidation() {
            // Add opening and closing balances
            let totalHandCash = openingHandCash + closingHandCash;
            let totalBankCash = openingBankCash + closingBankCash;
            let cheque_val = parseFloat($('#cheque_val').val().replace(/,/g, '')) || 0;
            let transaction_val = parseFloat($('#transaction_val').val().replace(/,/g, '')) || 0;
            let totalBankCashAmount = cheque_val + transaction_val;

            // Validate closing balance for Split Payment
            let submitButtonDisabled = false;

            if (payment_type === '1') {
                let settle_cash = parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0;
                if (settle_cash > totalHandCash) {
                    swalError('Warning', `Hand cash (₹${moneyFormatIndia(settle_cash)}) exceeds the available total balance (₹${moneyFormatIndia(totalHandCash)}).`);
                    $('#settle_cash').val('');
                    submitButtonDisabled = true;
                }                


                if (totalBankCashAmount > totalBankCash) {
                    swalError('Warning', `Bank cash (₹${moneyFormatIndia(totalBankCashAmount)}) exceeds the available total balance (₹${moneyFormatIndia(totalBankCash)}).`);
                    submitButtonDisabled = true;
                    $('#transaction_val').val('');
                    $('#cheque_val').val('');
                }
            } else if (payment_type === '2') { // Single Payment
                if (settle_type === '1') { // Settle with Cash
                    let settle_cash = parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0;
                    if (settle_cash > totalHandCash) {
                        swalError('Warning', `Hand cash (₹${moneyFormatIndia(settle_cash)}) exceeds the available total balance (₹${moneyFormatIndia(totalHandCash)}).`);
                        submitButtonDisabled = true;
                    }
                } else if (settle_type === '2') { // Settle with Bank
                    if (totalBankCashAmount > totalBankCash) {
                        swalError('Warning', `Bank cash (₹${moneyFormatIndia(totalBankCashAmount)}) exceeds the available total balance (₹${moneyFormatIndia(totalBankCash)}).`);
                        submitButtonDisabled = true;
                    }
                }
            }

            $('#submit_settle_info').attr('disabled', submitButtonDisabled);
        }

    });


    $('#submit_settle_info').click(function (event) {
        event.preventDefault();

        // Gathering form data into an object
        let settleInfo = {
            'auction_id': $('#groupid').val(),
            'group_id': $('#group_id').val(),
            'cus_id': $('#cus_id').val(),
            'settle_date': $('#settle_date').val(),
            'settle_amount': $('#settle_amount').val().replace(/,/g, ''),
            'settle_balance': parseFloat($('#settle_balance').val().replace(/,/g, '')) || 0,
            'payment_type': $('#payment_type').val(),
            'settle_type': $('#settle_type').val(),
            'bank_name': $('#bank_name').val(),
            'settle_cash': parseFloat($('#settle_cash').val().replace(/,/g, '')) || 0,
            'cheque_no': $('#cheque_no').val(),
            'cheque_val': parseFloat($('#cheque_val').val().replace(/,/g, '')) || 0,
            'cheque_remark': $('#cheque_remark').val(),
            'transaction_id': $('#transaction_id').val(),
            'transaction_val': parseFloat($('#transaction_val').val().replace(/,/g, '')) || 0,
            'transaction_remark': $('#transaction_remark').val(),
            'balance_amount': $('#balance_amount').val().replace(/,/g, ''),
            'gua_name': $('#gua_name').val(),
            'gua_relationship': $('#gua_relationship').val(),
        };

        // Validate the form data
        let isValid = isFormDataValid(settleInfo);

        // Validate settlement amounts for Split Payment
        if (settleInfo.payment_type == '1') { // Split Payment
            var totalAmount = settleInfo.settle_cash + settleInfo.cheque_val + settleInfo.transaction_val;

            // Compare totalAmount with settle_balance, ensuring both are in a comparable format (no commas)
            if (totalAmount > settleInfo.settle_balance) {
                swalError('Warning', 'The entered amount exceeds the settlement balance.');
                isValid = false; // Ensure the form doesn't submit if invalid
            }
        }

        // Check if the form is valid before submission
        if (isValid) {
            $.post('api/settlement_files/submit_settlement_info.php', settleInfo, function (response) {
                if (response == '1') {
                    swalSuccess('Success', 'Settlement Successfully');
                    $('.settlement_table_content').show();
                    $('#settlement_content, .back_btn').hide();
                    getSettlementTable();
                    $('#groupid').val('');
                    $('#settlement_screen').trigger('reset');
                } else {
                    swalError('Warning', 'Settlement Failed.');
                }
            });
        }
    });

///////////////////////////////////////////////////////////////////Document info START ////////////////////////////////////////////////////////////////////////////

$('#submit_doc_info').click(function (event) {
    event.preventDefault();
    let doc_name = $('#doc_name').val();
    let doc_type = $('#doc_type').val();
    let doc_holder_name = $('#doc_holder_name').val();
    let doc_relationship = $('#doc_relationship').val();
    let remarks = $('#remarks').val();
    let doc_upload = $('#doc_upload')[0].files[0];
    let doc_upload_edit = $('#doc_upload_edit').val();
    let doc_info_id = $('#doc_info_id').val();
    let cus_id = $('#cus_id').val();
    let auction_id = $('#groupid').val();
    var data = ['doc_name', 'doc_type', 'doc_holder_name', 'doc_relationship']

    var isValid = true;
    data.forEach(function (entry) {
        var fieldIsValid = validateField($('#' + entry).val(), entry);
        if (!fieldIsValid) {
            isValid = false;
        }
    });
    if (doc_upload === undefined && doc_upload_edit === '') {
        let isUploadValid = validateField('', 'doc_upload');
        let isHiddenValid = validateField('', 'doc_upload_edit');
        if (!isUploadValid || !isHiddenValid) {
            isValid = false;
        }
        else {
            $('#doc_upload').css('border', '1px solid #cecece');
            $('#doc_upload_edit').css('border', '1px solid #cecece');
        }
    }
    else {
        $('#doc_upload').css('border', '1px solid #cecece');
        $('#doc_upload_edit').css('border', '1px solid #cecece');
    }
    if (isValid) {
        let docInfo = new FormData();
        docInfo.append('doc_name', doc_name);
        docInfo.append('doc_type', doc_type);
        docInfo.append('doc_holder_name', doc_holder_name);
        docInfo.append('doc_relationship', doc_relationship);
        docInfo.append('remarks', remarks);
        docInfo.append('doc_upload', doc_upload);
        docInfo.append('doc_upload_edit', doc_upload_edit);
        docInfo.append('cus_id', cus_id);
        docInfo.append('groupid', auction_id);
        docInfo.append('id', doc_info_id);

        $.ajax({
            url: 'api/settlement_files/submit_document_info.php',
            type: 'post',
            data: docInfo,
            contentType: false,
            processData: false,
            cache: false,
            success: function (response) {
                if (response == '1') {
                    swalSuccess('Success', 'Document Info Updated Successfully')
                } else if (response == '2') {
                    swalSuccess('Success', 'Document Info Added Successfully')  
                } else {
                    swalError('Alert', 'Failed')
                }
                groupData()
                getDocCreationTable();
                $('#doc_info_form input:not(#grp_id):not(#grp_name):not(#auction_month)').val('');
                //$('#clear_doc_form').trigger('click');
                $('#doc_info_id').val('');
                $('#doc_upload_edit').val('');
            }
        });
    }
});

$(document).on('click', '.docActionBtn', function () {
    let id = $(this).attr('value');
    $.post('api/settlement_files/doc_info_data.php', { id }, function (response) {
        $('#doc_name').val(response[0].doc_name);
        $('#doc_type').val(response[0].doc_type);
        $('#doc_holder_name').val(response[0].holder_name);
        $('#doc_relationship').val(response[0].relationship);
        $('#remarks').val(response[0].remarks);
        $('#doc_upload_edit').val(response[0].upload);
        $('#doc_info_id').val(response[0].id);
    }, 'json');
});

$(document).on('click', '.docDeleteBtn', function () {
    let id = $(this).attr('value');
    swalConfirm('Delete', 'Are you sure you want to delete this document?', deleteDocInfo, id);
});

$('#clear_doc_form').click(function () {
    $('#doc_info_form input:not(#grp_id):not(#grp_name):not(#auction_month)').val('');
    $('#doc_info_id').val('');
    $('#doc_upload_edit').val('');
    $('#doc_info_form input').css('border', '1px solid #cecece');
    $('#doc_info_form select').css('border', '1px solid #cecece');
})
///////////////////////////////////////////////////////////////////Document info END ////////////////////////////////////////////////////////////////////////////

    ////////////////////////Document End/////////////////////////////////////////////

});
$(function () {
    getSettlementTable();
});

function getSettlementTable() {
    serverSideTable('#settlement_list_table', '', 'api/settlement_files/settlement_list.php');

}
function editGroupCreation(id) {
    $.post('api/settlement_files/settle_group_data.php', { id: id }, function (response) {
        if (response && response.length > 0) {
            let data = response[0];
            $('#groupid').val(id);
            $('#group_id').val(data.group_id);
            $('#group_name').val(data.grp_name);
            $('#chit_value').val(moneyFormatIndia(data.chit_value));
            $('#commission').val(moneyFormatIndia(data.commission));
            $('#total_members').val(data.total_members);
            $('#total_month').val(data.total_months);
            $('#start_month').val(data.start_month);
            $('#end_month').val(data.end_month);
            $('#grp_month').val(data.auction_month);
        }
    }, 'json');
}
function groupData(){
    let group_id= $('#group_id').val();
    $('#grp_id').val(group_id);
    let group_name = $('#group_name').val();
    $('#grp_name').val(group_name);
    let grp_month = $('#grp_month').val();
    $('#auction_month').val(grp_month);
}
function editCustomerCreation(id) {
    $.post('api/settlement_files/settle_customer_data.php', { id: id }, function (response) {
        if (response.length > 0) {
            $('#groupid').val(id);
            $('#cus_id').val(response[0].cus_id);
            $('#customer_name').val(response[0].cus_name); // Full name in a single field
            $('#place').val(response[0].place);
            $('#mobile1').val(response[0].mobile1);
            $('#occupation').val(response[0].occupations); // Assuming you have a field for occupations
            $('#referred_by').val(response[0].reference_type); // Assuming you have a field for reference_type

            // Handle image path if necessary
            let path = "uploads/customer_creation/cus_pic/";
            $('#per_pic').val(response[0].pic);
            var img = $('#imgshow');
            img.attr('src', path + response[0].pic);
        } else {
            alert("No data found for this customer.");
        }
    }, 'json');
}
function getGrelationshipName(guarantorId) {
    $.ajax({
        url: 'api/settlement_files/gua_name.php',
        type: 'POST',
        data: { id: guarantorId },
        dataType: 'json',
        cache: false,
        success: function (response) {
            $('#gua_relationship').val(response.guarantor_relationship || 'Customer');
        },
        error: function (xhr, status, error) {
            console.error('Error fetching guarantor relationship:', error);
            $('#gua_relationship').val('Customer');
        }
    });
}
function getDocrelationshipName(guarantorId) {
    $.ajax({
        url: 'api/settlement_files/gua_name.php',
        type: 'POST',
        data: { id: guarantorId },
        dataType: 'json',
        cache: false,
        success: function (response) {
            $('#doc_relationship').val(response.guarantor_relationship || 'Customer');
        },
        error: function (xhr, status, error) {
            console.error('Error fetching guarantor relationship:', error);
            $('#doc_relationship').val('Customer');
        }
    });
}

function getGuarantorRelationship(id) {
    $.post('api/settlement_files/get_guarantor_name.php', { id: id }, function (response) {
        let appendGuarantorOption = "<option value=''>Select Name</option>";
        $.each(response, function (index, val) {
            let selected = '';
            let editGId = $('#gua_name_edit').val(); // Existing guarantor ID (if any)
            if (val.type === 'Guarantor' && val.id == editGId) {
                selected = 'selected';
            }

            // Display type of the person (Guarantor or Customer)
            appendGuarantorOption += "<option value='" + val.id + "' " + selected + ">" + val.name + "</option>";
        });

        $('#gua_name').empty().append(appendGuarantorOption);
        // Clear the relationship field
        $('#gua_relationship').val('');
    }, 'json');
}
function getDocGuarantor() {
    let cus_id = $('#cus_id').val(); // Corrected: added $
    $.post('api/settlement_files/get_document_guarantor.php', { cus_id: cus_id }, function (response) {
        let appendGuarantorOption = "<option value=''>Select Name</option>";
        $.each(response, function (index, val) {
            let selected = '';
            appendGuarantorOption += "<option value='" + val.id + "' " + selected + ">" + val.name + "</option>"; 
        });

        $('#doc_holder_name').empty().append(appendGuarantorOption);
        $('#doc_relationship').val('');
    }, 'json');   
}
function deleteDocInfo(id) {
    $.post('api/settlement_files/delete_doc_info.php', { id }, function (response) {
        if (response == '1') {
            swalSuccess('success', 'Doc Info Deleted Successfully');
            getDocCreationTable();
        }else if (response == '2') {
            swalError('Access Denied', 'Used in NOC Summary');
        }   else {
            swalError('Alert', 'Delete Failed')
        }
    }, 'json');
}

function refreshDocModal() {
    $('#clear_doc_form').trigger('click');
}
function getBankName() {
    $.post('api/settlement_files/get_bank_name.php', function (response) {
        let appendBankOption = "<option value=''>Select Bank Name</option>";
        $.each(response, function (index, val) {
            let selected = '';
            let editGId = $('#bank_name_edit').val(); // Existing guarantor ID (if any)
            if (val.id == editGId) {
                selected = 'selected';
            }
            appendBankOption += "<option value='" + val.id + "' " + selected + ">" + val.bank_name + "</option>";
        });
        $('#bank_name').empty().append(appendBankOption);
    }, 'json');
}
function setSettlementFields(data) {
    const { chit_value, auction_value } = data[0];
    const settlementAmount = chit_value - auction_value;

    function formatDate(date) {
        let day = date.getDate();
        let month = date.getMonth() + 1; // Months are zero-based
        let year = date.getFullYear();

        // Add leading zeros if day or month is less than 10
        if (day < 10) day = '0' + day;
        if (month < 10) month = '0' + month;

        return day + '-' + month + '-' + year;
    }

    // Set Settlement Date to Current Date in dd-mm-yyyy format
    const currentDate = new Date();
    $('#settle_date').val(formatDate(currentDate));

    // Set Settlement Amount and Balance
    // $('#settle_amount').val(settlementAmount);
    $('#settle_amount').val(moneyFormatIndia(settlementAmount));
    checkBalance();
    // Update the UI based on payment and settlement types
    updateSettleAmount();
}
function fetchSettlementData(id) {
    $.post('api/settlement_files/get_settlement_amount.php', { id: id }, function (response) {
        if (response.length > 0) {
            setSettlementFields(response);
        } else {
            // Clear fields if no data found
            $('#settle_date').val('');
            $('#settle_amount').val('');
            $('#settle_balance').val('');
        }
    }, 'json');
}
function getDocCreationTable() {
    let cus_id = $('#cus_id').val();
    let auction_id = $('#groupid').val();
    $.post('api/settlement_files/doc_info_list.php', { cus_id,auction_id }, function (response) {
        let docInfoColumn = [
            "sno",
            "grp_name",
            "group_id",
            "auction_month",
            "doc_name",
            "doc_type",
            "guarantor_name",
            "relationship",
            "remarks",
            "upload",
            "action"
        ]
        appendDataToTable('#doc_creation_table', response, docInfoColumn);
        setdtable('#doc_creation_table')
        $('#doc_info_form input:not(#grp_id):not(#grp_name):not(#auction_month)').val('');
        $('#doc_info_form textarea').val('');
        $('#doc_info_form input').css('border', '1px solid #cecece');
        $('#doc_info_form select').css('border', '1px solid #cecece');
        $('#doc_info_form select').each(function () {
            $(this).val($(this).find('option:first').val());
        });
    }, 'json');
}
function getDocInfoTable() {
    let cus_id = $('#cus_id').val();
    let auction_id = $('#groupid').val();
    console.log(cus_id);
    $.post('api/settlement_files/doc_info_list.php', { cus_id,auction_id }, function (response) {
        let docColumn = [
            "sno",
            "grp_name",
            "group_id",
            "auction_month",
            "doc_name",
            "doc_type",
            "guarantor_name",
            "relationship",
            "remarks",
            "upload"
        ]
        appendDataToTable('#document_info', response, docColumn);
        setdtable('#document_info')
    }, 'json');
}
function updateSettleAmount() {
    const paymentType = $('#payment_type').val();
    const settleType = $('#settle_type').val();

    // Fetch the settle balance value
    const settleBalance = $('#settle_balance').val();

    // Hide all containers initially
    $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #bank_container').hide();

    // Show relevant containers based on payment type and settlement type
    if (paymentType == "2") { // Single Payment
        $('#cheque_no').val('');
        $('#cheque_remark').val('');
        $('#transaction_id').val('');
        $('#cheque_no').val('');
        $('#transaction_remark').val('');
        if (settleType == "1") { // Cash
            $('#settle_cash').val(settleBalance);
            $('#cash_container').show(); // Show the cash container
        } else if (settleType == "2") { // Cheque
            $('#cheque_val').val(settleBalance);
            $('#cheque_no_container, #cheque_val_container, #cheque_remark_container, #bank_container').show(); // Show cheque containers
        } else if (settleType == "3") { // Bank Transfer
            $('#transaction_val').val(settleBalance);
            $('#transaction_id_container, #transaction_val_container, #transaction_remark_container, #bank_container').show(); // Show transaction containers
        }
    } else if (paymentType == "1") { // Split Payment
        $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        $('#settle_type_container').show();
        $('#settle_cash').val('');
        $('#cheque_val').val('');
        $('#transaction_val').val('');
        $('#cheque_no').val('');
        $('#cheque_remark').val('');
        $('#transaction_id').val('');
        $('#cheque_no').val('');
        $('#transaction_remark').val('');
    } else {
        $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
    }
}

function calculateBalance() {
    // Get the settlement balance and remove commas, then parse it as a float
    let settlementBalance = parseFloat($('#settle_balance').val().replace(/,/g, '')) || 0;
    let cashVal = parseFloat($('#settle_cash').val()) || 0;
    let chequeVal = parseFloat($('#cheque_val').val()) || 0;
    let transactionVal = parseFloat($('#transaction_val').val()) || 0;

    // Calculate the remaining balance
    let remainingBalance = settlementBalance - (cashVal + chequeVal + transactionVal);

    // Format the remaining balance using the moneyFormatIndia function
    $('#balance_amount').val(moneyFormatIndia(remainingBalance));
}

function isFormDataValid(settleInfo) {
    let isValid = true;

    // Validate gua_name field
    if (!validateField(settleInfo.gua_name, 'gua_name')) {
        isValid = false;
    }

    // Validate payment type
    if (!validateField(settleInfo.payment_type, 'payment_type')) {
        isValid = false;
    }

    if (settleInfo.payment_type == "1") { // Split Payment
        // Validate settle type
        if (!validateField(settleInfo.settle_type, 'settle_type')) {
            isValid = false;
        }

        // Validate specific fields based on settle_type
        if (settleInfo.settle_type == "1") { // Cash
            if (!validateField(settleInfo.settle_cash, 'settle_cash')) {
                isValid = false;
            }
        } else if (settleInfo.settle_type == "2") { // Cheque
            if (!validateField(settleInfo.cheque_no, 'cheque_no') ||
                !validateField(settleInfo.cheque_val, 'cheque_val') ||
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
        } else if (settleInfo.settle_type == "3") { // Transaction
            if (!validateField(settleInfo.transaction_id, 'transaction_id') ||
                !validateField(settleInfo.transaction_val, 'transaction_val') ||
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
        }

        // Ensure that at least one payment method is filled
        let isCashFilled = settleInfo.settle_cash && settleInfo.settle_cash > 0;
        let isChequeFilled = settleInfo.cheque_val && settleInfo.cheque_val > 0;
        let isTransactionFilled = settleInfo.transaction_val && settleInfo.transaction_val > 0;

        if (!(isCashFilled || isChequeFilled || isTransactionFilled)) {
            isValid = false;
            $('#settle_cash, #cheque_val, #transaction_val').css('border', '1px solid #ff0000');
        } else {
            resetFieldBorders(['settle_cash', 'cheque_val', 'transaction_val']);
        }
    } else if (settleInfo.payment_type == "2") { // Single Payment
        if (!validateField(settleInfo.settle_type, 'settle_type')) {
            isValid = false;
        }

        if (settleInfo.settle_type == "1") { // Cash
            if (!validateField(settleInfo.settle_cash, 'settle_cash')) {
                isValid = false;
            }
        } else if (settleInfo.settle_type == "2") { // Cheque
            if (!validateField(settleInfo.cheque_no, 'cheque_no') ||
                !validateField(settleInfo.cheque_val, 'cheque_val') ||
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
        } else if (settleInfo.settle_type == "3") { // Transaction
            if (!validateField(settleInfo.transaction_id, 'transaction_id') ||
                !validateField(settleInfo.transaction_val, 'transaction_val') ||
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
        }
    }

    return isValid;
}

function resetFieldBorders(fields) {
    fields.forEach(field => {
        document.getElementById(field).style.border = '1px solid #cecece';
    });
}

function resetValidation() {
    const fieldsToReset = [
        'settle_type', 'settle_cash', 'cheque_no', 'bank_name',
        'cheque_val', 'cheque_remark', 'transaction_id', 'transaction_val',
        'transaction_remark', 'payment_type', 'gua_name',
    ];

    fieldsToReset.forEach(fieldId => {
        $('#' + fieldId).css('border', '1px solid #cecece');
    });
}

function getCashAck() {
    let auction_id = $('#groupid').val();
    let cus_id = $('#cus_id').val();
    $.post('api/settlement_files/get_cashack_list.php', { auction_id,cus_id }, function (response) {
        let tableBody = $('#guarantor_table tbody');
        tableBody.empty(); // Clear existing rows

        // Check if response is an array and has elements
        if (Array.isArray(response) && response.length > 0) {
            response.forEach((row, index) => {
                // Append new rows to the table
                tableBody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${row.settle_date}</td>
                        <td>${row.balance_amount}</td>
                        <td>${row.guarantor_name}</td>
                        <td>${row.guarantor_relationship}</td>
                    </tr>
                `);
            });
        } else {
            // Optionally, display a message if no data is available
            tableBody.append(`
                <tr>
                    <td colspan="5">No data available</td>
                </tr>
            `);
        }
    }, 'json');
}


function checkBalance() {
    let auction_id = $('#groupid').val();
    $.ajax({
        url: 'api/settlement_files/get_balance_amount.php',
        type: 'POST',
        data: { "auction_id": auction_id },
        dataType: 'json',
        success: function (response) {
            if (response && response.balance_amount !== undefined) {
                // Check if balance amount is zero
                let balanceAmount = response.balance_amount;
                if (balanceAmount === '0' || balanceAmount === 0) {
                    // Set balance to settlement amount if balance is zero
                    $('#settle_balance').val($('#settle_amount').val());
                } else {
                    $('#settle_balance').val(moneyFormatIndia(balanceAmount));
                }

            } else {
                console.error('Balance amount not found in response');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

