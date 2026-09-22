let product_list;
let icssmExist = false;

$(document).ready(function () {
  init();

  var originalValue = $('#so_head\\[preferred_login\\]').val();

  $('#so_head\\[preferred_login\\]').on('blur', function () {
    let currentValue = $(this).val();
    if (currentValue !== originalValue) {
      $("#btSave").prop("disabled", true);
      $("#btProfile").prop("disabled", true);
    }
  });

  $('#so_head\\[preferred_login\\]').on('input', function () {
    let currentValue = $(this).val();
    $("#btSave").prop("disabled", true);
    $("#btProfile").prop("disabled", true);
    if (currentValue.length == 0) {
      $("#btSave").prop("disabled", false);
      $("#btProfile").prop("disabled", false);
    }
  });

  if ($('#so_head\\[reg_type\\]').val() == 'r') {
    $('.company_related').toggle(false);
    $(".ic_related").insertAfter(".ssm_related");
    $(".ic-title").css("font-weight", "bold");
    $(".ssm-title").css("font-weight", "normal");
  } else {
    $('.company_related').toggle(true);
    $(".ic_related").insertAfter(".customer_name_field");
    $(".ic-title").css("font-weight", "normal");
    $(".ssm-title").css("font-weight", "bold");
  }

  if (typeof all_product_list === 'undefined') all_product_list = [];

  let regType = ($('#so_head\\[reg_type\\]').val() || '').toLowerCase();
  product_list = all_product_list.filter(item => {
    const category = item.category.toLowerCase();
    if (regType === 'r') {
      return category === 'r';
    } else if (regType === 'b') {
      return category === 'b' || category === 's';
    } else if (regType === 'o') {
      return category !== 'r';
    }
    return false;
  });

  $('#so_details\\[item_name\\]').autocomplete('option', 'source', product_list);

  $('#so_head\\[icno\\]').on('blur', function () {
    var value = $(this).val().trim();
    if (value !== '') {
      ajax_check_icno(value);
      if ($('#so_head\\[tin\\]').val().trim() !== '' && $('#so_head\\[reg_type\\]').val() == 'r') {
        ajax_validate_tin($('#so_head\\[tin\\]').val().trim(), value, 'NRIC');
      }
    }
  });

  $('#so_head\\[comp_num\\]').on('blur', function () {
    var value = $(this).val().trim();
    if (value !== '') {
      ajax_check_ssm(value);
      if ($('#so_head\\[tin\\]').val().trim() !== '' && $('#so_head\\[reg_type\\]').val() != 'r') {
        ajax_validate_tin($('#so_head\\[tin\\]').val().trim(), value, 'BRN');
      }
    }
  });

  $('#so_head\\[bill_email\\]').on('blur', function () {
    var value = $(this).val().trim();
    if (value !== '') {
      ajax_check_email(value);
    }
  });

  $('#so_head\\[tin\\]').on('blur', function () {
    var value = $(this).val().trim();
    if (value !== '' && $('#so_head\\[reg_type\\]').val() !== '' && ($('#so_head\\[icno\\]').val().trim() !== '' || $('#so_head\\[comp_num\\]').val().trim() !== '')) {
      let idValue = $('#so_head\\[reg_type\\]').val() == 'r' 
                      ? $('#so_head\\[icno\\]').val().trim() 
                      : $('#so_head\\[comp_num\\]').val().trim();
      let idType =  $('#so_head\\[reg_type\\]').val() == 'r' ? 'NRIC' : 'BRN';

      ajax_validate_tin(value, idValue, idType);
    }
  });

  if (($('#so_head\\[icno\\]').val() || '').trim() !== '') {
    ajax_check_icno($('#so_head\\[icno\\]').val().trim(), true);
  }

  if (($('#so_head\\[comp_num\\]').val() || '').trim() !== '') {
    ajax_check_ssm($('#so_head\\[comp_num\\]').val().trim(), true);
  }

  if (($('#so_head\\[bill_email\\]').val() || '').trim() !== '') {
    ajax_check_email($('#so_head\\[bill_email\\]').val().trim());
  }

  window.addEventListener("pageshow", function (event) {
    var historyTraversal = event.persisted ||
      (typeof window.performance != "undefined" &&
        window.performance.navigation.type === 2);
    if (historyTraversal) {
      // Handle page restore.
      window.location.reload();
    }
  });

});

