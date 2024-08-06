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
        let groupId = $(this).attr('value');
      //  viewAuctionTable(group_id)
        fetchAuctionDetails(groupId)
    })
    ///////////////////////////////////////////////////////Auction Modal Start/////////////////////////////////////////////////////////////////  
    $(document).on('click', '.auctionBtn', function (event) {
        event.preventDefault();
        $('#add_cus_map_modal').modal('show');
        var uniqueValue = $(this).data('value');
    var [groupId, date] = uniqueValue.split('_');
    $('#submit_cus_map').attr('data-group-id', groupId);
    $('#submit_cus_map').attr('data-date', date);
     $('.auction_close').attr('data-date', date);
    $('.auction_close').attr('data-group-id', groupId);
         getCusName(groupId);
        getCusNameTable(groupId, date);
    })

    $('#submit_cus_map').click(function(event) {
        event.preventDefault(); // Prevent the default form submission
    
        // Retrieve group_id and date from button attributes
        let group_id = $(this).attr('data-group-id');
        let date = $(this).attr('data-date');
        let cus_names = $('#cus_name').val(); // Get values as an array
    
        // Ensure there are selected customer names
        if (!Array.isArray(cus_names) || cus_names.length === 0) {
            SwalError('Error', 'Please select at least one customer.');
            return;
        }
    
        // Send data for each customer name individually
        let requests = cus_names.map(function(cus_name) {
            return $.post('api/auction_files/submit_auction_list.php', {
                cus_name: cus_name,
                group_id: group_id,
                date: date
            }).fail(function() {
                return { status: 'error' }; // Return an error status if the request fails
            });
        });
    
        // Wait for all requests to complete
        $.when.apply($, requests).done(function() {
            // Check if all requests succeeded
            let allSuccessful = true;
            requests.forEach(function(request) {
                let response = request[0]; // Extract the response from the request
                if (response && response.status !== 'success') {
                    allSuccessful = false;
                }
            });
    
            if (allSuccessful) {
                // Handle success
                swalSuccess('Success', 'Customer Mapping Inserted Successfully.');
    
                // Update the table with new data
                getCusNameTable(group_id, date);
            } else {
                // Handle error
                SwalError('Error', 'Failed to insert some customer mappings.');
            }
        }).fail(function() {
            // Handle request failure
            SwalError('Error', 'An error occurred while inserting customer mappings.');
        });
    });
    
    
    
    // $('#branch_name').closest('.choices').find('.choices__inner').css('border', '1px solid #cecece');auction_close

    // $('.auction_close').click(function() {
    //     let groupId = $(this).attr('data-group-id'); 
    //     let date = $(this).attr('data-date');
    
    //     let dataToSend = [];
    //     $('#cus_mapping_table tbody tr').each(function() {
    //         let customerId = $(this).find('td').eq(0).text(); // Assuming the customer ID is in the first cell
    //         let value = $(this).find('input').val();
    //         if (value) {
    //             dataToSend.push({
    //                 cus_id: customerId,
    //                 value: value
    //             });
    //         }
    //     });
    
    //     $.post('api/auction_files/submit_auction_close.php', { group_id: groupId, date: date, data: dataToSend }, function(response) {
    //         if (response.status === 'success') {
    //             SwalSuccess('Success', 'Auction closed and data updated successfully.', 'success');
    //         }
    //     }, 'json');
    // });

    $('.auction_close').click(function() {
        let groupId = $(this).data('group-id');
        let date = $(this).data('date');
        
        let dataToSend = [];
        let maxValue = 0;
        let maxCustomerName = '';

        $('#cus_mapping_table tbody tr').each(function() {
            let customerName = $(this).find('td').eq(1).text();
            let value = $(this).find('input').val();
            value = parseFloat(value);
            if (!isNaN(value)) {
                dataToSend.push({
                    cus_name: customerName,
                    value: value
                });
                if (value > maxValue) {
                    maxValue = value;
                    maxCustomerName = customerName;
                }
            }
        });

        if (dataToSend.length > 0) {
            $.ajax({
                url: 'api/auction_files/submit_auction_close.php',
                type: 'POST',
                data: {
                    group_id: groupId,
                    date: date,
                    auction_data: dataToSend,
                    max_value: maxValue,
                    max_customer: maxCustomerName
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Auction closed and data stored successfully');
                    } else {
                        alert('Failed to close auction');
                    }
                }
            });
        }
    });
    
    
    

    //////////////////////////////////////////////////////////Auction Modal End//////////////////////////////////////////////////////////////////////
    $(document).on('click', '.postponeBtn', function (event) {
        event.preventDefault();
        $('#add_pos_modal').modal('show');
        populateDates()

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
        // getCusName(customer_edit_it) 

    })
});



function getAuctionTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_list.php');
}

function getAuctionMonthTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_month_list.php');
}

function fetchAuctionDetails(groupId) {
    $.ajax({
        url: 'api/auction_files/view_auction_list.php', // Update this with the correct path to your PHP script
        type: 'POST',
        dataType: 'json',
        data: {
            group_id: groupId
        },
        success: function(response) {
            if (response.length > 0) {
                var tbody = $('#auction_table tbody');
                tbody.empty(); // Clear existing rows

                $.each(response, function(index, item) {
                    var auctionMonth = item.auction_month;
                    var date = item.date;
                    var lowValue = item.low_value;
                    var highValue = item.high_value;
                    var cusName = item.cus_name;
                    var auctionValue = item.auction_value;
                    var action = item.action;

                    var row = '<tr>' +
                        '<td>' + auctionMonth + '</td>' +
                        '<td>' + date + '</td>' +
                        '<td>' + lowValue + '</td>' +
                        '<td>' + highValue + '</td>' +
                        '<td>' + cusName + '</td>' +
                        '<td>' + auctionValue + '</td>' +
                        '<td>' + action + '</td>' +
                        '</tr>';

                    tbody.append(row);
                });

                $('.auction_detail_content').show(); // Show the card after data is loaded
            } else {
                alert('No auction details found.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error: ' + status + error);
        }
    });
}

function closeChartsModal() {
    $('#add_cus_map_modal').modal('hide');
//     cus_name.removeActiveItems();
//   $('#cus_name').closest('.choices').find('.choices__inner').css('border', '1px solid #cecece');
    $('#add_pos_modal').modal('hide');
    $('#add_view_modal').modal('hide');
    $('#add_Calculation_modal').modal('hide');
}

function getCusName(groupId) {
    $.post('api/auction_files/get_customerName_list.php', { group_id: groupId }, function (response) {
        cus_name.clearStore();

        let items = response.map(function (val) {
            return {
                value: val.id,
                label: val.cus_name,
                selected: false
            };
        });

        cus_name.setChoices(items, 'value', 'label', true);
    }, 'json');
}
function getCusNameTable(groupId, date) {
    $.post('api/auction_files/get_auction_list.php', { group_id: groupId, date: date }, function(response) {
        if (response.status === 'success') {
            let tableBody = $('#cus_mapping_table tbody');
            tableBody.empty(); // Clear any existing rows

            $.each(response.data, function(index, row) {
                let customerName = `${row.first_name} ${row.last_name}`;
                let rowHtml = `<tr data-cus-id="${row.id}" data-value="${row.value}">
                    <td>${index + 1}</td>
                    <td>${customerName}</td>
                    <td><input type="text" class="form-control" name="value_${index}" value="${row.value}"></td>
                </tr>`;
                tableBody.append(rowHtml);
            });

            // Make input fields read-only on change
            tableBody.find('input').on('change', function() {
                $(this).prop('readonly', true);
            });

            // Add click event to table rows
            tableBody.find('tr').on('click', function() {
                let cusId = $(this).data('cus-id');
                let value = $(this).data('value');
                insertIntoAuctionDetail(cusId, value);
            });
        }
    }, 'json');
}

function insertIntoAuctionDetail(cusId, value) {
    $.post('api/auction_files/insert_auction_list.php', {
        cus_id: cusId,
        value: value
    }, function(response) {
        if (response.status === 'success') {
            swalSuccess('Success', 'Data inserted into auction details successfully.');
        } else {
            SwalError('Error', 'Failed to insert data into auction details.');
        }
    }, 'json');
}





function populateDates() {
    var $grpDateSelect = $('#grp_date');
    var today = new Date();
    var currentDay = today.getDate();
    var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();

    // Clear any existing options
    $grpDateSelect.empty().append('<option value="">Select Date</option>');

    // Populate the dropdown with dates starting from today to the end of the month
    for (var day = currentDay; day <= lastDay; day++) {
        $grpDateSelect.append($('<option>', {
            value: day,
            text: day
        }));
    }
}

