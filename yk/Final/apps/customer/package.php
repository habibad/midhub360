<?php
include "login.check.php"; 
include "header.php"; 
?>


<div class="container mb-4 text-center">
            <div class="row mb-4 justify-content-center">
                <div class="col-auto">
					<a href="recharge_history.php">
                    <div class="icon icon-40 bg-default-light text-default mb-2 rounded-circle"><span class="material-icons">history</span> </div>
                    <p class="small text-white">Package History</p>
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



<div class="main-container">

<div class="tab-content" id="nav-tabContent">
    <div class="">
        <div class="main-items">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-11">
                        <div style="background-color: #652d82; color: white; border-radius: 5px; padding: 10px; text-align: center;">
                            Selected Operator: <?php echo $_GET['op_name'] ?>
                        </div>
                    </div>
                </div>
                <div class="row mt-5">
					
					<?php foreach(Package::getPkg($_GET['op_id']) as $package): ?>
					    <div class="col-6">
					        <a href="internet_package.php" onclick="event.preventDefault();
                               document.getElementById('pkg-form-<?php echo $package->id ?>').submit();">
					            <form action="internet_package.php" method="POST" style="display: none" id="pkg-form-<?php echo $package->id ?>">
                                    <input type="text" value="<?php echo $package->id ?>" name="pkg_id" />
                                    <input type="text" value="<?php echo $_GET['op_id'] ?>" name="op_id" />
					                <input type="text" value="<?php echo $_GET['op_name'] ?>" name="op_name" />
					                <input type="text" value="<?php echo $package->package_name ?>" name="pkg_name" />
					                <input type="text" value="<?php echo $package->amount ?>" name="pkg_amount" />
					                <input type="text" value="<?php echo $package->balance ?>" name="pkg_balance" />
					            </form>
					            <div class="card mb-2" style="background-color: #652d82; color: white; border-radius: 5px;">
                                      <div class="card-body">
                                        <h5 class="card-title"><?php echo $package->amount ?> Tk</h5>
                                        <p class="card-text"><?php echo $package->package_name ?></p>
                                        <p style="font-size: 70%">Commission: <?php echo $package->commission ?> Tk</p>
                                      </div>
                                </div>
					        </a>
					    </div>
					<?php endforeach; ?>
					
					<!-- -->
                </div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</section>


</div>
	</main>	
	
<?php include "footer.php"; ?>