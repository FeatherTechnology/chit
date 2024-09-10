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
        // getAuctionTodayTable()
        // getAuctionMonthTable()
        getAuctionTable();
        $('#cus_mapping_table tbody').empty(); 
        $('.auction_close').addClass('d-none');
        $('#pageHeaderName').text(` - Auction`);

        localStorage.setItem('dashboardAuc','');
    });
    $(document).on('click', '.today', function () {
        getAuctionTodayTable()
    });
    $(document).on('click', '.this_month', function () {
        getAuctionMonthTable()
    });
    $(document).on('click', '.auctionListBtn', function (event) {
        event.preventDefault();
    
        let groupId = $(this).attr('data-grpid'); // First part is group ID
        let groupName = $(this).attr('data-grpname'); // Second part is group name
        let chitValue = $(this).attr('data-chitval'); // Third part is chit value
    
        if (chitValue !== undefined && chitValue !== null) {
            // Convert chitValue to a string before formatting
            const formattedChitValue = moneyFormatIndia(chitValue.toString());
    
            // Update the page header with the dynamic values
            $('#pageHeaderName').text(` - Auction - Group ID: ${groupId}, Group Name: ${groupName}, Chit Value: ${formattedChitValue}`);
        } else {
            console.error('Chit value is undefined or null');
            // Handle the case where chitValue is not provided
        }
    
        // Hide and show the necessary sections
        $('.auction_table_content').hide();
        $('.auction_detail_content,.back_btn').show();
        $('.today').hide();
        $('.this_month').hide();
    
        // Fetch auction details based on the selected group ID
        fetchAuctionDetails(groupId);
    });
    

    ///////////////////////////////////////////////////////Auction Modal Start/////////////////////////////////////////////////////////////////  
    $(document).on('click', '.auctionBtn', function (event) {
        event.preventDefault();
        $('#add_cus_map_modal').modal('show');

        var uniqueDetail = $(this).data('value');
        var [groupId, date, id, low_value, high_value] = uniqueDetail.split('_');

        $('#submit_cus_map').attr('data-group_id', groupId);
        $('#submit_cus_map').attr('data-id', id);
        $('#submit_cus_map').attr('data-date', date);
        $('#submit_cus_map').attr('data-high_value', high_value);
        $('#submit_cus_map').attr('data-low_value', low_value);

        $('.auction_close').attr('data-group_id', groupId);
        $('.auction_close').attr('data-date', date);
        $('.auction_close').attr('data-id', id);

        getCusName(groupId); // Fetch customer names based on groupId
    });

// Function to update delete icon to appear only in the last row and in the correct column
function updateDeleteIcon() {
    // Remove any existing delete icons from all rows
    $('#cus_mapping_table tbody tr').each(function () {
        $(this).find('.delete-icon').remove(); // Remove previous delete icon
    });

    // Add delete icon to the last row's value column
    $('#cus_mapping_table tbody tr:last .value-column .input-container').append('<i class="icon-delete delete-icon" style="width:25px;"></i>');
}

// Event handler for adding new rows
$('#submit_cus_map').on('click', function (e) {
    e.preventDefault();

    // Get the selected customers (can be multiple)
    var selectedCustomers = $('#cus_name').val(); // Get the selected customer IDs
    if (selectedCustomers && selectedCustomers.length > 0) {
        // Iterate over each selected customer
        selectedCustomers.forEach(function (cusId) {
            // Get the customer name for the given ID
            var cusName = $('#cus_name option[value="' + cusId + '"]').text().trim(); // Trim to remove extra spaces

            // Create a unique identifier for each entry using timestamp
            var uniqueIdentifier = cusId + '_' + new Date().getTime(); // Using timestamp to ensure uniqueness

            // Append the selected customer to the table as a new row
            $('#cus_mapping_table tbody').append(`
                <tr data-cus-id="${cusId}" data-unique-id="${uniqueIdentifier}">
                    <td>${$('#cus_mapping_table tbody tr').length + 1}</td>
                    <td class="cus-name-column">${cusName}</td>
                    <td class="value-column">
                        <div class="input-container">
                            <input type="number" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value">
                            <i class="icon-delete delete-icon" style="width:25px;"></i> <!-- Add delete icon here -->
                        </div>
                    </td>
                </tr>
            `);
        });

        // Clear the selection
        $('#cus_name').removeClass('active'); // Assuming this is how you clear the selection
        let groupId = $(this).attr('data-group_id');
        getCusName(groupId); // Re-fetch the customer list if needed

        // Show the auction close button if rows exist
        if ($('#cus_mapping_table tbody tr').length > 0) {
            $('.auction_close').removeClass('d-none');
        }

        // Update delete icon to appear only in the last row
        updateDeleteIcon();
    }
});

