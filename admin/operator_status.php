<?php 
include "login.check.php";
include "header.php";


                $query = "SELECT * FROM settings where id='1'";        
                $result = mysqli_query($connect, $query);

                if(!$result){
                    die("Query Failed!" . mysqli_error($connect));      
                }        
                
                $reseller_data      = mysqli_fetch_object($result); 

                $recharge_low       = $reseller_data->recharge_low;
                $mbank_low          = $reseller_data->mbank_low;
                $pin_expire         = $reseller_data->pin_expire;
                $pass_expire        = $reseller_data->pass_expire;
                $recharge_min       = $reseller_data->recharge_min;
                $recharge_max       = $reseller_data->recharge_max;
                $mbank_min          = $reseller_data->mbank_min;
                $mbank_max          = $reseller_data->mbank_max;
               
               
    
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
                                            <li class="breadcrumb-item active">Operator On/Off</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Operator On/Off</h4>
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

                                    <form action="route.php?action=OperatorOnOff" method="post" accept-charset="utf-8">


                                    <div class="mb-3">
                                            <label for="product-category" class="form-label">Select Operator<span class="text-danger">*</span></label>
                                            <select name="operator" class="form-control select2" id="operator">                                                   
                                                    <option value="017">Grameenphone</option>
                                                    <option value="018">Robi</option>
                                                    <option value="019">Banglalink</option> 
                                                    <option value="016">Airtel</option>
                                                    <option value="015">Teletalk</option>                                               
                                           
                                            </select>
                                        </div>
                                       
                                            <div class="mb-3">
                                                <label class="mb-2">Status <span class="text-danger">*</span></label>
                                                <br/>
                                                    <div class="form-check-inline">
                                                        <input type="radio" id="customRadio1" name="active" class="form-check-input" value="1" checked="">
                                                        <label class="form-check-label" for="customRadio1">On</label>
                                                    </div>
                                                    <div class="form-check-inline">
                                                        <input type="radio" id="customRadio2" name="active" class="form-check-input" value="0">
                                                        <label class="form-check-label" for="customRadio2">Off</label>
                                                    </div>
                                            </div>


                                        <div class="mb-3">
                                            <label class="form-label">Opening Date</label>
                                            <input type="text" name="date" id="basic-datepicker" class="form-control" placeholder="Pick a Date">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Opening Time</label>
                                            <input type="text" name="time" id="preloading-timepicker" class="form-control" placeholder="Pick a Time">
                                        </div>
                                                                            
                                        
                                        <div class="mb-3">
                                            <label for="product-price">Message <span class="text-danger">*</span></label>
                                            <input type="text" name="msg" id="msg" class="form-control" placeholder="Enter Message">
                                        </div>


                                        <div class="button-list pe-xl-4 d-grid">
                            <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Update</button>                                                    
                        </div> 
                                       
                                       
                                    </div>
                                </div> <!-- end card -->
                            </div> <!-- end col -->

                            <div class="col-lg-6">                              
                               
                                <div class="card">
                                    <div class="card-body">                                       
                                      
                                       <h3>Operator Status</h3>   



                                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                        <th>Serial</th>
                                                        <th>Operator Name</th>                                                                                                        
                                                        <th>Status</th>                                                                                                   
                                                                                                        
                                                </tr>
                                            </thead>

                            <tbody>
                                                                              
                            <?php
							$userlevel = Session::get('level');
							$us_id = Session::get('us_id');
							$i=0;
                            $c=0;
							$query="SELECT * from operator_status order by id desc";
                            $result = mysqli_query($connect, $query);    
                            if(!$result){
                        
                                die("Query Failed2!" . mysqli_error($connect));
                            }
							while($row=mysqli_fetch_object($result))
							{
								
                                $c++;
								?>
                                        
                                        
                                                <tr>
                                                <td><?php echo $c; ?></td>                                                
                                                                                         
                                              <td>                                              
                                              <?php
											  if($row->extension == '017'):
                                                echo 'Grameenphone';
											  elseif($row->extension == '018'):
                                                echo 'Robi';
                                              elseif($row->extension == '019'):
                                                echo 'Banglalink';
                                               elseif($row->extension == '016'):
                                                echo 'Airtel';
                                               elseif($row->extension == '015'):
                                                  echo 'Teletalk';
                                               elseif($row->extension == '014'):
                                                 echo 'Banglalink'; 
                                                elseif($row->extension == '013'):
                                                  echo 'Grameenphone';                                                   

											  endif;
										        ?> 
                                              </td>


                                              <td>                                              
                                              <?php
											  if($row->status == '0'):
                                                echo '<span class="badge bg-danger p-2">Off</span>';
											  elseif($row->status == '1'):
                                                echo '<span class="badge bg-success p-2">On</span>';											  
											  endif;
										        ?> 
                                              </td>

                                                                                           
                                                </tr>                                              
                                             

                                                <?php } ?>
                                

                                            </tbody>
                                        </table>              

                                       
                                    </div>
                                </div> <!-- end card -->

                            </div> <!-- end col-->
                        </div>
                        <!-- end row -->                       

                        

                        <br>

                        <!-- end row -->


                        <!-- file preview template -->
                        <div class="d-none" id="uploadPreviewTemplate">
                            <div class="card mt-1 mb-0 shadow-none border">
                                <div class="p-2">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <img data-dz-thumbnail src="#" class="avatar-sm rounded bg-light" alt="">
                                        </div>
                                        <div class="col ps-0">
                                            <a href="javascript:void(0);" class="text-muted fw-bold" data-dz-name></a>
                                            <p class="mb-0" data-dz-size></p>
                                        </div>
                                        <div class="col-auto">
                                            <!-- Button -->
                                            <a href="" class="btn btn-link btn-lg text-muted" data-dz-remove>
                                                <i class="dripicons-cross"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>