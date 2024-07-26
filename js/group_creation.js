$(document).ready(function () {
    //Add Group creation & Back.
    $(document).on('click', '#add_group, #back_btn', function () {
        swapTableAndCreation();
    });

    $('input[name="grp_info_add"]').click(function () {
        if (this.value == 'cus_map') {
            $('#cus_mapping_card').show();
            $('#grp_details_card').hide();

        } else if (this.value == 'grp_details') {
            $('#cus_mapping_card').hide();
            $('#grp_details_card').show();
        }
    });

    document.getElementById('hours').addEventListener('input', function (e) {
        if (this.value.length > 2) this.value = this.value.slice(0, 2);
        if (this.value > 12) this.value = 12;
        if (this.value < 1 && this.value !== '') this.value = 1;
    });

    document.getElementById('minutes').addEventListener('input', function (e) {
        if (this.value.length > 2) this.value = this.value.slice(0, 2);
        if (this.value > 59) this.value = 59;
        if (this.value < 0) this.value = 0;
    });

    $('#start_month, #total_month').change(function () {
        let startMonth = $('#start_month').val();
        let totMonth = $('#total_month').val();
        if (totMonth == '') {
            $('#start_month').val('');
            swalError('Warning', 'Kindly fill the total month');
            return false;
        }
        if (totMonth != '' && startMonth != '') {
            var endDate = moment(startMonth, 'YYYY-MM').add(totMonth, 'months').subtract(1, 'month').format('YYYY-MM');//subract one month because by default its showing extra one month
            $('#end_month').val(endDate);
        }
    });

    $('#submit_group_info').click(function (event){
        event.preventDefault();
        let grpInfoData = {
            'groupid': $('#groupid').val(),
            'group_id': $('#group_id').val(),
            'chit_value': $('#chit_value').val(),
            'grp_date': $('#grp_date').val(),
            'group_name': $('#group_name').val(),
            'commission': $('#commission').val(),
            'hours': $('#hours').val(),
            'minutes': $('#minutes').val(),
            'ampm': $('#ampm').val(),
            'total_members': $('#total_members').val(),
            'total_month': $('#total_month').val(),
            'start_month': $('#start_month').val(),
            'end_month': $('#end_month').val(),
            'branch': $('#branch').val(),
            'grace_period': $('#grace_period').val()
        }

        if (isFormValid(grpInfoData)) {
            $.post('api/group_creation_files/submit_group_info.php', grpInfoData, function (response) {
                if(response.result =='1'){
                    swalSuccess('Success', 'Group Info Submitted Successfully');
                    $('#groupid').val(response.last_id);
                }else{
                    swalError('Error', 'Group Info Not Submitted');
                    $('#groupid').val('');
                }
            }, 'json');
        } else {
            // swalError('Warning', 'Kindly fill the mandatory fields');
        }
    });

    $('#submit_cus_map').click(function () {
        event.preventDefault();
        let cus_name = $('#cus_name').val();
        let groupid = $('#groupid').val();

        if (cus_name != '' && groupid !='') {
            $.post('api/group_creation_files/submit_cus_mapping.php', { cus_name, groupid }, function (response) {
                getDocNeedTable(groupid);
            }, 'json');

            $('#cus_name').val('');
        }

    });

}); //document END////

$(function () {

});

function swapTableAndCreation() {
    if ($('.group_table_content').is(':visible')) {
        $('.group_table_content').hide();
        $('#add_group').hide();
        $('#group_creation_content').show();
        $('#back_btn').show();
        callGrpFunctions();

    } else {
        $('.group_table_content').show();
        $('#add_group').show();
        $('#group_creation_content').hide();
        $('#back_btn').hide();
        $('#customer_mapping').trigger('click');
    }
}

function callGrpFunctions() {
    getDateDropDown('#grp_date', 'Select Date');
    getDateDropDown('#grace_period', 'Select Grace Period');
    getBranchList();
    getCustomerList();
}

function getDateDropDown(selector, selectOptn) {
    let dateOption = '';
    dateOption = "<option value=''>" + selectOptn + "</option>";
    for (let i = 1; i <= 31; i++) {
        dateOption += '<option value="' + i + '">' + i + '</option>';
    }
    $(selector).empty().append(dateOption);
}

function getBranchList() {
    $.post('api/common_files/get_branch_list.php', function (response) {
        let branchOptn = '';
        branchOptn = '<option value="">Select Branch</option>';
        response.forEach(val => {
            branchOptn += '<option value="' + val.id + '">' + val.branch_name + '</option>';
        });
        $('#branch').empty().append(branchOptn);
    }, 'json');
}

function getCustomerList() {
    $.post('api/common_files/get_customer_list.php', function (response) {
        let cusOptn = '';
        cusOptn = '<option value="">Select Customer Name</option>';
        response.forEach(val => {
            cusOptn += '<option value="' + val.id + '">' + val.first_name +''+ val.last_name + '</option>';
        });
        $('#cus_name').empty().append(cusOptn);
    }, 'json');
}

function isFormValid(formdata) {
    const excludedFields = ['groupid'];

    for (let key in formdata) {
        if (excludedFields.includes(key)) continue;
        if (!validateField(formdata[key], key)) {
            return false;
        }
    }

    return true;
}