<?php
    include "login.check.php";
    include "header.php"; 

    $query= "SELECT * from user where id='$us_id'";    
    
    $result = mysqli_query($connect, $query); 

    $reseller_data= mysqli_fetch_object($result);

    $full_name      = $reseller_data->fullname;
    $address        = $reseller_data->address;
    $email_address  = $reseller_data->email;
    $phone_number   = $reseller_data->phone;

?>
        
        
    <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="settings.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">settings</span> </div>
                    <p class="small text-white">Settigns</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="change_phone.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">phone_android</span> </div>
                    <p class="small text-white">Change Phone Number</p>
                    </a>
                </div>
                <div class="col-auto">
                <a href="help.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">receipt</span> </div>
                    <p class="small text-white">Contact</p>
                </a>
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
                            <div class="avatar avatar-40 bg-primary-light text-primary rounded mr-2"><span class="material-icons vm">add_location_alt</span></div>
                            Update Your Profile
                        </h6>
                    </div>

                    <form action="route.php?action=updateProfile" method="POST">
                    
                    <div class="card-body">
                        <div class="form-group float-label">
                        <label>Full Name:</label>
                            <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo "$full_name"; ?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "25">
                            
                        </div>
                        <div class="form-group float-label">
                            <label>Address</label>
                            <input type="text" name="address" id="address" class="form-control" value="<?php echo "$address"; ?>" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100">                                                        
                        </div>                                            
                        
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-block btn-default rounded">Update Profile</button>
                    </div>
                </div>
            </div>
        </div>
    </main>


<?php 
    include "footer.php"; 
?>