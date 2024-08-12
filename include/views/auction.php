<div class="col-12  text-center">
    <button class="btn btn-primary mx-4  today text-center">&nbsp;Today</button>
    <button class="btn btn-primary mx-4  this_month text-center">&nbsp;This Month</button>
</div>
<div class="col-12 text-right">
    <button class="btn btn-primary back_btn" style="display: none;"><span class="icon-arrow-left"></span>&nbsp;Back</button>
</div></br>
<!----------------------------- CARD START  Auction TABLE ------------------------------>
<div class="card auction_table_content">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <table id="auction_list_table" class="table custom-table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Chit Value</th>
                            <th>Total Month</th>
                            <th>Date</th>
                            <th>Auction Month</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!----------------------------- CARD END-Auction TABLE ------------------------------>
<!----------------------------- CARD Start-Auction  Detail TABLE ------------------------------>
<div class="card auction_detail_content" style="display: none;">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <table id="auction_table" class="table custom-table">
                    <thead>
                        <tr>
                            <th>Auction Month</th>
                            <th>Date</th>
                            <th>Low Value</th>
                            <th>High Value</th>
                            <th>Customer</th>
                            <th>Auction Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-----------------------------CARD END - Auction Detail TABLE --------------------------------->
<!--------------------------------------Auction Modal Start------------------------------------------->
<div class="modal fade" id="add_cus_map_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Auction</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeChartsModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="mapping_form">
                        <div class="row justify-content-center">
                            <input type="hidden" name="mapp_id" id="mapp_id">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="cus_name">Customer Name<span class="text-danger">*</span></label>
                                    <input type="hidden" id="customer_edit_it">
                                    <select class="form-control" id="cus_name" name="cus_name" tabindex="17" multiple>
                                        <option value="">Select Customer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12 align-self-end">
                                <div class="form-group">
                                    <button name="submit_cus_map" id="submit_cus_map" class="btn btn-primary" tabindex="3" style="margin-top: 18px;">
                                        <span class="icon-check"></span>&nbsp;Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table id="cus_mapping_table" class="table custom-table">
                            <thead>
                                <tr>
                                    <th width="20">S.No.</th>
                                    <th>Customer Name</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12 text-right">
                    <button class="btn btn-primary auction_close">&nbsp; Auction Close</button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="closeChartsModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<!--- --------------------------------------Auction Modal END ------------------------------- -->
<!------------------------------------------Postpone Modal start---------------------------------->
<div class="modal fade" id="add_pos_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Postpone</h5>
                <button type="button" class="close" data-dismiss="modal" tabindex="1" onclick="closeChartsModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <form id="mapping_form">
                        <div class="row justify-content-center">
                            <input type="hidden" name="mapping_id" id="mapping_id">
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="grp_date">Date</label><span class="text-danger">*</span>
                                    <input type="hidden" id="date_name_edit">
                                    <select class="form-control" id="grp_date" name="grp_date" tabindex="3">
                                        <option value="">Select Date</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12 align-self-end">
                                <div class="form-group">
                                    <button name="submit_postpone" id="submit_postpone" class="btn btn-primary" tabindex="3" style="margin-top: 18px;">&nbsp;Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" tabindex="1" onclick="closeChartsModal()">Close</button>
            </div>
        </div>
    </div>
</div>
<!------------------------------------------PostPone Modal End------------------------------------>

<!------------------------------------------View Modal Start--------------------------------------------------------------------------------------->
<div class="modal fade" id="add_view_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">View</h5>
                <button type="button" class="close" data-dismiss="modal" tabindex="1" onclick="closeChartsModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <table id="view_table" class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Customer Name</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody> </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="closeChartsModal()" tabindex="1">Close</button>
            </div>
        </div>
    </div>
</div>
<!---------------------------------------------------------View Modal End----------------------------------------------------------------------------------------->
<!---------------------------------------------------------CalCulation Modal Start--------------------------------------------------------------------------->
<!--Bank Info Modal Start-->
<div class="modal fade" id="add_Calculation_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Calculation</h5>
                <button type="button" class="close" data-dismiss="modal" onclick="closeChartsModal()" tabindex="1" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <button name="print_cal" id="print_cal" class="btn btn-primary" tabindex="1" style="margin-left: 650px;">&nbsp;Download</button>
                    <form id="Calculation_form">
                        <div class="row">
                            <input type="hidden" name="bank_id" id='bank_id'>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="group_name">Group Name</label>
                                    <input class="form-control" name="group_name" id="group_name" tabindex="1" placeholder="Enter Group Name" readonly>
                                    <input type="hidden" id="addgrp_name_id" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="auction_month">Auction Month</label>
                                    <input class="form-control" name="auction_month" id="auction_month" tabindex="1" placeholder="Enter Auction Month" readonly>
                                    <input type="hidden" id="addauction_id" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="cal_date">Date</label>
                                    <input class="form-control" name="cal_date" id="cal_date" tabindex="1" readonly>
                                    <input type="hidden" id="addcal_date" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="chit_value">Chit Value</label>
                                    <input class="form-control" name="chit_value" id="chit_value" tabindex="1" readonly>
                                    <input type="hidden" id="addchit_value" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="auction_value">Auction Value</label>
                                    <input class="form-control" name="auction_value" id="auction_value" tabindex="1" disabled>
                                    <input type="hidden" id="addvalue_id" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="Commission">Commission</label>
                                    <input class="form-control" name="Commission" id="Commission" tabindex="1" disabled>
                                    <input type="hidden" id="addcomm_id" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="total_value">Total Value</label>
                                    <input class="form-control" name="total_value" id="total_value" tabindex="1" disabled>
                                    <input type="hidden" id="addtot_id" value='0'>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label for="chit_amount">Chit Amount</label>
                                    <input class="form-control" name="chit_amount" id="chit_amount" tabindex="1" disabled>
                                    <input type="hidden" id="addchit_id" value='0'>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="closeChartsModal()" tabindex="1">Close</button>
            </div>
        </div>
    </div>
</div>
<!--Bank Info Modal End-->
<!------------------------------------------------------------Calculation Modal End------------------------------------------------------------------------>