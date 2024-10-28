$(document).ready(function () {
    $('input[name=accounts_type]').click(function () {
        let accountsType = $(this).val();
        if (accountsType == '1') { //Collection List
            $('#coll_card').show(); $('#settlement_card').hide(); $('#expenses_card').hide(); $('#other_transaction_card').hide();
            getBankName('#coll_bank_name');
        } else if (accountsType == '2') { //Loan Issued
            $('#coll_card').hide(); $('#settlement_card').show(); $('#expenses_card').hide(); $('#other_transaction_card').hide();
            getBankName('#issue_bank_name');
        } else if (accountsType == '3') { //Expenses
            $('#coll_card').hide(); $('#settlement_card').hide(); $('#expenses_card').show(); $('#other_transaction_card').hide();
            expensesTable('#accounts_expenses_table');
        } else if (accountsType == '4') { //Other Transaction
            $('#coll_card').hide(); $('#settlement_card').hide(); $('#expenses_card').hide(); $('#other_transaction_card').show();
            otherTransTable('#accounts_other_trans_table');
        }
    });

    $("input[name='coll_cash_type']").click(function () {
        let collCashType = $(this).val();

        if (collCashType == '2') {
            $('#coll_bank_name').val('').attr('disabled', false);
            $('#accounts_collection_table').DataTable().destroy();
            $('#accounts_collection_table tbody').empty()
        } else {
            $('#coll_bank_name').val('').attr('disabled', true);
            getCollectionList();
        }
    });

    $('#coll_bank_name').change(function () {
        getCollectionList();
    });

    $(document).on('click', '.collect-money', function (event) {
        event.preventDefault();
        let collectTableRowVal = {
            'username': $(this).closest('tr').find('td:nth-child(2)').text(),
            'id': $(this).attr('value'),
            'branch': $(this).closest('tr').find('td:nth-child(3)').text(),
            'no_of_customers': $(this).closest('tr').find('td:nth-child(4)').text(),
            'total_amount': $(this).closest('tr').find('td:nth-child(5)').text(),
            'cash_type': $("input[name='coll_cash_type']:checked").val(),
            'bank_id': $('#coll_bank_name :selected').val(),
        }
        swalConfirm('Collect', `Do you want to collect Money from ${collectTableRowVal.username}?`, submitCollect, collectTableRowVal);
    });


    $("input[name='issue_cash_type']").click(function () {
        let collCashType = $(this).val();

        if (collCashType == '2') {
            $('#issue_bank_name').val('').attr('disabled', false);
            $('#accounts_loanissue_table').DataTable().destroy();
            $('#accounts_loanissue_table tbody').empty()
        } else {
            $('#issue_bank_name').val('').attr('disabled', true);
            getLoanIssueList();
        }
    });

    $('#issue_bank_name').change(function () {
        getLoanIssueList();
    });

    $('#expenses_add').click(function () {
        getBankName('#expenses_bank_name');
        getInvoiceNo();
        getBranchList();
        expensesTable('#expenses_creation_table');
    });

    $("input[name='expenses_cash_type']").click(function () {
        let expCashType = $(this).val();
        $('#expenses_trans_id').val('');

        if (expCashType == '2') {
            $('#expenses_bank_name').val('').attr('disabled', false);
            $('.exp_trans_div').show();
        } else {
            $('.exp_trans_div').hide();
            $('#expenses_bank_name').val('').attr('disabled', true);
        }
    });



    $('#submit_expenses_creation').click(function (event) {
        event.preventDefault();

        let expensesData = {
            'coll_mode': $("input[name='expenses_cash_type']:checked").val(),
            'bank_id': $('#expenses_bank_name :selected').val(),
            'invoice_id': $('#invoice_id').val(),
            'branch_name': $('#branch_name :selected').val(),
            'expenses_category': $('#expenses_category :selected').val(),
            'description': $('#description').val(),
            'expenses_amnt': $('#expenses_amnt').val(),
            'expenses_trans_id': $('#expenses_trans_id').val(),
        };

        // Fetch closing balance and validate the expense amount before submitting
        getClosingBal(function (hand_cash_balance, bank_cash_balance) {
            let expensesAmount = parseFloat(expensesData.expenses_amnt);
            let collMode = expensesData.coll_mode;

            // Check if cash mode is 1 (Hand Cash) and expenses amount is greater than hand cash balance
            if (collMode == '1' && expensesAmount > hand_cash_balance) {
                swalError('Warning', 'Closing balance in hand cash is lesser than the expense amount.');
                return;
            }

            // Check if cash mode is 2 (Bank Transaction) and expenses amount is greater than bank cash balance
            if (collMode == '2' && expensesAmount > bank_cash_balance) {
                swalError('Warning', 'Closing balance in bank cash is lesser than the expense amount.');
                return;
            }

            // Proceed if the balance check passes
            if (expensesFormValid(expensesData)) {
                $.post('api/accounts_files/accounts/submit_expenses.php', expensesData, function (response) {
                    if (response == '1') {
                        swalSuccess('Success', 'Expenses added successfully.');
                        expensesTable('#expenses_creation_table');
                        getInvoiceNo();
                        getClosingBal(); // Update the closing balance after submission
                    } else {
                        swalError('Error', 'Failed to add expenses.');
                    }
                }, 'json');
            } else {
                swalError('Warning', 'Kindly Fill Mandatory Fields.');
            }
        });
    });

    $(document).on('click', '.expDeleteBtn', function () {
        let id = $(this).attr('value');
        swalConfirm('Delete', 'Are you sure you want to delete this Expenses?', deleteExp, id);
    });

    $(document).on('click', '.exp-clse', function () {
        expensesTable('#accounts_expenses_table');
    });


    $('#other_trans_add').click(function () {
        otherTransTable('#other_transaction_table');
    });
    $("input[name='othertransaction_cash_type']").click(function () {
        let otherCashType = $(this).val();
        $('#other_trans_id').val('');

        if (otherCashType == '2') {
            $('#othertransaction_bank_name').val('').attr('disabled', false);
            $('.other_trans_div').show();
            getBankName('#othertransaction_bank_name');
        } else {
            $('.other_trans_div').hide();
            $('#othertransaction_bank_name').val('').attr('disabled', true);
        }
    });
    // let category = '';
  // Handle the category change event
// Handle the category change event
$('#trans_category').change(function () {
    let category = $(this).val();

    if (category == '7') {  // If category is 7, show group-related inputs and functionalities
        $('#grp_id_cont').show();
        $('#name_id_cont').hide();
        $('#name_modl_btn').hide();

        getGroupID(); // Load group IDs for selection
        getRefId(category);
        // Bind the cat_type change event only when category == 7
        $('#cat_type').off('change').on('change', function () {  // Off to ensure previous bindings are removed
            let category_type = $(this).val();
            $('.other_month_div').show();

            if (category_type === '2') { // Debit
                getAuctionMonth(group_id);
                settleAmount(group_id);
                $('#other_amnt').prop('readonly', true);
                $('#auction_month').prop('readonly', true);
            } else {  // Credit or Both
                $('#other_amnt').val('');
                getCreditAuctionMonth(group_id, category_type, group_mem_id);
                $('#other_amnt').prop('readonly', false);
                $('#auction_month').prop('readonly', true);
            }
        });

    } else { // For all other categories
        $('#grp_id_cont').hide();
        $('#mem_id_cont').hide();
        $('#name_id_cont').show();
        $('#name_modl_btn').show();
        $('.other_month_div').hide();
        $('#other_amnt').prop('readonly', false);
        $('#group_id').val('');
        $('#group_mem').val('');
        $('#other_amnt').val('');
        $('#auction_month').val('');

        // Unbind the cat_type change event for other categories
        $('#cat_type').off('change');

        // Continue with other categories handling
        $('#trans_cat').val($(this).find(':selected').text());
        $('#trans_cat').attr('data-id', category);

        if (category != '') {
            $('#name_modal_btn')
                .attr('data-toggle', 'modal')
                .attr('data-target', '#add_name_modal');
        } else {
            $('#name_modal_btn')
                .removeAttr('data-toggle')
                .removeAttr('data-target');
        }

        nameDropDown();  // Populate names based on selected transaction category

        // Populate cat_type options based on category value
        let catTypeOptn = "<option value=''>Select Type</option>";
        if (category == '1' || category == '2' || category == '3' || category == '4' || category == '9') {
            catTypeOptn += "<option value='1'>Credit</option><option value='2'>Debit</option>";
        } else if (category == '5') {
            catTypeOptn += "<option value='2'>Debit</option>";
        } else if (category == '6' || category == '8') {
            catTypeOptn += "<option value='1'>Credit</option>";
        }

        $('#cat_type').empty().append(catTypeOptn); // Update cat_type options

        getRefId(category); // Fetch reference IDs based on category
    }
});


    let group_id = '';
    $('#group_id').change(function () {
        group_id = $(this).val();
        $('#name_id_cont').hide();
        $('#name_modl_btn').hide();
        $('#grp_id_cont').show();
        $('#mem_id_cont').show();
        $('.other_month_div').hide();
        getgroupMember(group_id);
        $('#other_amnt').prop('readonly', false);
        $('#other_amnt').val('');
        $('#cat_type').each(function () {
            $(this).val($(this).find('option:first').val());

        });
    });
    // if (category == '7') {
    //     $('#cat_type').change(function () {
    //         category_type = $(this).val();
    //         $('.other_month_div').show();
    //         if (category_type === '2') {
    //             getAuctionMonth(group_id);
    //             settleAmount(group_id);
    //             $('#other_amnt').prop('readonly', true);
    //             $('#auction_month').prop('readonly', true);
    //         } else {
    //             $('#other_amnt').val('');
    //             // $('#auction_month').val('');
    //             getCreditAuctionMonth(group_id, category_type, group_mem_id)
    //             $('#other_amnt').prop('readonly', false);
    //             $('#auction_month').prop('readonly', true);
    //         }
    //     });
    // }
    let group_mem_id = '';
    $('#group_mem').change(function () {
        $('.other_month_div').hide();
        $('#other_amnt').val('');
        $('#auction_month').val('');
        $('#other_amnt').prop('readonly', false);
        group_mem_id = $(this).val();  // Get the selected group member ID
        // Perform an AJAX request to fetch the cat_type based on the selected group member
        $.post('api/accounts_files/accounts/get_cat_type.php', { group_mem_id: group_mem_id, group_id: group_id }, function (response) {
            // Assuming the response contains 'cat_type' and other relevant data
            let data = JSON.parse(response);

            let catTypeSelect = $('#cat_type');
            catTypeSelect.empty(); // Clear previous options
            catTypeSelect.append(new Option('Select Type', '')); // Add a default placeholder option

            // Show options based on the returned cat_type, but don't automatically select them
            if (data.cat_type == 1 || data.cat_type == 'both') {
                // If Credit is applicable, add Credit as an option
                catTypeSelect.append(new Option('Credit', '1'));
            }
            if (data.cat_type == 2 || data.cat_type == 'both') {
                // If Debit is applicable, add Debit as an option
                catTypeSelect.append(new Option('Debit', '2'));
            }

            // Let the user choose between the available options
        });
    });



    // $('#trans_category').change(function () {
    //     let category = $(this).val();
    //     if (category != '') {
    //         $('#trans_cat').val($(this).find(':selected').text());
    //         $('#trans_cat').attr('data-id', $(this).val());
    //         $('#name_modal_btn')
    //             .attr('data-toggle', 'modal')
    //             .attr('data-target', '#add_name_modal');
    //     } else {
    //         $('#name_modal_btn')
    //             .removeAttr('data-toggle')
    //             .removeAttr('data-target');
    //     }

    //     nameDropDown(); //To show name based on transaction category.

    //     let catTypeOptn = '';
    //     catTypeOptn += "<option value=''>Select Type</option>";
    //     if (category == '1' || category == '2' || category == '3' || category == '4' || category == '9') { //credit / debit
    //         catTypeOptn += "<option value='1'>Credit</option>";
    //         catTypeOptn += "<option value='2'>Debit</option>";

    //     } else if (category == '5') { //debit
    //         catTypeOptn += "<option value='2'>Debit</option>";

    //     } else if (category == '6' || category == '8') { //credit
    //         catTypeOptn += "<option value='1'>Credit</option>";
    //     }

    //     $('#cat_type').empty().append(catTypeOptn); //To show Type based on transaction category.

    //     getRefId(category);
    // });

    $('#name_modal_btn').click(function () {
        if ($(this).attr('data-target')) {
            $('#add_other_transaction_modal').hide();
            getOtherTransNameTable();
        } else {
            swalError('Warning', 'Kindly select Transaction Category.');
        }
    });

    $('.name_close').click(function () {
        $('#add_other_transaction_modal').show();

        nameDropDown();
        $('#other_name').val('');
    });

    $('.clse-trans').click(function () {
        otherTransTable('#accounts_other_trans_table');
    });

    $('#submit_name').click(function (event) {
        event.preventDefault();
        let transCat = $('#trans_cat').attr('data-id');
        let name = $('#other_name').val();
        if (transCat == '' || name == '') {
            swalError('Warning', 'Kindly fill all the fields.');
            return false;
        }
        $.post('api/accounts_files/accounts/submit_other_name.php', { transCat, name }, function (response) {
            if (response == '1') {
                swalSuccess('Success', 'Transaction Name Added Successfully.');
                getOtherTransNameTable();
                $('#other_name').val('');
            } else {
                swalError('Error', 'Transaction Name Not Added. Try Again Later.');
            }
        }, 'json');
    });


    $('#submit_other_transaction').click(function (event) {
        event.preventDefault();

        let otherTransData = {
            'coll_mode': $("input[name='othertransaction_cash_type']:checked").val(),
            'bank_id': $('#othertransaction_bank_name :selected').val(),
            'group_id': $('#group_id :selected').val(),
            'group_mem': $('#group_mem :selected').val(),
            'trans_category': $('#trans_category :selected').val(),
            'other_trans_name': $('#other_trans_name :selected').val(),
            'cat_type': $('#cat_type :selected').val(), // Debit or Credit
            'other_ref_id': $('#other_ref_id').val(),
            'other_trans_id': $('#other_trans_id').val(),
            'other_amnt': parseFloat($('#other_amnt').val().replace(/,/g, '')),
            'auction_month': $('#auction_month').val(),
            'other_remark': $('#other_remark').val()
        };
        let otherAmount = otherTransData.other_amnt;
        let collMode = otherTransData.coll_mode;
        let catType = otherTransData.cat_type; // 1 = Credit, 2 = Debit
        let transCategory = parseInt(otherTransData.trans_category);
        // Fetch user's total credit and debit amounts
        $.post('api/accounts_files/accounts/get_user_transactions.php', {
            // 'coll_mode': otherTransData.coll_mode,
            'other_trans_name': otherTransData.other_trans_name,
            'group_id': otherTransData.group_id,
            'group_mem': otherTransData.group_mem
        }, function (response) {
            let totalCredit = parseFloat(response.total_type_1_amount || 0); // Total Credit
            let totalDebit = parseFloat(response.total_type_2_amount || 0);  // Total Debit

            let balance;
            if (catType == '2') { // Debit Transaction
                balance = totalCredit - totalDebit; // Calculate balance
            } else if (catType == '1') { // Credit Transaction
                balance = totalDebit - totalCredit; // Calculate balance  
            }

            // Validate Debit Transactions
            if (transCategory >= 3 && transCategory <= 9) {
                if (catType == '2') { // Debit Transaction
                    if (balance !== 0) {
                        // Allow debit if balance is zero or negative, as long as debit amount does not exceed the absolute value of the balance
                        if (otherAmount > Math.abs(balance)) {
                            const formattedBalance = moneyFormatIndia(Math.abs(balance));
                            swalError('Warning', 'You may only debit up to: ' + formattedBalance);
                            return;
                        }
                    }
                } else if (catType == '1') { // Credit Transaction
                    // Allow credit if balance is negative or zero
                    if (balance !== 0) {
                        if (otherAmount > Math.abs(balance)) {
                            const formattedBalance = moneyFormatIndia(Math.abs(balance));
                            swalError('Warning', 'You may only credit up to: ' + formattedBalance);
                            return;
                        }
                    }
                }
            } else if (transCategory <= 2) {
                if (catType == '2' && totalCredit < totalDebit + otherAmount) {
                    const formattedBalance = moneyFormatIndia(Math.abs(balance));
                    swalError('Warning', 'You may only debit up to: ' + formattedBalance);
                    return;
                }
            }

            // Fetch hand cash and bank cash balances for validation
            getClosingBal(function (hand_cash_balance, bank_cash_balance) {
                if (catType == '2') { // Debit Transaction
                    if (collMode == '1' && otherAmount > hand_cash_balance) {
                        swalError('Warning', 'Insufficient hand cash balance.');
                        return;
                    }
                    if (collMode == '2' && otherAmount > bank_cash_balance) {
                        swalError('Warning', 'Insufficient bank cash balance.');
                        return;
                    }
                }
                //    Proceed if all validations pass
                if (otherTransFormValid(otherTransData)) {
                    $.post('api/accounts_files/accounts/submit_other_transaction.php', otherTransData, function (response) {
                        if (response == '1') {
                            swalSuccess('Success', 'Other Transaction added successfully.');
                            otherTransTable('#other_transaction_table');
                            getClosingBal(); // Update closing balance after submission
                            $('#grp_id_cont').hide(); // Hide the group ID container for other categories
                            $('#mem_id_cont').hide();
                            $('#name_id_cont').show();
                            $('#name_modl_btn').show();
                            $('.other_month_div').hide();
                        } else {
                            swalError('Error', 'Failed to add transaction.');
                        }
                    }, 'json');
                }
                else {
                    swalError('Warning', 'Please fill all required fields.');
                }
            });
        }, 'json');
    });

    $(document).on('click', '.transDeleteBtn', function () {
        var unique = $(this).data('value');
        var [id, grp_id, group_mem, auction_month] = unique.split('_');
        swalConfirm('Delete', 'Are you sure you want to delete this Other Transaction?', function () {
            deleteTrans(id, grp_id, group_mem, auction_month);
        });

    });



    //Balance sheet

    $('#IDE_type').change(function () {
        $('#blncSheetDiv').empty();
        $('.IDE_nameDiv').hide();
        $('#IDE_view_type').val(''); $('#IDE_name_list').val('');
    });

    $('#IDE_view_type').change(function () {
        $('#blncSheetDiv').empty()

        var view_type = $(this).val();//overall/Individual
        var type = $('#IDE_type').val(); //investment/Deposit/EL

        if (view_type == 1 && type != '') {
            $('#IDE_name_list').val(''); //reset name value when using overall
            $('.IDE_nameDiv').hide() // hide name list div
            getIDEBalanceSheet();
        } else if (view_type == 2 && type != '') {
            balNameDropDown();
            $('.IDE_nameDiv').show()
        } else {
            $('.IDE_nameDiv').hide()
        }
    });

    $('#IDE_name_list').change(function () {
        var name_id = $(this).val();
        if (name_id != '') {
            getIDEBalanceSheet();
        }
    });

});  /////Document END.

