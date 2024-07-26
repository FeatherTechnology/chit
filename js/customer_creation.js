$(document).ready(function () {
    $(document).on('click', '#add_customer, #back_btn', function () {
        swapTableAndCreation();
    });
    $('#reference_type').change(function () {
        var referenceType = $(this).val();
        // Hide all fields initially
        $('#cus_name_container').hide();
        $('#name_container').hide();
        $('#mobile_container').hide();
        $('#declaration_container').hide();

        if (referenceType == '1') {
            // Promotion selected
            $('#declaration_container').show();
        } else if (referenceType == '2') {
            // Customer selected
            $('#cus_name_container').show();
            $('#cus_id_container').show();
            $('#declaration_container').show();
        } else if (referenceType == '3') {
            // Well Known Person selected
            $('#name_container').show();
            $('#mobile_container').show();
            $('#declaration_container').show();
        }
    });
})

function swapTableAndCreation() {
    if ($('.customer_table_content').is(':visible')) {
        $('.customer_table_content').hide();
        $('#add_customer').hide();
        $('#customer_creation_content').show();
        $('#back_btn').show();

    } else {
        $('.customer_table_content').show();
        $('#add_customer').show();
        $('#customer_creation_content').hide();
        $('#back_btn').hide();
    }
}