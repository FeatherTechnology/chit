$(document).ready(function () {
    //Add Group creation & Back.
    $(document).on('click', '#add_group, #back_btn', function () {
        swapTableAndCreation();
    });

    $('#grp_date,#start_month, #total_month').change(function () {
        //updateEndMonth();
        getModalAttr()
    });
    function getModalAttr() {
        let grp_date = $('#grp_date').val();
        let start_month = $('#start_month').val();
        if (grp_date!='' && start_month != '') {
            $('#auction_modal_btn')
                .attr('data-toggle', 'modal')
                .attr('data-target', '#add_auction_modal');
        } else {
            $('#auction_modal_btn')
                .removeAttr('data-toggle')
                .removeAttr('data-target');
        }
    }

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

    $('#submit_group_info').click(function (event) {
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
                if (response.result == '1') {
                    swalSuccess('Success', 'Group Info Submitted Successfully');
                    $('#groupid').val(response.last_id);
                } else {
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

        if (cus_name != '' && groupid != '') {
            $.post('api/group_creation_files/submit_cus_mapping.php', { cus_name, groupid }, function (response) {
                getCusMapTable(groupid);
            }, 'json');
            $('#cus_name').val('');
        }
    });

    $(document).on('click', '.cusMapDeleteBtn', function () {
        let id = $(this).attr('value');
        swalConfirm('Delete', 'Do you want to remove this customer mapping?', removeCusMap, id, '');
    });

}); //document END////

$(function () {
    getGroupCreationTable();
});

function getGroupCreationTable() {
    serverSideTable('#group_creation_table', '', 'api/group_creation_files/get_grp_creation_list.php');
}

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
function getAutoGenGroupId(id) {
    $.post('api/group_creation_files/get_autoGen_Group_id.php', { id }, function (response) {
        $('#group_id').val(response);
    }, 'json');
}
function callGrpFunctions() {
    getAutoGenGroupId('')
    getDateDropDown('#grp_date', 'Select Date');
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
            cusOptn += '<option value="' + val.id + '">' + val.first_name + ' ' + val.last_name + '</option>';
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

function getCusMapTable(groupid) {
    $.post('api/group_creation_files/get_cus_map_details.php', { groupid }, function (response) {
        let cusMapColumn = [
            "sno",
            "cus_id",
            "name",
            "place",
            "occ",
            "action"
        ]
        appendDataToTable('#cus_mapping_table', response, cusMapColumn);
        setdtable('#cus_mapping_table');
    }, 'json');
}

function removeCusMap(id) {
    $.post('api/group_creation_files/delete_cus_mapping.php', { id }, function (response) {
        if (response == 1) {
            swalSuccess('Success', 'Customer mapping removed successfully.')
            let groupid = $('#groupid').val();
            getCusMapTable(groupid);
        } else {
            swalError('Alert', 'Customer mapping remove failed.')
        }
    }, 'json');
}

// function updateEndMonth() {
//     let grp_date = $('#grp_date').val();
//     let startMonth = $('#start_month').val();
//     let totalMonths = parseInt($('#total_month').val(), 10);
//     if (grp_date && startMonth) {
//         $('#add_auction_modal').show();
//     } else {
//         swalError('Warning', 'Kindly Select the Date and Start Month!');
//     }
  
//     if (startMonth && totalMonths) {
//         let startDate = new Date(startMonth + "-01");
//         let endDate = new Date(startDate.setMonth(startDate.getMonth() + totalMonths - 1));
//         let endMonth = endDate.toISOString().slice(0, 7); // Get YYYY-MM format

//         $('#end_month').val(endMonth);

//         populateAuctionDetailsTable(totalMonths, startMonth, endMonth);
//     }
// }



  


// function populateAuctionDetailsTable(totalMonths, startMonth, endMonth) {
//     let tableBody = $('#grp_details_table tbody');
//     tableBody.empty();

//     let startDate = new Date(startMonth + "-01");

//     for (let i = 0; i < totalMonths; i++) {
//         let monthYear = new Date(startDate.setMonth(startDate.getMonth() + (i === 0 ? 0 : 1)));
//         if (monthYear > new Date(endMonth + "-01")) break;

//         let monthName = monthYear.toLocaleString('default', { month: 'short', year: 'numeric' });
//         let formattedDate = `2-${monthName}`; // Format: 2-Aug-2024

//         tableBody.append(`
//             <tr>
//                 <td>${i + 1}</td>
//                 <td>${monthName}</td>
//                 <td><input type="text" class="form-control low_value" placeholder="Enter Low Value"></td>
//                 <td><input type="text" class="form-control high_value" placeholder="Enter High Value"></td>
//             </tr>
//         `);
//     }

//     $('#grp_details_card').show();
// }
function updateEndMonth() {
    let grp_date = $('#grp_date').val();
    let startMonth = $('#start_month').val();
    let totalMonths = parseInt($('#total_month').val(), 10);
    let groupId = $('#group_id').val(); // Assuming you have a field with id `group_id` for the group ID
    
    if (grp_date && startMonth && groupId) {
        $.post('api/group_creation_files/get_auction_details.php', {
            grp_date: grp_date,
            start_month: startMonth,
            total_month: totalMonths,
            group_id: groupId // Add group_id to the POST data
        }, function(response) {
            let result = JSON.parse(response);

            if (result.result === 1) {
                // Data found in auction_details
                populateAuctionDetailsTable(result.data);
            } else if (result.result === 0) {
                // Generate new rows based on start month and end month
                $('#end_month').val(result.end_month);
                populateAuctionDetailsTableWithInputs(totalMonths, startMonth, result.end_month);
            } else {
                swalError('Error', result.error_message || 'An error occurred.');
            }
        });
    } else {
        swalError('Warning', 'Kindly Select the Date, Start Month, and Group ID!');
    }
}


function populateAuctionDetailsTable(data) {
    let tableBody = $('#grp_details_table tbody');
    tableBody.empty();

    data.forEach(auction => {
        tableBody.append(`
            <tr>
                <td>${auction.id}</td>
                <td>${auction.auction_month}</td>
                <td>${auction.low_value}</td>
                <td>${auction.high_value}</td>
            </tr>
        `);
    });

    $('#grp_details_card').show();
}

function populateAuctionDetailsTableWithInputs(totalMonths, startMonth, endMonth) {
    let tableBody = $('#grp_details_table tbody');
    tableBody.empty();
    let startDate = new Date(startMonth + "-01");

    for (let i = 0; i < totalMonths; i++) {
        let monthYear = new Date(startDate.setMonth(startDate.getMonth() + (i === 0 ? 0 : 1)));
        if (monthYear > new Date(endMonth + "-01")) break;

        let monthName = monthYear.toLocaleString('default', { month: 'short', year: 'numeric' });
        let formattedDate = `2-${monthName}`; // Format: 2-Aug-2024

        tableBody.append(`
            <tr>
                <td>${i + 1}</td>
                <td>${monthName}</td>
                <td><input type="text" class="form-control low_value" placeholder="Enter Low Value"></td>
                <td><input type="text" class="form-control high_value" placeholder="Enter High Value"></td>
            </tr>
        `);
    }

    $('#grp_details_card').show();
}

$('#group_creation').submit(function(event) {
    event.preventDefault();

    let groupId = $('#group_id').val();
    let groupDate = $('#grp_date').val(); // Assuming this is in the format YYYY-MM-DD
    let startMonth = $('#start_month').val();
    let totalMonths = parseInt($('#total_month').val(), 10);

    let auctionDetails = [];
    $('#grp_details_table tbody tr').each(function() {
        let month = $(this).find('td').eq(1).text();
        let lowValue = $(this).find('.low_value').val();
        let highValue = $(this).find('.high_value').val();

        auctionDetails.push({
            date: groupDate,
            auction_month: month,
            low_value: lowValue,
            high_value: highValue
        });
    });

    $.ajax({
        url: 'api/group_creation_files/submit_auction_details.php',
        method: 'POST',
        data: {
            group_id: groupId,
            auction_details: auctionDetails
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Auction details have been saved successfully.',
                confirmButtonColor: '#3085d6'
            });
            $('#group_creation')[0].reset();
            $('#grp_details_table tbody').empty();
            $('#grp_details_card').hide();
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving the auction details.',
                confirmButtonColor: '#d33'
            });
        }
    });
});
