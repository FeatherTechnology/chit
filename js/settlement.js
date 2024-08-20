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
            getBankName()
            $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').show();
            $('#settle_type_container').hide();
            $('#settle_cash, #cheque_val, #transaction_val').prop('readonly', false);
               // Calculate balance amount whenever the cash, cheque, or transaction values are entered
        $('#settle_cash, #cheque_val, #transaction_val').on('input', function() {
            calculateBalance();
        });
        } else if (paymentType == "2") { // Single Payment
            $('#settle_type_container').show();
            $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        } else {
            $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
        }
    });

    // Settlement Type Change
    $('#settle_type').change(function () {
        let settleType = $(this).val();
        updateSettleAmount();
        if (settleType == "1") { // Cash
            $('#cash_container').show();
            $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            $('#settle_cash').prop('readonly', true);
        } else if (settleType == "2") { // Cheque
            resetValidation()
            getBankName()
            $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').show();
            $('#cash_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            $('#cheque_val').prop('readonly', true);
        } else if (settleType == "3") { // Bank Transfer
            resetValidation()
            getBankName()
            $('#bank_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').show();
            $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').hide();
            $('#transaction_val').prop('readonly', true);
        } else {
            $('#cash_container, #bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
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
        fetchSettlementData(id);
        getCashAck()
        checkBalance()
    })

    $('#gua_name').on('change', function () {
        const guarantorId = $(this).val();
        if (guarantorId === '-1') {
            // If "Customer" is selected
            $('#gua_relationship').val('Customer');
        } else if (guarantorId) {
            // Fetch the guarantor relationship if a valid ID is selected
            getGrelationshipName(guarantorId);
        } else {
            // Clear the relationship field if no valid ID is selected
            $('#gua_relationship').val('');
        }
    });
    $('#submit_settle_info').click(function (event) {
        event.preventDefault();
    
        // Gathering form data into an object
        let settleInfo = {
            'auction_id': $('#groupid').val(),
            'settle_date': $('#settle_date').val(),
            'settle_amount': $('#settle_amount').val(),
            'settle_balance': parseFloat($('#settle_balance').val()) || 0,
            'payment_type': $('#payment_type').val(),
            'settle_type': $('#settle_type').val(),
            'bank_name': $('#bank_name').val(),
            'settle_cash': parseFloat($('#settle_cash').val()) || 0,
            'cheque_no': $('#cheque_no').val(),
            'cheque_val': parseFloat($('#cheque_val').val()) || 0,
            'cheque_remark': $('#cheque_remark').val(),
            'transaction_id': $('#transaction_id').val(),
            'transaction_val': parseFloat($('#transaction_val').val()) || 0,
            'transaction_remark': $('#transaction_remark').val(),
            'balance_amount': $('#balance_amount').val(),
            'gua_name': $('#gua_name').val(),
            'gua_relationship': $('#gua_relationship').val(),
        };
    
        // Validate the form data
        let isValid = isFormDataValid(settleInfo);
    
        // Validate settlement amounts for Split Payment
        if (settleInfo.payment_type == '1') { // Split Payment
            var totalAmount = settleInfo.settle_cash + settleInfo.cheque_val + settleInfo.transaction_val;
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
    $('#settle_cash,#cheque_val,#transaction_val').on('change', function () {
    var paymentType = $('#payment_type').val();
    var settleType = $('#settle_type').val();
    var settleBalance = parseFloat($('#settle_balance').val()) || 0;
    var cash = parseFloat($('#settle_cash').val()) || 0;
    var chequeVal = parseFloat($('#cheque_val').val()) || 0;
    var transactionVal = parseFloat($('#transaction_val').val()) || 0;

    if (paymentType == '1') { // Split Payment
        var totalAmount = cash + chequeVal + transactionVal;
        if (totalAmount > settleBalance) {
            swalError('Warning', 'The entered amount exceeds the settlement balance.');
        }
    }
})
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
        }
    }, 'json');
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
            $('#gua_relationship').val(response.guarantor_relationship || '');
        },
        error: function (xhr, status, error) {
            console.error('Error fetching guarantor relationship:', error);
        }
    });
}