// Event handler for clicking on customer name
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
            <td class="value-column">
                <div class="input-container">
                    <input type="number" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value">
                    <i class="icon-delete delete-icon" style="width:25px;"></i> <!-- Add delete icon here -->
                </div>
            </td>
        </tr>
    `);

    // Show the auction close button if rows exist
    if ($('#cus_mapping_table tbody tr').length > 0) {
        $('.auction_close').removeClass('d-none');
    }

    // Update delete icon to appear only in the last row
    updateDeleteIcon();
});

// Function to handle the delete icon click event
$(document).on('click', '.icon-delete', function () {
    $(this).closest('tr').remove(); // Remove the row

    // After deletion, update the row numbers
    $('#cus_mapping_table tbody tr').each(function (index) {
        $(this).find('td:first').text(index + 1); // Update row numbers
    });

    // After deletion, ensure the delete icon is updated in the new last row
    updateDeleteIcon();
});


    
    $(document).on('change', '#cus_mapping_table tbody input[type="number"]', function () {
        var inputValue = parseFloat($(this).val());
        var $currentRow = $(this).closest('tr');
        var $prevRow = $currentRow.prev('tr'); // Get the previous row
        var submitBtn = $('#submit_cus_map');
        var lowValue = parseFloat(submitBtn.attr('data-low_value'));
        var highValue = parseFloat(submitBtn.attr('data-high_value'));
    
        // Format lowValue and highValue using moneyFormatIndia
        var formattedLowValue = moneyFormatIndia(lowValue);
        var formattedHighValue = moneyFormatIndia(highValue);
    
        if (inputValue < lowValue || inputValue > highValue) {
            swalError('Warning', `Please enter a value between ${formattedLowValue} and ${formattedHighValue}.`);
            $(this).val(''); // Clear the invalid value
        } else if ($prevRow.length > 0) { // Check if there is a previous row
            var prevValue = parseFloat($prevRow.find('input[type="number"]').val());
            if (inputValue <= prevValue) {
                swalError('Warning', 'The value cannot be smaller than the value in the previous row.');
                $(this).val(''); // Clear the invalid value
            } else {
                $(this).prop('readonly', true); // Make the input readonly if the value is valid
            }
        } else {
            $(this).prop('readonly', true); // No previous row, value is valid, make readonly
        }
    });

    $(document).on('click', '.auction_close', function () {
        let group_id = $(this).attr('data-group_id');
        let date = $(this).attr('data-date');
        let id = $(this).attr('data-id');

        // Collect table data
        let tableData = [];
        let isValid = true; // Flag to track if all fields are valid

        $('#cus_mapping_table tbody tr').each(function () {
            let cusId = $(this).data('cus-id');
            let value = $(this).find('input[name="cus_value[]"]').val();

            // Check if value is empty
            if (!value) {
                isValid = false;
                $(this).find('input[name="cus_value[]"]').css('border', '1px solid red'); // Highlight empty field
            } else {
                $(this).find('input[name="cus_value[]"]').css('border', ''); // Reset border if filled
            }

            // Add the data to the tableData array
            tableData.push({
                cus_id: cusId,
                value: value,
                group_id: group_id,
                date: date,
                id: id
            });
        });

        // If any field is invalid, prevent the submission and show an alert
        if (!isValid) {
            swalError('Error', 'Please fill in all the values');
            return; // Stop further execution if validation fails
        }

        // Send data to server if all fields are valid
        $.ajax({
            url: 'api/auction_files/insert_auction_list.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ data: tableData }),
            success: function (response) {
                if (response.success) {
                    swalSuccess('Success', "Auction Completed successfully");
                    $('#add_cus_map_modal').modal('hide');
                    $('#add_Calculation_modal').modal('show');
                    calculationModal(group_id, date);
                    fetchAuctionDetails(group_id);
                }
            },
        });
    });



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
            swalError('Error', 'Please Select a Date');
            $('#grp_date').css('border-color', 'red'); // Highlight the input field with a red border
            return;
        }

        // Remove the red border if a valid date is selected
        $('#grp_date').css('border-color', '');

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
                swalSuccess('Success', "Date Postponed Successfully");
                $('#add_pos_modal').modal('hide');
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
                    <td>${$('#cal_date').val()}</td>
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

    $('#auction_list_table').on('init.dt', function(){
        checkDashboardData(); //call function after the table loaded.
    });
}

function checkDashboardData(){
    let fromDashboard = localStorage.getItem('dashboardAuc');
    if(fromDashboard){
        let links = document.querySelectorAll('.auctionListBtn');

        links.forEach(link => {
            if(link.getAttribute('data-grpid') == fromDashboard){
                $(link).trigger('click');
            }
        });
    }
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
                    var auctionValue = item.auction_value ? moneyFormatIndia(item.auction_value) : item.auction_value;

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
    $('#grp_date').css('border-color', '');

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
            let roundedAmount = Math.round(response.chit_amount); 
            let formattedAmount = moneyFormatIndia(roundedAmount);
            $('#chit_amount').val(formattedAmount);
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
        url: 'api/auction_files/close_fetch_data.php', // URL to the server-side script that fetches the data
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
            let roundAmount = Math.round(response.chit_amount); 
            let formattAmount = moneyFormatIndia(roundAmount);
            $('#chit_amount').val(formattAmount);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
