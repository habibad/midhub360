<?php 
    include "login.check.php";
    include "header.php"; 
?>

        <!-- page content start -->        

        <div class="main-container">

            <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">keyboard_arrow_left</span>
                    </button>
            </div>
                
            <div class="container">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="subtitle mb-0">
                            <div class="avatar avatar-40 bg-default-light text-default rounded mr-2"><span class="material-icons vm">portrait</span></div>
                            Change Photo
                        </h6>
                    </div>

                    <div class="upload-img-border"><img src="../img/user3.png" alt="" class="upload-img"></div>


                    <div class="card-body">                        
                        <div class="form-group float-label">
                        <p class="text-left text-secondary mb-1">Upload Photo (.jpg, .png only allowed)</p>
                        <input type="file" class="form-control">                             
                        </div>                        
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-block btn-default rounded">Update Photo</button>
                    </div>
                </div>
               
            </div>
        </div>
    </main>

<?php 
    include "footer.php"; 
?>
