const cus_name = new Choices('#cus_name', {
    removeItemButton: true,
    noChoicesText: 'Select Customer Name',
    allowHTML: true
});
$(document).ready(function () {
    $(document).on('click', '.add_loan, .back_btn', function () {
        //swapTableAndCreation();
        $('.auction_table_content').show();
        $('.auction_detail_content,.back_btn').hide();
        $('.today').show();
       $('.this_month').show();

    });
    $(document).on('click', '.today', function () {
        getAuctionTable(); 
    });
    $(document).on('click', '.this_month', function () {
        getAuctionMonthTable()
    });
    
    $(document).on('click', '.auctionListBtn', function (event) {
        event.preventDefault();
        $('.auction_table_content').hide();
        $('.auction_detail_content,.back_btn').show();
        $('.today').hide();
        $('.this_month').hide();
        let group_id = $(this).attr('value');    
        viewAuctionTable(group_id)

    })
    $(document).on('click', '.auctionBtn', function (event) {
        event.preventDefault();
        $('#add_cus_map_modal').modal('show');
        let group_id = $(this).attr('value');    
 
    })
    $(document).on('click', '.postponeBtn', function (event) {
        event.preventDefault();
        $('#add_pos_modal').modal('show');
        let group_id = $(this).attr('value');    
 
    })
    $(document).on('click', '.viewBtn', function (event) {
        event.preventDefault();
        $('#add_view_modal').modal('show');
        let group_id = $(this).attr('value');    
 
    })
    $(document).on('click', '.calculateBtn', function (event) {
        event.preventDefault();
        $('#add_Calculation_modal').modal('show');
        let group_id = $(this).attr('value');    
 
    })
});



function getAuctionTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_list.php');
}

function getAuctionMonthTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_month_list.php');
}
function viewAuctionTable(group_id) {
    $.post('api/auction_files/view_auction_list.php', { group_id }, function (response) {
        var columnMapping = [
            'auction_month',
            'date',
            'low_value',
            'high_value',
            'cus_id',
            'auction_value',
            'action'
        ];
        appendDataToTable('#auction_table', response, columnMapping);
        setdtable('#auction_table');
    }, 'json');
}
function closeChartsModal() {
    $('#add_cus_map_modal').modal('hide');
    $('#add_pos_modal').modal('hide');
    $('#add_view_modal').modal('hide');
    $('#add_Calculation_modal').modal('hide');
}
function getCusName(customer_edit_it) {

    $.post('api/auction_files/get_customerName_list.php', function (response) {
        cus_name.clearStore();
        $.each(response, function (index, val) {
            let selected = '';
            if (customer_edit_it.includes(val.id)) {
                selected = 'selected';
            }
            let items = [
                {
                    value: val.id,
                    label: val.cus_name,
                    selected: selected
                }
            ];
            cus_name.setChoices(items);
            cus_name.init();
        });
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