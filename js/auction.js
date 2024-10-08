const cus_name = new Choices('#cus_name', {
    removeItemButton: true,
    noChoicesText: 'Select Customer Name',
    allowHTML: true,
    shouldSort: false // Disable default sorting
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
        $('#submit_cus_map').prop('disabled', false);
        $('#pageHeaderName').text(` - Auction`);

        localStorage.setItem('dashboardAuc','');
    });
    $(document).on('click', '.today', function () {
        getAuctionTodayTable()
    });
    $(document).on('click', '.this_month', function () {
        getAuctionMonthTable()
    });
    $(document).on('click', '#back_to_list', function (event) {
        event.preventDefault();
    
        // Hide back button
        $('#back_to_list').hide(); 
    
        // Show auction details content and back button
        $('.auction_detail_content, .back_btn').show();
    
        // Hide the modal
        $('#add_cus_map_modal').hide();
    
        // Fetch the values from the input fields
        var groupId = $('#group_id').val(); // Get the Group ID
        var groupName = $('#grp_name').val(); // Get the Group Name
        var chitValue = $('#ch_value').val(); // Get the Chit Value (make sure this input exists)
        const formattedChitValue = moneyFormatIndia(chitValue.toString());
        // Update the page header with the fetched values
        $('#pageHeaderName').text(` - Auction - Group ID: ${groupId}, Group Name: ${groupName}, Chit Value: ${formattedChitValue}`);
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
        $('#back_to_list').hide(); 
        $('.today').hide();
        $('.this_month').hide();
        $('#add_cus_map_modal').hide();
        //  getModalAttr();
        // Fetch auction details based on the selected group ID
        fetchAuctionDetails(groupId);
    });
    

    ///////////////////////////////////////////////////////Auction Modal Start/////////////////////////////////////////////////////////////////  
    $(document).on('click', '.auctionBtn', function (event) {
        event.preventDefault();
    
        var uniqueMonth = $(this).data('value');
        var [groupId, auction_month] = uniqueMonth.split('_');
    
        // Fetch the auction details and validate date and time
        $.post('api/auction_files/validate_auction_date.php', { group_id: groupId, auction_month: auction_month }, function (response) {
            if (response.is_valid) {
                // If date and time match, open modal
                $('#pageHeaderName').text(` - Auction`);
                $('#add_cus_map_modal').show();
                $('.auction_detail_content').hide();
                $('#back_to_list').show(); 
                $('.back_btn').hide(); 
    
                var auctionDetail = response.auction_detail; // assuming auction details are returned
                var [date, id, low_value, high_value, group_name,chit_value] = [
                    auctionDetail.date,
                    auctionDetail.id,
                    auctionDetail.low_value,
                    auctionDetail.high_value,
                    auctionDetail.group_name,
                    auctionDetail.chit_value 
                ];
    
                // Convert date to dd-mm-yyyy format
                var formattedDate = formatDate(date); // Call the function to format the date
    
                // Set attributes for submission
                $('#submit_cus_map').attr('data-group_id', groupId);
                $('#submit_cus_map').attr('data-id', id);
                $('#submit_cus_map').attr('data-date', formattedDate); // Use formatted date
                $('#submit_cus_map').attr('data-high_value', high_value);
                $('#submit_cus_map').attr('data-low_value', low_value);
    
                // Set the values in the modal form fields
                $('#group_id').val(groupId); // Assuming you want to display the group ID
                $('#grp_name').val(group_name); // Populate group name
                $('#ch_value').val(chit_value); // Populate group name
                $('#auction_date').val(formattedDate); // Populate auction date in dd-mm-yyyy format
                $('#grp_month').val(auction_month); // Populate auction month
                var formattedLowValue = moneyFormatIndia(low_value);
                var formattedHighValue = moneyFormatIndia(high_value);
                $('#low_value').val(formattedLowValue); // Populate low value
                $('#high_value').val(formattedHighValue); // Populate high value
    
                // Set attributes for auction close button
                $('.auction_close').attr('data-group_id', groupId);
                $('.auction_close').attr('data-date', formattedDate); // Use formatted date
                $('.auction_close').attr('data-id', id);
    
                // Fetch customer names based on groupId
                getCusName(groupId, auction_month);
            } else {
                // Show warning message if validation fails
                // Convert date to dd-mm-yyyy format for display in warning
                var auctionDate = response.auction_detail ? response.auction_detail.date : 'N/A';
                var formattedAuctionDate = formatDate(auctionDate); // Format the auction date
                var auctionTime = response.auction_detail ? `${response.auction_detail.hours}:${response.auction_detail.minutes} ${response.auction_detail.ampm}` : 'N/A';
    
                swalError('Warning', `The scheduled auction date (${formattedAuctionDate}) and time (${auctionTime}) for this month have not yet arrived. Please wait.`);
            }
        }, 'json');
    
        // Function to format date to dd-mm-yyyy
        function formatDate(dateString) {
            if (!dateString) return 'N/A'; // Handle cases where date is undefined or null
            var dateParts = dateString.split('-'); // Assuming input format is 'yyyy-mm-dd'
            return `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`; // Return as 'dd-mm-yyyy'
        }
    });
    
