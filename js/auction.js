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
        getAuctionTodayTable()
        getAuctionMonthTable()
        getAuctionTable();

    });
    $(document).on('click', '.today', function () {
        getAuctionTodayTable()
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

        var uniqueDetail = $(this).data('value');
        var [groupId, date, id] = uniqueDetail.split('_');

        $('#submit_cus_map').attr('data-group_id', groupId);
        $('#submit_cus_map').attr('data-id', id);
        $('#submit_cus_map').attr('data-date', date);

        $('.auction_close').attr('data-group_id', groupId);
        $('.auction_close').attr('data-date', date);
        $('.auction_close').attr('data-id', id);

        getCusName(groupId); // Fetch customer names based on groupId
    });

    $('#submit_cus_map').on('click', function (e) {
        e.preventDefault();
    
        // Get the selected customers (can be multiple)
        var selectedCustomers = $('#cus_name').val(); // Get the selected customer IDs
        if (selectedCustomers && selectedCustomers.length > 0) {
            // Iterate over each selected customer
            selectedCustomers.forEach(function (cusId) {
                // Get the customer name for the given ID
                var cusName = $('#cus_name option[value="' + cusId + '"]').text().trim(); // Trim to remove extra spaces
    
                // Debug output
                console.log('Customer ID:', cusId);
                console.log('Customer Name:', cusName);
    
                // Create a unique identifier for each entry using timestamp and index
                var uniqueIdentifier = cusId + '_' + new Date().getTime(); // Using timestamp to ensure uniqueness
    
                // Append the selected customer to the table as a new row
                $('#cus_mapping_table tbody').append(`
                    <tr data-cus-id="${cusId}" data-unique-id="${uniqueIdentifier}">
                        <td>${$('#cus_mapping_table tbody tr').length + 1}</td>
                        <td class="cus-name-column">${cusName}</td>
                        <td><input type="text" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value"></td>
                    </tr>
                `);
            });
    
            // Clear the selection
            cus_name.removeActiveItems();
            let groupId = $(this).attr('data-group_id');
            getCusName(groupId); // Re-fetch the customer list if needed
        }
    });
    
    

