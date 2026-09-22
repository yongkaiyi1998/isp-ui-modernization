<style>
html {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

#editSignatureModal, #editEquipmentModal {
    display: none;
}

.modal {
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
}

.page {
    min-height: 310mm;
    max-height: 310mm;
}

@media (max-width: 991px) {
    .modal-dialog.modal-lg {
        width: 95vw !important;
        margin: 10px auto !important;
        left: 0 !important;
        right: 0 !important;
    }
}

#sig-canvas {
    max-width: 100%;
    height: auto;
}

@media (max-width: 992px) {
    .book .page {
        /*width: 100% !important;*/
        padding: 10px !important;
        overflow-x: hidden;
    }

    .span-inline {
        min-width: 50% !important; 
        width: auto !important;
    }

    .col-lg-12, .col-lg-5 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    table {
        width: 100% !important;
        table-layout: fixed;
    }
}

.td-label {
    padding-right: 10px;
    font-size: 14px;
}

.td-input {
    border: 1px solid black;
    height: 27px;
    font-size: 12px;
    padding-left: 5px;
}

.td-textarea {
    border: 1px solid black;
    height: 3em;
    font-size: 12px;
    padding-left: 5px;
    vertical-align: top;
}

.div-input {
    border: 1px solid black;
    width: 25px;
    height: 27px;
    font-size: 14px;
    padding-left: 10px;
    padding-top: 3px;
    padding-right: 10px;
    clear: both;
    float: left;
}

.div-input-right {
    border: 1px solid black;
    width: 25px;
    height: 27px;
    font-size: 14px;
    padding-left: 10px;
    padding-top: 3px;
    padding-right: 10px;
    clear: both;
    float: right;
}

.div-input-bit {
    border: 1px solid black;
    width: 80px;
    height: 27px;
    font-size: 14px;
    padding-left: 10px;
    padding-top: 3px;
    padding-right: 10px;
    clear: both;
    float: left;
}

.div-input-wider {
    border: 1px solid black;
    width: 150px;
    height: 27px;
    font-size: 14px;
    padding-left: 10px;
    padding-top: 3px;
    padding-right: 10px;
    clear: both;
    float: left;
}

.span-inline {
    font-size: 12px;
    min-width: 200px;
    display: inline-block;
    border-bottom: 1px solid black; 
}

.page h4 {
    background-color: #08b454;
    margin-top: 5px;
    margin-bottom: 5px;
    color: white;
    padding: 3px;
    font-size: 12px;
}

@media print {
    body, html {
        background-color: #ffffff !important;
        background: #ffffff !important;
    }

    .page {
        background-color: #ffffff !important;
        min-height: 296mm;
        max-height: 296mm;
    }

    .modal, .modal-backdrop, #editSignatureModal, .btn, .no-print, canvas, #editEquipmentModal {
        display: none !important;
        visibility: hidden !important;
    }

    a[href]:after {
        content: none !important;
    }
}
</style>

<link rel="stylesheet" href="<?php echo base_url("css/jquery.gritter.css?".cssjs_ver()); ?>" />

