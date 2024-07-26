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
        $('#cus_id_container').hide();

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
    $('#gua_relationship').change(function () {
        var relationshipType = $(this).val();
        console.log(relationshipType); 
    
        // Hide all fields initially
        $('#name1_container').hide();
        $('#existing_cus_container').hide();
        $('#details_container').hide();
        $('#photo_container').hide();
    
        // Show fields based on relationshipType
        if (relationshipType == 'Customer') {
            $('#existing_cus_container').show();
            $('#photo_container').show();
        } else if (relationshipType == '8') {
            $('#name1_container').show();
            $('#details_container').show();
            $('#photo_container').show();
        } else if (['1', '2', '3', '4', '5', '6', '7'].includes(relationshipType)) {
            $('#name1_container').show();
            $('#photo_container').show();
        } else {
            console.log(relationshipType);
        }
    });
    
    $('#pic').change(function () {
        let pic = $('#pic')[0];
        let img = $('#imgshow');
        img.attr('src', URL.createObjectURL(pic.files[0]));
        checkInputFileSize(this, 200, img)
    })

    $('#gu_pic').change(function () {
        let pic = $('#gu_pic')[0];
        let img = $('#gur_imgshow');
        img.attr('src', URL.createObjectURL(pic.files[0]));
        checkInputFileSize(this, 200, img)
    })
    // Function to format Aadhaar number input
    $('input[data-type="adhaar-number"]').keyup(function () {
        var value = $(this).val();
        value = value.replace(/\D/g, "").split(/(?:([\d]{4}))/g).filter(s => s.length > 0).join(" ");
        $(this).val(value);
    });

    $('input[data-type="adhaar-number"]').change(function () {
        let len = $(this).val().length;
        if (len < 14) {
            $(this).val('');
            swalError('Warning', 'Kindly Enter Valid Aadhaar Number');
        }
    });
    ////////////////////////////////////////////////////////// Place Modal START ///////////////////////////////////////////////////////////////////////
    $('#submit_place').click(function (event) {
        event.preventDefault();
        let place = $('#add_place').val(); let id = $('#add_place_id').val();
        var data = ['add_place']
        var isValid = true;
        data.forEach(function (entry) {
            var fieldIsValid = validateField($('#' + entry).val(), entry);
            if (!fieldIsValid) {
                isValid = false;
            }
        });
        if (place != '') {
            if (isValid) {
                $.post('api/user_creation_files/submit_place.php', { place, id }, function (response) {
                    if (response == '0') {
                        swalError('Warning', 'Place Already Exists!');
                    } else if (response == '1') {
                        swalSuccess('Success', 'Place Updated Successfully!');
                    } else if (response == '2') {
                        swalSuccess('Success', 'Place Added Successfully!');
                    }

                    getPlaceTable();
                }, 'json');
                clearPlace(); //To Clear All Fields in Place creation.
            }
        }
    });

    $(document).on('click', '.placeActionBtn', function () {
        var id = $(this).attr('value'); // Get value attribute
        $.post('api/user_creation_files/get_place_data.php', { id }, function (response) {
            $('#add_place_id').val(id);
            $('#add_place').val(response[0].place);
        }, 'json');
    });

    $(document).on('click', '.placeDeleteBtn', function () {
        var id = $(this).attr('value'); // Get value attribute
        swalConfirm('Delete', 'Do you want to Delete the Place?', deletePlace, id);
        return;
    });
    /////////////////////////////////////////////////////////// Place Modal END ///////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////Source Start/////////////////////////////////////////////////////////////////////////

    $('#add_src').click(function (event) {
        event.preventDefault();
        // Validation
        let cus_id = $('#cus_id').val()
        let occupation = $('#occupation').val();
        let occ_detail = $('#occ_detail').val();
        let source = $('#source').val();
        let income = $('#income').val();
        var data = ['occupation', 'occ_detail', 'source', 'income']

        var isValid = true;
        data.forEach(function (entry) {
            var fieldIsValid = validateField($('#' + entry).val(), entry);
            if (!fieldIsValid) {
                isValid = false;
            }
        });

        if (isValid) {
            $.post('api/customer_creation_files/submit_source_info.php', { cus_id, occupation, occ_detail, source, income}, function (response) {
                getSourceTable(cus_id);
            });
             $('#occupation').val('');
            $('#occ_detail').val('');
            $('#source').val('');
             $('#income').val(''); 
        }
    });


    $(document).on('click', '.sourceDeleteBtn', function () {
        var id = $(this).attr('value'); // Get value attribute
        $.post('api/customer_creation_files/delete_source.php', { id }, function (response) {
            if (response == '0') {
                // swalSuccess('Success', 'Document Need Deleted Successfully.');
                let cus_id = $('#cus_id').val()
                getSourceTable(cus_id);
            } else {
                // swalError('Error', 'Document Need Delete Failed.');
            }
        }, 'json');
    })

    ///////////////////////////////////////////////////////////////////Source End/////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////Family modal Start ///////////////////////////////////////////////////////////////////
    $('#submit_family').click(function (event) {
        event.preventDefault();
        // Validation
        let cus_id = $('#cus_id').val();// Remove spaces from cus_id
        let fam_name = $('#fam_name').val();
        let fam_relationship = $('#fam_relationship').val();
        let fam_age = $('#fam_age').val();
        let fam_live = $('#fam_live').val();
        let fam_occupation = $('#fam_occupation').val();
        let fam_aadhar = $('#fam_aadhar').val().replace(/\s/g, '');
        let fam_mobile = $('#fam_mobile').val();
        let family_id = $('#family_id').val();

        var data = ['fam_name', 'fam_relationship', 'fam_live', 'fam_aadhar', 'fam_mobile']

        var isValid = true;
        data.forEach(function (entry) {
            var fieldIsValid = validateField($('#' + entry).val(), entry);
            if (!fieldIsValid) {
                isValid = false;
            }
        });

        if (isValid) {
            $.post('api/customer_creation_files/submit_family_info.php', { cus_id, fam_name, fam_relationship, fam_age, fam_live, fam_occupation, fam_aadhar, fam_mobile, family_id }, function (response) {
                if (response == '1') {
                    swalSuccess('Success', 'Family Info Added Successfully!');
                } else {
                    swalSuccess('Success', 'Family Info Updated Successfully!');
                }
                // Refresh the family table
                getFamilyTable();
            });
        }
    });
    $(document).on('click', '.familyActionBtn', function () {
        var id = $(this).attr('value'); // Get value attribute
        $.post('api/customer_creation_files/family_creation_data.php', { id: id }, function (response) {
            $('#family_id').val(id);
            $('#fam_name').val(response[0].fam_name);
            $('#fam_relationship').val(response[0].fam_relationship);
            $('#fam_age').val(response[0].fam_age);
            $('#fam_live').val(response[0].fam_live);
            $('#fam_occupation').val(response[0].fam_occupation);
            $('#fam_aadhar').val(response[0].fam_aadhar);
            $('#fam_mobile').val(response[0].fam_mobile);
        }, 'json');
    });

    $(document).on('click', '.familyDeleteBtn', function () {
        var id = $(this).attr('value');
        swalConfirm('Delete', 'Do you want to Delete the Family Details?', getFamilyDelete, id);
        return;
    });
    //////////////////////////////////////////////////////////////////////Family Modal end //////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////Guarantor Modal Start/////////////////////////////////////////////////////////

    $('#submit_guarantor').click(function (event) {
        event.preventDefault();

        // Validation
        let cus_id = $('#cus_id').val();
        let gua_relationship = $('#gua_relationship').val();
        let guarantor_name = $("#guarantor_name").val();
        let existing_cus = $('#existing_cus').val();
        let details = $('#details').val();
        let gu_pic = $('#gu_pic')[0].files[0];
        let gur_pic = $('#gur_pic').val();
        let guarantor_id = $('#guarantor_id').val();

        let guaDetail = new FormData();
        guaDetail.append('cus_id', cus_id);
        guaDetail.append('gua_relationship', gua_relationship);
        guaDetail.append('guarantor_name', guarantor_name);
        guaDetail.append('existing_cus', existing_cus);
        guaDetail.append('details', details);
        guaDetail.append('gu_pic', gu_pic);
        guaDetail.append('gur_pic', gur_pic);
        guaDetail.append('guarantor_id', guarantor_id);

        if (isFormDataValid(guaDetail)) {
            $.ajax({
                url: 'api/customer_creation_files/submit_guarantor.php',
                type: 'post',
                data: guaDetail,
                contentType: false,
                processData: false,
                cache: false,
                success: function (response) {
                    if (response === 'Success') {
                        if (guarantor_id === '') {
                            swalSuccess('Success', 'Guarantor Info Added Successfully!');
                        } else {
                            swalSuccess('Success', 'Guarantor Info Updated Successfully!');
                        }
                    } else {
                        swalError('Error', 'Error in table');
                    }
                    getKycTable();
                }
            });
        }
    });

  












    ///////////////////////////////////////////////////////////////////////Guarantor Modal End////////////////////////////////////////////////////////////
})

