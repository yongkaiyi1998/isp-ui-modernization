<style>
th, td{
	font-size: 11px !important;
}

.input-group-addon{
	width: 110px;
}

.input-group{
	width: 100%;
}


</style>

<div class="container" >
	<div class="panel panel-default">

		<div class="panel-body" >
			<form type='post' enctype="multipart/form-data">
			<div class="category-border-main bg-success text-left">
				REGISTRATION FORM
			</div>

			<div style="padding-bottom:5px;">&nbsp;</div>
			
			<div class="category-border-main bg-success text-left">
				1. TYPE OF APPLICATION
			</div>
			
			<div class='row'>
				<div class="col-lg-12">
					<div class="row">
						<div class="col-lg-2">
							<div class="input-group">
								<input type="radio" name="type" /> Residential
							</div>
						</div>
						
						<div class="col-lg-2">
							<div class="input-group">
								<input type="radio" name="type" /> Business
							</div>
						</div>
					</div>
				</div>
			</div>

			<div style="padding-bottom:5px;">&nbsp;</div>
			<div class="category-border-main bg-success text-left">
				2. PACKAGE
			</div>
				
			<div class='row'>
			
				<div class="col-lg-12">
					<div class="row">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Building</span>
								<select class="col-lg-12" id="status" name="status">
									<option>-- Select --</option>
									<?php 
									foreach( $sel_building_list AS $val ){
										echo "<option value='".$val['building_no']."'>".$val['name']."</option>";
									}
									?>
								</select>
							</div>				
						</div>

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Package</span>
								<select class="col-lg-12" id="status" name="status">
									<option>-- Select --</option>
									<?php 
									foreach( $sel_package_list AS $val ){
										echo "<option value='".$val['package_no']."'>".$val['name']."</option>";
									}
									?>
								</select>
							</div>	
						</div>
					</div>
				</div>
			
			</div>

			<div style="padding-bottom:5px;">&nbsp;</div>
			<div class="category-border-main bg-success text-left">
				3. APPLICANT DETAIL
			</div>
				
			<div class='row'>
				<div class="col-lg-12">
					<div class="row">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Name</span>
								<input type="text" class="form-control" placeholder="" >
							</div>
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Username</span>
								<input type="text" class="form-control" placeholder="" />
							</div>				
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">DOB</span>
								<input type="text" class="form-control" placeholder="DD/MM/YY" />
							</div>				
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Password</span>
								<input type="text" class="form-control" placeholder="" />
							</div>				
						</div>

						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">NRIC / Passport</span>
								<input type="text" class="form-control" />
							</div>
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Attachment</span>
								<input type='file'  />
							</div>
						</div>
						
					</div>
				</div>
			
				<div class="col-lg-12">&nbsp;</div>
			
				<div class="col-lg-12">
					<div class="row">
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Installation Addr.</span>
								<textarea rows=6 style='width:100%;'></textarea>
							</div>
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">City</span>
								<input type="text" class="form-control" />
							</div>				
						</div>

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group"  >Postcode</span>
								<input type="text" class="form-control" />
							</div>	
						</div>
						
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">State</span>
								<input type="text" class="form-control" />
							</div>				
						</div>

						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon input_group">Country</span>
								<input type="text" class="form-control" />
							</div>	
						</div>
						
					</div>
				</div>
			
				<div class="col-lg-12">
					<div class="row">

						<div class="col-md-12">
							<div class="checkbox-inline">
								<label>
									<input id="subscribe" name="subscribe" type="checkbox">
									<div style='margin-top:8px'><span> I understand that by clicking on the 'Submit' button, I am allowing TM to contact me on Broadband related information, updates and promotions.  
									</span></div>
									
								</label>
							</div>
						</div>

						<div class="col-md-12">
							<div class="checkbox-inline">
								<label>
									<input id="terms" name="terms" type="checkbox">
									<div style='margin-top:8px'>
									<span>I have read and agreed to the Terms &amp; Conditions and Privacy Notice.</span>
									</div>
								</label>
							</div>
						</div>

						<div class="col-md-12 text-center">
							<input value="Submit" class="btn btn-orange btn-arrow" type="submit" />
						</div>

					</div>
				</div>
			
			</div>

				
<!--
				<div class="col-md-12">
					
					<input name="lister$txtNameSub" id="lister_txtNameSub" placeholder="Name" type="text">
				</div>
-->
			</div>
		</div>
		</form>
	</div>
	
</div>
