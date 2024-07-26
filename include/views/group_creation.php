<div class="row gutters">
    <div class="col-12">
        <div class="col-12 mt-3 text-right">
            <button class="btn btn-primary" id="add_group"><span class="icon-add"></span> Add Group Creation</button>
            <button class="btn btn-primary" id="back_btn" style="display: none;"><span class="icon-arrow-left"></span> Back</button>
        </div></br>
        <!----------------------------- CARD START GROUP CREATION TABLE------------------------------>
        <div class="card wow group_table_content">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table id="group_creation_table" class="table custom-table">
                            <thead>
                                <tr>
                                    <th width="50">S.No.</th>
                                    <th>Group ID</th>
                                    <th>Group Name</th>
                                    <th>Chit Value</th>
                                    <th>Total Month</th>
                                    <th>Date</th>
                                    <th>Commission(%)</th>
                                    <th>Branch</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody> </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!----------------------------- CARD END  group CREATION TABLE------------------------------>


        <!----------------------------- CARD START  group CREATION FORM------------------------------>
        <div id="group_creation_content" style="display: none;">
            <form id="group_creation" name="group_creation" method="post" enctype="multipart/form-data">
                <input type="hidden" id="groupid">
                <!-- Row start -->
                <div class="row gutters">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Group Info</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Fields -->
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="group_id">Group ID</label><span class="text-danger">*</span>
                                            <input type="text" class="form-control" id="group_id" name="group_id" placeholder="Enter Group ID" tabindex="1">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="chit_value">Chit Value</label><span class="text-danger">*</span>
                                            <input type="number" class="form-control" id="chit_value" name="chit_value" placeholder="Enter Chit Value" tabindex="2">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="grp_date">Date</label><span class="text-danger">*</span>
                                            <select class="form-control" id="grp_date" name="grp_date" tabindex="3">
                                                <option value="">Select Date</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="group_name">Group Name</label><span class="text-danger">*</span>
                                            <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Enter Group Name" tabindex="4">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="commission">Commission(%)</label><span class="text-danger">*</span>
                                            <input type="number" class="form-control" id="commission" name="commission" placeholder="Enter Commission" tabindex="5">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4 col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="hours">Time</label><span class="text-danger">*</span>
                                            <div class="form-row">
                                                <div class="col">
                                                    <input type="number" class="form-control" id="hours" name="hours" min="1" max="12" placeholder="HH" tabindex="6">
                                                </div>
                                                <div class="col-auto">
                                                    <span>:</span>
                                                </div>
                                                <div class="col">
                                                    <input type="number" class="form-control" id="minutes" name="minutes" min="0" max="59" placeholder="MM" tabindex="7">
                                                </div>
                                                <div class="col">
                                                    <select class="form-control" id="ampm" name="ampm" tabindex="8">
                                                        <option value="AM">AM</option>
                                                        <option value="PM">PM</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="total_members">Total Members</label><span class="text-danger">*</span>
                                            <input type="number" class="form-control" id="total_members" name="total_members" placeholder="Enter Total Members" tabindex="9">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="total_month">Total Month</label><span class="text-danger">*</span>
                                            <input type="number" class="form-control" id="total_month" name="total_month" placeholder="Enter Total Month" tabindex="10">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="start_month">Start Month</label><span class="text-danger">*</span>
                                            <input type="month" class="form-control" id="start_month" name="start_month" placeholder="Enter Start Month" tabindex="11">
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="end_month">End Month</label><span class="text-danger">*</span>
                                            <input type="month" class="form-control" id="end_month" name="end_month" placeholder="Enter End Month" tabindex="12" readonly>
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="branch">Branch</label><span class="text-danger">*</span>
                                            <select class="form-control" id="branch" name="branch" tabindex="13">
                                                <option value="">Select Branch</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-4 col-sm-4 col-md-4	col-lg-4 col-xl-4">
                                        <div class="form-group">
                                            <label for="grace_period">Grace Period(Days)</label><span class="text-danger">*</span>
                                            <select type="month" class="form-control" id="grace_period" name="grace_period" tabindex="14">
                                                <option value="">Select Grace Period</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3 text-right">
                                        <button name="submit_group_info" id="submit_group_info" class="btn btn-primary" tabindex="19"><span class="icon-check"></span>&nbsp;Save</button>
                                        <button type="reset" class="btn btn-outline-secondary" tabindex="20">Clear</button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="radio-container">
                    <div class="selector">
                        <div class="selector-item">
                            <input type="radio" id="customer_mapping" name="grp_info_add" class="selector-item_radio" value="cus_map" tabindex="15" checked>
                            <label for="customer_mapping" class="selector-item_label">Customer Mapping</label>
                        </div>
                        <div class="selector-item">
                            <input type="radio" id="group_details" name="grp_info_add" class="selector-item_radio" value="grp_details" tabindex="16">
                            <label for="group_details" class="selector-item_label">Group Details</label>
                        </div>
                    </div>
                </div>
                <br>

                <!--- -------------------------------------- Customer Mapping START ------------------------------- -->
                <div class="card" id="cus_mapping_card">
                    <div class="card-header">
                        <div class="card-title">Customer Mapping</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12"></div>
                            <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12">
                                <div class="form-group">
                                    <label for="cus_name">Customer Name</label><span class="text-danger">*</span>
                                    <select class="form-control" id="cus_name" name="cus_name" tabindex="17">
                                        <option value="">Select Customer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-1 col-lg-1 col-md-1 col-sm-1 col-12">
                                <div class="form-group">
                                    <label for="submit_cus_map"> </label>
                                    <input type="button" class="btn btn-primary modalBtnCss" id="submit_cus_map" name="submit_cus_map" value="Add" tabindex="18" style="margin: 16px;">
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12"></div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <table id="cus_mapping_table" class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Customer ID</th>
                                            <th>Name</th>
                                            <th>Place</th>
                                            <th>Occupation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <!--- -------------------------------------- Customer Mapping END ------------------------------- -->

                <!--- -------------------------------------- Group Details START ------------------------------- -->
                <div class="card" id="grp_details_card" style="display: none;">
                    <div class="card-header">
                        <div class="card-title">Group Details</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table id="grp_details_table" class="table custom-table">
                                    <thead>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Month</th>
                                            <th>Low Value</th>
                                            <th>High Value</th>
                                        </tr>
                                    </thead>
                                    <tbody> </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 mt-3 text-right">
                            <button name="submit_group_details" id="submit_group_details" class="btn btn-primary" tabindex="19"><span class="icon-check"></span>&nbsp;Submit</button>
                            <button type="reset" class="btn btn-outline-secondary" tabindex="20">Clear</button>
                        </div>
                    </div>

                </div>
                <!--- -------------------------------------- Group Details END ------------------------------- -->

            </form>
        </div>
        <!----------------------------- CARD END  group CREATION FORM------------------------------>

    </div>
</div>