$(function () {
    getOpeningBal();

});

function getOpeningBal() {
    $.post('api/accounts_files/accounts/opening_balance.php', function (response) {
        if (response.length > 0) {
            $('.opening_val').text(response[0]['opening_balance']);
            $('.op_hand_cash_val').text(response[0]['hand_cash']);
            $('.op_bank_cash_val').text(response[0]['bank_cash']);
        }
    }, 'json').then(function () {
        getClosingBal();
    });
}
function getGroupID() {
    $.post('api/accounts_files/accounts/getGroupid.php', function (response) {
        let appendOption = '';
        appendOption += "<option value=''>Select Group Name</option>";
        $.each(response, function (index, val) {
            appendOption += "<option value='" + val.grp_id + "'>" + val.grp_id + " - " + val.grp_name + "</option>";
        });
        $('#group_id').empty().append(appendOption);
    }, 'json');
}
function getgroupMember(group_id) {
    $.post('api/accounts_files/accounts/getGroupmember.php', { group_id }, function (response) {
        let appenderOption = '';
        appenderOption += "<option value=''>Select Group Member</option>";
        $.each(response, function (index, val) {
            appenderOption += "<option value='" + val.id + "'>" + val.cus_name + "</option>";
        });
        $('#group_mem').empty().append(appenderOption);
    }, 'json');
}
function getAuctionMonth(group_id) {
    // Post the group_id to the PHP script and handle the response
    $.post('api/accounts_files/accounts/getAuctionMonth.php', { group_id: group_id }, function (response) {
        // Check if the response has any data
        if (response.length > 0) {
            $('#auction_month').val(response[0].auction_month);
        } else {
            $('#auction_month').val(''); // Clear the field if no data is returned
        }
    }, 'json').fail(function () {
        $('#auction_month').val(''); // Clear the field if there's an error
    });
}
function getCreditAuctionMonth(group_id, category_type, group_mem_id) {
    // Post the group_id to the PHP script and handle the response
    $.post('api/accounts_files/accounts/getCreditAuctionMonth.php', { group_id: group_id, category_type: category_type, group_mem_id: group_mem_id }, function (response) {
        // Check if the response has any data
        if (response.length > 0) {
            $('#auction_month').val(response[0].auction_month);
        } else {
            $('#auction_month').val(''); // Clear the field if no data is returned
        }
    }, 'json').fail(function () {
        $('#auction_month').val(''); // Clear the field if there's an error
    });
}
function settleAmount(group_id) {
    $.post('api/accounts_files/accounts/getSettleAccounts.php', { group_id: group_id }, function (response) {
        // Check if the response has any data
        if (response.length > 0) {
            let settle_amount = moneyFormatIndia(response[0].settlement_amount)
            $('#other_amnt').val(settle_amount);
        } else {
            $('#other_amnt').val(''); // Clear the field if no data is returned
        }
    }, 'json').fail(function () {
        $('#other_amnt').val(''); // Clear the field if there's an error
    });
}
function getClosingBal(callback) {
    $.post('api/accounts_files/accounts/closing_balance.php', function (response) {
        if (response.length > 0) {
            let close = parseInt($('.opening_val').text()) + parseInt(response[0]['closing_balance']);
            let hand = parseInt($('.op_hand_cash_val').text()) + parseInt(response[0]['hand_cash']);
            let bank = parseInt($('.op_bank_cash_val').text()) + parseInt(response[0]['bank_cash']);

            $('.closing_val').text(close);
            $('.clse_hand_cash_val').text(hand);
            $('.clse_bank_cash_val').text(bank);

            // Call the callback function if defined
            if (typeof callback === "function") {
                callback(hand, bank);
            }
        }
    }, 'json');
}
function submitCollect(values) {
    $.post('api/accounts_files/accounts/submit_collect.php', values, function (response) {
        if (response == '1') {
            swalSuccess('Success', `Successfully collected ₹${(values.total_amount)} for ${values.no_of_customers} Customer from ${values.username}.`);
            getCollectionList();
            getClosingBal();
        } else {
            swalError('Error', 'Something went wrong.');
        }
    }, 'json');
}
function getCollectionList() {
    let cash_type = $("input[name='coll_cash_type']:checked").val();
    let bank_id = $('#coll_bank_name :selected').val();
    $.post('api/accounts_files/accounts/accounts_collection_list.php', { cash_type, bank_id }, function (response) {
        let columnMapping = [
            'sno',
            'name',
            'branch_names',
            'no_of_customers',
            'total_amount',
            'action'
        ];
        appendDataToTable('#accounts_collection_table', response, columnMapping);
        setdtable('#accounts_collection_table');
    }, 'json');
}