function init() {

  $("#so_head\\[date\\]").datepicker({
    format: 'yyyy-mm-dd',
    showButtonPanel: true,
    autoclose: true
  });

  $("#so_head\\[preferred_install_date\\]").datepicker({
    format: 'yyyy/mm/dd',
    showButtonPanel: true,
    autoclose: true
  });

  $(document).on('keydown', '[data-fieldname="so_detail.tax_rate"] input', function (event) {
    if (event.key === 'Tab') {
      event.preventDefault();
      const currentRow = $(this).closest('tr');
      const nextRow = currentRow.next('tr');
      if (currentRow.is($("#so_detail_body tr").last())) {
        addSodetail();
      } else if (nextRow.length) {
        nextRow.find('[data-fieldname="so_detail.item_name"] input').focus().select();
      }
    }
  });

  $(document).on('input change', '[data-fieldname="so_detail.price"] input, [data-fieldname="so_detail.tax_rate"] input, [data-fieldname="so_detail.quantity"] input', function () {
    calc_total($(this));
  });

  $('#salesorder_detail').submit(function (e) {
    
    $('.button-group button').prop('disabled', true);

    // your code here
    e.preventDefault();

    let submitter = e.originalEvent.submitter.id;
    let result = true;
    
    if (submitter == 'btProfile') {
      let package_id = $('#so_details\\[prod_id\\]').val();
      let product_selected = Array.isArray(product_list)
          ? product_list.find(product => String(product.id) === String(package_id))
          : '';
      
      let package = product_selected?.label ?? '';
      let confirm_message = '';
      if (package != '') {
        confirm_message = 'Create profile with the selected package: ' + package + '?';
      } else {
        confirm_message = 'No package selected. A profile will be created if one does not exist; otherwise, it will be skipped.';
      }

      if(icssmExist){
        warning_message = 'This IC/SSM already exists in the system.'; 
        if(customer_no == '' || customer_no == null) {
          warning_message += "\nCreating a new profile will link to the existing customer.";
        } else {
          warning_message += "\nIt will update the existing ACCOUNT ("+ customer_no +") infomation.\nPlease note: Profile information must be updated manually.";
        }
        confirm_message = warning_message + " \n\n" + confirm_message;
      } else {
          if(profile_id != '' && profile_id != null) {
            confirm_message += "\n\nNote: The existing profile linked to the previous IC will be retained, not deleted.";
          }
      }
      result = confirm(confirm_message);
    }

    if(!result){
      $('.button-group button').prop('disabled', false);
      return;
    } 

    $('#task').val(submitter);

    $('#so_head\\[del_state\\]').prop('disabled', false);

    // IC/SSM validation before submit
    $('#so_head\\[bill_state\\]').prop('disabled', false);

    $('#so_detail_rows').val(JSON.stringify(getDetailRows('so_detail')));

    $('#so_head\\[dia_vars\\]').val(JSON.stringify(getDIAJSON()));

    //$(this).unbind('submit').submit();

    let formData = new FormData(this);

    $('.input-group-addon').parent().removeClass('has-error');

    showLoadingIcon();

    $.ajax({
      dataType: 'json',
      url: $(this).attr('action'),
      type: 'POST',
      data: formData,
      success: function (data) {
        //alert(data)
        
        if (data['status'] == 'ER') {

          handle_ajax_error(data);

          $('.button-group button').prop('disabled', false);
          hideLoadingIcon();

        } else {
          window.location.href = base_url + data['url'];
        }

      },
      error: function (data) {
        $('.button-group button').prop('disabled', false);
        hideLoadingIcon();
        $.gritter.add({
          title: 'ERROR',
          text: 'Something wrong has occured during saving.',
          time: '5000',
          close_icon: 'l-arrows-remove s16',
          class_name: 'info-notice',
        });
      },
      complete: function (data) {
        $('.button-group button').prop('disabled', false);
        hideLoadingIcon();
      },
      cache: false,
      contentType: false,
      processData: false
    });
  });

  //should work with error reporting and also existing records
  auto_gen_lines();

  //dia
  auto_gen_dia();

  $('#same_with_billing').change(function () {
    if ($('input[name="same_with_billing"]:checked').val() == '1') {
      $('.del_addr').attr('readonly', 'readonly');
      $('#so_head\\[del_state\\]').attr('disabled', 'disabled');

      $('#so_head\\[del_unit_no\\]').val($('#so_head\\[bill_unit_no\\]').val());
      $('#so_head\\[del_company_name\\]').val($('#so_head\\[comp_name\\]').val());
      $('#so_head\\[del_attn\\]').val($('#so_head\\[bill_attn\\]').val());
      $('#so_head\\[del_addr_1\\]').val($('#so_head\\[bill_addr_1\\]').val());
      $('#so_head\\[del_addr_2\\]').val($('#so_head\\[bill_addr_2\\]').val());
      $('#so_head\\[del_addr_3\\]').val($('#so_head\\[bill_addr_3\\]').val());
      $('#so_head\\[del_postcode\\]').val($('#so_head\\[bill_postcode\\]').val());
      $('#so_head\\[del_city\\]').val($('#so_head\\[bill_city\\]').val());
      $('#so_head\\[del_state\\]').val($('#so_head\\[bill_state\\]').val());
      $('#so_head\\[del_tel\\]').val($('#so_head\\[bill_tel\\]').val());

    } else {
      $('.del_addr').removeAttr('readonly');
      $('#so_head\\[del_state\\]').removeAttr('disabled');
    }
  });

  //autocomplete

  $(document).on('focus', '[data-fieldname="so_detail.item_name"] input', function () {
    $('[data-fieldname="so_detail.item_name"] input').autocomplete({
      autoFocus: true,
      source: product_list,
      select: function (e, ui) {
        $(this).closest('tr').find('input[name="so_detail.prod_id"]').val(ui.item.id);
        $(this).closest('tr').find('input[name="so_detail.item_name"]').addClass('font-bold');
      },
      change: function (e, ui) {
        const row = $(this).closest('tr');
        const prodIdField = row.find('input[name="so_detail.prod_id"]');
        const itemNameField = row.find('input[name="so_detail.item_name"]');
        const diaField = row.find('input[name="so_detail.dia_vars"]');
        let result = product_list.find(item => item.value.trim().toLowerCase() === (e.target.value).trim().toLowerCase());
        if (result) {
          prodIdField.val(result.id);
          itemNameField.addClass('font-bold');

          diaField.val(result.dia_vars);
        } else {
          prodIdField.val(0);
          itemNameField.removeClass('font-bold');
        }

        $('#so_head\\[dia_vars\\]').val(JSON.stringify(getDIAJSON()));

        auto_gen_dia();
      }
    });
  });

  $('#so_details\\[item_name\\]').autocomplete({
    autoFocus: true,
    source: product_list,
    select: function (e, ui) {
      //$(this).closest('tr').find('input[name="so_detail.prod_id"]').val(ui.item.id);
      //$(this).closest('tr').find('input[name="so_detail.item_name"]').addClass('font-bold');

      $('#so_details\\[prod_id\\]').val(ui.item.id);
      $('#so_details\\[item_name\\]').val(ui.item.name);

      let result = product_list.find(item => item.value.trim().toLowerCase() === (ui.item.name).trim().toLowerCase());

      if (result) {
        $('#so_details\\[dia_vars\\]').val(result.dia_vars);
        auto_gen_dia();

        $('#so_details\\[deposit\\]').val(result.deposit);
        $('#so_details\\[monthly_charge\\]').val(result.monthly_charge);
        $('#so_details\\[installation\\]').val(result.installation);
        $('#so_details\\[one_time_charge\\]').val(result.one_time_charge);

        $('#so_head\\[base_amount\\]').val(result.monthly_charge);
        $('#so_head\\[grand_tax\\]').val(result.tax_amount);
        $('#so_head\\[grand_subtotal\\]').val(result.monthly_charge);
        $('#so_head\\[grand_total\\]').val(result.monthly_charge);
      }

    },
    change: function (e, ui) {
      /*const row = $(this).closest('tr');
      const prodIdField = row.find('input[name="so_detail.prod_id"]');
      const itemNameField = row.find('input[name="so_detail.item_name"]');
      const diaField = row.find('input[name="so_detail.dia_vars"]');
      let result = product_list.find(item => item.value.trim().toLowerCase() === (e.target.value).trim().toLowerCase());
      if (result) {
          prodIdField.val(result.id);
          itemNameField.addClass('font-bold');

          diaField.val(result.dia_vars);
      } else {
          prodIdField.val(0);
          itemNameField.removeClass('font-bold');
      }

      $('#so_head\\[dia_vars\\]').val(JSON.stringify(getDIAJSON()));

      auto_gen_dia();*/

      //real annoying bug to rely on change instead of select

      /*let result = product_list.find(item => item.value.trim().toLowerCase() === (e.target.value).trim().toLowerCase());

      if (result) {
        $('#so_details\\[dia_vars\\]').val(result.dia_vars);
        auto_gen_dia();

        $('#so_details\\[deposit\\]').val(result.deposit);
        $('#so_details\\[monthly_charge\\]').val(result.monthly_charge);
        $('#so_details\\[installation\\]').val(result.installation);

        $('#so_head\\[base_amount\\]').val(result.monthly_charge);
        $('#so_head\\[grand_subtotal\\]').val(result.monthly_charge);
        $('#so_head\\[grand_total\\]').val(result.monthly_charge);
      }*/

    }
  });

  $(document).on('input', '[data-fieldname="so_detail.item_name"] input', function () {
    const searchInput = $(this);
    const inputValue = searchInput.val().trim().toLowerCase();
    const row = $(this).closest('tr');
    const prodIdField = row.find('input[name="so_detail.prod_id"]');
    const itemNameField = row.find('input[name="so_detail.item_name"]');

    if (inputValue !== '') {
      let result = product_list.find(item => item.value.trim().toLowerCase() === inputValue);
      if (result) {
        prodIdField.val(result.id);
        itemNameField.addClass('font-bold');
      } else {
        prodIdField.val(0);
        itemNameField.removeClass('font-bold');
      }
    } else {
      prodIdField.val(0);
      itemNameField.removeClass('font-bold');
    }
  });

  btProfileWordingHandle();

}

