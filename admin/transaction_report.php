<?php 
include "login.check.php";
include "header.php";

$total_amount = Mixedfn::totalrecharge();


$trans_id = Xss::xssClean(Input::get('id'));

if(isset($trans_id) && $trans_id !=0 && !empty($trans_id))
{      
    /*  */
    $query= "SELECT * from reseller_balance where id='$trans_id'";
    global $connect;
    $result = mysqli_query($connect, $query);
    
    if(!$result){
        die("Query Failed!" . mysqli_error($connect));      
    } 
    
    $getdata= mysqli_fetch_object($result);

    $id              = $getdata->id;
    $user_id         = $getdata->user_id;
    $amount          = $getdata->amount;
    $submitted_date  = $getdata->submitted_date;
    $load_by         = $getdata->load_by;
    $updated         = $getdata->updated;
    $note            = $getdata->note;
    $ip              = $getdata->ip;
    $return_payment	 = $getdata->return_payment;
    $old_balance     = $getdata->old_balance;
    $new_balance     = $getdata->new_balance;
    

    $queryin  = "INSERT INTO  trush_reseller_balance (id,user_id,amount,submitted_date,load_by,updated,note,ip,return_payment,old_balance,new_balance)";
    $queryin .= "VALUES('$id','$user_id','$amount','$submitted_date','$load_by','$updated','$note','$ip','$return_payment','$old_balance','$new_balance')"; //Used concatenation (.) add two string

    $resultin = mysqli_query($connect, $queryin);    
    if(!$resultin)
    {
        
        die("Query Failed!" . mysqli_error($connect));
    }

   

    $querydl = "delete from reseller_balance where id='$trans_id'";    
    $resultdl = mysqli_query($connect, $querydl);  
    
    
    
}

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
                                            <li class="breadcrumb-item active">Transaction Report</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Transaction Report</h4>
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
                                                                                                                     
                                        <table id="contact-detail" class="table table-striped dt-responsive nowrap w-100">
                                   
                                        
                                        <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date</th>
                                                    <th>Username</th>                                                   
                                                    <th>Loaded By</th>
                                                    <th>Amount</th>
                                                    <th>Old Balance</th>
                                                    <th>New Balance</th>
                                                    <th>Type</th>
                                                    <th>Delete</th> 
                                                    <th>Note</th>                                                                                                 
                                                                                                   
                                                </tr>
                                            </thead>
                                        <tbody>                                                                              
                                           
                                        </tbody>
                                
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th>Total:</th>
                                                <th></th>                                                
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                
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
include "footer-data.php";
?>

<script>
    $(document).ready(function() {
    $('#contact-detail').DataTable( {
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
 
            // Remove the formatting to get integer data for summation

            
            var intVal = function ( i ) {
                return typeof i === 'string' ?
                    Number(i) :
                    typeof i === 'number' ?
                        i : 0;
            };

            //console.log(intVal("12"));
 
            // Total over all pages
            total = api
                .column( 4 )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(total);
 
            // Total over this page
            pageTotal = api
                .column( 4, { page: 'current'} )
                .data()
                .reduce( function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0 );
            
            //console.log(pageTotal);
        
 
            // Update footer
            $( api.column( 4 ).footer() ).html(
                //'Tk. '+pageTotal +' ( $'+ total +' total)'
                'Tk. '+pageTotal +' '
            );
        },

        
        "scrollX": true,
		"pagingType": "numbers",
        "processing": true,
        "serverSide": true,
        "ajax": "pagination/transaction_report.php"
       /* */

    } );
} );

</script>