function updateDeleteIcon() {
    // Remove any existing delete icons from all input containers
    $('#cus_mapping_table tbody tr').each(function () {
        // Remove previous delete icons from each row
        $(this).find('.delete-icon').remove(); // Remove any existing delete icons

        // Check if the row is a company row
        if (!$(this).hasClass('company-row')) {
            // Add a delete icon to the last input container if it's not a company row
            $(this).find('.value-column .input-container:last').append('<i class="icon-delete delete-icon" style="width:25px;"></i>');
        }
    });
}


$('#submit_cus_map').on('click', function (e) {
    e.preventDefault();

    // Get the selected customers (can be multiple)
    var selectedCustomers = $('#cus_name').val(); // Get the selected customer IDs
    var lowValue = $('#submit_cus_map').attr('data-low_value'); // Get the low_value

    // Check if "Company" already exists in the table
    var companyExists = $('#cus_mapping_table tbody .company-row').length > 0;

    if (selectedCustomers && selectedCustomers.length > 0) {
        // Flag to track if the "Company" entry has been added
        var companyAdded = false;

        // Iterate over each selected customer
        selectedCustomers.forEach(function (cusId) {
            // Handle special case for the company
            if (cusId == -1 && !companyExists) {
                // Create a unique identifier for the Company entry
                var uniqueIdentifier = 'company_' + new Date().getTime();

                // Append the "Company" entry to the table (without delete icon)
                $('#cus_mapping_table tbody').append(`
                    <tr data-cus-id="${cusId}" data-unique-id="${uniqueIdentifier}" class="company-row">
                        <td>${$('#cus_mapping_table tbody tr').length + 1}</td>
                        <td class="cus-name-column">Company</td>
                        <td class="value-column">
                            <div class="input-container">
                                <input type="number" name="cus_value[]" class="form-control" value="${lowValue}" placeholder="Enter value" readonly>
                                <!-- No delete icon for Company row -->
                            </div>
                        </td>
                    </tr>
                `);
                companyAdded = true; // Mark that the company has been added
                $('#submit_cus_map').prop('disabled', true);
            } else if (cusId != -1) {
                // Get the customer name for the given ID
                var cusName = $('#cus_name option[value="' + cusId + '"]').text().trim();

                // Create a unique identifier for each entry using timestamp
                var uniqueIdentifier = cusId + '_' + new Date().getTime();

                // Append the selected customer to the table as a new row (with delete icon)
                $('#cus_mapping_table tbody').append(`
                    <tr data-cus-id="${cusId}" data-unique-id="${uniqueIdentifier}">
                        <td>${$('#cus_mapping_table tbody tr').length + 1}</td>
                        <td class="cus-name-column">${cusName}</td>
                        <td class="value-column">
                            <div class="input-container">
                                <input type="number" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value">
                                <i class="icon-delete delete-icon" style="width:25px;"></i> <!-- Show delete icon -->
                            </div>
                        </td>
                    </tr>
                `);
            }
        });

        // Clear the selection
        cus_name.removeActiveItems();
        let groupId = $(this).attr('data-group_id');
        getCusName(groupId);

        // Show the auction close button if rows exist
        if ($('#cus_mapping_table tbody tr').length > 0) {
            $('.auction_close').removeClass('d-none');
        }

        // Update delete icon to appear only in the last input of each row
        updateDeleteIcon();
    }
});

