<?php
    include "login.check.php";
    include "header.php"; 
?>


<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
                    <a href="complain.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">comment</span> </div>
                    <p class="small text-white">New Complain</p>
                    </a>
                </div>
                <div class="col-auto">
                    <a href="send_recharge.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">phone_iphone</span> </div>
                    <p class="small text-white">Send Recharge</p>
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
        

        <!-- page content start -->

        <div class="main-container">

            <div class="col-auto px-0">
                    <button class="btn btn-40 btn-link back-btn" type="button">
                        <span class="material-icons">keyboard_arrow_left</span>
                    </button>
            </div>
            <div class="container">
                <div class="card mb-4">
                    <div class="card-header border-0 bg-none">
                        <div class="row">
                            <div class="col align-self-center">
                                <h6 class="mb-0">Complain List</h6>
                            </div>
                            
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="all font-weight-medium">ID</th>
                                    <th class="font-weight-medium">MSG</th>
                                    <th class="font-weight-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ID01</td>
                                    <td>
                                        <div class="media">                                            
                                            <div class="media-body">
                                                <p class="mb-0">John Alexandar</p>
                                                <p class="text-secondary small">Sydney, Australia</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-danger btn btn-sm btn-30 rounded">A</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ID02</td>
                                    <td>
                                        <div class="media">                                            
                                            <div class="media-body">
                                                <p class="mb-0">Anthony Desouza</p>
                                                <p class="text-secondary small">Sydney, Australia</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-warning btn btn-sm btn-30 rounded">A</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ID03</td>
                                    <td>
                                        <div class="media">                                            
                                            <div class="media-body">
                                                <p class="mb-0">Mark Zukarburgs</p>
                                                <p class="text-secondary small">Sydney, Australia</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-primary btn btn-sm btn-30 rounded">A</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ID04</td>
                                    <td>
                                        <div class="media">                                           
                                            <div class="media-body">
                                                <p class="mb-0">John Doe</p>
                                                <p class="text-secondary small">Sydney, Australia</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-success btn btn-sm btn-30 rounded">A</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ID05</td>
                                    <td>
                                        <div class="media">                                            
                                            <div class="media-body">
                                                <p class="mb-0">Mark Zukarburgs</p>
                                                <p class="text-secondary small">Sydney, Australia</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-success btn btn-sm btn-30 rounded">A</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </main>


<?php 
    include "footer.php"; 
?>