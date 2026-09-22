<style>

html {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

#editSignatureModal,
#editEquipmentModal {
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

@media (max-width:991px) {

    .modal-dialog.modal-lg {
        width:95% !important;
        margin:10px auto !important;
        left:0 !important;
        right:0 !important;
    }
}

#sig-canvas{
    max-width:100%;
    height:auto;
}

@media (max-width:992px){

    .book .page{
        width:100% !important;
        padding:10px !important;
        overflow-x:hidden;
    }

    .span-inline{
        min-width:50% !important;
        width:auto !important;
    }

    .col-lg-12,
    .col-lg-5{
        padding-left:0 !important;
        padding-right:0 !important;
    }

    table{
        width:100% !important;
        table-layout:fixed;
    }

}

.td-label{
    padding-right:10px;
    font-size:14px;
}

.td-input{
    border:1px solid #000;
    height:27px;
    font-size:12px;
    padding-left:5px;
}

.td-textarea{
    border:1px solid #000;
    height:3em;
    font-size:12px;
    padding-left:5px;
    vertical-align:top;
}

.div-input{
    border:1px solid #000;
    width:25px;
    height:27px;
    font-size:14px;
    padding-left:10px;
    padding-top:3px;
    padding-right:10px;
    clear:both;
    float:left;
}

.div-input-right{
    border:1px solid #000;
    width:25px;
    height:27px;
    font-size:14px;
    padding-left:10px;
    padding-top:3px;
    padding-right:10px;
    clear:both;
    float:right;
}

.span-inline{
    font-size:12px;
    min-width:200px;
    display:inline-block;
    border-bottom:1px solid #000;
}

.page h4{
    background:#08b454;
    color:#fff;
    margin-top:5px;
    margin-bottom:5px;
    padding:3px;
    font-size:12px;
}

@media print{

    body,
    html{
        background:#fff !important;
    }

    .page{
        background:#fff !important;
        min-height:296mm;
        max-height:296mm;
    }

    .modal,
    .modal-backdrop,
    #editSignatureModal,
    .btn,
    .no-print,
    canvas,
    #editEquipmentModal{
        display:none !important;
        visibility:hidden !important;
    }

    a[href]:after{
        content:none !important;
    }

}
</style>

