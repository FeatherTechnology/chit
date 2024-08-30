$(document).ready(function () {
    //Add Group creation & Back.
    $(document).on('click', '#back_btn', function () {
    });

    $('input[name=customer_data_type]').click(function () {
        let customerType = $(this).val();
        if (customerType == 'cus_profile') {
            $('.group_table_content').show();

        } else if (customerType == 'cus_summary') {
            $('.group_table_content').show();
        }
    })

    $(document).on('click', '#group_current', function () {
          $('.group_table_content').show(); 
          getGroupCreationTable();
    })
    $(document).on('click', '#group_closed', function () {
        $('.group_table_content').show();
        getGroupClosedCreationTable();
    })

/////////////////////////////////////////////////////////////////////Auction Chart Start////////////////////////////////////////////////////////////
$(document).on('click', '.auction_chart', function (event) {
    event.preventDefault();
    $('#auction_chart_model').modal('show');
    let dataValue = $(this).data('value');
    let dataParts = dataValue.split('_');
    let groupId = dataParts[0];
    getAuctionChart(groupId)
});
//////////////////////////////////////////////////////////////////////Auction chart End/////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////settlement chart Start////////////////////////////////
$(document).on('click', '.settle_chart', function (event) {
    event.preventDefault();
    $('#settlement_chart_model').modal('show');
    let dataValue = $(this).data('value');
    let dataParts = dataValue.split('_');
    let groupId = dataParts[0];
    getSettleChart(groupId)

});
//////////////////////////////////////////////////////////////////////////////settlement Chart End//////////////////////////////////////////////////////////////////
});
function closeChartsModal() {
    $('#auction_chart_model').modal('hide');
    $('#settlement_chart_model').modal('hide');
}
$(function () {
    getGroupCreationTable();
});

function getGroupCreationTable() {
    serverSideTable('#group_creation_table', '', 'api/group_summary_files/group_summary_list.php');
}
function getGroupClosedCreationTable() {
    serverSideTable('#group_creation_table', '', 'api/group_summary_files/group_closed_summary_list.php');
}
function getAuctionChart(groupId) {
    $.ajax({
        url: 'api/group_summary_files/auction_chart_data.php',
        type: 'POST',
        dataType: 'json',
        data: {
            group_id: groupId,
        },
        success: function (response) {
            var tbody = $('#auction_chart_table tbody');
            tbody.empty(); // Clear existing rows

            var hasRows = false;

            $.each(response, function (index, item) {
                var auctionMonth = item.auction_month;
                var auctionDate = item.auction_date;

                // Format the values using moneyFormatIndia
                var chitAmount = item.chit_amount ? moneyFormatIndia(Math.round(item.chit_amount)) : '';
                var auction_value = item.auction_value ? moneyFormatIndia(item.auction_value) : '';
                var commission = item.commission ? moneyFormatIndia(Math.round(item.commission)): ''; // Corrected typo
                var total_value = item.total_value ? moneyFormatIndia(item.total_value) : '';
                
                var row = '<tr>' +
                    '<td>' + auctionMonth + '</td>' +
                    '<td>' + auctionDate + '</td>' +
                    '<td>' + auction_value + '</td>' +
                    '<td>' + commission + '</td>' +
                    '<td>' + total_value + '</td>' +
                    '<td>' + chitAmount + '</td>' +
                    '</tr>';

                tbody.append(row);
                hasRows = true;
            });

            if (!hasRows) {
                tbody.append('<tr><td colspan="7">No data available</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + error);
        }
    });
}
function getAuctionChart(groupId) {
    $.ajax({
        url: 'api/group_summary_files/settlement_chart_data.php',
        type: 'POST',
        dataType: 'json',
        data: {
            group_id: groupId,
        },
        success: function (response) {
            var tbody = $('#auction_chart_table tbody');
            tbody.empty(); // Clear existing rows

            var hasRows = false;

            $.each(response, function (index, item) {
                var auctionMonth = item.auction_month;
                var settle_amount = item.settle_amount ? moneyFormatIndia(item.settle_amount) : '';
                
                var row = '<tr>' +
                    '<td>' + auctionMonth + '</td>' +
                    '<td>' + group_id + '</td>' +
                    '<td>' + group_name + '</td>' +
                    '<td>' + cus_id + '</td>' +
                    '<td>' + cus_name + '</td>' +
                    '<td>' + settle_date + '</td>' +
                    '<td>' + settle_amount + '</td>' +
                    '</tr>';

                tbody.append(row);
                hasRows = true;
            });

            if (!hasRows) {
                tbody.append('<tr><td colspan="7">No data available</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + error);
        }
    });
}