// Function to get customer name by ID
function getCustomerName(cusId) {
    var option = $('#cus_name option[value="' + cusId + '"]');
    return option.text().trim(); // Ensure there are no extra spaces or concatenation
}


    // Event handler for re-adding a customer from the table
    $(document).on('click', '#cus_mapping_table tbody tr .cus-name-column', function () {
        var $row = $(this).closest('tr');
        var cusId = $row.data('cus-id');
        var cusName = $row.find('td.cus-name-column').text();

        // Create a new unique identifier for the re-added row
        var uniqueIdentifier = cusId + '_' + new Date().getTime(); // Ensure new uniqueness

        // Append the same customer to the table as a new row
        $('#cus_mapping_table tbody').append(`
            <tr data-cus-id="${cusId}" data-unique-id="${uniqueIdentifier}">
                <td>${$('#cus_mapping_table tbody tr').length + 1}</td>
                <td class="cus-name-column">${cusName}</td>
                <td><input type="text" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value"></td>
            </tr>
        `);
    });


    // Event handler for making the input readonly once a value is entered
    $(document).on('change', '#cus_mapping_table tbody input[type="text"]', function () {
        $(this).prop('readonly', true);
    });

    $(document).on('click', '.auction_close', function () {
        let group_id = $(this).attr('data-group_id');
        let date = $(this).attr('data-date');
        let id = $(this).attr('data-id'); // Use attr() instead of data() for attributes

        // Collect table data
        let tableData = [];
        $('#cus_mapping_table tbody tr').each(function () {
            let cusId = $(this).data('cus-id');
            let value = $(this).find('input[name="cus_value[]"]').val();

            tableData.push({
                cus_id: cusId,
                value: value,
                group_id: group_id,
                date: date,
                id: id
            });
        });

        // Send data to server
        $.ajax({
            url: 'api/auction_files/insert_auction_list.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ data: tableData }),
            success: function (response) {
                if (response.success) {
                    swalSuccess('Success', "Auction data successfully inserted.")
                    $('#add_Calculation_modal').modal('show');
                    calculationModal(group_id, date)
                    fetchAuctionDetails(group_id)
                }
            },
        });
    });


    // $('#submit_cus_map').click(function (event) {
    //     event.preventDefault();
    //     let group_id = $(this).attr('data-group-id');
    //     let date = $(this).attr('data-date');
    //     let cus_names = $('#cus_name').val();
    //     if (!Array.isArray(cus_names) || cus_names.length === 0) {
    //         SwalError('Error', 'Please select at least one customer.');
    //         return;
    //     }
    //     // Send data for each customer name individually
    //     let requests = cus_names.map(function (cus_name) {
    //         return $.post('api/auction_files/submit_auction_list.php', {
    //             cus_name: cus_name,
    //             group_id: group_id,
    //             date: date
    //         }).fail(function () {
    //             return { status: 'error' }; // Return an error status if the request fails
    //         });
    //     });

    //     $.when.apply($, requests).done(function () {
    //         let allSuccessful = true;
    //         requests.forEach(function (request) {
    //             let response = request[0]; // Extract the response from the request
    //             if (response && response.status !== 'success') {
    //                 allSuccessful = false;
    //             }
    //         });

    //         if (allSuccessful) {
    //             cus_name.removeActiveItems();
    //             getCusNameTable(group_id, date);
    //         }
    //     });
    // });

    // $('.auction_close').on('click', function () {
    //     let groupId = $(this).data('group-id');
    //     let date = $(this).data('date');

    //     // Step 1: Fetch existing IDs and their values from auction_modal
    //     $.post('api/auction_files/get_auction_modal_id.php', {
    //         group_id: groupId,
    //         date: date
    //     }, function (response) {
    //         if (response.status === 'success') {
    //             let existingValues = response.data;
    //             let updatedValues = [];

    //             $('#cus_mapping_table tbody tr').each(function () {
    //                 let tr = $(this);
    //                 let cusId = tr.data('cus-id');
    //                 let value = tr.find('input').val();

    //                 // Find existing item based on cus_id
    //                 let existingItem = existingValues.find(item => item.id === cusId);
    //                 if (existingItem) {
    //                     // Update existing entry
    //                     updatedValues.push({ id: existingItem.id, value: value });
    //                 } else {
    //                     // Handle as required if no match found
    //                     updatedValues.push({ id: cusId, value: value });
    //                 }
    //             });

    //             // Ensure there are values to update
    //             if (updatedValues.length > 0) {
    //                 // Step 2: Send POST request to close auction
    //                 $('#add_Calculation_modal').modal('show');
    //                // This gets the unique value for fetching data
    //                 calculationModal(groupId,date)

    //                 $.post('api/auction_files/submit_auction_close.php', {
    //                     group_id: groupId,
    //                     date: date,
    //                     values: updatedValues
    //                 }, function (response) {
    //                     if (response.status === 'success') {
    //                         swalSuccess('Success', response.message);
    //                         fetchAuctionDetails(groupId)
    //                     } else {
    //                         swalError('Error', response.message);
    //                     }
    //                 }, 'json');
    //             } else {
    //                 swalError('Error', 'No values to update.');
    //             }
    //         } else {
    //             swalError('Error', response.message);
    //         }
    //     }, 'json');
    // });

    //////////////////////////////////////////////////////////Auction Modal End//////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////PostPone Modal Start//////////////////////////////////////////////////////////////
    $(document).on('click', '.postponeBtn', function () {
        var dataValue = $(this).data('value');
        var parts = dataValue.split('_');
        var groupId = parts[0];
        var auctionMonth = parts[1];

        $('#mapping_id').val(groupId);
        $('#date_name_edit').val(auctionMonth);

        $('#add_pos_modal').modal('show');
        populateDates();
    });

    $('#submit_postpone').click(function (event) {
        event.preventDefault();
        var selectedDate = $('#grp_date').val();
        var groupId = $('#mapping_id').val();
        var auctionMonth = $('#date_name_edit').val();

        if (selectedDate === "") {
            alert("Please select a date.");
            return;
        }

        // Convert selectedDate to the correct format (YYYY-MM-DD)
        var today = new Date();
        var year = today.getFullYear();
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var newDate = year + '-' + month + '-' + String(selectedDate).padStart(2, '0');

        // Send the data to the server using AJAX
        $.ajax({
            url: 'api/auction_files/update_auction_date.php',
            method: 'POST',
            data: {
                group_id: groupId,
                new_date: newDate,
                auction_month: auctionMonth
            },
            success: function (response) {
                fetchAuctionDetails(groupId)
            },

        });
    });
    /////////////////////////////////////////////////////////////////////////////////PostPone Modal end//////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////View Modal Start///////////////////////////////////////

    $(document).on('click', '.viewBtn', function (event) {
        event.preventDefault();
        $('#add_view_modal').modal('show');

        var uniqueValue = $(this).data('value');
        var [groupId, date] = uniqueValue.split('_');

        // Fetch data from server
        $.post('api/auction_files/auction_close_view.php', {
            group_id: groupId,
            date: date
        }, function (response) {
            if (response.status === 'success') {
                var data = response.data;
                var tableBody = $('#view_table tbody');
                tableBody.empty(); // Clear any existing rows

                // Populate table with data
                $.each(data, function (index, row) {
                    var formattedValue = moneyFormatIndia(row.value);
                    var rowHtml = `<tr>
                        <td>${index + 1}</td>
                        <td>${row.first_name} ${row.last_name}</td>
                        <td>${formattedValue}</td>
                    </tr>`;
                    tableBody.append(rowHtml);
                });
            } else {
                alert('Error: ' + response.message); // Show error message
            }
        }, 'json');
    });

    ////////////////////////////////////////////////////////////////View Modal End////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////CalCulation Modal Start///////////////////////////////////////////////////////
    $(document).on('click', '.calculateBtn', function (event) {
        event.preventDefault();
        $('#add_Calculation_modal').modal('show');
        let uniqueValue = $(this).attr('data-value'); // This gets the unique value for fetching data
        calculation(uniqueValue);

    });
    $(document).on('click', '#print_cal', function () {
        // Function to format date from yyyy-mm-dd to dd-mm-yyyy
        function formatDate(dateString) {
            const [year, month, day] = dateString.split('-');
            return `${day}-${month}-${year}`;
        }

        // Get the raw date value from the input
        const rawDate = $('#cal_date').val();

        // Format the date
        const formattedDate = formatDate(rawDate);
        const chitValue = $('#chit_value').val().replace(/,/g, ''); // Ensure no extra commas
        const commission = $('#Commission').val().replace(/,/g, '');
        const auctionValue = $('#auction_value').val().replace(/,/g, '');
        const chitAmount = $('#chit_amount').val().replace(/,/g, '');
        const totalValue = $('#total_value').val().replace(/,/g, '');


        const formattedChitValue = moneyFormatIndia(chitValue);
        const formattedCommission = moneyFormatIndia(commission);
        const formattedAuctionValue = moneyFormatIndia(auctionValue);
        const formattedChitAmount = moneyFormatIndia(chitAmount);
        const formattedTotalValue = moneyFormatIndia(totalValue);

        // Create the HTML content with formatted values
        const content = `
        <div id="print_content" style="text-align: center;">
            <h2 style="margin-bottom: 20px; display: flex; align-items: center; justify-content: center;">
                <img src="img/auction.png" width="25" height="25" style="margin-right: 10px;">
                Chit Company
            </h2>
            <table style="margin: 0 auto; border-collapse: collapse; width: 25%;">
                <tr>
                    <td><strong>Group Name</strong></td>
                    <td>${$('#group_name').val()}</td>
                </tr>
                <tr>
                    <td><strong>Auction Month</strong></td>
                    <td>${$('#auction_month').val()}</td>
                </tr>
                <tr>
                    <td><strong>Date</strong></td>
                    <td>${formattedDate}</td>
                </tr>
                <tr>
                    <td><strong>Chit Value</strong></td>
                    <td>${formattedChitValue}</td>
                </tr>
                <tr>
                    <td><strong>Auction Value</strong></td>
                    <td>${formattedAuctionValue}</td>
                </tr>
                <tr>
                    <td><strong>Commission</strong></td>
                    <td>${formattedCommission}</td>
                </tr>
                <tr>
                    <td><strong>Total Value</strong></td>
                    <td>${formattedTotalValue}</td>
                </tr>
                <tr>
                    <td><strong>Chit Amount</strong></td>
                    <td>${formattedChitAmount}</td>
                </tr>
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
    });

    ////////////////////////////////////////////////////////////////////CalCulation Modal End///////////////////////////////////////////////
});

$(function () {
    getAuctionTable();
});

function getAuctionTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_list.php');
}

function getAuctionMonthTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_month_list.php');
}

function getAuctionTodayTable() {
    serverSideTable('#auction_list_table', '', 'api/auction_files/auction_today_list.php');
}

function fetchAuctionDetails(groupId) {
    $.ajax({
        url: 'api/auction_files/view_auction_list.php', // Update this with the correct path to your PHP script
        type: 'POST',
        dataType: 'json',
        data: {
            group_id: groupId
        },
        success: function (response) {
            if (response.length > 0) {
                var tbody = $('#auction_table tbody');
                tbody.empty(); // Clear existing rows

                $.each(response, function (index, item) {
                    var auctionMonth = item.auction_month;
                    var date = item.date;

                    // Format the values using moneyFormatIndia
                    var lowValue = moneyFormatIndia(item.low_value);
                    var highValue = moneyFormatIndia(item.high_value);
                    var auctionValue = moneyFormatIndia(item.auction_value);

                    var cusName = item.cus_name;
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
            }
        },
        error: function (xhr, status, error) {
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
function calculation(uniqueValue) {
    const [group_id, date] = uniqueValue.split('_');

    $.ajax({
        url: 'api/auction_files/fetch_calculation_data.php',
        type: 'POST',
        data: {
            group_id: group_id,
            date: date
        },
        dataType: 'json',
        success: function (response) {
            $('#group_name').val(response.group_name);
            $('#auction_month').val(response.auction_month);

            // Format the date in dd-mm-yyyy format
            $('#cal_date').val(formatDate(response.cal_date));

            $('#chit_value').val(moneyFormatIndia(response.chit_value));
            $('#auction_value').val(moneyFormatIndia(response.auction_value));
            $('#Commission').val(moneyFormatIndia(response.commission));
            $('#total_value').val(moneyFormatIndia(response.total_value));
            $('#chit_amount').val(moneyFormatIndia(response.chit_amount));
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

function formatDate(dateString) {
    const [year, month, day] = dateString.split('-'); // Assuming date is in yyyy-mm-dd format
    return `${day}-${month}-${year}`;
}

function calculationModal(groupId, date) {
    // Split the uniqueValue into parts

    $.ajax({
        url: 'api/auction_files/fetch_calculation_data.php', // URL to the server-side script that fetches the data
        type: 'POST',
        data: {
            group_id: groupId, // Send the group_id part
            date: date // Send the date part
        },
        dataType: 'json',
        success: function (response) {
            // Assuming the server returns a JSON object with the relevant data
            $('#group_name').val(response.group_name);
            $('#auction_month').val(response.auction_month);
            $('#cal_date').val(formatDate(response.cal_date));
            $('#chit_value').val(moneyFormatIndia(response.chit_value));
            $('#auction_value').val(moneyFormatIndia(response.auction_value));
            $('#Commission').val(moneyFormatIndia(response.commission));
            $('#total_value').val(moneyFormatIndia(response.total_value));
            $('#chit_amount').val(moneyFormatIndia(response.chit_amount));
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
