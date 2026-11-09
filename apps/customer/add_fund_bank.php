<?php
    include "login.check.php";
    include "header.php"; 
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
                        <p class="small text-white">History</p>
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
            
            <div class="container mb-4">
            <div class="title-box title-msg">
                    Add Balance to Your Account by Bank Deposit/Transfer
                </div>
                <br>

                <div class="form-group">
                            <label class="form-control-label">Account Type</label>
                            <select class="form-control" id="exampleFormControlSelect1">
                                <option>Recharge Balance</option>
                                <option>Mobile Banking Balance</option>                                                                                      
                            </select>
                </div>

            <p class="text-left text-secondary mb-1 title-text">Enter Amount</p>
                <div class="form-group mb-1">
                    <input type="text" class="form-control large-gift-card" placeholder="00.00">
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
                            <label class="form-control-label">Bank Name</label>
                            <select class="form-control" id="exampleFormControlSelect1">
                                <option>Brac Bank Ltd.</option>
                                <option>Islami Bank Bangladesh Ltd.</option>
                                <option>Dutch Bangla Bank Ltd.</option>
                                <option>Sonali Bank Ltd.</option>                                                        
                            </select>
                        </div>
                        
                        <div class="form-group float-label">
                            <input type="text" class="form-control">
                            <label class="form-control-label">Account Name</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="text" class="form-control">
                            <label class="form-control-label">Account Number</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="text" class="form-control">
                            <label class="form-control-label">Deposit No/Transaction ID</label>
                        </div>                        
                        <div class="form-group float-label">   
                        <p class="text-left text-secondary mb-1">Upload Document (.jpg, .png only allowed)</p>                    
                            <input type="file" class="form-control">                            
                        </div>

                    </div>
                </div>
            </div>
            <div class="container text-center">
                <a href="thank_you.html" class="btn btn-default mb-2 mx-auto rounded">Add Balance</a>
            </div>
        </div>
    </main>

<?php 
    include "footer.php"; 
?>