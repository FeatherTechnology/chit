$(document).ready(function(){
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

    $("input[name='coll_cash_type']").click(function(){
        let collCashType = $(this).val();

        if (collCashType == '2') {
            $('#coll_bank_name').val('').attr('disabled', false);
            $('#accounts_collection_table').DataTable().destroy();
            $('#accounts_collection_table tbody').empty()
        }else{
            $('#coll_bank_name').val('').attr('disabled', true);
            getCollectionList();
        }
    });

    $('#coll_bank_name').change(function(){
        getCollectionList();
    });
    
    $(document).on('click', '.collect-money', function(event){
        event.preventDefault();
      
        let id = $(this).attr('value');
        let branch = $(this).closest('tr').find('td:nth-child(4)').text();
        let no_of_customers = $(this).closest('tr').find('td:nth-child(5)').text();
        let total_amount = $(this).closest('tr').find('td:nth-child(6)').text();
        let cash_type = $("input[name='coll_cash_type']:checked").val();
        let bank_id = $('#coll_bank_name :selected').val();
        
        $.post('api/accounts_files/accounts/submit_collect.php', {id, line, branch, no_of_customers, total_amount, cash_type, bank_id}, function(response){
            if (response == '1') {
                swalSuccess('Success', 'Collected Successfully.');
                getCollectionList();
                getClosingBal();
            }else{
                swalError('Error', 'Something went wrong.');
            }

        },'json');
    });
    
    $("input[name='issue_cash_type']").click(function(){
        let collCashType = $(this).val();

        if (collCashType == '2') {
            $('#issue_bank_name').val('').attr('disabled', false);
            $('#accounts_loanissue_table').DataTable().destroy();
            $('#accounts_loanissue_table tbody').empty()
        }else{
            $('#issue_bank_name').val('').attr('disabled', true);
            getLoanIssueList();
        }
    });
    
    $('#issue_bank_name').change(function(){
        getLoanIssueList();
    });

    $('#expenses_add').click(function(){
        getBankName('#expenses_bank_name');
        getInvoiceNo();
        getBranchList();
        expensesTable('#expenses_creation_table');
    });
    
    $("input[name='expenses_cash_type']").click(function(){
        let expCashType = $(this).val();

        if (expCashType == '2') {
            $('#expenses_bank_name').val('').attr('disabled', false);
        }else{
            $('#expenses_bank_name').val('').attr('disabled', true);
        }
    });

    $('#expenses_category').change(function(){
        let expCat = $(this).val();
        if(expCat =='14'){
            $('.agentDiv').show();
            getAgentName();
        }else{
            $('.agentDiv').hide();
        }
    });

    $('#submit_expenses_creation').click(function(event){
        event.preventDefault();
        let expensesData = {
            'coll_mode' : $("input[name='expenses_cash_type']:checked").val(),
            'bank_id' : $('#expenses_bank_name :selected').val(),
            'invoice_id' : $('#invoice_id').val(),
            'branch_name' : $('#branch_name :selected').val(),
            'expenses_category' : $('#expenses_category :selected').val(),
            'agent_name' : $('#agent_name :selected').val(),
            'expenses_total_issued' : $('#expenses_total_issued').val(),
            'expenses_total_amnt' : $('#expenses_total_amnt').val(),
            'description' : $('#description').val(),
            'expenses_amnt' : $('#expenses_amnt').val(),
            'expenses_trans_id' : $('#expenses_trans_id').val(),
        }
        if(expensesFormValid(expensesData)){
            $.post('api/accounts_files/accounts/submit_expenses.php',expensesData,function(response){
                if(response =='1'){
                    swalSuccess('Success', 'Expenses added successfully.');
                    expensesTable('#expenses_creation_table');
                    getInvoiceNo();
                    getClosingBal();
                }else{
                    swalError('Error', 'Failed.');
                }
            },'json');
        }else{
            swalError('Warning', 'Kindly Fill Mandatory Fields.');
        }
    });

    
    $(document).on('click', '.expDeleteBtn', function () {
        let id = $(this).attr('value');
        swalConfirm('Delete', 'Are you sure you want to delete this Expenses?', deleteExp, id);
    });

    $(document).on('click', '.exp-clse', function(){
        expensesTable('#accounts_expenses_table');
    });

    $('#agent_name').change(function(){
        let agentId = $(this).val();
        let coll_mode = $("input[name='expenses_cash_type']:checked").val();

        if(coll_mode =='' || coll_mode ==undefined){
            swalError('Warning', 'Kindly select the cash mode.')
            $('#expenses_total_issued').val('');
            $('#expenses_total_amnt').val('');
        }else{
            getAgentDetails(agentId, coll_mode);
        }
    });
    
    $('#other_trans_add').click(function(){
        otherTransTable('#other_transaction_table');
    });
    
    $("input[name='othertransaction_cash_type']").click(function(){
        let otherCashType = $(this).val();
        if (otherCashType == '2') {
            $('#othertransaction_bank_name').val('').attr('disabled', false);
            getBankName('#othertransaction_bank_name');
        }else{
            $('#othertransaction_bank_name').val('').attr('disabled', true);
        }
    });

    $('#trans_category').change(function(){
        let category = $(this).val();
        if (category != '') {
            $('#trans_cat').val($(this).find(':selected').text());
            $('#trans_cat').attr('data-id',$(this).val());
            $('#name_modal_btn')
                .attr('data-toggle', 'modal')
                .attr('data-target', '#add_name_modal');
        } else {
            $('#name_modal_btn')
                .removeAttr('data-toggle')
                .removeAttr('data-target');
        }

        nameDropDown(); //To show name based on transaction category.

        let catTypeOptn ='';
            catTypeOptn +="<option value=''>Select Type</option>";
        if(category == '1' || category == '2' || category == '3' || category == '4' ){ //credit / debit
            catTypeOptn +="<option value='1'>Credit</option>";
            catTypeOptn +="<option value='2'>Debit</option>";

        } else if(category == '5' || category == '7'){ //debit
            catTypeOptn +="<option value='2'>Debit</option>";            
        
        } else if(category == '6' || category == '8'){ //credit
            catTypeOptn +="<option value='1'>Credit</option>";
        }

        $('#cat_type').empty().append(catTypeOptn); //To show Type based on transaction category.

        if(category =='7'){
            $('#other_user_name').attr('disabled', false);
            getUserList();
        }else{
            $('#other_user_name').val('').attr('disabled', true);
        }
        
        getRefId(category);
    });
    
    $('#name_modal_btn').click(function () {
        if ($(this).attr('data-target')) {
            $('#add_other_transaction_modal').hide();
            getOtherTransNameTable();
        }else{
            swalError('Warning', 'Kindly select Transaction Category.');
        }
    });

    $('.name_close').click(function () {
        $('#add_other_transaction_modal').show();
        nameDropDown();
        $('#other_name').val('');
    });

    $('.clse-trans').click(function(){
        otherTransTable('#accounts_other_trans_table');
    });

    $('#submit_name').click(function(event){
        event.preventDefault();
        let transCat = $('#trans_cat').attr('data-id');
        let name = $('#other_name').val();
        if(transCat =='' || name ==''){
            swalError('Warning', 'Kindly fill all the fields.');
            return false;
        }
        $.post('api/accounts_files/accounts/submit_other_trans_name.php',{transCat, name},function(response){
            if(response == '1'){
                swalSuccess('Success', 'Transaction Name Added Successfully.');
                getOtherTransNameTable();
                $('#other_name').val('');
            }else{
                swalError('Error', 'Transaction Name Not Added. Try Again Later.');
            }
        },'json');
    });

    $('#submit_other_transaction').click(function(event){
        event.preventDefault();
        let otherTransData = {
            'coll_mode' : $("input[name='othertransaction_cash_type']:checked").val(),
            'bank_id' : $('#othertransaction_bank_name :selected').val(),
            'trans_category' : $('#trans_category :selected').val(),
            'other_trans_name' : $('#other_trans_name :selected').val(),
            'cat_type' : $('#cat_type :selected').val(),
            'other_ref_id' : $('#other_ref_id').val(),
            'other_trans_id' : $('#other_trans_id').val(),
            'other_user_name' : $('#other_user_name :selected').val(),
            'other_amnt' : $('#other_amnt').val(),
            'other_remark' : $('#other_remark').val()
        }
        if(otherTransFormValid(otherTransData)){
            $.post('api/accounts_files/accounts/submit_other_transaction.php',otherTransData,function(response){
                if(response =='1'){
                    swalSuccess('Success', 'Other Transaction added successfully.');
                    otherTransTable('#other_transaction_table');
                    getClosingBal();
                }else{
                    swalError('Error', 'Failed.');
                }
            },'json');
        }else{
            swalError('Warning', 'Kindly Fill Mandatory Fields.');
        }
    });
    
    $(document).on('click', '.transDeleteBtn', function () {
        let id = $(this).attr('value');
        swalConfirm('Delete', 'Are you sure you want to delete this Other Transaction?', deleteTrans, id);
    });

});  /////Document END.