$(function () {
    let customer_id = $('#customer_id').val();
    getAutoGenCusId(customer_id);
});

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
function clearPlace() {
    $('#add_place').val('');
    $('#add_place_id').val('0');
    $('#add_place').css('border', '1px solid #cecece');
}

function getPlaceTable() {
    $.post('api/user_creation_files/get_place_list.php', function (response) {
        let placeList = [
            "sno",
            "place",
            "action"
        ]
        appendDataToTable('#place_modal_table', response, placeList);
        setdtable('#place_modal_table');
    }, 'json');
}

function deletePlace(id) {
    $.post('api/user_creation_files/delete_place.php', { id }, function (response) {
        if (response == '1') {
            swalSuccess('Success', 'Place Deleted Successfully.');
            getPlaceTable();
        } else if (response == '0') {
            swalError('Access Denied', 'Used in User Creation');
        } else {
            swalError('Error', 'Place Delete Failed.');
        }
    }, 'json');
}

function getPlaceDropdown(place_name_id) {
    $.post('api/user_creation_files/get_place_list.php', function (response) {
        let appendPlaceNameOption = '';
        appendPlaceNameOption += '<option value="">Select Place</option>';
        $.each(response, function (index, val) {
            let selected = '';
            if (val.id == place_name_id) {
                selected = 'selected';
            }
            appendPlaceNameOption += '<option value="' + val.id + '" ' + selected + '>' + val.place + '</option>';
        });
        $('#place').empty().append(appendPlaceNameOption);

        clearPlace()
    }, 'json');
}
function getFamilyInfoTable() {
    let cus_id = $('#cus_id').val()
    
    $.post('api/customer_creation_files/family_creation_list.php', { cus_id }, function (response) {
        var columnMapping = [
            'sno',
            'fam_name',
            'fam_relationship',
            'fam_age',
            'fam_live',
            'fam_occupation',
            'fam_aadhar',
            'fam_mobile',
        ];
        appendDataToTable('#fam_info_table', response, columnMapping);
        setdtable('#fam_info_table');
    }, 'json')
}