$(document).on('click', '#cus_mapping_table tbody tr .cus-name-column', function () {
    var $row = $(this).closest('tr');
    var cusId = $row.data('cus-id');

    // Check if the clicked row is the "Company" row
    if (cusId == -1) {
        // Prevent adding a duplicate company entry
        return; // Exit the function
    }

    // Find the value column in the current row
    var $valueColumn = $row.find('.value-column');

    // Generate a unique ID for the new input
    var uniqueIdentifier = cusId + '_' + new Date().getTime();

    // Append a new input field after the existing one
    $valueColumn.append(`
        <div class="input-container mt-2"> <!-- Add margin for better spacing -->
            <input type="number" name="cus_value[]" class="form-control" data-cus-id="${cusId}" value="" placeholder="Enter value">
            <i class="icon-delete delete-icon" style="width:25px;"></i>
        </div>
    `);

    // Show the auction close button if rows exist
    if ($('#cus_mapping_table tbody tr').length > 0) {
        $('.auction_close').removeClass('d-none');
    }

    // Update delete icon to appear on the last input container of each row
    updateDeleteIcon();
});



$(document).on('click', '.icon-delete', function () {
    var $inputContainer = $(this).closest('.input-container'); // Find the closest input container
    var $row = $(this).closest('tr'); // Find the row for the clicked delete icon

    // Remove the specific input container
    $inputContainer.remove();

    // Check if the row has any input containers left
    if ($row.find('.input-container').length === 0) {
        // If no input containers are left, remove the entire row
        $row.remove();
     
    }

    // After deletion, update the row numbers for all rows
    $('#cus_mapping_table tbody tr').each(function (index) {
        $(this).find('td:first').text(index + 1); // Update row numbers
    });
    if ($('#cus_mapping_table tbody tr').length > 0) {
        $('.auction_close').removeClass('d-none'); // Show button if rows exist
    } else {
        $('.auction_close').addClass('d-none'); // Hide button if no rows
    }
    // After deletion, ensure the delete icon is updated in the new last input container of each row
    updateDeleteIcon();
});

    
    //Event handler for validating the input value
    $(document).on('change', '#cus_mapping_table tbody input[type="number"]', function () {
        var inputValue = parseFloat($(this).val());
        var submitBtn = $('#submit_cus_map');
        var lowValue = parseFloat(submitBtn.attr('data-low_value'));
        var highValue = parseFloat(submitBtn.attr('data-high_value')); // Use a high number if highValue is NaN
    
        // Format lowValue and highValue using moneyFormatIndia
        var formattedLowValue = moneyFormatIndia(lowValue);
        var formattedHighValue = moneyFormatIndia(highValue);
    
        // Check if the input value is within the specified range
        if (inputValue < lowValue || inputValue > highValue) {
            swalError('Warning', `Please enter a value between ${formattedLowValue} and ${formattedHighValue}.`);
            $(this).val(''); // Clear the invalid value
            return;
        }
    
        var isValid = true;
        var isUnique = true; // Flag for uniqueness check
        var hasPreviousValues = false; // Flag to check if there are previous values
    
        // Validate against all previous input values in the table
        $('#cus_mapping_table tbody input[type="number"]').each(function () {
            var prevValue = parseFloat($(this).val());
    
            // Skip the current input field value from comparison
            if (this !== event.target && !isNaN(prevValue)) {
                hasPreviousValues = true; // Set flag since we found a previous value
    
                // Check for uniqueness
                if (inputValue === prevValue) {
                    isUnique = false; // Found a duplicate value
                }
    
                // Check if the current input value is less than or equal to any previous value
                if (inputValue <= prevValue) {
                    isValid = false; // Input is not valid
                }
            }
        });
    
        // Show warning if the value must be greater than all previous values (only if previous values exist)
        if (hasPreviousValues && !isValid) {
            swalError('Warning', 'The value must be greater than all previous values in the table.');
            $(this).val(''); // Clear the invalid value
            return;
        }
    
        // Show warning if the value is not unique
        if (!isUnique) {
            swalError('Warning', 'The value must be unique across all input fields in the table.');
            $(this).val(''); // Clear the invalid value
            return;
        }
    
        // If all validations pass, make the input readonly
        $(this).prop('readonly', true);
    });
    
 
    $(document).on('click', '.auction_close', function (e) {
        e.preventDefault();
        let group_id = $(this).attr('data-group_id');
        let date = $(this).attr('data-date');
        let id = $(this).attr('data-id');
    
        // Collect table data
        let tableData = [];
        let isValid = true; // Flag to track if all fields are valid
        let overallMaxValue = -Infinity; // Initialize to the lowest possible value
    
        $('#cus_mapping_table tbody tr').each(function () {
            let cusId = $(this).data('cus-id');
            let values = $(this).find('input[name="cus_value[]"]').map(function () {
                return $(this).val(); // Collect all values for this customer
            }).get(); // Get values as an array
    
            // Check if values are valid
            let validValues = values.filter(function (value) {
                return value !== ''; // Filter out empty values
            });
    
            if (validValues.length === 0) {
                isValid = false;
                $(this).find('input[name="cus_value[]"]').css('border', '1px solid red'); // Highlight empty field
            } else {
                $(this).find('input[name="cus_value[]"]').css('border', ''); // Reset border if filled
            }
    
            // If there are valid values for the customer, process them
            if (validValues.length > 0) {
                // Convert valid values to numbers and push each one as a separate entry
                validValues.map(Number).forEach(value => {
                    tableData.push({
                        cus_id: cusId, // Customer ID
                        value: value, // Individual value
                        group_id: group_id,
                        date: date,
                        id: id
                    });
    
                    // Update overall maximum value
                    overallMaxValue = Math.max(overallMaxValue, value); // Update the overall max
                });
            }
        });
    
        // If any field is invalid, prevent the submission and show an alert
        if (!isValid) {
            swalError('Error', 'Please fill in all the values');
            return; // Stop further execution if validation fails
        }
    
        // Use the swalConfirm function to show confirmation alert
        swalConfirm(
            'Do you want to close the auction?', 
            `The Final value will be ${overallMaxValue}`, // Use overallMaxValue here
            closeAuction, 
            { group_id: group_id, date: date, id: id, tableData: tableData }
        );
    });
    
    // The closeAuction function remains the same
    function closeAuction(data) {
        $.ajax({
            url: 'api/auction_files/insert_auction_list.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify({ data: data.tableData }), // Send all entries for each customer
            success: function (response) {
                if (response.success) {
                    swalSuccess('Success', "Auction Completed successfully");
                    $('#add_cus_map_modal').hide();
                    $('#add_Calculation_modal').modal('show');
                    calculationModal(data.group_id, data.date);
                    fetchAuctionDetails(data.group_id);
                }
            },
        });
    }
    
    

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
    
                // Find the maximum value
                var maxValue = Math.max(...data.map(row => parseFloat(row.value)));
    
                // Populate table with data
                $.each(data, function (index, row) {
                    var formattedValue = moneyFormatIndia(row.value);
                    var isMaxValue = parseFloat(row.value) === maxValue; // Check if current row has the max value
    
                    // Add a class 'highlight-row' if it's the highest value
                    var rowHtml = `<tr class="${isMaxValue ? 'highlight-row' : ''}">
                        <td>${index + 1}</td>
                        <td>${row.customer_name}</td>  <!-- Use customer_name -->
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
    let params = { 'type': '' };
    serverSideTable('#auction_list_table', params, 'api/auction_files/auction_list.php');

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
    let params = { 'type': 'month' };
    serverSideTable('#auction_list_table', params, 'api/auction_files/auction_list.php');
}
function getAuctionTodayTable() {
    let params = { 'type': 'today' };
    serverSideTable('#auction_list_table', params, 'api/auction_files/auction_list.php');
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
                    var auctionValue = item.auction_value ? moneyFormatIndia(item.auction_value) : ''; // Show '—' if null

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
                $('#add_cus_map_modal').hide(); // Show the card after data is loaded

            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + error);
        }
    });
}


function closeChartsModal() {
  //  $('#add_cus_map_modal').modal('hide');
    //     cus_name.removeActiveItems();
    //   $('#cus_name').closest('.choices').find('.choices__inner').css('border', '1px solid #cecece');
    $('#grp_date').css('border-color', '');

    $('#add_pos_modal').modal('hide');
    $('#add_view_modal').modal('hide');
    $('#add_Calculation_modal').modal('hide');
}


function getCusName(groupId, auction_month) {
    $.post('api/auction_files/get_customerName_list.php', {
        group_id: groupId,
        auction_month: auction_month
    }, function (response) {
        cus_name.clearStore();

        // Initialize an array with the "Company" option
        let items = [{
            value: -1,
            label: 'Company',
            selected: false
        }];

        // Map the response to the desired format
        response.forEach(function (val) {
            items.push({
                value: val.id,
                label: val.cus_name,
                selected: false
            });
        });

        // Sort the items alphabetically, excluding the first item (Company)
        const companyOption = items.shift(); // Remove Company option
        items.sort((a, b) => a.label.localeCompare(b.label)); // Sort alphabetically
        items.unshift(companyOption); // Add Company back at the top

        // Set the choices with the new items array
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
