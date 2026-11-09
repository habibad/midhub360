<?php
    include "login.check.php";
    include "header.php"; 
?>

        <!-- page content start -->
        <!--
        <div class="container-fluid px-0">
            <div class="card overflow-hidden">
                <div class="card-body p-0 h-150">
                    <div class="background">
                        <img src="../img/image10.jpg" alt="" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid top-70 text-center mb-4">
            <div class="avatar avatar-140 rounded-circle mx-auto shadow">
                <div class="background">
                    <img src="../img/user1.png" alt="">
                </div>
            </div>
        </div>
        <div class="container mb-4 text-center text-white">
            <h6 class="mb-1">Errica Johnson</h6>
            <p>London, UK</p>
        </div>
        -->

        <!--
        <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">keyboard_arrow_left</span>
                    </button>
                </div>
        -->

        <div class="main-container">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Security</h6>
                    </div>
                    <div class="card-body px-0 pt-0">
                        <div class="list-group list-group-flush border-top border-color">
                            <a href="change_password.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">lock_open</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Change Password</h6>                                       
                                    </div>
                                </div>
                            </a>
                            <a href="change_pin.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">phonelink_lock</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Change Pin</h6>                                        
                                    </div>
                                </div>
                            </a>
                            <a href="change_phone.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">phone_iphone</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Change Phone Number</h6>                                       
                                    </div>
                                </div>
                            </a>
                            <a href="change_email.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">email</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Change Email Address</h6>                                       
                                    </div>
                                </div>
                            </a>

                            <a href="update_profile.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">person</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Update Profile</h6>                                       
                                    </div>
                                </div>
                            </a>
                            <a href="change_photo.php" class="list-group-item list-group-item-action border-color">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="avatar avatar-50 bg-default-light text-default rounded">
                                            <span class="material-icons">portrait</span>
                                        </div>
                                    </div>
                                    <div class="col align-self-center pl-0">
                                        <h6 class="mb-1">Change Profile Photo</h6>                                       
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php 
    include "footer.php"; 
?>
