<?php 
include "login.check.php";
include "header.php";

$date       = Xss::xssClean(Input::get('date'));
$datefrom   = Xss::xssClean(Input::get('datefrom'));
$dateto     = Xss::xssClean(Input::get('dateto'));

$curyesterday=date("Y-m-d", mktime(0, 0, 0, date("m"),date("d")-1,date("Y")));
$curweek=date("Y-m-d", mktime(0, 0, 0, date("m"),date("d")-7,date("Y")));
$curmonth=date("Y-m-d", mktime(0, 0, 0, date("m"),date("d")-30,date("Y")));

//echo "Yesterday " . $curweek;


//echo "Date " . $date;
if($date == "custom")
{
    $query = "WHERE user_id='$us_id' and status='1' and s_date >= '$datefrom' and s_date <= '$dateto'";
    $sub_date=$datefrom . " To " . $dateto;
}
elseif($date == 0)
{
    $query = "WHERE user_id='$us_id' and status='1' and s_date=CURDATE()";
    $sub_date="Today";
}
elseif($date == 1)
{
    $query = "WHERE user_id='$us_id' and status='1' and s_date >= '$curyesterday' and s_date< CURDATE()";
    $sub_date="Yesterday";
}
elseif($date == 7)
{
    $query = "WHERE user_id='$us_id' and status='1' and s_date>= '$curweek' and s_date< CURDATE()";
    $sub_date="Last 7 Days";
}
elseif($date == 30)
{
    $query = "WHERE user_id='$us_id' and status='1' and s_date>= '$curmonth' and s_date< CURDATE()";
    $sub_date="Last 30 Days";
}
elseif($date == 365)
{
    $query = "WHERE user_id='$us_id' and status='1'";
    $sub_date="All Time";
}


$recharge   = "send_query";
$drive      = "drive_request";
$mbank      = "bkash_send";
$billpay    = "billpay_request";


$recharge_sale  = Mixedfn::salesreport($recharge,$query);
$drive_sale     = Mixedfn::salesreport($drive,$query);
$mbank_sale     = Mixedfn::salesreport($mbank,$query);
$bill_sale      = Mixedfn::salesreport($billpay,$query);

$total_sale = $recharge_sale + $drive_sale + $mbank_sale + $bill_sale;




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
                                            <li class="breadcrumb-item active">Sales Report</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Sales Report</h4>
                                </div>
                            </div>
                        </div>     
                        <!-- end page title --> 

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">


                                    <div class="row mb-2">
                                            <div class="col-sm-4">
                                               
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="text-sm-end">
                                                    <button type="button" class="btn btn-success mb-2 me-1"><i class="mdi mdi-cog"></i></button>
                                                    <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                                    <button type="button" class="btn btn-light mb-2">Export</button>
                                                </div>
                                            </div><!-- end col-->
                                        </div>

                                        <div class="col-lg-8">                                                
                                                <form method="GET" action="" class="d-flex flex-wrap align-items-center">
                                                    <label for="inputPassword2" class="visually-hidden">Search</label>
                                                    
                                                    <label for="status-select" class="me-2">From</label>
                                                    <div class="me-sm-3">
                                                    <input type="text" name="datefrom" class="form-control my-1 my-lg-0" data-provide="datepicker" data-date-format="yyyy-mm-dd" placeholder="2020-01-01">
                                                    </div>
                                                    <label for="status-select" class="me-2">To</label>
                                                    <div class="me-sm-3">
                                                    <input type="text" name="dateto" class="form-control my-1 my-lg-0" data-provide="datepicker" data-date-format="yyyy-mm-dd" placeholder="2020-01-01">
                                                    </div>

                                                    <input type="hidden" name="date" id="bank_name" value="custom">
                                   
                                                    <button type="submit" class="btn btn-primary">Search</button>
                                                </form>                            
                                            </div>

                                           <br>
                                                                                                                     
                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        <a href="?date=0" class="btn btn-primary for-custom-space">Today</a> 
                                        <a href="?date=1" class="btn btn-primary for-custom-space">Yesterday</a>
                                        <a href="?date=7" class="btn btn-primary for-custom-space">Last 7 Days</a>
                                        <a href="?date=30" class="btn btn-primary for-custom-space">Last 30 Days</a>
                                        <a href="?date=365" class="btn btn-primary for-custom-space">All Time</a>
                                        
                                        <thead>
                                                <tr>                                                    
                                                    <th>Day</th>
                                                    <th>Service</th>                                                   
                                                    <th>Amount</th>                                                                                                
                                                                                                   
                                                </tr>
                                            </thead>
                                        <tbody>
                                            
                                        
                                                    <tr>
                                                        <td><?php echo $sub_date; ?></td>
                                                        <td>Recharge</td>
                                                        <td><?php echo $recharge_sale; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $sub_date; ?></td>
                                                        <td>Drive</td>
                                                        <td><?php echo $drive_sale; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $sub_date; ?></td>
                                                        <td>Mobile Banking</td>
                                                        <td><?php echo $mbank_sale; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $sub_date; ?></td>
                                                        <td>Bill Pay</td>
                                                        <td><?php echo $bill_sale; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?php echo $sub_date; ?></td>
                                                        <td>Banking</td>
                                                        <td>0</td>
                                                    </tr>


                                           
                                        </tbody>
                                
                                        <tfoot>
                                            <tr>
                                               
                                               
                                                <th></th>
                                                <th>Total:</th>
                                                <th><?php echo $total_sale; ?></th>
                                               
                                                
                                            </tr>
                                        </tfoot>                                         

                                    </table>

                                  

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->

                        
                    </div> <!-- container -->

                </div> <!-- content -->

<?php 
include "footer.php";
?>