function getLoanIssueList() {
    let cash_type = $("input[name='issue_cash_type']:checked").val();
    let bank_id = $('#issue_bank_name :selected').val();
    $.post('api/accounts_files/accounts/accounts_settlement_list.php', { cash_type, bank_id }, function (response) {
        let columnMapping = [
            'sno',
            'user_name',
            'branch_names',
            'settle_date',
            'no_of_customers',
            'total_settlement_amount'
        ];
        appendDataToTable('#accounts_loanissue_table', response, columnMapping);
        setdtable('#accounts_loanissue_table');
    }, 'json');
}

function getBankName(dropdowndId) {
    $.post('api/common_files/bank_name_list.php', function (response) {
        var bankName = '<option value="">Select Bank Name</option>';
        $.each(response, function (index, value) {
            bankName += '<option value="' + value.id + '" data-id="' + value.account_number + '">' + value.bank_name + '</option>';
        });
        $(dropdowndId).empty().html(bankName);
    }, 'json');
}

function getInvoiceNo() {
    $.post('api/accounts_files/accounts/get_invoice_no.php', {}, function (response) {
        $('#invoice_id').val(response);
    }, 'json');
}

function getBranchList() {
    $.post('api/common_files/user_mapped_branches.php', function (response) {
        let branchOption;
        branchOption += '<option value="">Select Branch Name</option>';
        $.each(response, function (index, value) {
            branchOption += '<option value="' + value.id + '">' + value.branch_name + '</option>';
        });
        $('#branch_name').empty().html(branchOption);
    }, 'json');
}


