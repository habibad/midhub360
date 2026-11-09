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
                                            <li class="breadcrumb-item active">API List</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">API List</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


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


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                                <a href="api_add.php" class="btn btn-danger mb-2"><i class="mdi mdi-plus-circle me-2"></i> Add New API</a>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>
                                                                              
                                        <table id="basic-datatable" class="table table-striped dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>Serial</th>
                                                    <th>Country</th>
                                                    <th>Company</th>
                                                    <th>Service</th>
                                                    <th>API User</th>
                                                    <th>API Key</th>
                                                    <th>Request URL</th>                                                   
                                                    <th>Status URL</th>
                                                    <th>Balance URL</th>
                                                    <th>Status</th>
                                                    <th>User Parame.</th>
                                                    <th>Key Parame.</th>
                                                    <th>Number Parame.</th>
                                                    <th>Amount Parame.</th>
                                                    <th>Type Parame.</th>
                                                    <th>Edit</th>
                                                                                                       
                                                </tr>
                                            </thead>
                                        
                                        
                                            <tbody>


                        <?php
							$userlevel = Session::get('level');
							$us_id = Session::get('us_id');
							$i=0;
							$query="SELECT * FROM api WHERE company!='BillsMama' and company!='Others' ORDER BY id DESC";
                            $result = mysqli_query($connect, $query);    
                            if(!$result){
                        
                                die("Query Failed2!" . mysqli_error($connect));
                            }
							while($row=mysqli_fetch_object($result))
							{
								
                                $status         = $row->api_status;
                                $request_url    = $row->request_url;
                                $status_url     = $row->status_url;
                                $balance_url    = $row->balance_url;
                                $username       = $row->username;
                                $ap_key         = $row->ap_key;

                                $pera_user      = $row->pera_user;
                                $pera_key       = $row->pera_key;
                                $pera_number    = $row->pera_number;
                                $pera_amount    = $row->pera_amount;
                                $pera_type      = $row->pera_type;
                                $pera_customone = $row->pera_customone;
                                $pera_customtwo = $row->pera_customtwo;
                                $country        = $row->country;
                                $company        = $row->company;
                                $service        = $row->service;



                                $i++;


								?>


                                                <tr>
                                                    <td><?php echo $i; ?></td>                                                   
                                                    <td><?php echo $country; ?></td>
                                                    <td><?php echo $company; ?></td>
                                                    <td><?php echo $service; ?></td>
                                                    <td><?php echo $username; ?></td>
                                                    <td><?php echo $ap_key; ?></td>
                                                    <td><?php echo $request_url; ?></td>
                                                    <td><?php echo $status_url; ?></td>
                                                    <td><?php echo $balance_url; ?></td>


                                                    <td>
                                        <?php
											  if($row->api_status == '0'):
													echo '<span class="badge bg-danger p-2">Inactive</span>';
											  elseif($row->api_status == '1'):
													echo '<span class="badge bg-success p-2">Active</span>';											 
											  endif;
										?> 
                                             </td>
                                                    
                                                    <td><?php echo $pera_user; ?></td>
                                                    <td><?php echo $pera_key; ?></td>
                                                    <td><?php echo $pera_number; ?></td>
                                                    <td><?php echo $pera_amount; ?></td>
                                                    <td><?php echo $pera_type; ?></td>
                                                    
                                                    <td><a href="api_edit.php?api_id=<?= $row->id ?>"><i class="mdi mdi-square-edit-outline action-icon"></i></a></td>
                                                   
                                                   
                                                
                                                </tr>
                                                
                                                <?php } ?>
                                            </tbody>
                                        </table>

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer-data.php";
?>