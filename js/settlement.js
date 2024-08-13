$(document).ready(function () {
    $(document).on('click', '.back_btn', function () {
        $('.settlement_table_content').show();
        $('.settlement_content,.back_btn').hide();
       

    });
   
        // Initial Hide of all optional fields
        $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
    
        // Payment Type Change
        $('#payment_type').change(function() {
            let paymentType = $(this).val();
            if (paymentType == "1") { // Split Payment
                $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').show();
                $('#settle_cash, #cheque_val, #transaction_val').prop('readonly', false);
            } else if (paymentType == "2") { // Single Payment
                $('#settle_type_container').show();
                $('#bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
            } else {
                $('#settle_type_container, #bank_container, #cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container, #balance_remark_container').hide();
            }
        });
    
        // Settlement Type Change
        $('#settle_type').change(function() {
            let settleType = $(this).val();
            if (settleType == "1") { // Cash
                $('#cash_container').show();
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
                $('#settle_cash').prop('readonly', true);
            } else if (settleType == "2") { // Cheque
                $('#bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').show();
                $('#cash_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
                $('#cheque_val').prop('readonly', true);
            } else if (settleType == "3") { // Bank Transfer
                $('#bank_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').show();
                $('#cash_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container').hide();
                $('#transaction_val').prop('readonly', true);
            } else {
                $('#cash_container, #bank_container, #cheque_no_container, #cheque_val_container, #cheque_remark_container, #transaction_id_container, #transaction_val_container, #transaction_remark_container').hide();
            }
        });
    
    
    ////////////////////////Document End/////////////////////////////////////////////
    
});