function expensesFormValid(expensesData) {
    for (key in expensesData) {
        if (key != 'agent_name' && key != 'expenses_total_issued' && key != 'expenses_total_amnt' && key != 'bank_id' && key != 'expenses_trans_id') {
            if (expensesData[key] == '' || expensesData[key] == null || expensesData[key] == undefined) {
                return false;
            }
        }
    }

    if (expensesData['coll_mode'] == '2') {
        if (expensesData['bank_id'] == '' || expensesData['bank_id'] == null || expensesData['bank_id'] == undefined || expensesData['expenses_trans_id'] == '' || expensesData['expenses_trans_id'] == null || expensesData['expenses_trans_id'] == undefined) {
            return false;
        }
    }

    if (expensesData['expenses_category'] == '14') {
        if (expensesData['agent_name'] == '' || expensesData['agent_name'] == null || expensesData['agent_name'] == undefined || expensesData['expenses_total_issued'] == '' || expensesData['expenses_total_issued'] == null || expensesData['expenses_total_issued'] == undefined || expensesData['expenses_total_amnt'] == '' || expensesData['expenses_total_amnt'] == null || expensesData['expenses_total_amnt'] == undefined) {
            return false;
        }
    }

    return true;
}

function expensesTable(tableId) {
    $.post('api/accounts_files/accounts/get_expenses_list.php', function (response) {
        let expensesColumn = [
            'sno',
            'invoice_id',
            'branch',
            'expenses_category',
            'description',
            'amount',
            'action'
        ];

        appendDataToTable(tableId, response, expensesColumn);
        setdtable(tableId);
        clearExpForm();
    }, 'json');
}