function getFamilyTable() {
    let cus_id = $('#cus_id').val()
    $.post('api/customer_creation_files/family_creation_list.php', { cus_id: cus_id }, function (response) {
        var columnMapping = [
            'sno',
            'fam_name',
            'fam_relationship',
            'fam_age',
            'fam_live',
            'fam_occupation',
            'fam_aadhar',
            'fam_mobile',
            'action'
        ];
        appendDataToTable('#family_creation_table', response, columnMapping);
        setdtable('#family_creation_table');
        $('#family_form input').val('');
        $('#family_form input').css('border', '1px solid #cecece');
        $('#family_form select').css('border', '1px solid #cecece');
        $('#fam_relationship').val('');
        $('#fam_live').val('');
    }, 'json')
}

function getFamilyDelete(id) {
    let cus_id = $('#cus_id').val()
    let customer_id = $('#customer_id').val();
    $.post('api/customer_creation_files/delete_family_creation.php', { id, cus_id, customer_id }, function (response) {
        if (response == '0') {
            swalError('Warning', 'Have to maintain atleast one Family Info');
        } else if (response == '1') {
            swalSuccess('Success', 'Family Info Deleted Successfully!');
            getFamilyTable();
        } else if (response == '2') {
            swalError('Access Denied', 'Family Member Already Used');
        } else {
            swalError('Warning', 'Error occur While Delete Family Info.');
        }
    }, 'json');
}

function getGuarantorRelationship() {
    let cus_id = $('#cus_id').val()
    $.post('api/customer_creation_files/get_guarantor_relationship.php', { cus_id }, function (response) {
        let appendGuarantorOption = '';
        appendGuarantorOption += "<option value=''>Select Guarantor Relationship</option>";
        $.each(response, function (index, val) {
            let selected = '';
            let editGId = $('#guarantor_name_edit').val();
            if (val.id == editGId) {
                selected = 'selected';
            }
            appendGuarantorOption += "<option value='" + val.id + "' " + selected + ">" + val.fam_relationship + "</option>";
        });
        $('#gua_relationship').empty().append(appendGuarantorOption);
    }, 'json');
}
function isFormDataValid(formData) {
    // Reset border styles for all fields
    $('#gua_relationship').css('border', '1px solid #cecece');
    $('#guarantor_name').css('border', '1px solid #cecece');
    $('#existing_cus').css('border', '1px solid #cecece');
    $('#details').css('border', '1px solid #cecece');

    let gua_relationship = formData.get('gua_relationship');

    if (gua_relationship === '') {
        validateField('', 'gua_relationship');
        return false;
    }

    if (gua_relationship === 'Customer') {
        if (!validateField(formData.get('existing_cus'), 'existing_cus')) {
            return false;
        }
    } else if (gua_relationship === 'Other') {
        if (!validateField(formData.get('guarantor_name'), 'guarantor_name') || !validateField(formData.get('details'), 'details')) {
            return false;
        }
    } else {
        if (!validateField(formData.get('guarantor_name'), 'guarantor_name')) {
            return false;
        }
    }

    return true;
}

function getAutoGenCusId(id) {
    $.post('api/customer_creation_files/get_autoGen_cus_id.php', { id }, function (response) {
        $('#cus_id').val(response);
    }, 'json');
}
function getSourceTable(cus_id) {
    $.post('api/customer_creation_files/source_list.php', { cus_id }, function (response) {
        let sourceColumn = [
            "sno",
            "occupation",
            "occ_detail",
            "source",
            "income",
            "action"
        ]
        appendDataToTable('#source_create', response, sourceColumn);
        setdtable('#source_create');
    }, 'json');
}