showLoadingIcon = function () {
  $('#loading-icon').show();
}

hideLoadingIcon = function () {
  $('#loading-icon').hide();
}

function ajax_check_icno(icno, initial=false) {
  $.ajax({
    type: 'POST',
    url: base_url + 'salesorder/ajax_check_icno',
    data: { 
      so_id: $('#so_head\\[so_id\\]').val(), 
      icno: icno 
    },
    dataType: 'json',
    success: function (parsed) {
      if (parsed.exist) {
        $('.icno-exist').css('display', 'block');
        if($('#form_action').val() == 'NEW'){
          $('#so_head\\[building_no\\]').val(0).prop('readonly', true);
        }
        if(!initial){
          $('#so_head\\[bill_email\\]').val(parsed.data.acc_email).prop('readonly', true);
          $('#so_head\\[cust_name\\]').val(parsed.data.acc_name).prop('readonly', true);
          $('#so_head\\[bill_tel\\]').val(parsed.data.acc_mobileno).prop('readonly', true);
          $('#so_head\\[bill_addr_1\\]').val(parsed.data.bill_addr_1).prop('readonly', true);
          $('#so_head\\[bill_addr_2\\]').val(parsed.data.bill_addr_2).prop('readonly', true);
          $('#so_head\\[bill_addr_3\\]').val(parsed.data.bill_addr_3).prop('readonly', true);
          $('#so_head\\[bill_city\\]').val(parsed.data.bill_city).prop('readonly', true);
          $('#so_head\\[bill_postcode\\]').val(parsed.data.bill_postcode).prop('readonly', true);
          $('#so_head\\[bill_unit_no\\]').val(parsed.data.bill_unit_no).prop('readonly', true);
          $('#so_head\\[tin\\]').val(parsed.data.tin).prop('readonly', true);
          $('#so_head\\[bill_state\\]').val(parsed.data.bill_state).prop('disabled', true);
        }
        icssmExist = true;
      } else {
        $('.icno-exist').css('display', 'none');
        if(!initial){
          $('#so_head\\[bill_email\\]').prop('readonly', false);
          $('#so_head\\[cust_name\\]').prop('readonly', false);
          $('#so_head\\[bill_tel\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_1\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_2\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_3\\]').prop('readonly', false);
          $('#so_head\\[bill_city\\]').prop('readonly', false);
          $('#so_head\\[bill_postcode\\]').prop('readonly', false);
          $('#so_head\\[bill_unit_no\\]').prop('readonly', false);
          $('#so_head\\[tin\\]').prop('readonly', false);
          $('#so_head\\[bill_state\\]').prop('disabled', false);
        }
        icssmExist = false;
      }

      ajax_check_email($('#so_head\\[bill_email\\]').val());
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error(xhr, ajaxOptions, thrownError);
    }
  });
}

