$(document).ready(function () {
    $('#branch_id').change(function () {
        getProcessingGroupList();
        getApprovalCounts();
        getCollectionCounts();
        getClosedCounts();
            
    });

    $('#group_list_title').click(function () {
        $('#group_list_body').slideToggle();
        setTimeout(() => {
            if ($('#group_list_body').is(':visible')) {
                getProcessingGroupList();
            }
        }, 500);
    });

    $('#auction_list_title').click(function () {
        $('#auction_list_body').slideToggle();
        setTimeout(() => {
            if ($('#auction_list_body').is(':visible')) {
                getApprovalCounts();
            }
        }, 500);
    });

    $('#collection_title').click(function () {
        $('#collection_body').slideToggle();
        setTimeout(() => {
            if ($('#collection_body').is(':visible')) {
                getCollectionCounts();
            }else{
                $('#total_coll').trigger('click');
            }
        }, 500);
    });

    $('#settlement_title').click(function () {
        $('#settlement_body').slideToggle();
        setTimeout(() => {
            if ($('#settlement_body').is(':visible')) {
                getClosedCounts();
            }
        }, 500);
    });

    $(document).on('click','.open-group-creation',function(event){
        event.preventDefault();
        let grpId = $(this).attr('value');
        localStorage.setItem('dashboardGrp', grpId);
        setlocalvariable(this);
    });

    $(document).on('click','.open-auction-list',function(event){
        event.preventDefault();
        let grpId = $(this).attr('data-grpid');
        localStorage.setItem('dashboardAuc', grpId);
        setlocalvariable(this);
    });

});

$(function () {
    checkUserScreenAccess();
});

function checkUserScreenAccess() {
    $.post('api/common_files/check_user_screen_access.php', {}, function (response) {

        let screens = response[0].screens.split(','); // Split the comma-separated string into an array

        if (screens.includes('5')) {
            $('.group-list-card').show();
        }
        if (screens.includes('8')) {
            $('.auction-list-card').show();
        }
        if (screens.includes('10')) {
            $('.collection-card').show();
        }
        if (screens.includes('9')) {
            $('.settlement-card').show();
        }
    }, 'json').then(function () {
        getBranchList();
    });
}

function getBranchList() {
    $.post('api/common_files/get_branch_list.php', function (response) {
        let appendBranchOption = '';
        appendBranchOption += '<option value="">Select Branch</option>';
        appendBranchOption += '<option value="0">All Branch</option>';
        $.each(response, function (index, val) {
            appendBranchOption += '<option value="' + val.id + '">' + val.branch_name + '</option>';
        });
        $('#branch_id').empty().append(appendBranchOption);

    }, 'json');
}

function getProcessingGroupList() {
    let branchId = $('#branch_id :selected').val();
    serverSideTable('#group_creation_table', branchId , 'api/dashboard_files/get_process_group_list.php');

}

function getApprovalCounts() {
    let branchId = $('#branch_id :selected').val();
    serverSideTable('#auction_list_table', branchId , 'api/dashboard_files/get_dashboard_auction_list.php');
}

function getCollectionCounts() {
    let branchId = $('#branch_id :selected').val();
    $.post('api/dashboard_files/get_collection_details.php', { branchId }, function (response) {
        $('#tot_paid').text(response['total_paid'])
        $('#today_paid').text(response['today_paid'])
    }, 'json');
}

function getClosedCounts() {
    let branchId = $('#branch_id :selected').val();
    $.post('api/dashboard_files/get_settlement_details.php', { branchId }, function (response) {
        $('#tot_settle').text(response['total_settle'])
        $('#today_settle').text(response['today_settle'])
    }, 'json');
}