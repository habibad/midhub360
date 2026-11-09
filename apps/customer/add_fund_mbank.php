<?php
include "login.check.php";
include "header.php"; 

$query= "SELECT * from payment_account where id='1'";

$result           = mysqli_query($connect, $query);
$reseller_data    = mysqli_fetch_object($result);
$bkash_agent      = $reseller_data->bkash_agent;
$bkash_personal	  = $reseller_data->bkash_personal;
$rocket_agent     = $reseller_data->rocket_agent;
$rocket_personal  = $reseller_data->rocket_personal;
$nagad_agent      = $reseller_data->nagad_agent;
$nagad_personal   = $reseller_data->nagad_personal;

?>

        
<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">search</span> </div>
                    <p class="small text-white">Check Status</p>
                </div>
                <div class="col-auto">
                    <a href="payment_received.php">
                        <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">payments</span> </div>
                        <p class="small text-white">Payment Hist.</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="help.php">
                        <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">support_agent</span> </div>
                        <p class="small text-white">Contact</p>
                    </a>
                </div>                
            </div>
        </div>


        
        
        <div class="main-container">

        <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">arrow_back</span>
                    </button>

					<span class="title-text">Add Balance to Your Account</span>
        </div>    <br>
        
            
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

                <form action="route.php?action=addFund" method="post" accept-charset="utf-8">   
            
                <div class="form-group">
                <p class="text-left text-secondary mb-1 title-text">Balance Type</p>
                            <select name="bltype" class="form-control" id="bltype">
                                <option value="Recharge">Recharge Balance</option>
                                <option value="Mobile Banking">Mobile Banking Balance</option>                                                                                      
                            </select>
                </div>

            <p class="text-left text-secondary mb-1 title-text">Enter Amount</p>
                <div class="form-group mb-1">
                    <input type="number" name="amount" id="amount" class="form-control large-gift-card" placeholder="00.00" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "7" required>
                </div>
                <p class="text-center text-secondary mb-4">Current Balance: Tk. 1,050.00 </p>                
            </div>

            <div class="container mb-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            
                            <div class="col">
                                <h6 class="subtitle mb-0">Payment Information</h6>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">            
                        <div class="form-group">
                            <label class="form-control-label">Company</label>
                            <select name="mcategory" class="form-control" id="mcategory">
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>                                                        
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-control-label">Type</label>
                            <select name="mtype" class="form-control" id="mtype">
                                <option value="cashin">Cash In</option>
                                <option value="CashOut">Cash Out</option>
                                <option value="sendmoney">Send Money</option>
                                <option value="payment">Payment</option>                       
                            </select>
                        </div>

                        <div class="form-group float-label">
                            <input type="number" name="acnumber" id="acnumber" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Payment Number</label>
                            <a data-target="#myModal" data-toggle="modal" class="for-custom-space" id="MainNavHelp" href="#myModal">List of Payment Number</a>
                        </div>
                        <div class="form-group float-label">
                            <input type="text" name="transid" id="transid" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>
                            <label class="form-control-label">Transaction ID</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="number" name="sendnumber" id="sendnumber" class="form-control" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "12" required>
                            <label class="form-control-label">Sending Number</label>
                        </div>
                    </div>

                    <div class="card-footer">                    
                        <button type="submit" class="btn btn-block btn-default rounded">Submit</button>
                    </div>
                </div>
            </div>
            
        </div>
    </main>



     <!-- Modals  -->
     <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">List of Payment Number</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">

                <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="all font-weight-medium">Type</th>
                                    <th class="min-tablet font-weight-medium">Number</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>bKash Agent</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="transaction-id mb-0"><?php echo $bkash_agent;?></p>                                                
                                            </div>
                                        </div>
                                    </td>                                   
                                </tr>
                                <tr>
                                    <td>bKash Personal</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="transaction-id mb-0"><?php echo $bkash_personal;?></p>                                                
                                            </div>
                                        </div>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td>Rocket Agent</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="transaction-amount mb-0"> <strong><?php echo $rocket_agent;?></strong></p>                                                
                                            </div>
                                        </div>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td>Rocket Personal</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="transaction-amount mb-0"><?php echo $rocket_personal;?></p>                                                
                                            </div>
                                        </div>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td>Nagad Agent</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="text-danger mb-0"><strong><?php echo $nagad_agent;?></strong></p>                                                
                                            </div>
                                        </div>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td>Nagad Personal</td>
                                    <td>
                                        <div class="media">
                                           
                                            <div class="media-body">
                                                <p class="text-danger mb-0"><strong><?php echo $nagad_personal;?></strong></p>                                                
                                            </div>
                                        </div>
                                    </td>                                    
                                </tr>


                               
                            </tbody>
                        </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>                    
                </div>

            </div>
        </div>
    </div>
	

<?php 
include "footer.php"; 
?>