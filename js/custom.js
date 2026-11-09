$(document).ready(function () {
  //report modal of transaction
  $(document).on("show.bs.modal", "#reportModal", function (e) {
    e.stopImmediatePropagation();

    var type = $(e.relatedTarget).data("type");
    var url = "modal_load_report.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        type: type,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_report_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //add payment for reseller
  $(document).on("show.bs.modal", "#addPaymentModal", function (e) {
    e.stopImmediatePropagation();

    var userid = $(e.relatedTarget).data("userid");
    var user = $(e.relatedTarget).data("user");
    var url = "modal_add_payment_for_user.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        userid: userid,
        user: user,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_add_payment_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Return payment for reseller
  $(document).on("show.bs.modal", "#ReturnPaymentModal", function (e) {
    e.stopImmediatePropagation();

    var userid = $(e.relatedTarget).data("userid");
    var user = $(e.relatedTarget).data("user");
    var url = "modal_return_payment_for_user.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        userid: userid,
        user: user,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_return_payment_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Recharge Success
  $(document).on("show.bs.modal", "#suceessrecharge", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var url = "modal_recharge_success.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Recharge cancel
  $(document).on("show.bs.modal", "#cancelrecharge", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var url = "modal_recharge_success.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_cldata").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });
  //Recharge Success
  $(document).on("show.bs.modal", "#suceessbank", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var url = "modal_bank_success.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Recharge cancel
  $(document).on("show.bs.modal", "#cancelbank", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var url = "modal_bank_success.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_cldata").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Recharge cancel
  $(document).on("show.bs.modal", "#resendrecharge", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var url = "modal_recharge_success.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_redatadata").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Drive cancel
  $(document).on("show.bs.modal", "#canceldrive", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    var number = $(e.relatedTarget).data("number");
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var company = $(e.relatedTarget).data("company");
    var cost = $(e.relatedTarget).data("cost");
    var url = "modal_drive_cancel.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        ucid: ucid,
        company: company,
        cost: cost,
        number: number,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_request_cldata").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Payment Request Accept
  $(document).on("show.bs.modal", "#paymentreqaccept", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    //var number = $(e.relatedTarget).data('number');
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var username = $(e.relatedTarget).data("username");
    var bltype = $(e.relatedTarget).data("bltype");
    var url = "modal_payment_request.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        username: username,
        bltype: bltype,
        ucid: ucid,
      },
      dataType: "HTML",
      success: function (data) {
        $("#balance_request_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //Payment Request Accept
  $(document).on("show.bs.modal", "#paymentreqcancel", function (e) {
    e.stopImmediatePropagation();

    var id = $(e.relatedTarget).data("id");
    //var number = $(e.relatedTarget).data('number');
    var amount = $(e.relatedTarget).data("amount");
    var ucid = $(e.relatedTarget).data("ucid");
    var username = $(e.relatedTarget).data("username");
    var bltype = $(e.relatedTarget).data("bltype");
    var url = "modal_payment_request.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        id: id,
        amount: amount,
        username: username,
        bltype: bltype,
        ucid: ucid,
      },
      dataType: "HTML",
      success: function (data) {
        $("#payment_request_cldata").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  //payment history for reseller
  $(document).on("show.bs.modal", "#paymentHistoryModal", function (e) {
    e.stopImmediatePropagation();

    var userid = $(e.relatedTarget).data("userid");
    var user = $(e.relatedTarget).data("user");
    var url = "modal_reseller_payment_history.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        userid: userid,
        user: user,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_payment_history_data").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  $(document).on("show.bs.modal", "#historyModal", function (e) {
    e.stopImmediatePropagation();

    var service = $(e.relatedTarget).data("service");
    var service_name = $(e.relatedTarget).data("servicename");
    var url = "modal_service_history.php";

    $.ajax({
      type: "post",
      url: url,
      data: {
        service_name: service_name,
        service: service,
      },
      dataType: "HTML",
      success: function (data) {
        $("#load_history").html(data);
      },
      error: function (jqXHR, errorThrown, errorThrown) {
        alert(errorThrown);
      },
    });
  });

  $(document).on("show.bs.modal", "#confirmation-modal", function (e) {
    e.stopImmediatePropagation();

    var number = $("#number").val();
    var amount = $("#amount").val();
    var type = $("input[name=type]:checked").val();
    var service = $("#service").val();
    var url = "modal_send_request.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          number: number,
          amount: amount,
          type: type,
          service: service,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  $(document).on("show.bs.modal", "#confirmation-modal-mnp", function (e) {
    e.stopImmediatePropagation();

    var number = $("#number").val();
    var amount = $("#amount").val();
    var operator = $("#operator").val();
    var type = $("input[name=type]:checked").val();
    var service = $("#service").val();
    var url = "modal_send_request_mnp.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          number: number,
          amount: amount,
          operator: operator,
          type: type,
          service: service,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  $(document).on("show.bs.modal", "#confirmation-modal-mbank", function (e) {
    e.stopImmediatePropagation();

    var number = $("#m_number").val();
    var amount = $("#amount").val();
    var type = $("#m_type").val();
    var service = $("#service").val();
    var m_banking_type = $("#bank_name").val();
    var url = "modal_m_request.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          number: number,
          amount: amount,
          type: type,
          m_banking_type: m_banking_type,
          service: service,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  $(document).on("show.bs.modal", "#confirmation-modal-billpay", function (e) {
    e.stopImmediatePropagation();

    var number = $("#number").val();
    var amount = parseFloat($("#amount").val()) || 100;
    var type = $("#type").val();
    var company = $("#company").val();
    var company_type = $("#company_type").val();
    var cs_name = $("#cs_name").val();
    var month = $("#month").val();
    var contact_number = $("#contact_number").val();

    var url = "modal_billpay_request.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      //   return false;
      //   $("#confirmation-modal-billpay").modal("hide");
      e.preventDefault(); // stops modal from opening
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          number: number,
          amount: amount,
          type: type,
          company: company,
          cs_name: cs_name,
          month: month,
          contact_number: contact_number,
          company_type: company_type,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  /* Bank payment request start */

  $(document).on("show.bs.modal", "#confirmation-modal-bank", function (e) {
    e.stopImmediatePropagation();

    var acnumber = $("#acnumber").val();
    var amount = $("#amount").val();
    var branch = $("#branch").val();
    var bankname = $("#bankname").val();
    var ac_type = $("#ac_type").val();
    var acname = $("#acname").val();
    var note = $("#note").val();
    var contact_number = $("#contact_number").val();

    var url = "modal_bank_request.php";

    if (acnumber == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          acnumber: acnumber,
          amount: amount,
          branch: branch,
          bankname: bankname,
          acname: acname,
          note: note,
          contact_number: contact_number,
          ac_type: ac_type,
        },
        dataType: "HTML",
        success: function (data) {
          $("#bank_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  /* Bank payment request end */

  $(document).on("show.bs.modal", "#confirmation-modal-internet", function (e) {
    e.stopImmediatePropagation();

    var number = $("#number").val();
    var amount = $("#amount").val();
    var cost = $("#cost").val();
    //var type = $("#type").val();
    var pkg_id = $("#pkg_id").val();
    var type = $("input[name=type]:checked").val();

    // var service = $("#service").val();
    var url = "modal_internet_request.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          pkg_id: pkg_id,
          number: number,
          amount: amount,
          type: type,
          cost: cost,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });

  $(document).on("show.bs.modal", "#confirmation-modal-drive", function (e) {
    e.stopImmediatePropagation();

    var number = $("#number").val();
    var amount = $("#amount").val();
    var cost = $("#cost").val();
    //var type = $("#type").val();
    var pkg_id = $("#pkg_id").val();
    var type = $("input[name=type]:checked").val();

    // var service = $("#service").val();
    var url = "modal_drive_request.php";

    if (number == "" || amount == "") {
      alert("Fill the form correctly");
      return false;
    } else {
      $.ajax({
        type: "post",
        url: url,
        data: {
          pkg_id: pkg_id,
          number: number,
          amount: amount,
          type: type,
          cost: cost,
        },
        dataType: "HTML",
        success: function (data) {
          $("#load_request_data").html(data);
        },
        error: function (jqXHR, errorThrown, errorThrown) {
          alert(errorThrown);
        },
      });
    }
  });
});

function checkallbox(iobj) {
  inputArray = document.getElementsByTagName("input");
  for (i = 0; i < inputArray.length; i++) {
    if (
      inputArray[i].type.toLowerCase() == "checkbox" &&
      inputArray[i] != iobj
    ) {
      tempCheckbox = inputArray[i];
      if (tempCheckbox.checked) {
        tempCheckbox.checked = false;
      } else {
        tempCheckbox.checked = true;
      }
    }
  }
}

function checkallper(iobj) {
  inputArray = document.getElementsByName("per[]");
  for (i = 0; i < inputArray.length; i++) {
    if (
      inputArray[i].type.toLowerCase() == "checkbox" &&
      inputArray[i] != iobj
    ) {
      tempCheckbox = inputArray[i];
      if (tempCheckbox.checked) {
        tempCheckbox.checked = false;
      } else {
        tempCheckbox.checked = true;
      }
    }
  }
}

function PopUp(itarget, width, height) {
  try {
    width = width != null ? width : 400;
    height = height != null ? height : 400;
    var top, left;
    top = screen.height / 2 - height / 2;
    left = screen.width / 2 - width / 2;
    var lwin = window.open(
      itarget,
      "",
      "location=no,height=" +
        height +
        ",width=" +
        width +
        ",status=no,resizable=yes,scrollbars=yes,top=" +
        top +
        ",left=" +
        left
    );
    lwin.focus();
  } catch (oException) {}
}

function onlyNumbers(evt) {
  var e = event || evt; // for trans-browser compatibility
  var charCode = e.which || e.keyCode;
  if (charCode > 31 && (charCode < 48 || charCode > 57)) return false;
  return true;
}

function tableSubmit() {
  var chk_arr = document.getElementsByName("id[]");
  var chklength = chk_arr.length;
  $total = 0;
  for (k = 0; k < chklength; k++) {
    if (chk_arr[k].checked == "1") $total++;
  }
  if ($total < 1) {
    alert("You must select at least one item");
    return false;
  }
  var ans = confirm("Are your sure?");
  if (ans == true) {
    document.forms["dataform"].submit();
  }
}

function pendingSubmit() {
  var chk_arr = document.getElementsByName("id[]");
  var chklength = chk_arr.length;
  $total = 0;
  for (k = 0; k < chklength; k++) {
    if (chk_arr[k].checked == "1") $total++;
  }
  var action = document.getElementById("action");
  var ass = action.value;
  if (ass == "") return false;
  if ($total < 1) {
    alert("You must select at least one item");
    return false;
  }
  var ans = confirm("Are your sure?");
  if (ans == true) {
    document.forms["dataform"].submit();
  }
}

// $(function() {
// $('#message').smsCounter('#count');
// });

// $(document).ready(function(){
//     $('#message').keyup(function(){
//         var text = $(this).val();
//         if(is_ascii(text) === true){return false;}
//     })
// })

// $(document).ready(function() {
// $('#date1,#date2').datepicker({
// format: "yyyy-mm-dd"
// });
// $('#date1,#date2').on('changeDate', function(ev) {
// $(this).datepicker('hide');
// });

// jQuery(".chosen").chosen();

// });

function explode(delimiter, string, limit) {
  if (
    arguments.length < 2 ||
    typeof delimiter === "undefined" ||
    typeof string === "undefined"
  )
    return null;
  if (delimiter === "" || delimiter === false || delimiter === null)
    return false;
  if (
    typeof delimiter === "function" ||
    typeof delimiter === "object" ||
    typeof string === "function" ||
    typeof string === "object"
  ) {
    return {
      0: "",
    };
  }
  if (delimiter === true) delimiter = "1";

  // Here we go...
  delimiter += "";
  string += "";

  var s = string.split(delimiter);

  if (typeof limit === "undefined") return s;

  // Support for limit
  if (limit === 0) limit = 1;

  // Positive limit
  if (limit > 0) {
    if (limit >= s.length) return s;
    return s.slice(0, limit - 1).concat([s.slice(limit - 1).join(delimiter)]);
  }

  // Negative limit
  if (-limit >= s.length) return [];

  s.splice(s.length + limit);
  return s;
}

function fillBill() {
  var id = $("#receiver").val();

  $.get("main/fillBill/" + id, function (data) {
    if (data != "") {
      obj = JSON.parse(data);
      $("#ac_title").val(obj.ac_title);
      $("#ac_area").val(obj.ac_area);
      $("#provider").val(obj.type);
      $("#amount").focus();
    }
  });
}

$("form").submit(function () {
  $("button[type=submit]").attr("disabled", "disabled");
  $("button[type=submit]").empty().html("Please wait...");
});

function getPackage(val) {
  jQuery.ajax({
    url: "./action.php?request=getPackage",
    type: "post",
    dataType: "text",
    data: {
      val: val,
    },
    success: function (data) {
      $("#Package").html(data);
    },
  });
}

function setAmount(val) {
  $("#amount").val(val);
}

$("body").on("click", "#expenseByOpt", function (e) {
  //e.stopImmediatePropagation();
  e.preventDefault();
  var type = $(this).data("opt");

  var url = "ajax_flexi_expense.php";

  $.ajax({
    type: "post",
    url: url,
    data: {
      type: type,
    },
    dataType: "HTML",
    success: function (data) {
      $("#load_expense_data").html(data);
    },
    error: function (jqXHR, errorThrown, errorThrown) {
      alert(errorThrown);
    },
  });
});

$("body").on("click", "#expenseByOptMb", function (e) {
  e.preventDefault();
  var type = $(this).data("opt");
  var url = "ajax_mbanking_expense.php";

  $.ajax({
    type: "post",
    url: url,
    data: {
      type: type,
    },
    dataType: "HTML",
    success: function (data) {
      $("#load_mb_expense").html(data);
    },
    error: function (jqXHR, errorThrown, errorThrown) {
      alert(errorThrown);
    },
  });
});
