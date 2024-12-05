<?php
include "../../ajaxconfig.php";

$group_id = $_POST['groupId'];


$grp_qry = $pdo->query("SELECT gc.grp_id, gc.grp_name, bc.branch_name, gc.chit_value, gc.total_months FROM `group_creation` gc 
	JOIN branch_creation bc ON gc.branch = bc.id
    WHERE gc.grp_id = '$group_id' ");
$grp = $grp_qry->fetch(PDO::FETCH_ASSOC);

$qry = $pdo->query("SELECT date, auction_month, chit_amount FROM auction_details WHERE group_id ='$group_id' ");
$auction_details = $qry->fetchAll(PDO::FETCH_ASSOC);

$qry = $pdo->query("SELECT cc.cus_id, gcm.id AS cus_mapping_id, CONCAT(cc.first_name,' ', cc.last_name) AS cus_name, gcm.settle_status 
                    FROM group_cus_mapping gcm 
                    JOIN customer_creation cc ON gcm.cus_id = cc.id 
                    WHERE gcm.grp_creation_id = '$group_id' ");
$customer_details = $qry->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .th_cls{
        text-align: end;
    }

    .th_bg > th{
        background-color: #fba8a1 !important;
    }
    .th_bg1 > th{
        background-color: #f99d95 !important;
    }
    .th_bg2 > th{
        background-color: #ff8f86 !important;
    }
    .th_bg3 > th{
        background-color: #ed7d74 !important;
    }
</style>
<table id="ledger_view_chart_table" class="table custom-table">
    <thead>
            <tr class="th_bg">
                <th colspan="<?php echo intval($grp['total_months']) + 4;?>" style="font-size: 22px; text-align: center;"> <?php echo "Group ID: " . $grp['grp_id'] . " | Group Name: " . $grp['grp_name'] . " | Branch Name: " . $grp['branch_name'] . " | Chit Value: " . $grp['chit_value']; ?> </th>
                <th></th>
            </tr>
            <tr class="th_bg1">
                <th colspan="4" class="th_cls">Auction Date</th>
                <?php 
                    foreach($auction_details AS $row){
                ?>
                    <th><?php echo date('d-m-Y',strtotime($row['date'])); ?></th>
                <?php
                    }
                ?>
                <th></th>
            </tr>
            <tr class="th_bg2">
                <th colspan="4" class="th_cls">Chit Amount</th>
                <?php 
                    foreach($auction_details AS $row){
                ?>
                    <th><?php echo $row['chit_amount']; ?></th>
                <?php
                    }
                ?>
                <th></th>
            </tr>
            <tr class="th_bg3">
                <th colspan="4" class="th_cls">Auction Month</th>
                <?php 
                    foreach($auction_details AS $row){
                ?>
                    <th><?php echo $row['auction_month']; ?></th>
                <?php
                    }
                ?>
                <th></th>
            </tr>
            <tr>
                <th>Sl.No</th>
                <th>Customer Id</th>
                <th>Customer Name</th>
                <th>Settlement</th>
                <?php 
                    foreach($auction_details AS $row){
                ?>
                    <th><?php echo ''; ?></th>
                <?php
                    }
                ?>
                <th>Chart</th>
            </tr>

        </thead>

        <tbody>
            <?php 
                $i=1;
                foreach($customer_details AS $cus_info){
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $cus_info['cus_id']; ?></td>
                    <td><?php echo $cus_info['cus_name']; ?></td>
                    <td><?php echo $cus_info['settle_status']; ?></td>

                    <?php 
                    foreach($auction_details AS $row){
                        $qry = $pdo->query("SELECT SUM(collection_amount) AS coll_amnt FROM collection WHERE group_id = '$group_id' AND cus_mapping_id = '". $cus_info['cus_mapping_id']. "' AND auction_month = '". $row['auction_month'] ."' ");
                        $row = $qry->fetch();

                    ?>
                        <td><?php echo $row['coll_amnt']; ?></td>
                    <?php
                    } //second foreach end
                    ?>
                    <td> <input type="button" class="btn btn-primary" id="due_chart" value="Due Chart" 
                    data-value='<?php echo json_encode(['group_id' => $group_id,
                    'cus_mapping_id' => $cus_info['cus_mapping_id'],
                    'cus_id' => $cus_info['cus_id'], 
                    'cus_name' => $cus_info['cus_name'], 
                    'settle_sts' => ($cus_info['settle_status']) ?? 'No' 
                    ]); ?>' 
                    > </td>
                </tr>
            <?php
                } //first foreach end
            ?>
        </tbody>

</table>