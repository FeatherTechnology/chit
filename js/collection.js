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
        let dataValue = $(this).data('value');
        let dataParts = dataValue.split('_');
        let groupId = dataParts[0];
        let customerId = dataParts[1];
        let auctionId = dataParts[2];
        let cusMappingID = dataParts[3]; // Extract cus_mapping_id from data attribute
        let cusId = dataParts[4];
        $.ajax({
            url: 'api/collection_files/fetch_pay_details.php',
            type: 'POST',
            data: {
                group_id: groupId,
                cus_mapping_id: cusMappingID
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Round off chit_amount and payable_amnt
                    let roundedChitAmount = Math.round(response.chit_amount);
                    let roundedPayableAmnt = Math.round(response.payable_amnt);
        
                    // Populate the form fields with the fetched and rounded data
                    $('#group_name').val(response.group_name);
                    $('#auction_month').val(response.auction_month);
                    $('#date').val(response.date);
                    $('#chit_value').val(response.chit_value);
                    $('#chit_amt').val(roundedChitAmount);
                    $('#pending_amt').val(response.pending_amt);
                    $('#payable_amnt').val(roundedPayableAmnt);
                    $('#collection_amount').val('');
        
                } else {
                    swalError('Warning', 'Failed to save the collection details');
                }
            }
        });
        

        $('#submit_collection').unbind('click').click(function (event) {
            event.preventDefault();

            let collectionDate = $('#collection_date').val();
            let collectionAmount = $('#collection_amount').val(); // Get the collection amount
            let payableAmount = Math.round(parseFloat($('#payable_amnt').val())); // Get and round off the payable amount
            let chitAmount = Math.round(parseFloat($('#chit_amt').val())); // Get and round off the chit amount

            let isValid = true; // Assume form is valid unless proven otherwise

            // Validate the collection amount field
            if (!validateField(collectionAmount, 'collection_amount')) {
                isValid = false;
            }

            // Check if collection amount is less than or equal to payable amount
            if (parseFloat(collectionAmount) > payableAmount) {
                isValid = false;
                swalError('Warning', 'Collection amount cannot be greater than payable amount.');
            }

            if (isValid) {
                // Send the data to the server using AJAX
                $.ajax({
                    url: 'api/collection_files/submit_collection.php',
                    method: 'POST',
                    data: {
                        group_id: groupId,
                        cus_id: customerId,
                        auction_id: auctionId,
                        cus_mapping_id: cusMappingID, // Pass cus_mapping_id
                        auction_month: $('#auction_month').val(),
                        chit_value: $('#chit_value').val(),
                        chit_amount: chitAmount, // Use rounded chit amount
                        pending_amt: $('#pending_amt').val(),
                        payable_amnt: payableAmount, // Use rounded payable amount
                        collection_amount: collectionAmount,
                        collection_date: collectionDate
                    },
                    success: function (response) {
                        if (response == '1') {
                            swalSuccess('Success', "Collected Successfully");
                            // Optionally clear the form fields
                            $('#collection_amount').val('');
                            viewCustomerGroups(cusId);
                            $('.colls-cntnr,#back_to_coll_list').show();
                            $('.coll_details, #back_to_pay_list').hide();
                        } else {
                            swalError('Warning', 'Failed to save the collection details');
                        }
                        // Ensure response is parsed correctly
                    },
                });
            }
        });
    });


    ////////////////////////////////////////////////Pay End/////////////////////////////////////////////////
    ////////////////////////////////////////////////////////Commitement  Start////////////////////////////////////////////
    $(document).on('click', '.add_commitment', function (event) {
        event.preventDefault();

        // Show the modal
        $('#add_commitment_modal').modal('show');

        // Pre-fill the modal or attach necessary data if required
        let dataValue = $(this).data('value');
        let dataParts = dataValue.split('_');
        let groupId = dataParts[0];
        let cusMappingID = dataParts[1];
        // getCommitmentInfoTable(cusMappingID,groupId)

        $('#add_commit').on('click', function (event) {
            event.preventDefault();

            // Validation
            let label = $('#label').val();
            let remark = $('#remark').val();

            var isValid = true;

            // Validate each field
            if (!validateField(label, 'label')) {
                isValid = false;
            }
            if (!validateField(remark, 'remark')) {
                isValid = false;
            }

            // If all fields are valid, proceed with the AJAX call
            if (isValid) {
                $.post('api/collection_files/submit_commitement.php', {
                    group_id: groupId,
                    cus_mapping_id: cusMappingID, // Pass cus_mapping_id
                    label: label,
                    remark: remark
                }, function (response) {
                    if (response == '1') {
                        swalSuccess('Success', 'Commitment Added Successfully!');
                        $('#label').val('');
                        $('#remark').val('');
                        getCommitmentInfoTable(cusMappingID, groupId)
                    } else {
                        swalError('Warning', 'Commitment Not Added!');
                    }
                });
            }
        });

        $(document).on('click', '.commitDeleteBtn', function () {
            var id = $(this).attr('value');
            swalConfirm('Delete', 'Do you want to Delete the Commitment Details?', function () {
                getCommitDelete(id, cusMappingID, groupId); // Pass cusMappingID to delete function
            });
        });
    });

    ///////////////////////////////////////////////////////Commitement  End/////////////////////////////////////////////////
    ///////////////////////////////////////////////////////Due Start/////////////////////////////////////////////
    $(document).on('click', '.add_due', function (event) {
        event.preventDefault();
        $('#due_chart_model').modal('show');
        let dataValue = $(this).data('value');
        let dataParts = dataValue.split('_');
        let groupId = dataParts[0];
        let cusMappingID = dataParts[1];
        let auction_month = dataParts[2];
        getDueChart(groupId, cusMappingID, auction_month);

        setTimeout(() => {
            $('.print_due_coll').click(function () {
                // Fetch the data from the server and create a table with it
                const coll_id = $(this).attr('id');
                $.ajax({
                    url: 'api/collection_files/print_collection.php', // Update with the correct path to your PHP script
                    type: 'POST',
                    data: {
                        coll_id: coll_id,
                    },
                    dataType: 'json',
                    success: function (response) {
                        // Create the HTML content with formatted values
                        let rows = response.map(row => `
                            <tr>
                                <td><strong>Group ID</strong></td>
                                <td>${row.group_id}</td>
                            </tr>
                            <tr>
                                <td><strong>Group Name</strong></td>
                                <td>${row.group_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer Name</strong></td>
                                <td>${row.cus_name}</td>
                            </tr>
                            <tr>
                                <td><strong>Auction Month</strong></td>
                                <td>${row.auction_month}</td>
                            </tr>
                            <tr>
                                <td><strong>Chit Amount</strong></td>
                                <td>${row.chit_amount}</td>
                            </tr>
                            <tr>
                                <td><strong>Payable</strong></td>
                                <td>${row.payable}</td>
                            </tr>
                            <tr>
                                <td><strong>Collection Date</strong></td>
                                <td>${row.collection_date}</td>
                            </tr>
                            <tr>
                                <td><strong>Collection Amount</strong></td>
                                <td>${row.collection_amount}</td>
                            </tr>
                            <tr>
                                <td><strong>Pending</strong></td>
                                <td>${row.pending}</td>
                            </tr>
                        `).join('');

                        const content = `
                            <div id="print_content" style="text-align: center;">
                                <h2 style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                                    <img src="img/auction.png" width="25" height="25" style="margin-right: 10px;">
                                    Chit Company
                                </h2>
                                <table style="margin: 0 auto; border-collapse: collapse; width: 50%;">
                                    ${rows}
                                </table>
                            </div>
                        `;

                        const tempDiv = $('<div>').html(content).css({
                            position: 'absolute',
                            top: '-500px',
                            left: '-500px',
                            width: '800px',  // Adjust width
                            height: 'auto',  // Adjust height or set a specific height like '600px'
                            padding: '20px', // Optional: add padding for better layout in the image
                            backgroundColor: '#fff', // Ensure background is white (or any color you prefer)
                            textAlign: 'center', // Center-aligns the content
                            fontFamily: 'Arial, sans-serif' // Optional: for better font rendering
                        }).appendTo('body');

                        html2canvas(tempDiv[0], {
                            scale: 2,  // Increase the scale factor to improve the resolution
                            width: tempDiv.outerWidth(), // Set canvas width to the width of the div
                            height: tempDiv.outerHeight() // Set canvas height to the height of the div
                        }).then(canvas => {
                            const imgData = canvas.toDataURL('image/png');
                            const link = document.createElement('a');
                            link.href = imgData;
                            link.download = 'calculation_details.png';
                            link.click();
                            tempDiv.remove();
                        }).catch(err => {
                            console.error('Error generating image:', err);
                        });
                    },
                });
            });
        }, 1000);


    });
    ////////////////////////////////////////////////////////Due End////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////Commitement Chart Start////////////////////////////////////////////
    $(document).on('click', '.commitment_chart', function (event) {
        event.preventDefault();
        $('#commitment_chart_model').modal('show');
        let dataValue = $(this).data('value');
        let dataParts = dataValue.split('_');
        let groupId = dataParts[0];
        let cusMappingID = dataParts[1];
        getCommitmentChartTable(cusMappingID, groupId)

    });
    ///////////////////////////////////////////////////////Commitement Chart End/////////////////////////////////////////////////
    /////////////////////////////////////Document End//////////////////////////////////////////////////////////////////    
})
function closeChartsModal() {
    $('#due_chart_model').modal('hide');
    $('#commitment_chart_model').modal('hide');
    $('#add_commitment_modal').modal('hide');
    $('#label').val('');
    $('#remark').val('');
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
        // Iterate through the response to round off chit_amount
        response.forEach(function(item) {
            item.chit_amount = Math.round(item.chit_amount); // Round off chit_amount
        });

        let cashList = [
            "sno",
            "grp_id",
            "grp_name",
            "chit_value",
            "chit_amount",  // Rounded chit_amount will be used here
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
function getCommitmentInfoTable(cusMappingID, groupId) {
    $.post('api/collection_files/commitment_info_data.php', { cus_mapping_id: cusMappingID, group_id: groupId }, function (response) {
        var columnMapping = [
            'sno',
            'label',
            'remark',
            'action'

        ];
        appendDataToTable('#commit_form_table', response, columnMapping);
        setdtable('#commit_form_table');
    }, 'json')
}
function getCommitDelete(id, cusMappingID, groupId) {
    $.post('api/collection_files/delete_commitment.php', { id: id }, function (response) {
        if (response === '1') {
            swalSuccess('Success', 'Commitment Info Deleted Successfully!');
            getCommitmentInfoTable(cusMappingID, groupId)// Pass cusMappingID to refresh the table
        } else {
            swalError('Error', 'Failed to Delete Commitment: ' + response);
        }
    }, 'json');
}
function getCommitmentChartTable(cusMappingID, groupId) {
    $.post('api/collection_files/commitment_chart_data.php', { cus_mapping_id: cusMappingID, group_id: groupId }, function (response) {
        var columnMapping = [
            'sno',
            'created_on',
            'label',
            'remark',
        ];
        appendDataToTable('#commitment_chart_table', response, columnMapping);
        setdtable('#commitment_chart_table');
    }, 'json')
}
function getDueChart(groupId, cusMappingID, auction_month) {
    $.ajax({
        url: 'api/collection_files/due_chart_data.php', // Update this with the correct path to your PHP script
        type: 'POST',
        dataType: 'json',
        data: {
            group_id: groupId,
            cus_mapping_id: cusMappingID,
            auction_month: auction_month
        },
        success: function (response) {
            console.log(response);
            var tbody = $('#due_chart_table tbody');
            tbody.empty(); // Clear existing rows

            // Track whether we have added any rows
            var hasRows = false;

            $.each(response, function (index, item) {
                var auctionMonth = item.auction_month;
                var auctionDate = item.auction_date;

                // Format the values using moneyFormatIndia
                var chitAmount = item.chit_amount ? moneyFormatIndia(item.chit_amount) : '';
                var payable = item.payable ? moneyFormatIndia(item.payable) : '';
                var collectionDate = item.collection_date ? item.collection_date : '';
                var collectionAmount = item.collection_amount ? moneyFormatIndia(item.collection_amount) : '';
                //  var pending = item.pending;
                var pending = item.pending !== null && item.pending !== undefined ? moneyFormatIndia(item.pending) : '';

                var action = item.action ? item.action : '';

                var row = '<tr>' +
                    '<td>' + auctionMonth + '</td>' +
                    '<td>' + auctionDate + '</td>' +
                    '<td>' + chitAmount + '</td>' +
                    '<td>' + payable + '</td>' +
                    '<td>' + collectionDate + '</td>' +
                    '<td>' + collectionAmount + '</td>' +
                    '<td>' + pending + '</td>' +
                    '<td>' + action + '</td>' +
                    '</tr>';

                tbody.append(row);
                hasRows = true;
            });

            // If no data was found in the response
            if (!hasRows) {
                // Display a row with auction_month and a 'No data available' message
                var noDataRow = '<tr>' +
                    '<td>' + auction_month + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '<td>' + '' + '</td>' +
                    '</tr>';
                tbody.append(noDataRow);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + error);
        }
    });
}