$(function(){
    getOpeningBal();
    
});

function getOpeningBal(){
    $.post('api/accounts_files/accounts/opening_balance.php',function(response){
        if(response.length > 0){
            $('.opening_val').text(response[0]['opening_balance']);
            $('.op_hand_cash_val').text(response[0]['hand_cash']);
            $('.op_bank_cash_val').text(response[0]['bank_cash']);
        }
    },'json').then(function(){
        getClosingBal();
    });
}

function getClosingBal(){
    $.post('api/accounts_files/accounts/closing_balance.php',function(response){
        if(response.length > 0){
            let close = parseInt($('.opening_val').text()) + parseInt(response[0]['closing_balance']);
            let hand = parseInt($('.op_hand_cash_val').text()) + parseInt(response[0]['hand_cash']);
            let bank = parseInt($('.op_bank_cash_val').text()) + parseInt(response[0]['bank_cash']);
            $('.closing_val').text(close);
            $('.clse_hand_cash_val').text(hand);
            $('.clse_bank_cash_val').text(bank);
        }
    },'json');
}

function getCollectionList(){
    let cash_type = $("input[name='coll_cash_type']:checked").val();
    let bank_id = $('#coll_bank_name :selected').val();
    $.post('api/accounts_files/accounts/accounts_collection_list.php', {cash_type, bank_id},function(response){
        let columnMapping = [
            'sno',
            'name',
            'branch_name',
            'no_of_customers',
            'total_amount',
            'action'
        ];
        appendDataToTable('#accounts_collection_table', response, columnMapping);
        setdtable('#accounts_collection_table');
    },'json');
}

