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
    })
    $(document).on('click', '#add_pay', function (event) {
        event.preventDefault();
         $('.colls-cntnr,#back_to_coll_list').hide();
        $('.coll_details,#back_to_pay_list').show();
    })
/////////////////////////////////////Document End//////////////////////////////////////////////////////////////////    
})
// function closeChartsModal() {
//     $('#due_chart_model').modal('hide');
//     $('#penalty_model').modal('hide');
//     $('#fine_model').modal('hide');
// }
$(function () {
    getCollectionTable();
});

function getCollectionTable() {
    serverSideTable('#collection_list_table', '', 'api/collection_files/collection_list.php');

}