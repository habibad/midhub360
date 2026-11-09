<?php
    include "login.check.php";
    include "header.php"; 
?>
        
        <div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="complain_list.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">comment</span> </div>
                    <p class="small text-white">Complain List</p>
                    </a>
                </div>
                <div class="col-auto">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">phone_iphone</span> </div>
                    <p class="small text-white">Send Recharge</p>
                </div>
                <div class="col-auto">
                    <a href="help.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">support_agent</span> </div>
                    <p class="small text-white">Contact</p>
                    </a>
                </div>                
            </div>
        </div>
        
        
        <!-- page content start -->

        <div class="main-container">

        <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">keyboard_arrow_left</span>
                    </button>
            </div>
            
            <div class="container">
                <!--
                <div class="alert alert-success">
                    <b>Congratulations!</b> Your address has been saved to your adresss list successfully.
                </div>
                -->

                <div class="card">
                    <div class="card-header">
                        <h6 class="subtitle mb-0">
                            <div class="avatar avatar-40 bg-primary-light text-primary rounded mr-2"><span class="material-icons vm">rate_review</span></div>
                            Write Your Complain
                        </h6>
                    </div>
                    <div class="card-body">

                        <div class="form-group float-label active">
                           <select class="form-control">
                               <option>Others</option>
                               <option selected>Payment</option>
                               <option selected>Recharge</option>
                            </select>
                            <label class="form-control-label">Select Type</label>
                        </div>
                        <div class="form-group float-label">
                            <input type="text" class="form-control">
                            <label class="form-control-label">Subject</label>
                        </div>

                        <div class="form-group">
                                <label for="exampleFormControlTextarea1">Description</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                            </div>                       
                        
                    </div>
                    
                    <div class="card-footer">
                        <button class="btn btn-block btn-default rounded">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </main>


<?php 
    include "footer.php"; 
?>