function ajax_check_ssm(ssm, initial = false) {
  $.ajax({
    type: 'POST',
    url: base_url + 'salesorder/ajax_check_ssm',
    data: { 
      so_id: $('#so_head\\[so_id\\]').val(), 
      ssm: ssm 
    },
    success: function (data) {
      var parsed = JSON.parse(data);
      
      if (parsed.exist) {
        $('.ssm-exist').css('display', 'block');
        if($('#form_action').val() == 'NEW'){
          $('#so_head\\[building_no\\]').val(0).prop('readonly', true);
        }
        if(!initial){
          $('#so_head\\[bill_email\\]').val(parsed.data.acc_email).prop('readonly', true);
          $('#so_head\\[cust_name\\]').val(parsed.data.acc_name).prop('readonly', true);
          $('#so_head\\[bill_tel\\]').val(parsed.data.acc_mobileno).prop('readonly', true);
          $('#so_head\\[bill_addr_1\\]').val(parsed.data.bill_addr_1).prop('readonly', true);
          $('#so_head\\[bill_addr_2\\]').val(parsed.data.bill_addr_2).prop('readonly', true);
          $('#so_head\\[bill_addr_3\\]').val(parsed.data.bill_addr_3).prop('readonly', true);
          $('#so_head\\[bill_city\\]').val(parsed.data.bill_city).prop('readonly', true);
          $('#so_head\\[bill_postcode\\]').val(parsed.data.bill_postcode).prop('readonly', true);
          $('#so_head\\[bill_unit_no\\]').val(parsed.data.bill_unit_no).prop('readonly', true);
          $('#so_head\\[tin\\]').val(parsed.data.tin).prop('readonly', true);

          $('#so_head\\[comp_name\\]').val(parsed.data.comp_name).prop('readonly', true);
          $('#so_head\\[bill_state\\]').val(parsed.data.bill_state).prop('disabled', true);
        }
        icssmExist = true;
      } else {
        $('.ssm-exist').css('display', 'none');
        if(!initial){
          $('#so_head\\[bill_email\\]').prop('readonly', false);
          $('#so_head\\[cust_name\\]').prop('readonly', false);
          $('#so_head\\[bill_tel\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_1\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_2\\]').prop('readonly', false);
          $('#so_head\\[bill_addr_3\\]').prop('readonly', false);
          $('#so_head\\[bill_city\\]').prop('readonly', false);
          $('#so_head\\[bill_postcode\\]').prop('readonly', false);
          $('#so_head\\[bill_unit_no\\]').prop('readonly', false);
          $('#so_head\\[tin\\]').prop('readonly', false);
          $('#so_head\\[comp_name\\]').prop('readonly', false);
          $('#so_head\\[bill_state\\]').prop('disabled', false);
        }
        icssmExist = false;
      }

      ajax_check_email($('#so_head\\[bill_email\\]').val());
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error(xhr, ajaxOptions, thrownError);
    }
  });
}