function getGuarantorRelationship(id) {
    $.post('api/settlement_files/get_guarantor_name.php', { id: id }, function (response) {
        let appendGuarantorOption = "<option value=''>Select Guarantor Name</option>";
        $.each(response, function (index, val) {
            let selected = '';
            let editGId = $('#gua_name_edit').val(); // Existing guarantor ID (if any)
            if (val.id == editGId) {
                selected = 'selected';
            }
            appendGuarantorOption += "<option value='" + val.id + "' " + selected + ">" + val.guarantor_name + "</option>";
        });
        appendGuarantorOption += "<option value='-1'>Customer</option>";
        $('#gua_name').empty().append(appendGuarantorOption);
        // Clear the relationship field
        $('#gua_relationship').val('');
    }, 'json');
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
    $('#settle_amount').val(settlementAmount);
   // $('#settle_balance').val(moneyFormatIndia(settlementAmount));
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

function updateSettleAmount() {
    const paymentType = $('#payment_type').val();
    const settleType = $('#settle_type').val();
    
    // Fetch the settle balance value
    const settleBalance = $('#settle_balance').val();

    // Hide all containers initially
    $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #bank_container').hide();
    
    // Show relevant containers based on payment type and settlement type
    if (paymentType == "2") { // Single Payment
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
        $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').show();
        $('#settle_type_container').hide();
        $('#settle_cash').val('');
        $('#cheque_val').val('');
        $('#transaction_val').val('');
    } else {
        $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
    }
}

function calculateBalance() {
    // Assuming `settlementBalance` is the total balance amount fetched from the server
    let settlementBalance = parseFloat($('#settle_balance').val()) || 0;
    let cashVal = parseFloat($('#settle_cash').val()) || 0;
    let chequeVal = parseFloat($('#cheque_val').val()) || 0;
    let transactionVal = parseFloat($('#transaction_val').val()) || 0;
    let remainingBalance = settlementBalance - (cashVal + chequeVal + transactionVal);
    $('#balance_amount').val(remainingBalance);
}
function isFormDataValid(settleInfo) {
    let isValid = true;

    // Validate gua_name field
    if (!validateField(settleInfo.gua_name, 'gua_name')) {
        isValid = false;
    }

    // Validate payment type and related fields
    if (!validateField(settleInfo.payment_type, 'payment_type')) {
        isValid = false;
    }

    if (settleInfo.payment_type == "1") { // Split Payment
        // Check if at least one of the fields is filled
        let isCashFilled = settleInfo.settle_cash > 0;
        let isChequeFilled = settleInfo.cheque_val > 0;
        let isTransactionFilled = settleInfo.transaction_val > 0;

        if (!(isCashFilled || isChequeFilled || isTransactionFilled)) {
            isValid = false;
            $('#settle_cash, #cheque_val, #transaction_val').css('border', '1px solid #ff0000');
        } else {
            resetFieldBorders(['settle_cash', 'cheque_val', 'transaction_val']);
        }
        if (isChequeFilled) {
            if (!validateField(settleInfo.cheque_no, 'cheque_no') && 
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
        }
        if (isTransactionFilled) {
            if (!validateField(settleInfo.transaction_id, 'transaction_id') && 
                !validateField(settleInfo.bank_name, 'bank_name')) {
                isValid = false;
            }
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
        'settle_type', 'settle_cash', 'cheque_no','bank_name',
        'cheque_val', 'cheque_remark', 'transaction_id', 'transaction_val',
        'transaction_remark', 'payment_type', 'gua_name'
    ];

    fieldsToReset.forEach(fieldId => {
        $('#' + fieldId).css('border', '1px solid #cecece');

    });
}
function getCashAck() {
    let auction_id = $('#groupid').val();
    $.post('api/settlement_files/get_cashack_list.php', { auction_id }, function (response) {
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
        success: function(response) {
            if (response && response.balance_amount !== undefined) {
                // Check if balance amount is zero
                let balanceAmount = response.balance_amount;
                if (balanceAmount === '0' || balanceAmount === 0) {
                    // Set balance to settlement amount if balance is zero
                    $('#settle_balance').val($('#settle_amount').val());
                } else {
                    $('#settle_balance').val(balanceAmount);
                }

            } else {
                console.error('Balance amount not found in response');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