function clearExpForm() {
    $('#expenses_amnt').val('');
    $('#expenses_trans_id').val('');
    $('#expenses_form select').val('');
    $('#expenses_form textarea').val('');
}

function deleteExp(id) {
    $.post('api/accounts_files/accounts/delete_expenses.php', { id }, function (response) {
        if (response == '1') {
            swalSuccess('success', 'Expenses Deleted Successfully');
            expensesTable('#expenses_creation_table');
            expensesTable('#accounts_expenses_table');
            getInvoiceNo();
            getClosingBal();
        } else {
            swalError('Alert', 'Delete Failed')
        }
    }, 'json');
}
function getOtherTransNameTable() {
    let transCat = $('#trans_category :selected').val();
    $.post('api/accounts_files/accounts/get_other_trans_name_table.php', { transCat }, function (response) {
        let nameColumns = [
            'sno',
            'trans_cat',
            'name'
        ];
        appendDataToTable('#other_trans_name_table', response, nameColumns);
        setdtable('#other_trans_name_table');
    }, 'json');
}

function nameDropDown() {
    let transCat = $('#trans_category :selected').val();
    $.post('api/accounts_files/accounts/get_other_trans_name_table.php', { transCat }, function (response) {
        let nameOptn = '';
        nameOptn += "<option value=''>Select Name</option>";
        $.each(response, function (index, val) {
            nameOptn += "<option value='" + val.id + "'>" + val.name + "</option>";
        });
        $('#other_trans_name').empty().append(nameOptn);
    }, 'json');
}