function getLoanIssueList(){
    let cash_type = $("input[name='issue_cash_type']:checked").val();
    let bank_id = $('#issue_bank_name :selected').val();
    $.post('api/accounts_files/accounts/accounts_loan_issue_list.php', {cash_type, bank_id},function(response){
        let columnMapping = [
            'sno',
            'name',
            'linename',
            'no_of_loans',
            'issueAmnt',
            'balance'
        ];
        appendDataToTable('#accounts_loanissue_table', response, columnMapping);
        setdtable('#accounts_loanissue_table');
    },'json');
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

function getInvoiceNo(){
    $.post('api/accounts_files/accounts/get_invoice_no.php',{},function(response){
        $('#invoice_id').val(response);
    },'json');
}

function getBranchList(){
    $.post('api/common_files/user_mapped_branches.php',function(response){
        let branchOption;
        branchOption += '<option value="">Select Branch Name</option>';
        $.each(response,function(index,value){
            branchOption += '<option value="'+value.id+'">'+value.branch_name+'</option>';
            });
        $('#branch_name').empty().html(branchOption);
    },'json');
}

function getAgentName() {
    $.post('api/agent_creation/agent_creation_list.php', function (response) {
        let appendAgentIdOption = '';
        appendAgentIdOption += '<option value="">Select Agent Name</option>';
        $.each(response, function (index, val) {
            let selected = '';
            let agent_id_edit_it = '';
            if (val.id == agent_id_edit_it) {
                selected = 'selected';
            }
            appendAgentIdOption += '<option value="' + val.id + '" ' + selected + '>' + val.agent_name + '</option>';
        });
        $('#agent_name').empty().append(appendAgentIdOption);
    }, 'json');
}

function expensesFormValid(expensesData){
    for(key in expensesData){
        if(key !='agent_name' && key !='expenses_total_issued' && key !='expenses_total_amnt' && key !='bank_id'){
            if(expensesData[key] =='' || expensesData[key] ==null || expensesData[key] ==undefined){
                return false;
            }
        }
    }

    if(expensesData['coll_mode'] =='2'){
        if(expensesData['bank_id'] =='' || expensesData['bank_id'] ==null || expensesData['bank_id'] == undefined){
            return false;
        }
    }

    if(expensesData['expenses_category']=='14'){
        if(expensesData['agent_name'] =='' || expensesData['agent_name'] ==null || expensesData['agent_name'] ==undefined || expensesData['expenses_total_issued'] =='' || expensesData['expenses_total_issued'] ==null || expensesData['expenses_total_issued'] ==undefined || expensesData['expenses_total_amnt'] =='' || expensesData['expenses_total_amnt'] ==null || expensesData['expenses_total_amnt'] ==undefined ){
            return false;
        }
    }

    return true;
}

function expensesTable(tableId){
    $.post('api/accounts_files/accounts/get_expenses_list.php',function(response){
        let expensesColumn = [
            'sno',
            'coll_mode',
            'bank_id',
            'invoice_id',
            'branch',
            'expenses_category',
            'agent_id',
            'total_issued',
            'total_amount',
            'description',
            'amount',
            'trans_id',
            'action'
        ];

        appendDataToTable(tableId, response, expensesColumn);
        setdtable(tableId);
        clearExpForm();
    },'json');
}

function clearExpForm(){
    $('#expenses_total_issued').val('');
    $('#expenses_total_amnt').val('');
    $('#expenses_amnt').val('');
    $('#expenses_trans_id').val('');
    $('#expenses_form select').val('');
    $('#expenses_form textarea').val('');
    $('.agentDiv').hide();
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

function getAgentDetails(id, coll_mode){
    $.post('api/accounts_files/accounts/get_agent_cash_details.php',{id, coll_mode},function(response){
        $('#expenses_total_issued').val(response[0].total_issued);
        let issuedAmount = (response[0].total_issued =='0') ? '0' : response[0].total_amount;
        $('#expenses_total_amnt').val(issuedAmount);
    },'json');
}

function getOtherTransNameTable(){
    let transCat = $('#trans_category :selected').val();
    $.post('api/accounts_files/accounts/get_other_trans_name_table.php',{transCat},function(response){
        let nameColumns = [
            'sno',
            'trans_cat',
            'name'
        ];
        appendDataToTable('#other_trans_name_table',response,nameColumns);
        setdtable('#other_trans_name_table');
    },'json');
}

function nameDropDown(){
    let transCat = $('#trans_category :selected').val();
    $.post('api/accounts_files/accounts/get_other_trans_name_table.php',{transCat},function(response){
        let nameOptn='';
            nameOptn +="<option value=''>Select Name</option>";
            $.each(response, function(index, val){
                nameOptn += "<option value='"+val.id+"'>"+val.name+"</option>";
            });
        $('#other_trans_name').empty().append(nameOptn);
    },'json');
}

function getUserList(){
    $.post('api/accounts_files/accounts/user_list.php',function(response){
        let userNameOptn='';
            userNameOptn +="<option value=''>Select User Name</option>";
            $.each(response, function(index, val){
                userNameOptn += "<option value='"+val.id+"'>"+val.name+"</option>";
            });
        $('#other_user_name').empty().append(userNameOptn);
    },'json');
}

function otherTransFormValid(data){
    for(key in data){
        if(key !='other_user_name' && key !='expenses_total_amnt' && key !='bank_id'){
            if(data[key] =='' || data[key] ==null || data[key] ==undefined){
                return false;
            }
        }
    }
    
    if(data['coll_mode'] =='2'){
        if(data['bank_id'] =='' || data['bank_id'] ==null || data['bank_id'] == undefined){
            return false;
        }
    }

    if(data['trans_category'] =='7'){
        if(data['other_user_name'] =='' || data['other_user_name'] ==null || data['other_user_name'] == undefined){
            return false;
        }
    }

    return true;
}

function otherTransTable(tableId){
    $.post('api/accounts_files/accounts/get_other_trans_list.php',function(response){
        let expensesColumn = [
            'sno',
            'coll_mode',
            'bank_namecash',
            'trans_cat',
            'name',
            'type',
            'ref_id',
            'trans_id',
            'username',
            'amount',
            'remark',
            'action'
        ];

        appendDataToTable(tableId, response, expensesColumn);
        setdtable(tableId);
        clearTransForm();
    },'json');
}

function clearTransForm(){
    $('#other_ref_id').val('');
    $('#other_trans_id').val('');
    $('#other_amnt').val('');
    $('#other_transaction_form select').val('');
    $('#other_transaction_form textarea').val('');
}

function deleteTrans(id) {
    $.post('api/accounts_files/accounts/delete_other_transaction.php', { id }, function (response) {
        if (response == '1') {
            swalSuccess('success', 'Other Transaction Deleted Successfully');
            otherTransTable('#other_transaction_table');
            otherTransTable('#accounts_other_trans_table');
            getClosingBal();
        } else {
            swalError('Alert', 'Delete Failed')
        }
    }, 'json');
}

function getRefId(trans_cat){
    $.post('api/accounts_files/accounts/get_ref_id.php', {trans_cat}, function (response){
        $('#other_ref_id').val(response)
    },'json');
}