function ajax_check_email($email) {
  if($email == ''){
    $('.email-exist').css('display', 'none');
    return;
  }

  $.ajax({
    type: 'POST',
    url: base_url + 'salesorder/ajax_check_email',
    data: { 
      so_id: $('#so_head\\[so_id\\]').val(), 
      email: $email 
    },
    success: function (data) {
      var parsed = JSON.parse(data);
      if (parsed.exist) {
        $('.email-exist').css('display', 'block');
      } else {
        $('.email-exist').css('display', 'none');
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error(xhr, ajaxOptions, thrownError);
    }
  });
}

function ajax_validate_tin(tin, idValue, idType) {  
  $.ajax({
    type: 'POST',
    url: base_url + 'ajax/validate_tin',
    data: { 
      tin: tin, 
      idValue: idValue,
      idType: idType
    },
    dataType: 'json',
    success: function (response) {
        $('.tin-invalid').css('display', 'none');
        $('.tin-valid').css('display', 'none');
        $('.fail-connection').css('display', 'none');

        if(response.connected == false){
          $('.fail-connection').css('display', 'block');
        }else if(response.is_valid){
          $('.tin-valid').css('display', 'block');
        }else{
          $('.tin-invalid').css('display', 'block');
        }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.error(xhr, ajaxOptions, thrownError);
    }
  });
}

$('#so_head\\[reg_type\\]').change(function () {
  let companyFields = $('.company_related');
  let idValue = '';
	let idType = '';
  if ($('#so_head\\[reg_type\\]').val() == 'r') {
    companyFields.toggle(false);
    ajax_check_icno($('#so_head\\[icno\\]').val());
    $('.ssm-exist').css('display', 'none');
    $(".ic_related").insertAfter(".ssm_related");
    $(".ic-title").css("font-weight", "bold");
    $(".ssm-title").css("font-weight", "normal");
    idValue = $('#so_head\\[icno\\]').val().trim();
	  idType = 'NRIC';
  } else {
    companyFields.toggle(true);
    ajax_check_ssm($('#so_head\\[comp_num\\]').val());
    $('.icno-exist').css('display', 'none');
    $(".ic_related").insertAfter(".customer_name_field");
    $(".ic-title").css("font-weight", "normal");
    $(".ssm-title").css("font-weight", "bold");
    idValue = $('#so_head\\[comp_num\\]').val().trim();
	  idType = 'BRN';
  }

  if($('#so_head\\[tin\\]').val() !== '') {
		ajax_validate_tin($('#so_head\\[tin\\]').val().trim(), idValue, idType);
	}

  const regType = $('#so_head\\[reg_type\\]').val().toLowerCase();
  product_list = all_product_list.filter(item => {
    const category = item.category.toLowerCase();
    if (regType === 'r') {
      return category === 'r';
    } else if (regType === 'b') {
      return category === 'b' || category === 's';
    } else if (regType === 'o') {
      return category !== 'r';
    }
    return false;
  });
  $('#so_details\\[item_name\\]').autocomplete('option', 'source', product_list);

  $('#so_details\\[item_name\\]').val('');
  $('#so_details\\[deposit\\]').val(0.00);
  $('#so_details\\[monthly_charge\\]').val(0.00);
  $('#so_details\\[installation\\]').val(0.00);
  $('#so_details\\[one_time_charge\\]').val(0.00);

  $('#so_head\\[base_amount\\]').val(0.00);
  $('#so_head\\[grand_subtotal\\]').val(0.00);
  $('#so_head\\[grand_total\\]').val(0.00);
});

$(document).on('click', '.btn-verify', function (e) {

  if ($.trim($('#so_head\\[preferred_login\\]').val()) != '') {

    $.ajax({
      type: "POST",
      url: base_url + "salesorder/ajax_verify_radius_account",
      dataType: "json",
      data: { login_username: $("#so_head\\[preferred_login\\]").val() },
      success: function (data) {

        if (data['exist'] == 1) {

          $("#so_head[preferred_login]").css("border", "1px solid red");
          $(".verifyAccountFailed").show();
          $(".verifyAccountSuccess").hide();
          $("#btSave").prop("disabled", true);
          $("#btProfile").prop("disabled", true);

        } else {

          $("#so_head[preferred_login]").css("border", "1px solid lightgray");
          $(".verifyAccountFailed").hide();
          $(".verifyAccountSuccess").show();
          $("#btSave").prop("disabled", false);
          $("#btProfile").prop("disabled", false);
          btProfileWordingHandle();
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(xhr);
        console.log(ajaxOptions);
        console.log(thrownError);
      }
    });

  } else {
    alert('Fill in login username to verify ...');
  }
});

function addSodetail() {
  let newLineNum = $("#so_detail_body tr").length ? parseInt($("#so_detail_body tr").last().attr('data-line_num')) + 1 : 1;

  let sodetailTr = $("#so_detail_body").append($("#so_detail_row").first().html()).children().last();
  sodetailTr.attr({ 'data-id': '0', 'data-line_num': newLineNum });
  sodetailTr.find('[data-fieldname="so_detail.line_num"]').text(newLineNum);
  sodetailTr.find('[data-fieldname="so_detail.item_name"] input').focus().select();
}

function delSodetail(e) {
  const row = $(e).closest('tr');

  //if (row.data('id')!="0") 
  //deleteDetailRow('po_detail', row.data('id'));

  //modify dia vars before removing the row
  $('#so_head\\[dia_vars\\]').val(JSON.stringify(getDIAJSON()));
  let this_line_num = $(e).parent().parent().attr('data-line_num');
  let total_vars = JSON.parse($('.so_detail-row[data-line_num="' + this_line_num + '"] input[name="so_detail.dia_vars"]').val()).length;
  //console.log(total_vars);
  let middleIndex = 0;

  $('#so_detail_table input[name="so_detail.dia_vars"]').each(function (i, obj) {

    let this_line_no = $(this).parent().attr('data-line_num');
    if (this_line_no == this_line_num) {
      return false;
    }

    let this_vars = JSON.parse($(this).val());
    for (let i = 0; i < this_vars.length; i++) {
      middleIndex++;
    }
  });

  //console.log(middleIndex);

  //modify the dia json if u removing one line
  let existing_dia_vars = JSON.parse($('#so_head\\[dia_vars\\]').val());
  let new_array = [...existing_dia_vars.slice(0, middleIndex), ...existing_dia_vars.slice(middleIndex + parseInt(total_vars))];
  $('#so_head\\[dia_vars\\]').val(JSON.stringify(new_array));

  row.remove();

  $("#so_detail_body tr").each((i, el) => {
    $(el).attr('data-line_num', i + 1)
      .find('[data-fieldname="so_detail.line_num"]').text(i + 1);
  });

  auto_gen_dia();

  calc_grandtotal();
}

function calc_total(el) {
  const currentRow = el.closest('tr');

  const price = parseFloat(currentRow.find('[data-fieldname="so_detail.price"] input').val()) || 0;
  const quantity = parseFloat(currentRow.find('[data-fieldname="so_detail.quantity"] input').val()) || 0;

  const tax_percent = parseFloat(currentRow.find('[data-fieldname="so_detail.tax_rate"] input').val()) || 0;

  let pre_subtotal = (price * quantity);
  const subtotal = pre_subtotal.toFixed(2);

  let tax = 0.00;
  if (tax_percent > 0) {
    tax = ((pre_subtotal * tax_percent) / 100).toFixed(2);
    pre_subtotal = pre_subtotal + ((pre_subtotal * tax_percent) / 100);
  }

  const total = pre_subtotal.toFixed(2);

  currentRow.find('[data-fieldname="so_detail.subtotal"]').text(subtotal);
  currentRow.find('[data-fieldname="so_detail.tax"]').text(tax);
  currentRow.find('[data-fieldname="so_detail.total"]').text(total);

  calc_grandtotal();
}

function calc_grandtotal() {
  let grandTotal = 0;
  let grandSubtotal = 0;
  let grandTax = 0;
  $("#so_detail_body tr").each(function () {
    const rowSubtotal = parseFloat($(this).find('[data-fieldname="so_detail.subtotal"]').text()) || 0;
    const rowTotal = parseFloat($(this).find('[data-fieldname="so_detail.total"]').text()) || 0;
    const rowTax = parseFloat($(this).find('[data-fieldname="so_detail.tax"]').text()) || 0;
    grandSubtotal += rowSubtotal;
    grandTotal += rowTotal;
    grandTax += rowTax;
  });

  const formattedTotal = grandTotal.toFixed(2);
  const formattedSubTotal = grandSubtotal.toFixed(2);
  const formattedTax = grandTax.toFixed(2);

  $('#so_head\\[grand_subtotal\\]').val(formattedSubTotal);
  $('#so_head\\[grand_tax\\]').val(formattedTax);
  $('#so_head\\[grand_total\\]').val(formattedTotal);
  $('#so_head\\[base_amount\\]').val(formattedSubTotal);
  $('#grand_total_so').html(formattedTotal);
}

function getDetailRows(tableName) {
  var detail_rows = [];
  let childRow = $(`.${tableName}-row:visible`);
  let count = childRow.length;
  if (count > 0) {
    detail_rows = new Array(count).fill().map((_, i) => ({}));

    $(childRow).each(function (i, row) {
      $(row).find(`[name^='${tableName}.'],[data-fieldname^='${tableName}.']`).each(function (f, e) {
        let fieldname = ($(e).prop('name') || $(e).data('fieldname') || '').split('.')[1];
        detail_rows[i][fieldname] = $(e).val() || $(e).text() || '';
      })
    })
  }
  return detail_rows;
}

function getDIAJSON() {
  let submit_obj = [];

  //submit_obj = new Array($('.so_detail-row').length).fill().map((_,i)=>({}));

  $('.dia_text_row').each(function (i, obj) {
    try {
      //let dash_split = $(this).attr('name').split("-");
      //let underscore_split = dash_split[1].split("_");

      submit_obj.push($(this).val());

    } catch (e) {
      //do nothing
    }
  });

  return submit_obj;
}

function auto_gen_lines() {
  try {
    let existing_rows = JSON.parse($('#so_detail_rows').val());

    for (var key in existing_rows) {
      //console.log(existing_rows[key]);

      addSodetail();
      let line_num = existing_rows[key].line_num;
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.po_detail_id"]').val(existing_rows[key].po_detail_id);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.prod_id"]').val(existing_rows[key].prod_id);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.dia_vars"]').val(existing_rows[key].dia_vars);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.item_name"]').val(existing_rows[key].item_name);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.price"]').val(existing_rows[key].price);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.quantity"]').val(existing_rows[key].quantity);
      $('.so_detail-row[data-line_num="' + line_num + '"] input[name="so_detail.tax_rate"]').val(existing_rows[key].tax_rate);
      $('.so_detail-row[data-line_num="' + line_num + '"] td[data-fieldname="so_detail.tax"]').html(existing_rows[key].tax);
      $('.so_detail-row[data-line_num="' + line_num + '"] td[data-fieldname="so_detail.subtotal"]').html(existing_rows[key].subtotal);
      $('.so_detail-row[data-line_num="' + line_num + '"] td[data-fieldname="so_detail.total"]').html(existing_rows[key].total);
    }

    //after done filling, calc grand total
    calc_grandtotal();

  } catch (e) {
    //do nothing
  }
}