<div class="book" >	
<?php if(!empty($data)):?>	
		<div class="page" data-page="{data}" > 
			<div ><!-- class="subpage" -->

				<div class="col-lg-12">
				<table style="width:100%;">
					<tr>
						<td style="width:50%;">
							<strong>{comp_name}</strong><br />
							<span style="font-size:10px;">
							{company_addr_1}<br />
							{company_addr_2}<br />
							<?php if (!empty($company_addr_3)) { echo $company_addr_3."<br />"; } ?>
							<?php if (!empty($company_city)) { echo $company_city.","; } ?> <?php if (!empty($company_postal)) { echo $company_postal.","; } ?> Penang<br />
							<?php if (!empty($company_phone)) { echo "Call us +".$company_phone; } ?>
							</span>
						</td>
						<td style="width:50%;text-align:right;">
							<div>
								<?php
								$company_logo = $this->config->item('logo_img'); 
								?>
								<?php if (!empty($company_logo)) { ?>
								<img style="width:150px;" src="<?php echo $company_logo; ?>" >
								<?php } else { ?>
								<img src="<?php echo base_url("/images/telco-icon.png"); ?>" >ITELCO
								<?php } ?>
							</div>
						</td>
					</tr>
				</table>
				</div>

				<div class="col-lg-12 content_double_line"></div>
				<div class="col-lg-12"><h4>TROUBLE TICKET FORM</h4></div>
				<!--
				<div class="col-lg-4 text-center"><h4 style='background-color:lightgrey;'><?php echo $data['cs_no']; ?></h4></div>
				-->
				<div class="col-lg-4"><h4>INFORMATION</h4></div>
				<div class="col-lg-12 content_body">
					<div class="col-lg-12">
					<table style='width:100%;border-spacing:5px;'>
						
						<tr>
							<td class="text-right td-label" style='width:20%;'>Name / Company </td>
							<td class="td-input" style='width:70%;' colspan=3><?php echo $data['customer_name'];?></td>
						</tr>

						<tr>
							<td class="text-right td-label" style='width:20%;'>Customer ID</td>
							<td class="td-input" style='width:30%;'><?php echo $data['customer_no'];?></td>
							<td class="text-right td-label" style='width:20%;'>Contact Number</td>
							<td class="td-input" style='width:30%;'><?php echo $data['contact_no']; ?></td>
						</tr>
						
						<tr>
							<td class="text-right td-label" style='width:20%;vertical-align:top;'>Site Address </td>
							<td colspan="3" class="td-textarea" style='width:80%;'>
								<?php echo $data['customer_addr']; ?>
							</td>
						</tr>
						
						<tr>
							<td class="text-right td-label" style='width:20%;'>Report Date </td>
							<td class="td-input" style='width:30%;'>
								<?php echo $data['report_on'] != '-' ? date('Y-m-d', strtotime($data['report_on']) ) : ''; ?>
							</td>
							<td class="text-right td-label" style='width:20%;'>Report Time</td>
							<td class="td-input" style='width:30%;'>
								<?php echo $data['report_on'] != '-' ? date('H:i:s', strtotime($data['report_on']) ) : ''; ?>
							</td>
						</tr>
						
						<tr>
							<td class="text-right td-label" style='width:20%;'>On Site Date </td>
							<td class="td-input" style='width:30%;'>
								<?php echo $data['onsite_on'] != '-' ? date('Y-m-d', strtotime($data['onsite_on']) ) : '' ; ?>
							</td>
							<td class="text-right td-label" style='width:20%;'>On Site Time</td>
							<td class="td-input" style='width:30%;border: 1px solid black;height:1.5em;'>
								<?php echo $data['onsite_on'] != '-' ? date('H:i:s', strtotime($data['onsite_on']) ) : ''; ?>
							</td>
						</tr>


						<tr>
							<td class="text-right td-label" style='vertical-align:top;width:20%;'>Type of Services </td>
							<td colspan="3" style='width:80%;'>
								
								<div style="width:32%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_type'] == 3 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Attend Complaint </span>
								</div>
								</div>
								</div>
								
								<div style="width:31%;float:left;">

								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_type'] == 4 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Follow Up </span>
								</div>
								</div>
								
								</div>
								
								<div style="width:37%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input">
									<?php 
										if( $data['service_type'] == 1 ){ echo "/" ; $remark = "New Installation"; }
										if( $data['service_type'] == 2 ){ echo "/" ; $remark = "Relocation"; }
										if( $data['service_type'] == 5 ){ echo "/" ; $remark = "Termination"; }
									?>
								</div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Others : </span>
								<span class="span-inline" style="min-width:110px;"><?php echo $remark ?? ''; ?></span>
								</div>
								</div>
								</div>
								
							</td>
						</tr>
					</table>
					</div>
					
					<div class="col-lg-4"><h4>WORK DETAILS</h4></div>
					
					<div class="col-lg-12">
					<table style='width:100%;border-spacing:5px;'>
						<tr>
							<td class="text-right td-label" style='width:20%;'> </td>
							<td colspan="3" style='width:80%;'>
								<div style="width:100%;">
								<div class="div-input">
								<?php echo $data['premises_type'] == 'r' ? '/' : ''; ?>
								</div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Residential Package : </span>
								<span class="span-inline">
								<?php echo $data['premises_type'] == 'r' ? $data['package_name'] : '' ; ?>
								</span>
								</div>
								</div>
								
								<div style="width:100%;padding-top: 5px;">
								<div class="div-input">
								<?php echo $data['premises_type'] == 'c' ? '/' : ''; ?>
								</div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Business Package : </span>
								<span class="span-inline" style="margin-left:10px;">
								<?php echo $data['premises_type'] == 'c' ? $data['package_name'] : '' ; ?>
								</span>
								</div>
								</div>
								
							</td>
						</tr>
						
						<tr>
							<td class="text-right td-label" style='vertical-align:top;width:20%;'>Reported Issue </td>
							<td colspan="3" style='width:80%;'>
								
								<div style="width:32%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_remark'] == 2 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Cable </span>
								</div>
								</div>
								</div>
								
								<div style="width:31%;float:left;">

								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_remark'] == 1 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Media Converter </span>
								</div>
								</div>
								
								</div>
								
								<div style="width:37%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_remark'] == 5 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Network (Customer side) </span>
								</div>
								</div>
								</div>

								<div style="width:32%;float:left;padding-top:5px;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_remark'] == 4 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Network (Provider side) </span>
								</div>
								</div>
								</div>
								
								<div style="width:31%;float:left;padding-top:5px;">

								<div style="width:100%;">
								<div class="div-input"><?php echo $data['service_remark'] == 6 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">CPE</span>
								</div>
								</div>
								
								</div>
								
								<div style="width:37%;float:left;padding-top:5px;">
								
								<div style="width:100%;">
								<div class="div-input">
								<?php echo $data['service_remark'] == '4' ? '/' : ''; ?>
								</div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Others : </span>
								<span class="span-inline" style="min-width:110px;">
								</span>
								</div>
								</div>
								</div>
								<!--
								<div style="width:40%;float:left;">
								
									<div style="width:100%;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '2' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">Cable </span>
									</div>
									</div>
									
									<div style="width:100%;padding-top: 5px;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '4' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">Network (Provider side)</span>
									</div>
									</div>
									
									<div style="width:100%;padding-top: 5px;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '6' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">CPE</span>
									</div>
									</div>
								
								</div>
								
								<div style="width:55%;float:left;">
								
									<div style="width:100%;padding-top: 5px;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '1' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">Media Converter </span>
									</div>
									</div>
									
									<div style="width:100%;padding-top: 5px;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '5' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">Network (Customer side)</span>
									</div>
									</div>
									
									<div style="width:100%;padding-top: 5px;">
									<div class="div-input">
									<?php echo $data['service_remark'] == '4' ? '/' : ''; ?>
									</div>
									<div>
									<span style="font-size:12px;padding:0px 10px 0px 10px;">Others :</span>
									<span class="span-inline">
									<?php echo $data['service_remark'] == '4' ? '' : '' ; ?>
									</span>
									</div>
									</div>
								
								</div>
								-->
							</td>
						</tr>
						
						<tr>
							<td class="text-right td-label" style='width:20%;vertical-align:top;'>
								Action Taken
							</td>
							<td class="td-input" colspan="3" style='width:80%;height:3.8em;vertical-align:top;'>
								<?php echo $data['action_remark']; ?>
							</td>
						</tr>

					</table>
					
					<table style="padding-top:5px;font-size:12px;width:98.5%;margin-left:5px;">
					<tr>
						<td class="td-input" style="width:46%;padding:5px;text-align:center;">
							Speed Test
						</td>
						<td class="td-input text-center" style="width:30%;padding:5px;">http://www.speedtest.com.my</td>
						<td class="td-input" style="width:25%;padding:5px 5px 5px 35px;text-align:right;">Download (Kbps)<br/>Upload (Kbps)<br/>Latency (ms)</td>
						<td class="td-input" style="width:25%;padding:5px 5px 5px 20px;"> 
							<p style="width: 200px; display: table; margin: 0px;">
								<span class='text-center' style="display: table-cell; border-bottom: 1px solid black;"><?php echo $data['test_download']; ?></span>
							</p>

							<p style="width: 200px; display: table; margin: 0px;">
								<span class='text-center' style="display: table-cell; border-bottom: 1px solid black;"><?php echo $data['test_upload']; ?></span>
							</p>

							<p style="width: 200px; display: table; margin: 0px;">
								<span class='text-center' style="display: table-cell; border-bottom: 1px solid black;"><?php echo $data['test_latency']; ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<td class="td-input" style="width:20%;padding:5px;text-align:center;">
							Internet
						</td>
						<td class="td-input text-center" style="width:30%;padding:5px;">www.google.com</td>
						<td class="td-input" style="width:50%;padding:5px 5px 5px 35px;text-align:center;" colspan=2>
							<span <?php echo $data['open_website'] == 0 ? 'style="text-decoration: line-through;"' : ''; ?> >Yes</span> / <span <?php echo $data['open_website'] == 1 ? 'style="text-decoration: line-through;"' : ''; ?>>No</span>
						</td>
					</tr>
					</table>
					
					<table style='width:100%;border-spacing:5px;padding-top:5px;'>
						<tr>
							<td class="text-left td-label" style='width:20%;text-align:right;'>Status </td>
							<td colspan="3" style='width:80%;'>
								
								<div style="width:15%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['cs_status'] == 3 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Closed </span>
								</div>
								</div>
								</div>

								<div style="width:20%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['cs_status'] == 1 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">In Progress </span>
								</div>
								</div>
								</div>
								
								<div style="width:15%;float:left;">

								<div style="width:100%;">
								<div class="div-input"><?php echo $data['cs_status'] == 2 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">KIV </span>
								</div>
								</div>
								
								</div>
								
								<div style="width:37%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input">
								</div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Others : </span>
								<span class="span-inline" style="min-width:110px;"></span>
								</div>
								</div>
								</div>
								<!--
								<div style="width:35%;float:left;">
								<?php if( $data['cs_status'] == '1' ){ ?>
								<i class="ace-icon fa fa-dot-circle-o red"></i>
								<?php }else{ ?>
								<i class="ace-icon fa fa-circle-o red"></i>	
								<?php } ?>
								<span style="font-size:12px;padding:0px 40px 0px 10px;">Closed </span>
								</div>
								
								<div style="width:65%;float:left">
								<?php if( $data['cs_status'] == '0' ){ ?>
								<i class="ace-icon fa fa-dot-circle-o red"></i>
								<?php }else{ ?>
								<i class="ace-icon fa fa-circle-o red"></i>	
								<?php } ?>
								<span style="font-size:12px;padding:0px 40px 0px 10px;">Escalate to 3rd Level Support </span>
								</div>
								-->
							</td>
						</tr>
						
					
						<tr>
							<td class="text-right td-label" style='width:20%;vertical-align:top;'>
								Support Remark
							</td>
							<td class="td-input" colspan="3" style='width:80%;height:3.8em;vertical-align:top;'>
								
							</td>
						</tr>
					
						
						<tr>
							<td class="text-left td-label" style='width:20%;text-align:right;'>Date : </td>
							<td colspan="3" style='width:80%;'>
								<div style="width:40%;float:left;font-size:12px;">
								&nbsp;
								</div>
								<div style="width:37%;float:left;font-size:12px;">
								Time :
								</div>
							</td>
						</tr>
						<tr>
							<td class="text-left td-label" style='width:20%;text-align:right;'>Attending Techician : </td>
							<td colspan="3" style='width:80%;'>
								<div style="width:40%;float:left;font-size:12px;">
								&nbsp;
								</div>
								<div style="width:37%;float:left;font-size:12px;">
								Signature :
								</div>
							</td>
						</tr>
						</table>
					</div>	
					
					<div class="col-lg-4"><h4>Customer Sign Off</h4></div>
					
					<div class="col-lg-12">
						<table style='width:100%;border-spacing:5px;'>
						<tr>
							<td class="text-left td-label" style='width:20%;text-align:right;'>Comment</td>
							<td colspan="3" style='width:80%;'>
								
								<div style="width:32%;float:left;">
								
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['customer_comment'] == 1 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Excellent </span>
								</div>
								</div>
								</div>
								
								<div style="width:31%;float:left;">

								<div style="width:100%;">
								<div class="div-input"><?php echo $data['customer_comment'] == 2 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Good </span>
								</div>
								</div>
								
								</div>
								
								<div style="width:37%;float:left;">
								<div style="width:100%;">
								<div class="div-input"><?php echo $data['customer_comment'] == 3 ? '/' : ''; ?></div>
								<div>
								<span style="font-size:12px;padding:0px 10px 0px 10px;">Poor </span>
								</div>
								</div>
								</div>
								
							</td>
						</tr>
						
						<tr>
							<td>&nbsp;</td>
							<td class="text-left td-input" style='width:100%;height:3.8em'>
								<?php echo $data['customer_remark']; ?>
							</td>
						</tr>
					
					</table>
					
					
					
					<table style='width:100%;border-spacing:5px;'>
						<tr>
							<td  style="width:70%;font-size:12px;">
								<br />
								______________________________<br />
								Customer Signature / Date<br />
								Name <br/>
								I/C No
							</td>
							<td style="width:30%;font-size:12px;text-align:right;vertical-align:bottom;border:1px solid;" >
								Company Stamp (If applicable)
							</td>
						</tr>
					</table>
					
					
					</div>
				</div>
			
			</div>
		</div>		
<?php endif; ?>	
</div>