<div class="book"> 
    <div class="page" data-page="{data}"> 
        <div>
            <div class="col-lg-12">
                <table style="width:100%;">
                    <tr>
                        <td style="width:50%;">
                            <strong>{comp_name}</strong><br />
                            <span style="font-size:10px;">
                                {company_addr_1}<br />
                                {company_addr_2}<br />
                                <?php if (!empty($company_addr_3)) echo $company_addr_3."<br />"; ?>
                                <?php if (!empty($company_city)) echo $company_city.","; ?> <?php if (!empty($company_postal)) echo $company_postal.","; ?> Penang<br />
                                <?php if (!empty($company_phone)) echo "Call us +".$company_phone; ?>
                            </span>
                        </td>
                        <td style="width:50%; text-align:right;">
                            <div>
                                <?php $company_logo = $this->config->item('logo_img'); ?>
                                <?php if (!empty($company_logo)) { ?>
                                    <img style="width:150px;" src="<?php echo $company_logo; ?>">
                                <?php } else { ?>
                                    <img src="<?php echo base_url("/images/telco-icon.png"); ?>">ITELCO
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="col-lg-12 content_double_line" style="margin-top:10px; margin-bottom:10px;"></div>
            <div class="col-lg-12"><h4>TERMINATION FORM (BROADBAND SERVICES)</h4></div>
            <div class="col-lg-5"><h4 style="margin:0;">INFORMATION</h4></div>
            
            <div class="col-lg-12 content_body">
                <div class="col-lg-12">
                    <table style="width:100%; border-spacing:5px;">
                        <tr>
                            <td class="td-label" style="width:18%;">Name / Company</td>
                            <td class="td-input" style="width:70%;" colspan="3">{name}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%;">Contact Person</td>
                            <td class="td-input" style="width:70%;" colspan="3">{pic_name}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%;">Contact Number</td>
                            <td class="td-input" style="width:70%;" colspan="3">{pic_mobile}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%;">Username</td>
                            <td class="td-input" style="width:30%;">{login_username}</td>
                            <td class="td-label text-right" style="width:20%;">Password</td>
                            <td class="td-input" style="width:20%;">{login_password}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%; vertical-align:top;">Site Address</td>
                            <td colspan="3" class="td-textarea" style="width:80%;">
                                <?php 
                                    if($building_name != '' && $inst_unit_no != '') echo $inst_unit_no . ', ' . $building_name . '<br />';
                                    if($inst_addr1 != '') echo $inst_addr1 . ' ';
                                    if($inst_addr2 != '') echo $inst_addr2 . ' ';
                                    if($inst_addr1 != '' || $inst_addr2 != '') echo '<br />';
                                    if($inst_postcode != '') echo $inst_postcode . ' ';
                                    if($inst_city != '') echo $inst_city . ' ';
                                    if($inst_state != '') echo strtoupper('Penang'); 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%;">Building Name</td>
                            <td class="td-input" style="width:80%;" colspan="3">{building_name}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:18%;">Service Order No.</td>
                            <td class="td-input" style="width:80%;" colspan="3">{customer_no} - {name}</td>
                        </tr>
                        <!--<tr>
                            <td class="td-label" style="vertical-align:top; width:18%;">Nature of Work</td>
                            <td colspan="3" style="width:80%;">
                                <div style="width:40%; float:left;">
                                    <div class="div-input"></div>
                                    <span style="font-size:12px; padding:0 10px;">New</span>
                                </div>
                                <div style="width:60%; float:left;">
                                    <div class="div-input"></div>
                                    <span style="font-size:12px; padding:0 10px;">Relocation</span>
                                </div>
                                <div style="width:40%; float:left; padding-top:5px;">
                                    <div class="div-input"></div>
                                    <span style="font-size:12px; padding:0 10px;">Others</span>
                                </div>
                                <div style="width:60%; float:left; padding-top:5px;">
                                    <div class="div-input" style="width:100%;"></div>
                                </div>
                            </td>
                        </tr>-->
                    </table>
                </div>

                <div class="col-lg-5"><h4>Product Type</h4></div>
                <div class="col-lg-12">
                    <table style="width:100%; border-spacing:5px;">
                        <tr>
                            <td class="td-label" style="vertical-align:top; width:20%;">Category</td>
                            <td colspan="3" style="width:80%;">
                                <div style="width:40%; float:left;">
                                    <div class="div-input">{residential_check}</div>
                                    <span style="font-size:14px; padding:0 10px;">Residential</span>
                                </div>
                                <div style="width:40%; float:left;">
                                    <div class="div-input">{business_check}</div>
                                    <span style="font-size:14px; padding:0 10px;">Business</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:30%;">Package Name</td>
                            <td class="td-input" style="width:80%;" colspan="3">{package_name}</td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:30%;">Contract</td>
                            <td class="td-input" style="width:80%;" colspan="3">{contract_month}</td>
                        </tr>
                        <?php 
                            for ($i = 0; $i < 3; $i++) { 
                                $item = isset($equipment_list[$i]) ? $equipment_list[$i] : null;
                                $eq_name = $item ? $item['equipment_name'] : '';
                                $serial = $item ? $item['serial_no'] : '';
                        ?>
                            <tr>
                                <td class="td-label" style="width:30%;"><?php echo ($i === 0) ? 'Equipment Installed' : ''; ?></td>
                                <td class="td-label" style="width:20%;">
                                    <div class="div-input"><?php echo ($eq_name != '') ? '/' : ''; ?></div>
                                    <span style="font-size:10px; padding:0 10px;"><?php echo ($i+1) . '.' . ' ' . $eq_name; ?></span>
                                </td>
                                <td class="td-input" style="width:50%;" colspan="2">
                                    <?php echo $serial; ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if(($print_mode ?? 0) != 1): ?>
                            <tr class="no-print">
                                <td></td>
                                <td>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="td-label" style="width:30%; font-size: 13px;">Additional Requirement</td>
                            <td class="td-label" style="width:20%; ">
                                <div class="div-input"></div>
                                <span style="font-size:10px; padding:0 10px;">IP Address</span>
                            </td>
                            <td class="td-input" style="width:50%;" colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:30%;"></td>
                            <td class="td-label" style="width:20%; ">
                                <div class="div-input"></div>
                                <span style="font-size:10px; padding:0 10px;">Others</span>
                            </td>
                            <td class="td-input" style="width:50%;" colspan="2"></td>
                        </tr>
                        <tr>
                            <td class="td-label" style="width:30%;">Termination Remarks</td>
                            <td class="td-input" style="width:80%;" colspan="3"><br><br><br><br></td>
                        </tr>

                        <tr>
                            <td class="td-label" style="width:30%; font-size: 13px;">Unbilled Amount</td>
                            <td class="td-label" style="width:20%; ">
                                <div class="div-input-bit"><?php echo $termination_data['data_unbilled_months'] ?? ''; ?></div>
                                <span style="font-size:10px; padding:0 10px;">Months</span>
                            </td>
                            <td class="td-label" style="width:5%; font-size: 13px;">Amount</td>
                            <td class="td-label" style="width:30%; ">
                                <div class="div-input-wider"><b>RM <?php echo $termination_data['data_unbilled_amt'] ?? ''; ?></b></div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-lg-12">                    
                    <table style="width:100%; border-spacing:5px; font-weight:800; margin-top:10px; table-layout: fixed;">
                        <colgroup>
                            <col style="width:30%">
                            <col style="width:40%">
                            <col style="width:30%">
                        </colgroup>
                        <tr><td colspan="3" style="border-top:3px solid black;"></td></tr>
                        <tr>
                            <td colspan="3" style="font-size:14px;"><strong>I hereby confirm that the equipment/ service installation is acceptable and is to my satisfaction.</strong></td>
                        </tr>
                        <tr>
                            <td style="font-size:12px; position:relative; text-align:left; min-height:120px;">

                                <?php if (!empty($signature) && ( ($current_termination_flow == 'D') || ($current_termination_flow == 'C') )) { ?>
                                    <img style="height:75px; max-width:100%;" id="signature" src="<?php echo $signature; ?>">
                                <?php } ?>
                                <br />
                                ______________________________<br />
                                Customer Signature <br />
                                Date: <?php echo !empty($sign_date) ? $sign_date : ''; ?><br>
                                Name: <?php echo !empty($signer_name) ? $signer_name : ''; ?><br/>
                                I/C No: <?php echo !empty($signer_ic) ? $signer_ic : ''; ?>

                            </td>
                            <td>
                                <?php if(($print_mode ?? 0) != 1): ?>
                                <!-- check if its the customer PIC only show this button -->
                                    <?php if (!empty($is_pic)) { ?>
                                    <button 
                                        style="
                                            transform: translate(-30%, -130%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(8,180,84,0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(8,180,84,1)'"
                                        onmouseout="this.style.background='rgba(8,180,84,0.85)'"
                                        class="no-print"
                                        onclick="open_signature_popup();"
                                    >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        Edit Signature
                                    </button>
                                    <?php } ?>
                                <?php endif; ?>

                                <!-- for Termination initiator to process -->
                                <?php if(($print_mode ?? 0) != 1): ?>
                                <?php if ($termination_init) { ?>
                                <?php if ($current_termination_flow == 'P' || $current_termination_flow == 'F') { ?>
                                    <button 
                                        style="
                                            transform: translate(-30%, -130%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(8,180,84,0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(8,180,84,1)'"
                                        onmouseout="this.style.background='rgba(8,180,84,0.85)'"
                                        class="no-print"
                                        onclick="open_staff_popup();"
                                    >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        Prepare and Send to Customer PIC
                                    </button>
                                <?php } else if ($current_termination_flow == 'S') { ?>
                                    <button 
                                        style="
                                            transform: translate(-30%, -130%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(8,180,84,0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(8,180,84,1)'"
                                        onmouseout="this.style.background='rgba(8,180,84,0.85)'"
                                        class="no-print"
                                        onclick="open_staff_popup();"
                                    >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        Awaiting Customer Signature
                                    </button>
                                <?php } ?>
                                <?php } ?>
                                <!-- customer -->
                                <?php if ($is_customer) { ?>
                                <?php if ($current_termination_flow == 'S') { ?>
                                    <button 
                                        style="
                                            transform: translate(-30%, -130%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(8,180,84,0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(8,180,84,1)'"
                                        onmouseout="this.style.background='rgba(8,180,84,0.85)'"
                                        class="no-print"
                                        onclick="open_signature_popup();"
                                    >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        Please confirm and Sign Here
                                    </button>
                                <?php } ?>
                                <?php } ?>

                                <!-- confirmation staff -->
                                <?php if ($termination_confirm && $current_termination_flow == 'D') { ?>
                                    <button 
                                        style="
                                            transform: translate(-30%, -130%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(8,180,84,0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(8,180,84,1)'"
                                        onmouseout="this.style.background='rgba(8,180,84,0.85)'"
                                        class="no-print"
                                        onclick="open_staff_popup_end();"
                                    >
                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                        Confirm Termination
                                    </button>
                                <?php } ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; text-align:center; vertical-align:middle; border:1px solid #000; height:80px;">
                                Company Stamp<br>(If applicable)
                                <?php if ($is_customer) { ?>
                                <?php if ($current_termination_flow == 'S') { ?>
                                    <button 
                                        style="
                                            transform: translate(-110%, -185%);
                                            display:flex;
                                            align-items:center;
                                            gap:6px;
                                            padding:7px 14px;
                                            background:rgba(255, 0, 0, 0.85);
                                            color:#fff;
                                            border:none;
                                            border-radius:20px;
                                            font-size:12px;
                                            font-weight:600;
                                            cursor:pointer;
                                            box-shadow:0 2px 6px rgba(0,0,0,0.2);
                                            transition:all 0.2s ease;
                                            white-space:nowrap;
                                        "
                                        onmouseover="this.style.background='rgba(255, 0, 0, 1)'"
                                        onmouseout="this.style.background='rgba(255, 0, 0, 0.85)'"
                                        class="no-print"
                                        onclick="reject_form();"
                                    >
                                        Reject Form
                                    </button>
                                <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-size:12px;">

                                <?php if ($is_customer) { ?>

                                <div style="
                                    display:flex;
                                    flex-direction:column;
                                    gap:6px;
                                " id="tnc_div">
                                    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                                        <input type="checkbox" id="is_terms_accepted" name="is_terms_accepted" required style="margin-top:3px;" <?php echo !empty($customer_sign) ? 'checked=checked' : '' ?> <?php if ($current_termination_flow != 'S') { echo "disabled"; } ?>>
                                        <span style="margin-top: 0.7rem;">
                                            I have read and agree to the 
                                            <a href="<?= $tnc_url; ?>" target="_blank" style="color:#007bff; text-decoration:underline;">
                                                Terms & Conditions and Privacy Policy.
                                            </a> 
                                        </span>
                                    </label>
                                </div>

                                <?php } ?>

                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>      
</div>

<!-- rejection -->
<div id="rejectTermination" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Customer Reject</h4>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Reason for Rejection<span class="red">*</span></label>
                    <textarea id="reject-reason" name="reject-reason" class="form-control"></textarea>
                </div>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-danger" id="reject-submit" onclick="reject_form_save()">
                    Reject Termination Form
                </button>

            </div>

        </div>
    </div>
</div>

<!-- staff section modal -->
<div id="editTerminationPIC" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Send To PIC</h4>
            </div>

            <div class="modal-body">
                <div id="account_detail_card"></div>

                <input type="hidden" id="term_customer_no" name="term_customer_no" value="" />
                <input type="hidden" id="customer_no" name="customer_no" value="<?php echo $customer_no; ?>" />

                <!-- PIC input fields -->
                <div class="form-group">
                    <label>PIC Name<span class="red">*</span></label>
                    <input type="text" id="term_pic_name" name="term_pic_name" class="form-control" placeholder="Name of the PIC">
                </div>
                <div class="form-group">
                    <label>PIC Email<span class="red">*</span></label>
                    <input type="text" id="term_email" name="term_email" class="form-control" placeholder="Email of the PIC">
                </div>
                <div class="form-group">
                    <label>PIC Mobile</label>
                    <input type="text" id="term_mobile" name="term_mobile" class="form-control" placeholder="Phone number of the PIC e.g 601xxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Effective Date<span class="red">*</span></label>
                    <input type="text" id="term_effective_date" name="term_effective_date" class="form-control" placeholder="effective date off termination">
                </div>
                <div class="form-group">
                    <input type="checkbox" id="term_penalty" name="term_penalty" value="1" style="margin-bottom:5px;" onclick="switch_unbilled();">
                    Bill the remaining months of the contract?
                </div>

                <div class="row unbilled_area">
                    <div class="form-group col-lg-6">
                        <label>Unbilled Months</label>
                        <input type="text" id="term_unbilled_months" name="term_unbilled_months" class="form-control" placeholder="Unbilled Months">
                    </div>

                    <div class="form-group col-lg-6">
                        <label>Amount Charged</label>
                        <input type="text" id="term_amount" name="term_amount" class="form-control" placeholder="Unbilled Amount">
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <div class="pull-left">
                    <button type="button" class="btn btn-success" id="term-send" onclick="save_term_only(1)">
                        <i class="menu-icon fa fa-send grey"></i>
                        <?php if ($current_termination_flow == 'P') { ?>
                        Send to PIC
                        <?php } else if ($current_termination_flow == 'S' || $current_termination_flow == 'F') { ?>
                        Resend to PIC
                        <?php } ?>
                    </button>
                </div>

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-primary" id="term-save" onclick="save_term_only(0)">
                    <i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i>
                    Save
                </button>

            </div>

        </div>
    </div>
</div>

<!-- finalize termination modal -->
<div id="finalizeTermination" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Finalize Termination</h4>
            </div>

            <div class="modal-body">
                <div id="account_detail_card_2"></div>
                <div id="termination_data_card"></div>
            </div>

            <div class="modal-footer">

                <button type="button" id="finalize-close" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-primary" id="finalize-save" onclick="finalize_term()">
                    <i class="menu-icon fa fa-edit white" data-toggle="tooltip" title=""></i>
                    Finalize Termination
                </button>

            </div>

        </div>
    </div>
</div>

<div id="editSignatureModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Customer Signature</h4>
            </div>

            <div class="modal-body">

                <div class="panel panel-default" style="margin-bottom:20px;">
                    <div class="panel-heading">
                        <strong>Customer Information</strong>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="signer_name"
                                   placeholder="Signer Name">
                        </div>

                        <div class="form-group">
                            <label for="customer_ic">IC Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="signer_ic"
                                   placeholder="Signer IC number">
                        </div>

                    </div>
                </div>

                <!-- Signature Section -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong>Signature <span class="text-danger">*</span></strong>
                        <span class="text-muted" style="font-size:12px;">(Please sign inside the box)</span>
                    </div>

                    <div class="panel-body text-center">

                        <div style="width:100%; overflow:auto;">
                            <canvas id="sig-canvas"
                                    width="604"
                                    height="304"
                                    style="width:100%; max-width:100%; height:auto; border:2px dashed #ccc; background:#fff;">
                            </canvas>
                        </div>

                    </div>
                </div>

            </div>

            <div class="modal-footer">

                <div class="pull-left">
                    <button type="button" class="btn btn-danger" id="sig-clearBtn">
                        Clear Signature
                    </button>
                </div>

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>

                <button type="button" class="btn btn-primary" id="sig-submitBtn" onclick="saveSignature()">
                    Save Signature
                </button>

            </div>

        </div>
    </div>
</div>

<div id="editEquipmentModal" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Customer Equipment</h4>
            </div>

            <div class="modal-body">

                <div class="panel panel-default" style="margin-bottom:20px;">
                    <div class="panel-heading">
                        <strong>Equipment Information</strong>
                    </div>
                    <div class="panel-body">
                        <?php for ($i = 1; $i <= 3; $i++) { ?>
                            <div class="equipment-row" style="<?php echo $i > 1 ? 'margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee;' : ''; ?>">
                                <h5>Equipment #<?php echo $i; ?> <?php echo $i === 1 ? '<span class="text-danger">*</span>' : '(Optional)'; ?></h5>
                                
                                <div class="form-group">
                                    <label>Equipment Type</label>
                                    <select name="equipment[<?php echo $i; ?>][type_id]" class="form-control select2 equipment-type">
                                        <option value="">--Please Select--</option>
                                        <?php foreach($equipment_types as $type) { ?>
                                            <option value="<?php echo $type['equipment_type_id']; ?>"><?php echo $type['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Serial Number</label>
                                    <input type="text" name="equipment[<?php echo $i; ?>][serial_no]" class="form-control equipment-serial" placeholder="Enter Serial Number">
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>

            </div>

        </div>
    </div>
</div>

<script src="<?php echo base_url("js/jquery.gritter.js?".cssjs_ver()); ?>"></script>
<script src="<?php echo base_url("js/itelco/termination_form.js?".cssjs_ver()); ?>"></script>

<script>
    var sel_state_list = <?php echo json_encode($sel_state_list); ?>;
    var sel_building_list = <?php echo json_encode($sel_building_list); ?>;
</script>