function auto_gen_dia() {
  let dia_html = ``;
  let existing_dia_vars = [];

  if ($('#so_head\\[dia_vars\\]').val() != undefined) {
    existing_dia_vars = JSON.parse($('#so_head\\[dia_vars\\]').val());
  }

  let cnt = 0;

  $('#dia_rows_area').html('');

  try {
    let this_vars = JSON.parse($('#so_details\\[dia_vars\\]').val());

    for (let i = 0; i < this_vars.length; i++) {
      if (this_vars[i]) {

        let this_value = '';
        if (existing_dia_vars[cnt]) {
          this_value = existing_dia_vars[cnt];
        }

        let this_pkg_name = $('#so_details\\[item_name\\]').val();

        dia_html = `
        <div class="col-lg-6">
          <div class="input-group">
            <span class="input-group-addon input_group"  >`+ this_pkg_name + ` - ` + this_vars[i] + `</span>
            <input 
              name    = "dia_vars_fields-`+ i + `" 
              value   = "`+ this_value + `"
              type    = "text" 
              class   = "form-control dia_text_row" 
               />
          </div>
        </div>
        `;

        $('#dia_rows_area').append(dia_html);

        cnt++;
      }
    }

  } catch (e) {
    //do nothing
  }

  let dia_vars_count = $('.dia_text_row').length;

  if (dia_vars_count > 0) {
    $('#dia_area').css('display', '');
  } else {
    $('#dia_area').css('display', 'none');
  }

}

