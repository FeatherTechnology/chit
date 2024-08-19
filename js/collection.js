$(document).ready(function () {
    $(document).on('click', '#back_to_coll_list', function () {
        $('#collection_list').show();
        getCollectionTable();
        $('#coll_main_container,#back_to_coll_list').hide();

    });
    $(document).on('click', '#back_to_pay_list', function (event) {
        event.preventDefault();
        $('.colls-cntnr,#back_to_coll_list').show();
        $('.coll_details,#back_to_pay_list').hide();


    })


    $(document).on('click', '.collectionListBtn', function (event) {
        event.preventDefault();
        $('#collection_list').hide();
        $('#coll_main_container,#back_to_coll_list').show();
        let id = $(this).attr('value');
        $('#group_id').val(id);
        // editGroupCreation(id)
        viewCustomerGroups(id);
        editCustomerCreation(id)
    })
    /////////////////////////////////////////////////////Pay Start//////////////////////////////////////////////////////////
    $(document).on('click', '.add_pay', function (event) {
        event.preventDefault();
       
        // Hide and show the appropriate sections
        $('.colls-cntnr, #back_to_coll_list').hide();
        $('.coll_details, #back_to_pay_list').show();
        collectDate();
        
        // Extract the group and customer ID from the data attribute
        let dataValue = $(this).data('value');
        let dataParts = dataValue.split('_');
        
        // Log the dataParts array to verify its content
        console.log('Data Parts:', dataParts);
        
        let groupId = dataParts[0];
        let customerId = dataParts[1];
        let auctionId = dataParts[2];
        let cusMappingID = dataParts[3]; // Extract cus_mapping_id from data attribute
        
        console.log('Group ID:', groupId);
        console.log('Customer ID:', customerId);
        console.log('Auction ID:', auctionId);
        console.log('Customer Mapping ID:', cusMappingID); // Log cusMappingID to verify
        
        // Check if cusMappingID is empty
        if (!cusMappingID) {
            console.error('Customer Mapping ID is empty');
        }
        
        // Make an AJAX call to fetch the details
        $.ajax({
            url: 'api/collection_files/fetch_pay_details.php',
            type: 'POST',
            data: {
                group_id: groupId,
                cus_id: customerId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Populate the form fields with the fetched data
                    $('#group_name').val(response.group_name);
                    $('#auction_month').val(response.auction_month);
                    $('#date').val(response.date);
                    $('#chit_value').val(response.chit_value);
                    $('#chit_amt').val(response.chit_amount);
                    $('#pending_amt').val(response.pending_amt);
                    $('#payable_amnt').val(response.payable_amnt);
                    $('#collection_amount').val('');
                } else {
                    swalError('Warning', 'Failed to save the collection details');
                }
            },
           
        });
    
        $('#submit_collection').click(function (event) {
            event.preventDefault();
            let collectionDate = $('#collection_date').val();
            let collectionAmount = $('#collection_amount').val(); // Get the collection amount
    
            // Send the data to the server using AJAX
            $.ajax({
                url: 'api/collection_files/submit_collection.php',
                method: 'POST',
                data: {
                    group_id: groupId,
                    cus_id: customerId,
                    id: auctionId,
                    cus_mapping_id: cusMappingID, // Pass cus_mapping_id
                    pending_sts: $('#pending_sts').val(),
                    auction_month: $('#auction_month').val(),
                    chit_value: $('#chit_value').val(),
                    chit_amount: $('#chit_amt').val(),
                    pending_amt: $('#pending_amt').val(),
                    payable_amnt: $('#payable_amnt').val(),
                    collection_amount: collectionAmount,
                    collection_date: collectionDate
                },
                success: function (response) {
                    // Ensure response is parsed correctly
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    } 
                    if (response.success) {
                        swalSuccess('Success', "Collected Successfully");
                        // Optionally clear the form fields
                        $('#collection_amount').val('');
                    } else {
                        swalError('Warning', 'Failed to save the collection details');
                    }
                },
            });
        });
    });
    

    ////////////////////////////////////////////////Payy End/////////////////////////////////////////////////
    ////////////////////////////////////////////////////////Commitement  Start////////////////////////////////////////////
    $(document).on('click', '.add_commitment', function (event) {
        event.preventDefault();
        $('#add_commitment_modal').modal('show');
    });
    ///////////////////////////////////////////////////////Commitement  End/////////////////////////////////////////////////
    ///////////////////////////////////////////////////////Due Start/////////////////////////////////////////////
    $(document).on('click', '.add_due', function (event) {
        event.preventDefault();
        $('#due_chart_model').modal('show');
    });
    ////////////////////////////////////////////////////////Due End////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////Commitement Chart Start////////////////////////////////////////////
    $(document).on('click', '.commitment_chart', function (event) {
        event.preventDefault();
        $('#commitment_chart_model').modal('show');
    });
    ///////////////////////////////////////////////////////Commitement Chart End/////////////////////////////////////////////////
    /////////////////////////////////////Document End//////////////////////////////////////////////////////////////////    
})
function closeChartsModal() {
    $('#due_chart_model').modal('hide');
    $('#commitment_chart_model').modal('hide');
    $('#add_commitment_modal').modal('hide');
}
$(function () {
    getCollectionTable();
});

function getCollectionTable() {
    serverSideTable('#collection_list_table', '', 'api/collection_files/collection_list.php');
}
function editCustomerCreation(id) {
    $.post('api/collection_files/collection_customer_data.php', { id: id }, function (response) {
        if (Array.isArray(response) && response.length > 0) {
            $('#group_id').val(id);
            $('#cus_id').val(response[0].cus_id);
            $('#cus_name').val(response[0].cus_name); // Full name in a single field
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
    }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX request failed:", textStatus, errorThrown);
    });
}
function viewCustomerGroups(id) {
    $.post('api/collection_files/collection_group_data.php', { id: id }, function (response) {
        let cashList = [
            "sno",
            "grp_id",
            "grp_name",
            "chit_value",
            "chit_amount",
            "status",
            "grace_period",
            "charts",
            "action"
        ];

        appendDataToTable('#group_list_table', response, cashList);
        setdtable('#group_list_table');
        setDropdownScripts();
    }, 'json');
}
function collectDate() {
    var today = new Date();
    var day = String(today.getDate()).padStart(2, '0');
    var month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0
    var year = today.getFullYear();

    var currentDate = day + '-' + month + '-' + year;
    $('#collection_date').val(currentDate);
}