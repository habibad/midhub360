<?php 
include "login.check.php";
include "header.php";


$api_id   = Xss::xssClean(Input::get('api_id'));


if(!isset($api_id) || empty($api_id)){
    session_destroy();
    header("location:../index.php");
    die();
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
                                            <li class="breadcrumb-item active">Edit API</li>
                                        </ol>
                                    </div>
                                    <h4 class="page-title">Edit API</h4>

                                    <!-- Error Code Start-->

                                    <?php if (Session::exists('error')){
							?>
							<div class="alert alert-danger alert-dismissible fade show" role="alert">
							  <?=Session::Flush('error');?>							 
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">   
								
							  </button>
							</div>
							<?php
					  }?>
					<?php if (Session::exists('success')){
						?>
							<div class="alert alert-success alert-dismissible fade show" role="alert">
							  <strong>Well Done!</strong> <?=Session::Flush('success');?>
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">							 
								
							  </button>
							</div>
							<?php
					}?>

                     <!-- Error Code End-->

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-6 col-xl-6">
                                <div class="card text-center">
                                    <div class="card-body">

                                   
                                    <?php 
                    
                                        $query = "SELECT * FROM api where id='$api_id' and (company!='BillsMama' and company!='Others')";        
                                        $result = mysqli_query($connect, $query);

                                        if(!$result){
                                            die("Query Failed!" . mysqli_error($connect));      
                                        }        
                                        
                                        $reseller_data  = mysqli_fetch_object($result); 

                                        $status         = $reseller_data->api_status;
                                        $request_url    = $reseller_data->request_url;
                                        $status_url     = $reseller_data->status_url;
                                        $balance_url    = $reseller_data->balance_url;
                                        $username       = $reseller_data->username;
                                        $ap_key         = $reseller_data->ap_key;

                                        $pera_user      = $reseller_data->pera_user;
                                        $pera_key       = $reseller_data->pera_key;
                                        $pera_number    = $reseller_data->pera_number;
                                        $pera_amount    = $reseller_data->pera_amount;
                                        $pera_type      = $reseller_data->pera_type;
                                        $pera_customone = $reseller_data->pera_customone;
                                        $pera_customtwo = $reseller_data->pera_customtwo;
                                        $company        = $reseller_data->company;
                                        $service        = $reseller_data->service;


                                        ?>

    
                                        <form action="route.php?action=APIUpdate" method="post" accept-charset="utf-8">
                                       
                                       <div class="text-start mt-3">

                                      
                                                <input type="hidden" name="apiid" id="apiid" value="<?php echo $api_id;?>">


                                                <div class="mb-3">
                                                    <label for="product-category" class="form-label">Country<span class="text-danger">*</span></label>
                                                        <select name="country" class="form-control select2" id="country">                                                                                                 
                                                            <option value="Afganistan">Afghanistan</option>
                                                            <option value="Albania">Albania</option>
                                                            <option value="Algeria">Algeria</option>
                                                            <option value="American Samoa">American Samoa</option>
                                                            <option value="Andorra">Andorra</option>
                                                            <option value="Angola">Angola</option>
                                                            <option value="Anguilla">Anguilla</option>
                                                            <option value="Antigua & Barbuda">Antigua & Barbuda</option>
                                                            <option value="Argentina">Argentina</option>
                                                            <option value="Armenia">Armenia</option>
                                                            <option value="Aruba">Aruba</option>
                                                            <option value="Australia">Australia</option>
                                                            <option value="Austria">Austria</option>
                                                            <option value="Azerbaijan">Azerbaijan</option>
                                                            <option value="Bahamas">Bahamas</option>
                                                            <option value="Bahrain">Bahrain</option>
                                                            <option value="Bangladesh">Bangladesh</option>
                                                            <option value="Barbados">Barbados</option>
                                                            <option value="Belarus">Belarus</option>
                                                            <option value="Belgium">Belgium</option>
                                                            <option value="Belize">Belize</option>
                                                            <option value="Benin">Benin</option>
                                                            <option value="Bermuda">Bermuda</option>
                                                            <option value="Bhutan">Bhutan</option>
                                                            <option value="Bolivia">Bolivia</option>
                                                            <option value="Bonaire">Bonaire</option>
                                                            <option value="Bosnia & Herzegovina">Bosnia & Herzegovina</option>
                                                            <option value="Botswana">Botswana</option>
                                                            <option value="Brazil">Brazil</option>
                                                            <option value="British Indian Ocean Ter">British Indian Ocean Ter</option>
                                                            <option value="Brunei">Brunei</option>
                                                            <option value="Bulgaria">Bulgaria</option>
                                                            <option value="Burkina Faso">Burkina Faso</option>
                                                            <option value="Burundi">Burundi</option>
                                                            <option value="Cambodia">Cambodia</option>
                                                            <option value="Cameroon">Cameroon</option>
                                                            <option value="Canada">Canada</option>
                                                            <option value="Canary Islands">Canary Islands</option>
                                                            <option value="Cape Verde">Cape Verde</option>
                                                            <option value="Cayman Islands">Cayman Islands</option>
                                                            <option value="Central African Republic">Central African Republic</option>
                                                            <option value="Chad">Chad</option>
                                                            <option value="Channel Islands">Channel Islands</option>
                                                            <option value="Chile">Chile</option>
                                                            <option value="China">China</option>
                                                            <option value="Christmas Island">Christmas Island</option>
                                                            <option value="Cocos Island">Cocos Island</option>
                                                            <option value="Colombia">Colombia</option>
                                                            <option value="Comoros">Comoros</option>
                                                            <option value="Congo">Congo</option>
                                                            <option value="Cook Islands">Cook Islands</option>
                                                            <option value="Costa Rica">Costa Rica</option>
                                                            <option value="Cote DIvoire">Cote DIvoire</option>
                                                            <option value="Croatia">Croatia</option>
                                                            <option value="Cuba">Cuba</option>
                                                            <option value="Curaco">Curacao</option>
                                                            <option value="Cyprus">Cyprus</option>
                                                            <option value="Czech Republic">Czech Republic</option>
                                                            <option value="Denmark">Denmark</option>
                                                            <option value="Djibouti">Djibouti</option>
                                                            <option value="Dominica">Dominica</option>
                                                            <option value="Dominican Republic">Dominican Republic</option>
                                                            <option value="East Timor">East Timor</option>
                                                            <option value="Ecuador">Ecuador</option>
                                                            <option value="Egypt">Egypt</option>
                                                            <option value="El Salvador">El Salvador</option>
                                                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                                                            <option value="Eritrea">Eritrea</option>
                                                            <option value="Estonia">Estonia</option>
                                                            <option value="Ethiopia">Ethiopia</option>
                                                            <option value="Falkland Islands">Falkland Islands</option>
                                                            <option value="Faroe Islands">Faroe Islands</option>
                                                            <option value="Fiji">Fiji</option>
                                                            <option value="Finland">Finland</option>
                                                            <option value="France">France</option>
                                                            <option value="French Guiana">French Guiana</option>
                                                            <option value="French Polynesia">French Polynesia</option>
                                                            <option value="French Southern Ter">French Southern Ter</option>
                                                            <option value="Gabon">Gabon</option>
                                                            <option value="Gambia">Gambia</option>
                                                            <option value="Georgia">Georgia</option>
                                                            <option value="Germany">Germany</option>
                                                            <option value="Ghana">Ghana</option>
                                                            <option value="Gibraltar">Gibraltar</option>
                                                            <option value="Great Britain">Great Britain</option>
                                                            <option value="Greece">Greece</option>
                                                            <option value="Greenland">Greenland</option>
                                                            <option value="Grenada">Grenada</option>
                                                            <option value="Guadeloupe">Guadeloupe</option>
                                                            <option value="Guam">Guam</option>
                                                            <option value="Guatemala">Guatemala</option>
                                                            <option value="Guinea">Guinea</option>
                                                            <option value="Guyana">Guyana</option>
                                                            <option value="Haiti">Haiti</option>
                                                            <option value="Hawaii">Hawaii</option>
                                                            <option value="Honduras">Honduras</option>
                                                            <option value="Hong Kong">Hong Kong</option>
                                                            <option value="Hungary">Hungary</option>
                                                            <option value="Iceland">Iceland</option>
                                                            <option value="Indonesia">Indonesia</option>
                                                            <option value="India">India</option>
                                                            <option value="Iran">Iran</option>
                                                            <option value="Iraq">Iraq</option>
                                                            <option value="Ireland">Ireland</option>
                                                            <option value="Isle of Man">Isle of Man</option>
                                                            <option value="Israel">Israel</option>
                                                            <option value="Italy">Italy</option>
                                                            <option value="Jamaica">Jamaica</option>
                                                            <option value="Japan">Japan</option>
                                                            <option value="Jordan">Jordan</option>
                                                            <option value="Kazakhstan">Kazakhstan</option>
                                                            <option value="Kenya">Kenya</option>
                                                            <option value="Kiribati">Kiribati</option>
                                                            <option value="Korea North">Korea North</option>
                                                            <option value="Korea Sout">Korea South</option>
                                                            <option value="Kuwait">Kuwait</option>
                                                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                                                            <option value="Laos">Laos</option>
                                                            <option value="Latvia">Latvia</option>
                                                            <option value="Lebanon">Lebanon</option>
                                                            <option value="Lesotho">Lesotho</option>
                                                            <option value="Liberia">Liberia</option>
                                                            <option value="Libya">Libya</option>
                                                            <option value="Liechtenstein">Liechtenstein</option>
                                                            <option value="Lithuania">Lithuania</option>
                                                            <option value="Luxembourg">Luxembourg</option>
                                                            <option value="Macau">Macau</option>
                                                            <option value="Macedonia">Macedonia</option>
                                                            <option value="Madagascar">Madagascar</option>
                                                            <option value="Malaysia">Malaysia</option>
                                                            <option value="Malawi">Malawi</option>
                                                            <option value="Maldives">Maldives</option>
                                                            <option value="Mali">Mali</option>
                                                            <option value="Malta">Malta</option>
                                                            <option value="Marshall Islands">Marshall Islands</option>
                                                            <option value="Martinique">Martinique</option>
                                                            <option value="Mauritania">Mauritania</option>
                                                            <option value="Mauritius">Mauritius</option>
                                                            <option value="Mayotte">Mayotte</option>
                                                            <option value="Mexico">Mexico</option>
                                                            <option value="Midway Islands">Midway Islands</option>
                                                            <option value="Moldova">Moldova</option>
                                                            <option value="Monaco">Monaco</option>
                                                            <option value="Mongolia">Mongolia</option>
                                                            <option value="Montserrat">Montserrat</option>
                                                            <option value="Morocco">Morocco</option>
                                                            <option value="Mozambique">Mozambique</option>
                                                            <option value="Myanmar">Myanmar</option>
                                                            <option value="Nambia">Nambia</option>
                                                            <option value="Nauru">Nauru</option>
                                                            <option value="Nepal">Nepal</option>
                                                            <option value="Netherland Antilles">Netherland Antilles</option>
                                                            <option value="Netherlands">Netherlands (Holland, Europe)</option>
                                                            <option value="Nevis">Nevis</option>
                                                            <option value="New Caledonia">New Caledonia</option>
                                                            <option value="New Zealand">New Zealand</option>
                                                            <option value="Nicaragua">Nicaragua</option>
                                                            <option value="Niger">Niger</option>
                                                            <option value="Nigeria">Nigeria</option>
                                                            <option value="Niue">Niue</option>
                                                            <option value="Norfolk Island">Norfolk Island</option>
                                                            <option value="Norway">Norway</option>
                                                            <option value="Oman">Oman</option>
                                                            <option value="Pakistan">Pakistan</option>
                                                            <option value="Palau Island">Palau Island</option>
                                                            <option value="Palestine">Palestine</option>
                                                            <option value="Panama">Panama</option>
                                                            <option value="Papua New Guinea">Papua New Guinea</option>
                                                            <option value="Paraguay">Paraguay</option>
                                                            <option value="Peru">Peru</option>
                                                            <option value="Phillipines">Philippines</option>
                                                            <option value="Pitcairn Island">Pitcairn Island</option>
                                                            <option value="Poland">Poland</option>
                                                            <option value="Portugal">Portugal</option>
                                                            <option value="Puerto Rico">Puerto Rico</option>
                                                            <option value="Qatar">Qatar</option>
                                                            <option value="Republic of Montenegro">Republic of Montenegro</option>
                                                            <option value="Republic of Serbia">Republic of Serbia</option>
                                                            <option value="Reunion">Reunion</option>
                                                            <option value="Romania">Romania</option>
                                                            <option value="Russia">Russia</option>
                                                            <option value="Rwanda">Rwanda</option>
                                                            <option value="St Barthelemy">St Barthelemy</option>
                                                            <option value="St Eustatius">St Eustatius</option>
                                                            <option value="St Helena">St Helena</option>
                                                            <option value="St Kitts-Nevis">St Kitts-Nevis</option>
                                                            <option value="St Lucia">St Lucia</option>
                                                            <option value="St Maarten">St Maarten</option>
                                                            <option value="St Pierre & Miquelon">St Pierre & Miquelon</option>
                                                            <option value="St Vincent & Grenadines">St Vincent & Grenadines</option>
                                                            <option value="Saipan">Saipan</option>
                                                            <option value="Samoa">Samoa</option>
                                                            <option value="Samoa American">Samoa American</option>
                                                            <option value="San Marino">San Marino</option>
                                                            <option value="Sao Tome & Principe">Sao Tome & Principe</option>
                                                            <option value="Saudi Arabia">Saudi Arabia</option>
                                                            <option value="Senegal">Senegal</option>
                                                            <option value="Seychelles">Seychelles</option>
                                                            <option value="Sierra Leone">Sierra Leone</option>
                                                            <option value="Singapore">Singapore</option>
                                                            <option value="Slovakia">Slovakia</option>
                                                            <option value="Slovenia">Slovenia</option>
                                                            <option value="Solomon Islands">Solomon Islands</option>
                                                            <option value="Somalia">Somalia</option>
                                                            <option value="South Africa">South Africa</option>
                                                            <option value="Spain">Spain</option>
                                                            <option value="Sri Lanka">Sri Lanka</option>
                                                            <option value="Sudan">Sudan</option>
                                                            <option value="Suriname">Suriname</option>
                                                            <option value="Swaziland">Swaziland</option>
                                                            <option value="Sweden">Sweden</option>
                                                            <option value="Switzerland">Switzerland</option>
                                                            <option value="Syria">Syria</option>
                                                            <option value="Tahiti">Tahiti</option>
                                                            <option value="Taiwan">Taiwan</option>
                                                            <option value="Tajikistan">Tajikistan</option>
                                                            <option value="Tanzania">Tanzania</option>
                                                            <option value="Thailand">Thailand</option>
                                                            <option value="Togo">Togo</option>
                                                            <option value="Tokelau">Tokelau</option>
                                                            <option value="Tonga">Tonga</option>
                                                            <option value="Trinidad & Tobago">Trinidad & Tobago</option>
                                                            <option value="Tunisia">Tunisia</option>
                                                            <option value="Turkey">Turkey</option>
                                                            <option value="Turkmenistan">Turkmenistan</option>
                                                            <option value="Turks & Caicos Is">Turks & Caicos Is</option>
                                                            <option value="Tuvalu">Tuvalu</option>
                                                            <option value="Uganda">Uganda</option>
                                                            <option value="United Kingdom">United Kingdom</option>
                                                            <option value="Ukraine">Ukraine</option>
                                                            <option value="United Arab Erimates">United Arab Emirates</option>
                                                            <option value="United States of America">United States of America</option>
                                                            <option value="Uraguay">Uruguay</option>
                                                            <option value="Uzbekistan">Uzbekistan</option>
                                                            <option value="Vanuatu">Vanuatu</option>
                                                            <option value="Vatican City State">Vatican City State</option>
                                                            <option value="Venezuela">Venezuela</option>
                                                            <option value="Vietnam">Vietnam</option>
                                                            <option value="Virgin Islands (Brit)">Virgin Islands (Brit)</option>
                                                            <option value="Virgin Islands (USA)">Virgin Islands (USA)</option>
                                                            <option value="Wake Island">Wake Island</option>
                                                            <option value="Wallis & Futana Is">Wallis & Futana Is</option>
                                                            <option value="Yemen">Yemen</option>
                                                            <option value="Zaire">Zaire</option>
                                                            <option value="Zambia">Zambia</option>
                                                            <option value="Zimbabwe">Zimbabwe</option>                                                                                             
                                                                                
                                                        </select>
                                                </div>


                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Company<span class="text-danger">*</span></label>
                                                    <input type="text" name="company" id="company" class="form-control" value="<?php echo $company; ?>" placeholder="Enter Company Name" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-category" class="form-label">Service Type<span class="text-danger">*</span></label>
                                                    <select name="service" class="form-control select2" id="service">                                                                                                 
                                                        <option value="Recharge">Recharge</option>
                                                        <option value="MBanking">Mobile Banking</option>
                                                        <option value="BillPay">Bill Pay</option> 
                                                        <option value="SMS">SMS</option>                                                                                                                                                    
                                                    </select>
                                                </div>
                                                <br>

                                                <h4 class="page-title">Parameters</h4> <br>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Username/UserID<span class="text-danger">*</span></label>
                                                    <input type="text" name="perauser" id="perauser" class="form-control" value="<?php echo $pera_user; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Key/Password<span class="text-danger">*</span></label>
                                                    <input type="text" name="perakey" id="perakey" class="form-control" value="<?php echo $pera_key; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Number<span class="text-danger">*</span></label>
                                                    <input type="text" name="peranumber" id="peranumber" class="form-control" value="<?php echo $pera_number; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Amount<span class="text-danger">*</span></label>
                                                    <input type="text" name="peraamount" id="peraamount" class="form-control" value="<?php echo $pera_amount; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Type<span class="text-danger">*</span></label>
                                                    <input type="text" name="peratype" id="peratype" class="form-control" value="<?php echo $pera_type; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Custom 1 (Optional)</label>
                                                    <input type="text" name="peracustomone" id="apiusername" class="form-control" value="<?php echo $pera_customone; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20">                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Custom 2 (Optional)</label>
                                                    <input type="text" name="peracustomtwo" id="peracustomtwo" class="form-control" value="<?php echo $pera_customtwo; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20">                                                    
                                                </div> 
                                                <br>

                                                 <h4 class="page-title">Access Information</h4> <br>                                              
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">API Username<span class="text-danger">*</span></label>
                                                    <input type="text" name="apiusername" id="apiusername" class="form-control" value="<?php echo $username; ?>" placeholder="Enter API Username" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "20" required>                                                    
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">API Key<span class="text-danger">*</span></label>
                                                    <input type="text" name="apikey" id="apikey" class="form-control" value="<?php echo $ap_key; ?>" placeholder="Enter API Key" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "50" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Request Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="requestlink" id="requestlink" class="form-control" value="<?php echo $request_url; ?>" placeholder="Enter Website Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>
                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Status Check Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="statuslink" id="statuslink" class="form-control" value="<?php echo $status_url; ?>" placeholder="Enter Status Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>

                                                <div class="mb-3">
                                                    <label for="product-name" class="form-label">Balance Check Link<span class="text-danger">*</span></label>
                                                    <input type="text" name="balancelink" id="balancelink" class="form-control" value="<?php echo $balance_url; ?>" placeholder="Enter Balance Check Link" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "100" required>                                                    
                                                </div>

                                                
                                        <?php                                  
                                            if ($status == 1 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1" checked> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?> 

                                          <?php                                  
                                            if ($status == 0 )
                                            {
                                        ?>

                                                <div class="form-check mb-4 form-check-primary">                                           
                                                    <input name="active" class="form-check-input" type="checkbox" value="1" id="customckeck1"> 
                                                    <label class="form-check-label" for="customckeck1">Active</label>                                          
                                                </div>  

                                        <?php
                                            }                                
                                         ?>                                                                                                  
                                                
                                                <div class="button-list pe-xl-4 d-grid">
                                                    <button type="submit" class="btn btn-lg btn-primary waves-effect waves-light">Submit</button>                                                    
                                                </div> 

                                               

                                   </form>
                                       
                                           </div> 
                                    
                   
                                    </div>                                 
                                </div> <!-- end card -->

                            </div> <!-- end col-->

                            <div class="col-lg-6 col-xl-6">
                                <div class="card">
                                    <div class="card-body">
                                        <ul class="nav nav-pills nav-fill navtab-bg">
                                           
                                            <li class="nav-item">
                                                <a href="#timeline" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                                                    Help
                                                </a>
                                            </li>
                                            
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane" id="aboutme">
    
                                                No Data


    
                                            </div> <!-- end tab-pane -->
                                            <!-- end about me section content -->
    
                                            <div class="tab-pane show active" id="timeline">
    
                                             
                                               Comming Soon....
    
                                            </div>
                                            <!-- end timeline content-->
    
                                            <div class="tab-pane" id="settings">


                                                 <!-- Start Mobile Banking Payment History Table-->


                                                

                    <!-- End Mobile Banking Payment History Table-->



                                            </div>
                                            <!-- end settings content-->
    
                                        </div> <!-- end tab-content -->
                                    </div>
                                </div> <!-- end card-->

                            </div> <!-- end col -->
                        </div>
                        <!-- end row-->

                    </div> <!-- container -->

                </div> <!-- content -->


    

<!-- Recharge Request modal start-->
<div id="confirmation-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">                                                
                <div class="modal-content">   
                    <div class="modal-header">
                            <h4 class="modal-title" id="myCenterModalLabel">Recharge Request Confirmation</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>   
                    
        <form action="route.php?action=requestSend" role="form" method="post" accept-charset="utf-8">
              
            <div class="modal-body" id="load_request_data">
                                                       
            </div>    
            
            
                <div class="modal-footer">
					<button type="submit" class="btn btn-success">Next</button>
					<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
				</div>
        </form>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<!-- Recharge Request modal End-->



<?php    
include "footer-data.php";
?>
