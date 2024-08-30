<div class="radio-container">
    <div class="selector">
        <div class="selector-item">
            <input type="radio" id="group_current" name="customer_data_type" class="selector-item_radio" value="cus_profile" checked>
            <label for="group_current" class="selector-item_label">Current</label>
        </div>
        <div class="selector-item">
            <input type="radio" id="group_closed" name="customer_data_type" class="selector-item_radio" value="cus_summary">
            <label for="group_closed" class="selector-item_label">Closed</label>
        </div>
    </div>
</div>
<br>
<div class="text-right">
    <button type="button" class="btn btn-primary" id="back_btn" style="display:none;"><span class="icon-arrow-left"></span>&nbsp; Back </button>
</div>
<br>
<!----------------Group Table---------------------------->
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
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Group Status</th>
                            <th>Collection Status</th>
                            <th>Charts</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody> </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--------------Group table end-------------------------->
<!----------------------------- CARD Start-Auction  Detail TABLE ------------------------------>
<div class="card auction_detail_content" style="display: none;">
    <div class="card-header">
        <div class="card-title">Auction List</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <table id="auction_table" class="table custom-table">
                    <thead>
                        <tr>
                            <th>Auction Month</th>
                            <th>Date</th>
                            <th>Auction Value</th>
                            <th>Customer</th>
                            <th>Auction Status</th>
                            <th>Group status</th>
                            <th>Collection status</th>
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
<!-- /////////////////////////////////////////////////////////////////// Auction Chart Modal Start ////////////////////////////////////////////////////////////////////// -->
<div class="modal fade bd-example-modal-lg" id="auction_chart_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document" style="max-width: 70% !important">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="dueChartTitle">Auction Chart</h5>
                <button type="button" class="close" data-dismiss="modal" tabindex="1" aria-label="Close" onclick="closeChartsModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="due_chart_table_div">
                    <table id="auction_chart_table" class="table custom-table">
                        <thead>
                            <th>Auction Month</th>
                            <th>Date</th>
                            <th>Auction Value</th>
                            <th>Commision</th>
                            <th>Total amount</th>
                            <th>Chit Amount</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="closeChartsModal()" tabindex="4">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /////////////////////////////////////////////////////////////////// Auction Chart Modal END ////////////////////////////////////////////////////////////////////// -->
 <!-- /////////////////////////////////////////////////////////////////// Settlement Chart Modal Start ////////////////////////////////////////////////////////////////////// -->
<div class="modal fade bd-example-modal-lg" id="settlement_chart_model" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg " role="document" style="max-width: 70% !important">
        <div class="modal-content" style="background-color: white">
            <div class="modal-header">
                <h5 class="modal-title" id="dueChartTitle">Settlement Chart</h5>
                <button type="button" class="close" data-dismiss="modal" tabindex="1" aria-label="Close" onclick="closeChartsModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid" id="settle_chart_table_div">
                    <table id="settle_chart_table" class="table custom-table">
                        <thead>
                            <th>Auction Month</th>
                            <th>Group ID</th>
                            <th>Group Name</th>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Settlement Date</th>
                            <th>Settlement Amount</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal" onclick="closeChartsModal()" tabindex="4">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- /////////////////////////////////////////////////////////////////// Settlement Chart Modal END ////////////////////////////////////////////////////////////////////// -->