function otherTransFormValid(data) {

    for (key in data) {
        if (key != 'bank_id' && key != 'other_trans_id' && key != 'group_id' && key != 'group_mem' && key != 'auction_month' && key != 'other_trans_name') {
            if (data[key] == '' || data[key] == null || data[key] == undefined) {
                return false;
            }
        }
    }

    if (data['coll_mode'] == '2') {
        if (data['bank_id'] == '' || data['bank_id'] == null || data['bank_id'] == undefined || data['other_trans_id'] == '' || data['other_trans_id'] == null || data['other_trans_id'] == undefined) {
            return false;
        }
    }
    if (data['trans_category'] != '7') {
        if (data['other_trans_name'] == '' || data['other_trans_name'] == null || data['other_trans_name'] == undefined) {
            return false;
        }
    }
    if (data['trans_category'] == '7') {
        if (data['group_id'] == '' || data['group_id'] == null || data['group_id'] == undefined || data['group_mem'] == '' || data['group_mem'] == null || data['group_mem'] == undefined) {
            return false;
        }
    }

    return true;
}


function otherTransTable(tableId) {
    $.post('api/accounts_files/accounts/get_other_trans_list.php', function (response) {
        let expensesColumn = [
            'sno',
            'trans_cat',
            'group_id',
            'name',
            'cus_name',
            'type',
            'bank_namecash',
            'ref_id',
            'trans_id',
            'amount',
            'auction_month',
            'remark',
            'action'
        ];

        appendDataToTable(tableId, response, expensesColumn);
        setdtable(tableId);
        clearTransForm();
    }, 'json');
}