function xxxauto_gen_dia() {

  let dia_html = ``;

  let existing_dia_vars = JSON.parse($('#so_head\\[dia_vars\\]').val());
  let cnt = 0;

  $('#dia_rows_area').html('');

  $('#so_detail_table input[name="so_detail.dia_vars"]').each(function (i, obj) {

    let this_line_no = $(this).parent().attr('data-line_num');
    let this_pkg_name = $(this).parent().find('input[name="so_detail.item_name"]').val();
    //console.log(this_line_no);

    try {
      let this_vars = JSON.parse($(this).val());
      for (let i = 0; i < this_vars.length; i++) {
        if (this_vars[i]) {

          let this_value = '';
          if (existing_dia_vars[cnt]) {
            this_value = existing_dia_vars[cnt];
          }

          dia_html = `
          <div class="col-lg-6">
            <div class="input-group">
              <span class="input-group-addon input_group"  >`+ this_pkg_name + ` - ` + this_vars[i] + `</span>
              <input 
                name    = "dia_vars_fields-`+ this_line_no + `_` + i + `" 
                value   = "`+ this_value + `"
                type    = "text" 
                class   = "form-control dia_text_row" 
                 />
            </div>
          </div>
          `;

          $('#dia_rows_area').append(dia_html);

          cnt++;
        }
      }
    } catch (e) {
      //do nothing
    }
  });

  let dia_vars_count = $('.dia_text_row').length;

  if (dia_vars_count > 0) {
    $('#dia_area').css('display', '');
  } else {
    $('#dia_area').css('display', 'none');
  }

}

