<div class="col-12 text-right">
    <button class="btn btn-primary back_btn" style="display: none;"><span class="icon-arrow-left"></span>&nbsp;Back</button>
</div></br>
<!----------------------------- CARD START  Settlement TABLE ------------------------------>
<div class="card settlement_table_content">
    <div class="card-header">
        <div class="card-title">Settlement List</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <table id="settlement_list_table" class="table custom-table">
                    <thead>
                        <tr>
                            <th>S.No.</th>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Chit Value</th>
                            <th>Total Members</th>
                            <th>Total Month</th>
                            <th>Auction Month</th>
                            <th>Customer</th>
                            <th>Auction Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!----------------------------- CARD END-Settlement TABLE ------------------------------>
<div id="settlement_content"><!--- style="display: none;"-->
    <form id="settlement_screen" name="settlement_screen" method="post" enctype="multipart/form-data">
        <input type="hidden" id="groupid">
        <!-- Row start -->
        <div class="row gutters">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Group Info</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fields -->
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="group_id">Group ID</label>
                                    <input type="text" class="form-control" id="group_id" name="group_id" readonly placeholder="Enter Group ID" tabindex="1" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="group_name">Group Name</label>
                                    <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Enter Group Name" tabindex="2" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="chit_value">Chit Value</label>
                                    <input type="number" class="form-control" id="chit_value" name="chit_value" placeholder="Enter Chit Value" tabindex="3" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="commission">Commission</label>
                                    <input type="number" class="form-control" id="commission" name="commission" placeholder="Enter Commission" tabindex="4" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="total_members">Total Members</label>
                                    <input type="number" class="form-control" id="total_members" name="total_members" placeholder="Enter Total Members" tabindex="5" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="total_month">Total Month</label>
                                    <input type="number" class="form-control" id="total_month" name="total_month" placeholder="Enter Total Month" tabindex="6" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="start_month">Start Month</label>
                                    <input type="month" class="form-control" id="start_month" name="start_month" placeholder="Enter Start Month" tabindex="7" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="end_month">End Month</label>
                                    <input type="month" class="form-control" id="end_month" name="end_month" placeholder="Enter End Month" tabindex="8" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-------------------------------------------------Group card------------------------------------>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Customer Info</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="cus_id"> Customer ID</label>
                                            <input type="text" class="form-control " id="cus_id" name="cus_id" readonly tabindex="9">
                                            <input type="hidden" id="cus_id_upd" name="cus_id_upd">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="customer_name">Customer Name</label>
                                            <input type="text" class="form-control " id="customer_name" name="customer_name" readonly tabindex="10">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="place">Place</label>
                                            <input type="text" class="form-control " id="place" name="place" readonly tabindex="11">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="mobile1">Mobile</label>
                                            <input type="number" class="form-control" id="mobile1" name="mobile1" readonly onKeyPress="if(this.value.length==10) return false;" tabindex="12">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="occupation">Occupation</label>
                                            <input type="text" class="form-control " id="occupation" name="occupation" readonly tabindex="13">
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="referred_by"> Referred By </label>
                                            <input type="text" class="form-control " id="referred_by" name="referred_by" readonly tabindex="14">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label for="pic"> Photo</label><br>
                                            <img id='imgshow' class="img_show" src='img\avatar.png' />
                                            <!-- <input type="file" class="form-control  personal_info_disble" id="pic" name="pic" tabindex="18">
                                            <input type="hidden" class="personal_info_disble" id="per_pic"> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!------------------------------------------------------Customer card End--------------------------->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Settlement Info</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fields -->
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="settle_date">Settlement Date</label>
                                    <input type="text" class="form-control" id="settle_date" name="settle_date" readonly tabindex="15">
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="settle_amount">Settlement Amount</label>
                                    <input type="text" class="form-control" id="settle_amount" name="settle_amount" tabindex="16" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="settle_balance">Settlement Balance</label>
                                    <input type="number" class="form-control" id="settle_balance" name="settle_balance" tabindex="17" readonly>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="payment_type">Payment Type</label>
                                    <select class="form-control" id="payment_type" name="payment_type" tabindex="18">
                                        <option value="">Select Payment Type</option>
                                        <option value="1">Split Payment</option>
                                        <option value="2">Single Payment</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4" id="settle_type_container">
                                <div class="form-group">
                                    <label for="settle_type">Settlement Type</label>
                                    <select class="form-control" id="settle_type" name="settle_type" tabindex="19">
                                        <option value="">Select Settlement Type</option>
                                        <option value="1">Cash</option>
                                        <option value="2">Cheque</option>
                                        <option value="3">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4" id="bank_container" style="display: none;">
                                <div class="form-group">
                                    <label for="bank_name">Bank Name</label>
                                    <select class="form-control" id="bank_name" name="bank_name" tabindex="20">
                                        <option value="">Select Bank</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row for Cash -->
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12" id="cash_container" style="display: none;">
                                <div class="form-group">
                                    <label for="settle_cash">Cash</label>
                                    <input type="number" class="form-control" id="settle_cash" name="settle_cash" tabindex="21">
                                </div>
                            </div>
                        </div>

                        <!-- Other fields -->
                        <div class="row">
                            <div class="col-4" id="cheque_no_container" style="display: none;">
                                <div class="form-group">
                                    <label for="cheque_no">Cheque Number</label>
                                    <input type="number" class="form-control" id="cheque_no" name="cheque_no" tabindex="22">
                                </div>
                            </div>
                            <div class="col-4" id="cheque_val_container" style="display: none;">
                                <div class="form-group">
                                    <label for="cheque_val">Cheque Value</label>
                                    <input type="number" class="form-control" id="cheque_val" name="cheque_val" tabindex="23">
                                </div>
                            </div>
                            <div class="col-4" id="cheque_remark_container" style="display: none;">
                                <div class="form-group">
                                    <label for="cheque_remark">Cheque Remark</label>
                                    <input type="text" class="form-control" id="cheque_remark" name="cheque_remark" tabindex="24">
                                </div>
                            </div>
                            <div class="col-4" id="transaction_id_container" style="display: none;">
                                <div class="form-group">
                                    <label for="transaction_id">Transaction ID</label>
                                    <input type="number" class="form-control" id="transaction_id" name="transaction_id" tabindex="24">
                                </div>
                            </div>
                            <div class="col-4" id="transaction_val_container" style="display: none;">
                                <div class="form-group">
                                    <label for="transaction_val">Transaction Value</label>
                                    <input type="number" class="form-control" id="transaction_val" name="transaction_val" tabindex="25">
                                </div>
                            </div>
                            <div class="col-4" id="transaction_remark_container" style="display: none;">
                                <div class="form-group">
                                    <label for="transaction_remark">Transaction Remark</label>
                                    <input type="text" class="form-control" id="transaction_remark" name="transaction_remark" tabindex="26">
                                </div>
                            </div>
                            <div class="col-4" id="balance_remark_container" style="display: none;">
                                <div class="form-group">
                                    <label for="balance_amount">Balance Amount</label>
                                    <input type="number" class="form-control" id="balance_amount" name="balance_amount" readonly tabindex="27">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-----------------------------------------------------Settlement Card End-------------------------->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cash Acknowledgement</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Fields -->
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="gua_name">Name</label>
                                    <select type="text" class="form-control" id="gua_name" name="gua_name" tabindex="28">
                                        <option value="">Select Guarantor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                <div class="form-group">
                                    <label for="gua_relationship">Relationship</label>
                                    <input type="text" class="form-control" id="gua_relationship" name="gua_relationship" tabindex="29" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <table id="guarantor_table" class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Settlement Date</th>
                                            <th>Settlement Amount</th>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                        </tr>
                                    </thead>
                                    <tbody> </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!------------------------------------------------------Cash AcknowledgCard End------------------->
            </div>
        </div>

        <div class="col-12 mt-3 text-right">
            <button name="submit_settle_info" id="submit_settle_info" class="btn btn-primary" tabindex="19"><span class="icon-check"></span>&nbsp;Submit</button>
            <button type="reset" id="reset_clear" class="btn btn-outline-secondary" tabindex="20">Clear</button>
        </div>
    </form>
</div>