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
        var uniqueValue = $(this).data('value');
        var [groupId, date, id] = uniqueValue.split('_'); // Split to get groupId, date, and id
        $('#submit_cus_map').attr('data-groupId', groupId); 
        $('#submit_cus_map').attr('data-id', id); // Store id in data-id attribute
        $('#submit_cus_map').attr('data-date', date); // Store date
        $('.auction_close').attr('data-date', date); // Store date in auction close button
        $('.auction_close').attr('data-id', id); // Store id in auction close button
        getCusName(id); // Fetch customer names based on id
        getCusNameTable(id, date); // Fetch customer name table based on id and date
    });

    $('#submit_cus_map').click(function (event) {
        event.preventDefault();
        let group_id = $(this).attr('data-group-id');
        let date = $(this).attr('data-date');
        let cus_names = $('#cus_name').val();
        if (!Array.isArray(cus_names) || cus_names.length === 0) {
            SwalError('Error', 'Please select at least one customer.');
            return;
        }
        // Send data for each customer name individually
        let requests = cus_names.map(function (cus_name) {
            return $.post('api/auction_files/submit_auction_list.php', {
                cus_name: cus_name,
                group_id: group_id,
                date: date
            }).fail(function () {
                return { status: 'error' }; // Return an error status if the request fails
            });
        });

        $.when.apply($, requests).done(function () {
            let allSuccessful = true;
            requests.forEach(function (request) {
                let response = request[0]; // Extract the response from the request
                if (response && response.status !== 'success') {
                    allSuccessful = false;
                }
            });

            if (allSuccessful) {
                cus_name.removeActiveItems();
                getCusNameTable(group_id, date);
            }
        });
    });

    $('.auction_close').on('click', function () {
        let groupId = $(this).data('group-id');
        let date = $(this).data('date');

        // Step 1: Fetch existing IDs and their values from auction_modal
        $.post('api/auction_files/get_auction_modal_id.php', {
            group_id: groupId,
            date: date
        }, function (response) {
            if (response.status === 'success') {
                let existingValues = response.data;
                let updatedValues = [];

                $('#cus_mapping_table tbody tr').each(function () {
                    let tr = $(this);
                    let cusId = tr.data('cus-id');
                    let value = tr.find('input').val();

                    // Find existing item based on cus_id
                    let existingItem = existingValues.find(item => item.id === cusId);
                    if (existingItem) {
                        // Update existing entry
                        updatedValues.push({ id: existingItem.id, value: value });
                    } else {
                        // Handle as required if no match found
                        updatedValues.push({ id: cusId, value: value });
                    }
                });

                // Ensure there are values to update
                if (updatedValues.length > 0) {
                    // Step 2: Send POST request to close auction
                    $('#add_Calculation_modal').modal('show');
                   // This gets the unique value for fetching data
                    calculationModal(groupId,date)

                    $.post('api/auction_files/submit_auction_close.php', {
                        group_id: groupId,
                        date: date,
                        values: updatedValues
                    }, function (response) {
                        if (response.status === 'success') {
                            swalSuccess('Success', response.message);
                            fetchAuctionDetails(groupId)
                        } else {
                            swalError('Error', response.message);
                        }
                    }, 'json');
                } else {
                    swalError('Error', 'No values to update.');
                }
            } else {
                swalError('Error', response.message);
            }
        }, 'json');
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
                    var rowHtml = `<tr>
                        <td>${index + 1}</td>
                        <td>${row.first_name} ${row.last_name}</td>
                        <td>${row.value}</td>
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
        const content = `
            <div id="print_content" style="text-align: center;">
                <h2 style="margin-bottom: 20px;">Chit Company</h2>
                <img src="img/auction.png" width="50" height="50" style="display: block; margin: 0 auto 20px;">
                <p><strong>Group Name:</strong> ${$('#group_name').val()}</p>
                <p><strong>Auction Month:</strong> ${$('#auction_month').val()}</p>
                <p><strong>Date:</strong> ${$('#cal_date').val()}</p>
                <p><strong>Chit Value:</strong> ${$('#chit_value').val()}</p>
                <p><strong>Auction Value:</strong> ${$('#auction_value').val()}</p>
                <p><strong>Commission:</strong> ${$('#Commission').val()}</p>
                <p><strong>Total Value:</strong> ${$('#total_value').val()}</p>
                <p><strong>Chit Amount:</strong> ${$('#chit_amount').val()}</p>
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
            textAlign: 'center' // Center-aligns the content
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

// function getCusNameTable(groupId, date) {
//     $.post('api/auction_files/get_auction_list.php', { group_id: groupId, date: date }, function(response) {
//         if (response.status === 'success') {
//             let tableBody = $('#cus_mapping_table tbody');
//             tableBody.empty(); // Clear any existing rows

//             $.each(response.data, function(index, row) {
//                 let customerName = `${row.first_name} ${row.last_name}`;
//                 let rowHtml = `<tr data-cus-name="${customerName}">
//                     <td>${index + 1}</td>
//                     <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                     <td><input type="text" class="form-control" name="value_${index}" value=""></td>
//                 </tr>`;
//                 tableBody.append(rowHtml);
//             });

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to customer names
//             tableBody.find('.customer-name').on('click', function() {
//                 let tr = $(this).closest('tr');
//                 let customerName = tr.data('cus-name');
//                 let value = tr.find('input').val();

//                 // Fetch cus_id using customer name
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_name: customerName
//                 }, function(response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     } else {
//                         SwalError('Error', response.message);
//                     }
//                 }, 'json');
//             });
//         } else {
//             SwalError('Error', 'Failed to fetch data.');
//         }
//     }, 'json');
// }

// function insertIntoAuctionDetail(groupId, date, cusId, customerName, value) {
//     $.post('api/auction_files/insert_auction_list.php', {
//         group_id: groupId,
//         date: date,
//         cus_id: cusId,
//         value: value
//     }, function(response) {
//         if (response.status === 'success') {
//             swalSuccess('Success', 'Data inserted into auction details successfully.');

//             // Append the new row to the table
//             let tableBody = $('#cus_mapping_table tbody');
//             let newIndex = tableBody.find('tr').length; // Get the new index
//             let newRowHtml = `<tr data-cus-name="${customerName}">
//                 <td>${newIndex + 1}</td>
//                 <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                 <td><input type="text" class="form-control" name="value_${newIndex}" value="${value}"></td>
//             </tr>`;
//             tableBody.append(newRowHtml);

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to the new customer name
//             tableBody.find('.customer-name:last').on('click', function() {
//                 let tr = $(this).closest('tr');
//                 let customerName = tr.data('cus-name');
//                 let value = tr.find('input').val();

//                 // Fetch cus_id using customer name
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_name: customerName
//                 }, function(response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     } else {
//                         SwalError('Error', response.message);
//                     }
//                 }, 'json');
//             });
//         } else {
//             SwalError('Error', response.message);
//         }
//     }, 'json');
// }
// function getCusNameTable(groupId, date) {
//     $.post('api/auction_files/get_auction_list.php', { group_id: groupId, date: date }, function(response) {
//         if (response.status === 'success') {
//             let tableBody = $('#cus_mapping_table tbody');
//             tableBody.empty(); // Clear any existing rows

//             $.each(response.data, function(index, row) {
//                 let customerName = `${row.first_name} ${row.last_name}`;
//                 let rowHtml = `<tr data-cus-id="${row.id}" data-cus-name="${customerName}">
//                     <td>${index + 1}</td>
//                     <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                     <td><input type="text" class="form-control" name="value_${index}" value=""></td>
//                 </tr>`;
//                 tableBody.append(rowHtml);
//             });

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to customer names
//             tableBody.find('.customer-name').on('click', function() {
//                 let tr = $(this).closest('tr');
//                 let customerId = tr.data('cus-id');
//                 let customerName = tr.data('cus-name');
//                 let value = tr.find('input').val();

//                 // Fetch cus_id using customer ID
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_id: customerId
//                 }, function(response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     }
//                 }, 'json');
//             });
//         } else {
//             SwalError('Error', 'Failed to fetch data.');
//         }
//     }, 'json');
// }
// function insertIntoAuctionDetail(groupId, date, cusId, customerName, value) {
//     $.post('api/auction_files/insert_auction_list.php', {
//         group_id: groupId,
//         date: date,
//         cus_id: cusId,
//         value: value
//     }, function(response) {
//         if (response.status === 'success') {
//             swalSuccess('Success', 'Data inserted into auction details successfully.');

//             // Append the new row to the table
//             let tableBody = $('#cus_mapping_table tbody');
//             let newIndex = tableBody.find('tr').length; // Get the new index
//             let newRowHtml = `<tr data-cus-id="${cusId}" data-cus-name="${customerName}">
//                 <td>${newIndex + 1}</td>
//                 <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                 <td><input type="text" class="form-control" name="value_${newIndex}" value="${value}"></td>
//             </tr>`;
//             tableBody.append(newRowHtml);

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to the new customer name
//             tableBody.find('.customer-name:last').on('click', function() {
//                 let tr = $(this).closest('tr');
//                 let customerId = tr.data('cus-id');
//                 let customerName = tr.data('cus-name');
//                 let value = tr.find('input').val();

//                 // Fetch cus_id using customer ID
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_id: customerId
//                 }, function(response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     } 
//                 }, 'json');
//             });
//         } else {
//             SwalError('Error', response.message);
//         }
//     }, 'json');
// }
// function getCusNameTable(groupId, date) {
//     $.post('api/auction_files/get_auction_list.php', { group_id: groupId, date: date }, function (response) {
//         if (response.status === 'success') {
//             let tableBody = $('#cus_mapping_table tbody');
//             tableBody.empty(); // Clear any existing rows

//             $.each(response.data, function (index, row) {
//                 let customerName = `${row.first_name} ${row.last_name}`;
//                 let rowHtml = `<tr data-cus-id="${row.id}" data-cus-name="${customerName}">
//                     <td>${index + 1}</td>
//                     <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                     <td><input type="text" class="form-control" name="value_${index}" value=""></td>
//                 </tr>`;
//                 tableBody.append(rowHtml);
//             });

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to customer names
//             tableBody.find('.customer-name').on('click', function () {
//                 let tr = $(this).closest('tr');
//                 let customerName = tr.data('cus-name');

//                 // Fetch cus_id using customer name
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_name: customerName // Pass customer_name instead of customer_id
//                 }, function (response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         let value = tr.find('input').val();
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     }
//                 }, 'json');
//             });
//         }
//     }, 'json');
// }

// function insertIntoAuctionDetail(groupId, date, cusId, customerName, value) {
//     $.post('api/auction_files/insert_auction_list.php', {
//         group_id: groupId,
//         date: date,
//         cus_id: cusId,
//         customer_name: customerName, // Pass customer_name if needed
//         value: value
//     }, function (response) {
//         if (response.status === 'success') {
//             // Append the new row to the table
//             let tableBody = $('#cus_mapping_table tbody');
//             let newIndex = tableBody.find('tr').length; // Get the new index
//             let newRowHtml = `<tr data-cus-id="${cusId}" data-cus-name="${customerName}">
//                 <td>${newIndex + 1}</td>
//                 <td class="customer-name" style="cursor: pointer;">${customerName}</td>
//                 <td><input type="text" class="form-control" name="value_${newIndex}" value="${value}"></td>
//             </tr>`;
//             tableBody.append(newRowHtml);

//             tableBody.find('input').on('change', function () {
//                 $(this).prop('readonly', true);
//             });

//             // Add click event to the new customer name
//             tableBody.find('.customer-name:last').on('click', function () {
//                 let tr = $(this).closest('tr');
//                 let customerName = tr.data('cus-name');
//                 let value = tr.find('input').val();

//                 // Fetch cus_id using customer name
//                 $.post('api/auction_files/get_cus_id.php', {
//                     group_id: groupId,
//                     customer_name: customerName // Pass customer_name
//                 }, function (response) {
//                     if (response.status === 'success') {
//                         let cusId = response.cus_id;
//                         insertIntoAuctionDetail(groupId, date, cusId, customerName, value);
//                     }
//                 }, 'json');
//             });
//         }
//     }, 'json');
// }

function getCusNameTable(groupId, date) {
        $.post('api/auction_files/get_auction_list.php', { group_id: groupId, date: date }, function (response) {
            if (response.status === 'success') {
                let tableBody = $('#cus_mapping_table tbody');
                tableBody.empty(); // Clear any existing rows
    
                $.each(response.data, function (index, row) {
                    let customerName = `${row.first_name} ${row.last_name}`;
                    let rowHtml = `<tr data-cus-id="${row.id}" data-cus-name="${customerName}">
                        <td>${index + 1}</td>
                        <td class="customer-name" style="cursor: pointer;">${customerName}</td>
                        <td><input type="text" class="form-control" name="value_${index}" value=""></td>
                    </tr>`;
                    tableBody.append(rowHtml);
                });
    
                tableBody.find('input').on('change', function () {
                    $(this).prop('readonly', true);
                });
    
                // Add click event to customer names
                tableBody.find('.customer-name').on('click', function () {
                    let tr = $(this).closest('tr');
                    let customerName = tr.data('cus-name');

                            let value = tr.find('input').val();
                            insertIntoAuctionDetail(id);
                    
                });
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
function calculation(uniqueValue) {
    // Split the uniqueValue into parts
    const [group_id, date] = uniqueValue.split('_');
    
    $.ajax({
        url: 'api/auction_files/fetch_calculation_data.php', // URL to the server-side script that fetches the data
        type: 'POST',
        data: {
            group_id: group_id, // Send the group_id part
            date: date // Send the date part
        },
        dataType: 'json',
        success: function (response) {
            // Assuming the server returns a JSON object with the relevant data
            $('#group_name').val(response.group_name);
            $('#auction_month').val(response.auction_month);
            $('#cal_date').val(response.cal_date);
            $('#chit_value').val(response.chit_value);
            $('#auction_value').val(response.auction_value);
            $('#Commission').val(response.commission);
            $('#total_value').val(response.total_value);
            $('#chit_amount').val(response.chit_amount);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
function calculationModal(groupId,date) {
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
            $('#cal_date').val(response.cal_date);
            $('#chit_value').val(response.chit_value);
            $('#auction_value').val(response.auction_value);
            $('#Commission').val(response.commission);
            $('#total_value').val(response.total_value);
            $('#chit_amount').val(response.chit_amount);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}
