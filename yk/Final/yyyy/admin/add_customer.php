<?php 
include "login.check.php";    
include "header.php"; 

$admin_name      = Mixedfn::levelnamechange("1","admin");
$subadmin_name   = Mixedfn::levelnamechange("1","subadmin");
$level4_name     = Mixedfn::levelnamechange("1","level4");
$level3_name     = Mixedfn::levelnamechange("1","level3");
$level2_name     = Mixedfn::levelnamechange("1","level2");
$level1_name     = Mixedfn::levelnamechange("1","level1");
?>
        
            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->
        
       <div class="content-page">
	         <div class="content">

                 <!-- Start Content-->	   
				 <div class="container-fluid">
            
						<!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>                                           
                                            <li class="breadcrumb-item active">Add Customer</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Add Customer</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

        
					<?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>							 
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">   
								
							  </button>
							</div>
							<?php
					  }?>
					<?php if (Session::exists('success')){
						?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
							  <strong>Well Done!</strong> <?=Session::Flush('success');?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">							 
								
							  </button>
							</div>
							<?php
					}?>
					
					
					<div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-body">

                                    <form action="route.php?action=addNewReseller" method="post" accept-charset="utf-8">

                                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">General</h5>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Customer Type <span class="text-danger">*</span></label>
                                            <select name="level" class="form-control select2" id="level">
                                                    <option value="4"><?php echo $subadmin_name; ?></option>                                               
                                                    <option value="3"><?php echo $level4_name; ?></option>
                                                    <option value="2"><?php echo $level3_name; ?></option>
                                                    <option value="1"><?php echo $level2_name; ?></option>
                                                    <option value="0"><?php echo $level1_name; ?></option>                                             
             
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-name" class="form-label">Username<span class="text-danger">*</span></label>
                                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-reference" class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                                        </div>
                                                                              

                                        <div class="mb-3">
                                            <label for="product-price">Pincode <span class="text-danger">*</span></label>
                                            <input type="password" name="pin" id="pin" class="form-control" id="product-price" placeholder="Enter Pincode" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" id="full_name" class="form-control" id="product-price" placeholder="Enter Fullname" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Phone Number <span class="text-danger">*</span></label>
                                            <input type="number" name="mobile" id="mobile" class="form-control" id="product-price" placeholder="Enter Phone Number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "11" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" id="email" class="form-control" id="product-price" placeholder="Enter Email Address" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "30" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Address <span class="text-danger">*</span></label>
                                            <input type="text" name="address" id="address" class="form-control" id="product-price" placeholder="Enter Customer Address" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>
                                        </div>

                                       
                                       
                                    </div> <!-- end card-body-->
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">
                                
                               

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-uppercase mt-0 mb-3 bg-light p-2">Service and Security</h5>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Recharge</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Drive</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Package</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Bill Pay</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">bKash</label>
                                        </div>
                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Rocket</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Nagad</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Upay</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">MNP</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Online Banking</label>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">
                                            <input class="form-check-input" type="checkbox" value="" id="customckeck1" checked>
                                            <label class="form-check-label" for="customckeck1">Global Recharge</label>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">OTP Type <span class="text-danger">*</span></label>
                                            <select name="otptype" class="form-control select2" id="otptype">                                                                                                 
                                                    <option value="1">Pin Code</option>
                                                    <option value="2">SMS</option>
                                                    <option value="3">Email</option>                                                                                               
             
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-category" class="form-label">Device Permission<span class="text-danger">*</span></label>
                                            <select name="deviceno" class="form-control select2" id="deviceno">                                                                                                 
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option> 
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>                                                                                                
             
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="product-price">Balance Limit <span class="text-danger">*</span></label>
                                            <input type="number" name="bllimit" id="bllimit" class="form-control" id="product-price" placeholder="Enter Customer Limit Balance" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" required>
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">                                           
                                            <input name="api_allow" class="form-check-input" type="checkbox" value="1" id="api_allow"> 
                                            <label class="form-check-label" for="customckeck1">Allow API</label>                                          
                                        </div>

                                        <div class="form-check mb-2 form-check-primary">                                           
                                            <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                            <label class="form-check-label" for="customckeck1">Active</label>                                          
                                        </div>

                                       
                                    </div> <!-- end card-body-->
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->

                       

                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Add Customer</button>                                                    
                        </div> 

                        </form>

                        <br>

                        <!-- end row -->
						
						
						
		 </div> <!-- container -->
	</div> <!-- content -->
          

<?php 
include "footer.php"; 
?>