function clearTransForm() {
    $('#other_ref_id').val('');
    $('#other_trans_id').val('');
    $('#other_amnt').val('');
    $('#auction_month').val('');
    $('#other_transaction_form select').val('');
    $('#other_transaction_form textarea').val('');
    $('#grp_id_cont').hide(); // Hide the group ID container for other categories
    $('#mem_id_cont').hide();
    $('#name_id_cont').show();
    $('#name_modl_btn').show();
    $('.other_month_div').hide();
    $('#other_amnt').prop('readonly', false);
}

function deleteTrans(id, grp_id, group_mem, auction_month) {
    $.post('api/accounts_files/accounts/delete_other_transaction.php', {
        id: id,
        group_id: grp_id,
        group_mem: group_mem,
        auction_month: auction_month
    }, function (response) {
        if (response == '1') {
            swalSuccess('success', 'Other Transaction Deleted Successfully');
            otherTransTable('#other_transaction_table');
            otherTransTable('#accounts_other_trans_table');
            getClosingBal();
        } else {
            swalError('Alert', 'Delete Failed');
        }
    }, 'json');
}

function getRefId(trans_cat) {
    $.post('api/accounts_files/accounts/get_ref_id.php', { trans_cat }, function (response) {
        $('#other_ref_id').val(response)
    }, 'json');
}
function balNameDropDown() {
    let transCat = $('#IDE_type :selected').val();
    $.post('api/accounts_files/accounts/get_other_trans_name_table.php', { transCat }, function (response) {
        let nameOptn = '';
        nameOptn += "<option value=''>Select Name</option>";
        $.each(response, function (index, val) {
            nameOptn += "<option value='" + val.id + "'>" + val.name + "</option>";
        });
        $('#IDE_name_list').empty().append(nameOptn);
    }, 'json');
}
function getIDEBalanceSheet() {
    var type = $('#IDE_type').val(); //investment/Deposit/EL
    var view_type = $('#IDE_view_type').val();//overall/Individual
    var IDE_name_id = $('#IDE_name_list').val();//show by name wise

    $.ajax({
        url: 'api/accounts_files/accounts/dep_bal_sheet.php',
        data: { 'IDEview_type': view_type, 'IDEtype': type, 'IDE_name_id': IDE_name_id },
        type: 'post',
        cache: false,
        success: function (response) {
            $('#blncSheetDiv').empty()
            $('#blncSheetDiv').html(response)
        }
    })
}

function resetBlncSheet() {
    $('#IDE_type').val('');
    $('#IDE_view_type').val('');
    $('#IDE_name_list').val('');
    $('.IDE_nameDiv').hide();
    $('#open_balance').hide();
    $('#blncSheetTable').parent().hide();
}