<?php 
include "login.check.php";
include "header.php";
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
                                            <li class="breadcrumb-item active">Others Company API Setup</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Others Company API Setup</h4>

                                    <!-- Error Code Start-->

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

                     <!-- Error Code End-->

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-6 col-xl-6">
                                <div class="card text-center">
                                    <div class="card-body">

                                    <ul class="nav nav-pills nav-fill navtab-bg">
                                            <li class="nav-item">
                                                <a href="#aboutme" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Mobile Banking
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Recharge
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#settings" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                                                    Bill Pay
                                                </a>
                                            </li>
                                        </ul>


                                    <div class="tab-content"> 

                                        <div class="tab-pane" id="aboutme">
    
                                        <?php 
                    
                                                $query = "SELECT * FROM api where id='5' and company='Others'";        
                                                $result = mysqli_query($connect, $query);

                                                if(!$result){
                                                    die("Query Failed!" . mysqli_error($connect));      
                                                }        
                                                
                                                $reseller_data  = mysqli_fetch_object($result); 

                                                $status         = $reseller_data->api_status;
                                                $request_url    = $reseller_data->request_url;
                                                $status_url     = $reseller_data->status_url;
                                                $balance_url    = $reseller_data->balance_url;
                                                $username       = $reseller_data->username;
                                                $ap_key         = $reseller_data->ap_key;

                                                $pera_user      = $reseller_data->pera_user;
                                                $pera_key       = $reseller_data->pera_key;
                                                $pera_number    = $reseller_data->pera_number;
                                                $pera_amount    = $reseller_data->pera_amount;
                                                $pera_type      = $reseller_data->pera_type;
                                                $pera_customone = $reseller_data->pera_customone;
                                                $pera_customtwo = $reseller_data->pera_customtwo;

                                        ?>


                                            <form action="route.php?action=APIUpdate" method="post" accept-charset="utf-8">
                                        
                                        <div class="text-start mt-3">

                                                    <h4 class="page-title">Parameters</h4> <br>

                                                    <input type="hidden" name="company" id="company" value="Others">                                                
                                                    <input type="hidden" name="apiid" id="apiid" value="5">

                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Username/UserID<span class="text-danger">*</span></label>
                                                        <input type="text" name="perauser" id="perauser" class="form-control" value="<?php echo $pera_user; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Key/Password<span class="text-danger">*</span></label>
                                                        <input type="text" name="perakey" id="perakey" class="form-control" value="<?php echo $pera_key; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Number<span class="text-danger">*</span></label>
                                                        <input type="text" name="peranumber" id="peranumber" class="form-control" value="<?php echo $pera_number; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                        <input type="text" name="peraamount" id="peraamount" class="form-control" value="<?php echo $pera_amount; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Type<span class="text-danger">*</span></label>
                                                        <input type="text" name="peratype" id="peratype" class="form-control" value="<?php echo $pera_type; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Custom 1 (Optional)</label>
                                                        <input type="text" name="peracustomone" id="apiusername" class="form-control" value="<?php echo $pera_customone; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Custom 2 (Optional)</label>
                                                        <input type="text" name="peracustomtwo" id="peracustomtwo" class="form-control" value="<?php echo $pera_customtwo; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div> 
                                                    <br>

                                                    <h4 class="page-title">Access Information</h4> <br>                                              
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">API Username<span class="text-danger">*</span></label>
                                                        <input type="text" name="apiusername" id="apiusername" class="form-control" value="<?php echo $username; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">API Key<span class="text-danger">*</span></label>
                                                        <input type="text" name="apikey" id="apikey" class="form-control" value="<?php echo $ap_key; ?>" placeholder="Enter API Key" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Request Link<span class="text-danger">*</span></label>
                                                        <input type="text" name="requestlink" id="requestlink" class="form-control" value="<?php echo $request_url; ?>" placeholder="Enter Website Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Status Check Link<span class="text-danger">*</span></label>
                                                        <input type="text" name="statuslink" id="statuslink" class="form-control" value="<?php echo $status_url; ?>" placeholder="Enter Status Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="product-name" class="form-label">Balance Check Link<span class="text-danger">*</span></label>
                                                        <input type="text" name="balancelink" id="balancelink" class="form-control" value="<?php echo $balance_url; ?>" placeholder="Enter Balance Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                    </div>

                                                    
                                            <?php                                  
                                                if ($status == 1 )
                                                {
                                            ?>

                                                    <div class="form-check mb-4 form-check-primary">                                           
                                                        <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                                        <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                    </div>  

                                            <?php
                                                }                                
                                            ?> 

                                            <?php                                  
                                                if ($status == 0 )
                                                {
                                            ?>

                                                    <div class="form-check mb-4 form-check-primary">                                           
                                                        <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                                        <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                    </div>  

                                            <?php
                                                }                                
                                            ?>                                                                                                  
                                                    
                                                    <div class="button-list pe-xl-4 d-grid">
                                                        <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                                                    </div> 

                                                

                                        </form>
                                        
                                            </div> 



                                        </div> <!-- end tab-pane -->
                                        <!-- end about me section content -->

                                        <div class="tab-pane show active" id="timeline">

                                        <?php 
                    
                                        $query = "SELECT * FROM api where id='4' and company='Others'";        
                                        $result = mysqli_query($connect, $query);

                                        if(!$result){
                                            die("Query Failed!" . mysqli_error($connect));      
                                        }        
                                        
                                        $reseller_data  = mysqli_fetch_object($result); 

                                        $status         = $reseller_data->api_status;
                                        $request_url    = $reseller_data->request_url;
                                        $status_url     = $reseller_data->status_url;
                                        $balance_url    = $reseller_data->balance_url;
                                        $username       = $reseller_data->username;
                                        $ap_key         = $reseller_data->ap_key;

                                        $pera_user      = $reseller_data->pera_user;
                                        $pera_key       = $reseller_data->pera_key;
                                        $pera_number    = $reseller_data->pera_number;
                                        $pera_amount    = $reseller_data->pera_amount;
                                        $pera_type      = $reseller_data->pera_type;
                                        $pera_customone = $reseller_data->pera_customone;
                                        $pera_customtwo = $reseller_data->pera_customtwo;

                                        ?>

    
                                        <form action="route.php?action=APIUpdate" method="post" accept-charset="utf-8">
                                       
                                       <div class="text-start mt-3">

                                       <h4 class="page-title">Parameters</h4> <br>

                                                <input type="hidden" name="company" id="company" value="Others">                                                
                                                <input type="hidden" name="apiid" id="apiid" value="4">

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Username/UserID<span class="text-danger">*</span></label>
                                                    <input type="text" name="perauser" id="perauser" class="form-control" value="<?php echo $pera_user; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Key/Password<span class="text-danger">*</span></label>
                                                    <input type="text" name="perakey" id="perakey" class="form-control" value="<?php echo $pera_key; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Number<span class="text-danger">*</span></label>
                                                    <input type="text" name="peranumber" id="peranumber" class="form-control" value="<?php echo $pera_number; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                    <input type="text" name="peraamount" id="peraamount" class="form-control" value="<?php echo $pera_amount; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Type<span class="text-danger">*</span></label>
                                                    <input type="text" name="peratype" id="peratype" class="form-control" value="<?php echo $pera_type; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Custom 1 (Optional)</label>
                                                    <input type="text" name="peracustomone" id="apiusername" class="form-control" value="<?php echo $pera_customone; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Custom 2 (Optional)</label>
                                                    <input type="text" name="peracustomtwo" id="peracustomtwo" class="form-control" value="<?php echo $pera_customtwo; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div> 
                                                <br>

                                                 <h4 class="page-title">Access Information</h4> <br>                                              
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">API Username<span class="text-danger">*</span></label>
                                                    <input type="text" name="apiusername" id="apiusername" class="form-control" value="<?php echo $username; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">API Key<span class="text-danger">*</span></label>
                                                    <input type="text" name="apikey" id="apikey" class="form-control" value="<?php echo $ap_key; ?>" placeholder="Enter API Key" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Request Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="requestlink" id="requestlink" class="form-control" value="<?php echo $request_url; ?>" placeholder="Enter Website Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Status Check Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="statuslink" id="statuslink" class="form-control" value="<?php echo $status_url; ?>" placeholder="Enter Status Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Balance Check Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="balancelink" id="balancelink" class="form-control" value="<?php echo $balance_url; ?>" placeholder="Enter Balance Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>

                                                
                                        <?php                                  
                                            if ($status == 1 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?> 

                                          <?php                                  
                                            if ($status == 0 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?>                                                                                                  
                                                
                                                <div class="button-list pe-xl-4 d-grid">
                                                    <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                                                </div> 

                                               

                                   </form>
                                       
                                           </div> 
                                           

                                        </div> <!-- end tab-pane -->
                                        <!-- end timeline section content -->

                                        <div class="tab-pane" id="settings">
    
                                       
                                        <?php 
                    
                                            $query = "SELECT * FROM api where id='6' and company='Others'";        
                                            $result = mysqli_query($connect, $query);

                                            if(!$result){
                                                die("Query Failed!" . mysqli_error($connect));      
                                            }        
                                            
                                            $reseller_data  = mysqli_fetch_object($result); 

                                            $status         = $reseller_data->api_status;
                                            $request_url    = $reseller_data->request_url;
                                            $status_url     = $reseller_data->status_url;
                                            $balance_url    = $reseller_data->balance_url;
                                            $username       = $reseller_data->username;
                                            $ap_key         = $reseller_data->ap_key;

                                            $pera_user      = $reseller_data->pera_user;
                                            $pera_key       = $reseller_data->pera_key;
                                            $pera_number    = $reseller_data->pera_number;
                                            $pera_amount    = $reseller_data->pera_amount;
                                            $pera_type      = $reseller_data->pera_type;
                                            $pera_customone = $reseller_data->pera_customone;
                                            $pera_customtwo = $reseller_data->pera_customtwo;

                                        ?>


                            <form action="route.php?action=APIUpdate" method="post" accept-charset="utf-8">
                        
                        <div class="text-start mt-3">

                            <h4 class="page-title">Parameters</h4> <br>

                            <input type="hidden" name="company" id="company" value="Others">                                                
                            <input type="hidden" name="apiid" id="apiid" value="6">

                            <div class="mb-3">
                                <label for="product-name" class="form-label">Username/UserID<span class="text-danger">*</span></label>
                                <input type="text" name="perauser" id="perauser" class="form-control" value="<?php echo $pera_user; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Key/Password<span class="text-danger">*</span></label>
                                <input type="text" name="perakey" id="perakey" class="form-control" value="<?php echo $pera_key; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Number<span class="text-danger">*</span></label>
                                <input type="text" name="peranumber" id="peranumber" class="form-control" value="<?php echo $pera_number; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                <input type="text" name="peraamount" id="peraamount" class="form-control" value="<?php echo $pera_amount; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Type<span class="text-danger">*</span></label>
                                <input type="text" name="peratype" id="peratype" class="form-control" value="<?php echo $pera_type; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Custom 1 (Optional)</label>
                                <input type="text" name="peracustomone" id="apiusername" class="form-control" value="<?php echo $pera_customone; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Custom 2 (Optional)</label>
                                <input type="text" name="peracustomtwo" id="peracustomtwo" class="form-control" value="<?php echo $pera_customtwo; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div> 
                            <br>

                             <h4 class="page-title">Access Information</h4> <br>                                              
                            <div class="mb-3">
                                <label for="product-name" class="form-label">API Username<span class="text-danger">*</span></label>
                                <input type="text" name="apiusername" id="apiusername" class="form-control" value="<?php echo $username; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                            </div>

                            <div class="mb-3">
                                <label for="product-name" class="form-label">API Key<span class="text-danger">*</span></label>
                                <input type="text" name="apikey" id="apikey" class="form-control" value="<?php echo $ap_key; ?>" placeholder="Enter API Key" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Request Link<span class="text-danger">*</span></label>
                                <input type="text" name="requestlink" id="requestlink" class="form-control" value="<?php echo $request_url; ?>" placeholder="Enter Website Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                            </div>
                            <div class="mb-3">
                                <label for="product-name" class="form-label">Status Check Link<span class="text-danger">*</span></label>
                                <input type="text" name="statuslink" id="statuslink" class="form-control" value="<?php echo $status_url; ?>" placeholder="Enter Status Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                            </div>

                            <div class="mb-3">
                                <label for="product-name" class="form-label">Balance Check Link<span class="text-danger">*</span></label>
                                <input type="text" name="balancelink" id="balancelink" class="form-control" value="<?php echo $balance_url; ?>" placeholder="Enter Balance Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                            </div>

                            
                    <?php                                  
                        if ($status == 1 )
                        {
                    ?>

                            <div class="form-check mb-4 form-check-primary">                                           
                                <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                <label class="form-check-label" for="customckeck1">Active</label>                                          
                            </div>  

                    <?php
                        }                                
                     ?> 

                      <?php                                  
                        if ($status == 0 )
                        {
                    ?>

                            <div class="form-check mb-4 form-check-primary">                                           
                                <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                <label class="form-check-label" for="customckeck1">Active</label>                                          
                            </div>  

                    <?php
                        }                                
                     ?>                                                                                                  
                            
                            <div class="button-list pe-xl-4 d-grid">
                                <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                            </div> 

                           

               </form>
                   
                       </div> 

                    


                                        </div> <!-- end tab-pane -->
                                        <!-- end about me section content -->

                                    
                                    </div> <!-- end tab-content -->

                    
                                    </div>                                 
                                </div> <!-- end card -->

                            </div> <!-- end col-->

                            <div class="col-lg-6 col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-pills nav-fill navtab-bg">
                                           
                                            <li class="nav-item">
                                                <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Help
                                                </a>
                                            </li>
                                            
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane" id="aboutme">
    
                                                No Data


    
                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->
    
                                            <div class="tab-pane show active" id="timeline">
    
                                             
                                               Comming Soon....
    
                                            </div>
                                            <!-- end timeline content-->
    
                                            <div class="tab-pane" id="settings">


                                                 <!-- Start Mobile Banking Payment History Table-->


                                                

                    <!-- End Mobile Banking Payment History Table-->



                                            </div>
                                            <!-- end settings content-->
    
                                        </div> <!-- end tab-content -->
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                    </div> <!-- container -->

                </div> <!-- content -->


    

<!-- Recharge Request modal start-->
<div id="confirmation-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Recharge Request Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>   
                    
        <form action="route.php?action=requestSend" role="form" method="post" accept-charset="utf-8">
              
            <div class="modal-body" id="load_request_data">
                                                       
            </div>    
            
            
                <div class="modal-footer">
					<button type="submit" class="btn btn-success">Next</button>
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
				</div>
        </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Request modal End-->



<?php    
include "footer-data.php";
?>
