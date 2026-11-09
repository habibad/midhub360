<?php 
include "login.check.php";    
include "header.php"; 

$level4_name     = Mixedfn::levelnamechange("1","level4");
$level3_name     = Mixedfn::levelnamechange("1","level3");
$level2_name     = Mixedfn::levelnamechange("1","level2");
$level1_name     = Mixedfn::levelnamechange("1","level1");
?>
        
        
        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="customer_list.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">groups</span> </div>
                    <p class="small text-white">Customer List</p>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">payments</span> </div>
                    <p class="small text-white">Give Payment</p>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">receipt</span> </div>
                    <p class="small text-white">Payment Hist.</p>
                </div>                
            </div>
        </div>
        
        
        <!-- page content start -->

        <div class="main-container">

        <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>
        </div>
            
        <div class="container">

            <?php if (Session::exists('error')){
									?>
									<div class="alert alert-danger alert-dismissible fade show" role="alert">
									   <?=Session::Flush('error');?>
									  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									  </button>
									</div>
									<?php
							  }?>
							<?php if (Session::exists('success')){
								?>
									<div class="alert alert-success alert-dismissible fade show" role="alert">
									  <strong>Well Done!</strong> <?=Session::Flush('success');?>
									  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									  </button>
									</div>
									<?php
							}?>

                <div class="card">
                    <div class="card-header">
                        <h6 class="subtitle mb-0">
                            <div class="avatar avatar-40 bg-primary-light text-primary rounded mr-2"><span class="material-icons vm">person_add</span></div>
                            Add Customer
                        </h6>
                    </div>
                    <div class="card-body">

                    <form action="route.php?action=addNewReseller" method="post" accept-charset="utf-8">

                        <?php                                  
                            if ($level == 4 )
                            {
                        ?>

                        <div class="form-group">
                            <label class="form-control-label">Customer Type</label>
                            <select name="level" class="form-control" id="level">
                                <option value="3"><?php echo $level4_name; ?></option>
                                <option value="2"><?php echo $level3_name; ?></option>
                                <option value="1"><?php echo $level2_name; ?></option>
                                <option value="0"><?php echo $level1_name; ?></option>                                                          
                            </select>
                        </div>
                        <?php
                        }                                
                        ?> 

                        <?php                                  
                            if ($level == 3 )
                            {
                        ?>

                        <div class="form-group">
                            <label class="form-control-label">Customer Type</label>
                            <select name="level" class="form-control" id="level">                                
                                <option value="2"><?php echo $level3_name; ?></option>
                                <option value="1"><?php echo $level2_name; ?></option>
                                <option value="0"><?php echo $level1_name; ?></option>                                                            
                            </select>
                        </div>
                        <?php
                        }                                
                        ?> 

                        <?php                                  
                            if ($level == 2 )
                            {
                        ?>

                        <div class="form-group">
                            <label class="form-control-label">Customer Type</label>
                            <select name="level" class="form-control" id="level">                           
                                <option value="1"><?php echo $level2_name; ?></option>
                                <option value="0"><?php echo $level1_name; ?></option>                                                          
                            </select>
                        </div>
                        <?php
                        }                                
                        ?> 

                        <?php                                  
                            if ($level == 1 )
                            {
                        ?>

                        <div class="form-group">
                            <label class="form-control-label">Customer Type</label>
                            <select name="level" class="form-control" id="level">             
                                <option value="0"><?php echo $level1_name; ?></option>                                                          
                            </select>
                        </div>
                        <?php
                        }                                
                        ?>     

                        <div class="form-group float-label">
                            <input type="text" name="username" id="username" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Username<span class="field-mando">*</span></label>
                        </div>
                        <div class="form-group float-label">
                            <input type="password" name="password" id="password" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                            <label class="form-control-label">Password<span class="field-mando">*</span></label>                            
                        </div>                         
                        <div class="form-group float-label">
                            <input type="password" name="pin" id="pin" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                            <label class="form-control-label">Pin Code<span class="field-mando">*</span></label>                            
                        </div>                         

                        <div class="form-group float-label">
                            <input type="text" name="full_name" id="full_name" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                            <label class="form-control-label">Full Name<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="form-group float-label">
                            <input type="number" name="mobile" id="mobile" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                            <label class="form-control-label">Phone Number<span class="field-mando">*</span></label>                            
                        </div>
                        <div class="form-group float-label">
                            <input type="email" name="email" id="email" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>
                            <label class="form-control-label">Email Address<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="form-group float-label">
                            <input type="text" name="address" id="address" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>
                            <label class="form-control-label">Address<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="form-group float-label">
                            <input type="text" name="limitbl" id="limitbl" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>
                            <label class="form-control-label">Balance Limit<span class="field-mando">*</span></label>                            
                        </div>

                        <div class="input-group-text">
                            <input type="checkbox" name="api_allow" value="1"> <span style="padding-left: 5px;">API Allow<span>
                            
                        </div>    <br>

                        <div class="input-group-text">
                            <input type="checkbox" name="active" value="1" checked> <span style="padding-left: 5px;">Active<span>
                            
                        </div>   
                         

                    </div>
                    
                    <div class="card-footer">                    
                        <button type="submit" class="btn btn-block btn-default rounded">Add Customer</button>
                    </div>
                </div>
            </div>
        </div>
    </main>


<?php 
    include "footer.php"; 
?>