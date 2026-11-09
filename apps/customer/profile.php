<?php
include "login.check.php"; 
include "header.php"; 
?>

<!-- page content start -->
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
            <img src="../img/user3.png" alt="">
        </div>
    </div>
</div>

<div class="container mb-4 text-center text-white">
    <h6 class="mb-1"><?php echo $fullname;?></h6>
    <p><?php echo $address;?></p>
    <p class="mb-1"><?php echo $email_address;?></p>
    <p><?php echo $phone_number;?></p>
    <p>Joining Date: <?php echo $regi_date;?></p>
</div>

<div class="main-container">
    <div class="container mb-4">
        <div class="row mb-4">
            <div class="col-6">
                <button 
                    class="btn btn-outline-default px-2 btn-block rounded"
                    onclick="shareQRCode()">
                    <span class="material-icons mr-1">qr_code_scanner</span> Share QR
                </button>
            </div>
            <div class="col-6">
                <button 
                    class="btn btn-outline-default px-2 btn-block rounded"
                    onclick="sendBill()">
                    <span class="material-icons mr-1">receipt_long</span> Send Bill
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="card" style="margin-top:10px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h6 class="mb-1">Your Recharge Balance low limit: Tk. <?php echo $limit_balance;?></h6>
                            </div>
                        </div>
                        <div class="progress h-5 mt-3">
                            <div class="progress-bar bg-default" role="progressbar" style="width:35%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card" style="margin-top:10px;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <h6 class="mb-1">Your M. Banking Balance low limit: Tk. <?php echo $limit_balance;?></h6>
                            </div>
                        </div>
                        <div class="progress h-5 mt-3">
                            <div class="progress-bar bg-default" role="progressbar" style="width:35%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<script>
function shareQRCode() {
    const qrCodeUrl = 'path/to/qr-code-image.png'; // Replace with your QR code URL
    const shareData = {
        title: 'Share QR Code',
        text: 'Here is my QR code.',
        url: qrCodeUrl
    };

    if (navigator.share) {
        navigator.share(shareData).then(() => {
            console.log('QR Code shared successfully');
        }).catch((error) => {
            console.error('Error sharing QR Code:', error);
        });
    } else {
        alert('Sharing not supported on this browser.');
    }
}

function sendBill() {
    // Implement the logic to send the bill here
    alert('Bill has been sent successfully.');
    console.log('Send Bill button clicked');
}
</script>

<?php 
include "footer.php"; 
?>