function autoFillAddress() {

  if ($('#so_head\\[building_no\\]').val() == '0') {
    return false;
  }

  $.ajax({
    dataType: 'json',
    type: 'POST',
    data: { 'building_no': $('#so_head\\[building_no\\]').val() },
    url: base_url + 'ajax/get_building_details',
    beforeSend: function (data) {
    },
    success: function (data) {
      var building = data;

      if(!icssmExist){
        $('#so_head\\[bill_addr_1\\]').val(building['addr_1']);
        $('#so_head\\[bill_addr_2\\]').val(building['addr_2']);
        $('#so_head\\[bill_addr_3\\]').val(building['addr_3']);
        $('#so_head\\[bill_city\\]').val(building['city']);
        $('#so_head\\[bill_state\\]').val(building['state']);
        $('#so_head\\[bill_postcode\\]').val(building['postcode']);
      }

      $('#so_head\\[del_addr_1\\]').val(building['addr_1']);
      $('#so_head\\[del_addr_2\\]').val(building['addr_2']);
      $('#so_head\\[del_addr_3\\]').val(building['addr_3']);
      $('#so_head\\[del_city\\]').val(building['city']);
      $('#so_head\\[del_state\\]').val(building['state']);
      $('#so_head\\[del_postcode\\]').val(building['postcode']);
    },
    complete: function (msg) {
    },
    error: function (a, b, c) {
      console.log(a);
      console.log(b);
      console.log(c);
    }
  });
}

function print_salesorder() {
  let salesorder_id = $('#so_head\\[so_id\\]').val();
  let url = base_url + 'salesorder/print_quotation/' + salesorder_id;
  //window.open(url, '_blank');
  window.location = url;
  return false;
}

function btProfileWordingHandle() {
  if (typeof customer_no !== 'undefined' && customer_no != null && customer_no != '') {
    $("#btProfile").html('<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i> Recreate Profile');
  }
}

function ajax_filter(filter_pressed = 0) {
  //post values

  if (filter_pressed == 1) {
    $("#page_item_no").val(0);
  }

  $.ajax({
    type: "POST",
    url: base_url + "salesorder/salesorder_rows/0",
    data: {
      page_item_no: $("#page_item_no").val(),
      txt_search: $("#txt_search").val(),
      sel_status: $("#sel_status").val(),
      sel_installation: $("#sel_installation").val(),
    },
    success: function (data) {
      $('.table_rows_area').html(data);
    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.log(xhr);
      console.log(ajaxOptions);
      console.log(thrownError);
    }
  });

}

function ajax_clear() {
  $("#page_item_no").val(0);
  $("#txt_search").val('');
  $("#sel_status").val('1');
  $("#sel_installation").val('all');
  ajax_filter(1);
}