$(document).ready(function() {
    $('#is_terms_accepted').on('change', function() {
        let is_checked = $(this).is(':checked') ? 1 : 0;
        let $checkbox = $(this);
        $checkbox.prop('disabled', true); // avoid multiple access

        //let current_url = window.location.pathname;
        //let url_parts = current_url.split('/');
        //let customer_no = url_parts[url_parts.length - 1]; 

        let customer_no = $('#customer_no').val();

        $.ajax({
            type: "POST",
            url: base_url + "chome/update_is_termination_terms_accepted",
            dataType: "json",
            data: { 
                is_checked: is_checked,
                customer_no: customer_no
            },
            success: function(response) {
                $checkbox.prop('disabled', false); 

                if(response.success) {
                    $.gritter.add({
                        title: 'SUCCESS',
                        text: response.message,
                        time: '5000',
                        class_name: 'info-notice'
                    });
                } else {
                    $.gritter.add({
                        title: 'ERROR',
                        text: response.message,
                        time: '5000',
                        class_name: 'danger-notice'
                    });
                    $checkbox.prop('checked', !is_checked);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $checkbox.prop('disabled', false); 
                $checkbox.prop('checked', !is_checked); 
                console.log(thrownError);
                $.gritter.add({
                    title: 'ERROR',
                    text: 'Network error occurred.',
                    time: '5000',
                    class_name: 'danger-notice'
                });
            }
        });
    });

    //init
    $('#editEquipmentModal input').prop('disabled', true);
    $('#editEquipmentModal select').prop('disabled', true);

    $("#term_effective_date").datepicker({
        format: 'yyyy-mm-dd',
        startDate: '+0d'
    });

});

(function () {
  window.requestAnimFrame = (function (callback) {
    return (
      window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.oRequestAnimationFrame ||
      window.msRequestAnimationFrame ||
      function (callback) {
        window.setTimeout(callback, 1000 / 60);
      }
    );
  })();

  var $canvas = $("#sig-canvas"),
    ctx = $canvas[0].getContext("2d"),
    drawing = false,
    mousePos = { x: 0, y: 0 },
    lastPos = mousePos,
    $sigImage = $("#sig-image"),
    $clearBtn = $("#sig-clearBtn"),
    $submitBtn = $("#sig-submitBtn");

    ctx.strokeStyle = "#222222";
    ctx.lineWidth = 4;

    function getMousePos(canvasDom, mouseEvent) {
      var rect = canvasDom.getBoundingClientRect();
      var scaleX = canvasDom.width / rect.width;
      var scaleY = canvasDom.height / rect.height;

      return {
          x: (mouseEvent.clientX - rect.left) * scaleX,
          y: (mouseEvent.clientY - rect.top) * scaleY
      };
  }

  function getTouchPos(canvasDom, touchEvent) {
      var rect = canvasDom.getBoundingClientRect();
      var scaleX = canvasDom.width / rect.width;
      var scaleY = canvasDom.height / rect.height;

      return {
          x: (touchEvent.touches[0].clientX - rect.left) * scaleX,
          y: (touchEvent.touches[0].clientY - rect.top) * scaleY
      };
  }

  function renderCanvas() {
    if (drawing) {
      ctx.moveTo(lastPos.x, lastPos.y);
      ctx.lineTo(mousePos.x, mousePos.y);
      ctx.stroke();
      lastPos = mousePos;
    }
  }

  function clearCanvas() {
    $canvas[0].width = $canvas[0].width;
    ctx.lineWidth = 4;
  }

  function drawSignature() {
    var dataUrl = getCanvasDataURL();
    $sigImage.attr("src", dataUrl);
  }

  function preventScroll(e) {
    if ($(e.target).is($canvas)) {
      e.preventDefault();
    }
  }

  $canvas
    .on("mousedown", function (e) {
      drawing = true;
      lastPos = getMousePos($canvas[0], e);
    })
    .on("mouseup", function () {
      drawing = false;
    })
    .on("mousemove", function (e) {
      mousePos = getMousePos($canvas[0], e);
    })
    .on("touchstart", function (e) {
      mousePos = getTouchPos($canvas[0], e.originalEvent);
      var touch = e.originalEvent.touches[0],
        me = new MouseEvent("mousedown", {
          clientX: touch.clientX,
          clientY: touch.clientY,
        });
      $canvas[0].dispatchEvent(me);
    })
    .on("touchmove", function (e) {
      var touch = e.originalEvent.touches[0],
        me = new MouseEvent("mousemove", {
          clientX: touch.clientX,
          clientY: touch.clientY,
        });
      $canvas[0].dispatchEvent(me);
    })
    .on("touchend", function () {
      var me = new MouseEvent("mouseup", {});
      $canvas[0].dispatchEvent(me);
    });

  $("body").on("touchstart touchend touchmove", preventScroll);

  (function drawLoop() {
    requestAnimFrame(drawLoop);
    renderCanvas();
  })();

  $clearBtn.on("click", function () {
    clearCanvas();
    $sigImage.attr("src", "");
    drawSignature();
  });

  $submitBtn.on("click", function () {
    drawSignature();
  });

  $(()=>{
    drawSignature();
  })
})();

function open_signature_popup() {
    let customer_no   = $('#customer_no').val();
    $.ajax({
        url: base_url + 'customer/get_form_signature/'+customer_no+'/installation form',
        type: 'GET',
        success: function (response) {
          $('#signer_name').val(response.signer_name || '');
          $('#signer_ic').val(response.signer_ic || '');
          $("#editSignatureModal").modal({ focus: false });
          //snap focus to dialog
            setTimeout(() => {
                $("#signer_name").focus();
            }, 500);
        }
    });
}

function open_staff_popup() {
    let customer_no   = $('#customer_no').val();
    $.ajax({
        url: base_url + 'customer/init_termination_send/'+customer_no,
        type: 'GET',
        success: function (response) {
          //$('#signer_name').val(response.signer_name || '');
          //$('#signer_ic').val(response.signer_ic || '');
          //$("#editSignatureModal").modal({ focus: false });

            let inst_addr = '';

            let inst_unit_no = (response.inst_unit_no || '');
            let inst_building = (response.building || '');
            let inst_addr1 = (response.inst_addr1 || '');
            let inst_addr2 = (response.inst_addr2 || '');
            let inst_addr3 = (response.inst_addr3 || '');
            let inst_city = (response.inst_city || '');
            let inst_postcode = (response.inst_postcode || '');
            let inst_state = (response.inst_state || '');

            let inst_state_text = sel_state_list.find(
                item => item.state_code == inst_state 
            )?.name ?? '';

            let inst_building_text = sel_building_list.find(
                item => item.building_no == inst_building 
            )?.name ?? '';

            if (inst_building != '') {
                inst_addr += inst_building_text+' ';
            }
            if (inst_unit_no != '') {
                inst_addr += inst_unit_no+'<br>';
            }
            if (inst_addr1 != '') {
                inst_addr += inst_addr1+'<br>';
            }
            if (inst_addr2 != '') {
                inst_addr += inst_addr2+'<br>';
            }
            if (inst_addr3 != '') {
                inst_addr += inst_addr3+'<br>';
            }
            if (inst_city != '') {
                inst_addr += inst_city+' ';
            }
            if (inst_postcode != '') {
                inst_addr += inst_postcode+'<br>';
            }
            if (inst_state != '') {
                inst_addr += inst_state_text;
            }

            //generate a card with information from latest contract 
            detail_card = `
            <div class="alert alert-info" style="margin-bottom: 15px; border-left: 5px solid #31708f; background-color: #f7fbfd;">
                <span style="margin-top: 0; color: #31708f;">
                    <strong>
                        <i class="fa fa-info-circle"></i> Customer Account Info
                    </strong>
                </span>
                <hr style="margin-top: 5px; margin-bottom: 10px; border-top: 1px solid #bce8f1;">
                <table style="margin-bottom: 10px; background: transparent;">
                    <tbody>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Account No:</td>
                            <td class="text-primary"><strong>`+(response.customer_no || '')+`</strong></td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Name/Company:</td>
                            <td class="text-primary">`+(response.name || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Package:</td>
                            <td class="text-primary">`+(response.data_package_name || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Contract Months:</td>
                            <td class="text-primary">`+(response.contract_month || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Contract:</td>
                            <td class="text-primary">`+(response.contract_start_date || '')+` to `+(response.contract_end_date || '')+`</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:text-top; width: 30%; text-align: right; padding-right: 15px;" class="black">Installation Address:</td>
                            <td class="text-primary">`+inst_addr+`</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            `;
            $('#account_detail_card').html(detail_card);

            $('#term_customer_no').val((response.customer_no || ''));

            let load_pic_name = (response.data_pic_name || '');
            let load_pic_email = (response.data_pic_email || '');
            let load_pic_mobile = (response.data_pic_mobile || '');

            if (load_pic_name == '') {
                load_pic_name = (response.profile_name || '');
            }

            if (load_pic_email == '') {
                load_pic_email = (response.profile_email_1 || '');
            }

            if (load_pic_mobile == '') {
                load_pic_mobile = (response.profile_mobile_num || '');
            }

            $('#term_pic_name').val(load_pic_name);
            $('#term_email').val(load_pic_email);
            $('#term_mobile').val(load_pic_mobile);

            if ((response.data_penalty || '0') != '1') {
                $('#term_penalty').prop("checked", false);
            } else {
                $('#term_penalty').prop("checked", true);
            }
            switch_unbilled();

            let data_unbilled_months = parseInt((response.data_unbilled_months || '0'));
            let data_unbilled_amt = parseFloat((response.data_unbilled_amt || '0.00'));

            if (data_unbilled_months > 0) {
                //do nothing
            } else {
                data_unbilled_months = (response.ori_unbilled_months || '0');
            }

            if (data_unbilled_amt > 0.001) {
                //do nothing
            } else {
                data_unbilled_amt = (response.ori_unbilled_amt || '0.00');
            }

            $('#term_unbilled_months').val(data_unbilled_months);
            $('#term_amount').val(data_unbilled_amt);

            const today = new Date().toISOString().split('T')[0];

            let data_effective_date = (response.data_effective_date || today);

            $('#term_effective_date').val(data_effective_date);

            //put default input fields such as unbilled months and amount

            $("#editTerminationPIC").modal({ focus: false });
        }
    });
}

function open_staff_popup_end() {
    let customer_no   = $('#customer_no').val();
    $.ajax({
        url: base_url + 'customer/finalize_termination/'+customer_no,
        type: 'GET',
        success: function (response) {
          //$('#signer_name').val(response.signer_name || '');
          //$('#signer_ic').val(response.signer_ic || '');
          //$("#editSignatureModal").modal({ focus: false });

            let inst_addr = '';

            let inst_unit_no = (response.inst_unit_no || '');
            let inst_building = (response.building || '');
            let inst_addr1 = (response.inst_addr1 || '');
            let inst_addr2 = (response.inst_addr2 || '');
            let inst_addr3 = (response.inst_addr3 || '');
            let inst_city = (response.inst_city || '');
            let inst_postcode = (response.inst_postcode || '');
            let inst_state = (response.inst_state || '');

            let inst_state_text = sel_state_list.find(
                item => item.state_code == inst_state 
            )?.name ?? '';

            let inst_building_text = sel_building_list.find(
                item => item.building_no == inst_building 
            )?.name ?? '';

            if (inst_building != '') {
                inst_addr += inst_building_text+' ';
            }
            if (inst_unit_no != '') {
                inst_addr += inst_unit_no+'<br>';
            }
            if (inst_addr1 != '') {
                inst_addr += inst_addr1+'<br>';
            }
            if (inst_addr2 != '') {
                inst_addr += inst_addr2+'<br>';
            }
            if (inst_addr3 != '') {
                inst_addr += inst_addr3+'<br>';
            }
            if (inst_city != '') {
                inst_addr += inst_city+' ';
            }
            if (inst_postcode != '') {
                inst_addr += inst_postcode+'<br>';
            }
            if (inst_state != '') {
                inst_addr += inst_state_text;
            }

            //generate a card with information from latest contract 
            detail_card = `
            <div class="alert alert-info" style="margin-bottom: 15px; border-left: 5px solid #31708f; background-color: #f7fbfd;">
                <span style="margin-top: 0; color: #31708f;">
                    <strong>
                        <i class="fa fa-info-circle"></i> Customer Account Info
                    </strong>
                </span>
                <hr style="margin-top: 5px; margin-bottom: 10px; border-top: 1px solid #bce8f1;">
                <table style="margin-bottom: 10px; background: transparent;">
                    <tbody>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Account No:</td>
                            <td class="text-primary"><strong>`+(response.customer_no || '')+`</strong></td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Name/Company:</td>
                            <td class="text-primary">`+(response.name || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Package:</td>
                            <td class="text-primary">`+(response.data_package_name || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Contract Months:</td>
                            <td class="text-primary">`+(response.contract_month || '')+`</td>
                        </tr>
                        <tr>
                            <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Contract:</td>
                            <td class="text-primary">`+(response.contract_start_date || '')+` to `+(response.contract_end_date || '')+`</td>
                        </tr>
                        <tr>
                            <td style="vertical-align:text-top; width: 30%; text-align: right; padding-right: 15px;" class="black">Installation Address:</td>
                            <td class="text-primary">`+inst_addr+`</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            `;
            $('#account_detail_card_2').html(detail_card);

            let termination_data = `
            <div class="alert alert-info" style="margin-bottom: 15px; border-left: 5px solid #348a2d; background-color: #e8fae1;">
                <span style="margin-top: 0; color: #348a2d;">
                    <strong>
                        <i class="fa fa-info-circle"></i> Termination Info
                    </strong>
                    <hr style="margin-top: 5px; margin-bottom: 10px; border-top: 1px solid #c1ebb7;">
                    <table style="margin-bottom: 10px; background: transparent;">
                        <tbody>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">PIC Name:</td>
                                <td class="text-success"><strong>`+(response.data_pic_name || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">PIC Email:</td>
                                <td class="text-success"><strong>`+(response.data_pic_email || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">PIC Mobile:</td>
                                <td class="text-success"><strong>`+(response.data_pic_mobile || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Effective Date:</td>
                                <td class="text-success"><strong>`+(response.data_effective_date || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Unbilled Months:</td>
                                <td class="text-success"><strong>`+(response.data_unbilled_months || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Unbilled Amount:</td>
                                <td class="text-success"><strong>`+(response.data_unbilled_amt || '')+`</strong></td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: right; padding-right: 15px;" class="black">Signature:</td>
                                <td style="font-size:12px; position:relative; text-align:left; min-height:120px;" id="cloned_signature_area">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </span>
            </div>
            `;

            $('#termination_data_card').html(termination_data);

            $('#signature').clone().attr('id', 'cloned_signature').appendTo('#cloned_signature_area');

            $("#finalizeTermination").modal({ focus: false });
        }
    });
}

function finalize_term() {
    let formData        = new FormData();
    let $saveBtn        = $('#finalize-save');
    let $closeBtn       = $('#finalize-close');

    formData.append('customer_no', $('#customer_no').val());

    $saveBtn.prop('disabled', true).text('Processing...');
    $closeBtn.prop('disabled', true);
    
    $.ajax({
        url: base_url + 'customer/finalize_term',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                $.gritter.add({
                  title: 'SUCCESS',
                  text: 'Termination has been finalized.',
                  time: '5000',
                  class_name: 'info-notice'
                });
                setTimeout(function() {
                    window.location.reload();
                }, 2000); // 2000 milliseconds = 2 seconds
            } else {
                $.gritter.add({
                  title: 'ERROR',
                  text: data.msg,
                  time: '5000',
                  class_name: 'info-notice'
                });
                $saveBtn.prop('disabled', false).text('Finalize Termination');
                $closeBtn.prop('disabled', false);
            }
        }
    });
}

function switch_unbilled() {
    let tick_unbilled = $('#term_penalty').prop("checked");
    if (tick_unbilled) {
        $('.unbilled_area').css('display', '');
    } else {
        $('.unbilled_area').css('display', 'none');
    }
}

function save_term_only(send_form=0) {

    let formData = new FormData();
    formData.append('customer_no', $('#term_customer_no').val());
    formData.append('pic_name', $('#term_pic_name').val());
    formData.append('pic_email', $('#term_email').val());
    formData.append('pic_mobile', $('#term_mobile').val());
    let term_penalty = '0';
    if ($('#term_penalty').prop("checked")) {
        term_penalty = '1';
    }
    formData.append('penalty', term_penalty);
    formData.append('unbilled_months', $('#term_unbilled_months').val());
    formData.append('unbilled_amt', $('#term_amount').val());
    formData.append('effective_date', $('#term_effective_date').val());
    //dont send yet
    formData.append('send_form', send_form);

    $.ajax({
        url: base_url + 'customer/save_term_only',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                if (send_form == 1) {
                    //after send do what?
                    $.gritter.add({
                      title: 'SUCCESS',
                      text: 'Sucessfully sent temporary password to user for further action.',
                      time: '5000',
                      class_name: 'info-notice'
                    });

                    $("#term-send").prop("disabled", true);
                    $("#term-save").prop("disabled", true);

                    $("#editTerminationPIC").modal('hide');

                    setTimeout(function() {
                        window.location.reload();
                    }, 2000); // 2000 milliseconds = 2 seconds

                } else {
                    $.gritter.add({
                      title: 'SUCCESS',
                      text: 'Sucessfully saved termination details.',
                      time: '5000',
                      class_name: 'info-notice'
                    });

                    $("#editTerminationPIC").modal('hide');
                }
            } else {
                $.gritter.add({
                  title: 'ERROR',
                  text: data.msg,
                  time: '5000',
                  class_name: 'info-notice'
                });
            }
        }
    });

}

function open_customer_signature_popup() {
    let customer_no   = $('#customer_no').val();
    $.ajax({
        url: base_url + 'customer/get_form_signature/'+customer_no+'/termination form',
        type: 'GET',
        success: function (response) {
          $('#signer_name').val(response.signer_name || '');
          $('#signer_ic').val(response.signer_ic || '');
          $("#editSignatureModal").modal({ focus: false });
        }
    });
}

function open_equipment_popup() {
    let customer_no = $('#customer_no').val();

    $.ajax({
        url: base_url + 'customer/get_equipment_form/' + customer_no,
        type: 'GET',
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;

            $('.equipment-type').val('').trigger('change');
            $('.equipment-serial').val('');

            if (Array.isArray(data)) {
                data.forEach((item, index) => {
                    let rowNum = index + 1;
                    $(`select[name="equipment[${rowNum}][type_id]"]`).val(item.equipment_type_id).trigger('change');
                    $(`input[name="equipment[${rowNum}][serial_no]"]`).val(item.serial_no);
                });
            }

            $("#editEquipmentModal").modal({ focus: false });
        }
    });
}

function getCanvasDataURL() {
  var canvas = $("#sig-canvas")[0];
  var ctx = canvas.getContext("2d");

  var tempCanvas = document.createElement("canvas");
  var tempCtx = tempCanvas.getContext("2d");

  tempCanvas.width = canvas.width;
  tempCanvas.height = canvas.height;

  tempCtx.fillStyle = "#ffffff";
  tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

  tempCtx.drawImage(canvas, 0, 0);

  return tempCanvas.toDataURL("image/jpeg", 0.8);
}


function isCanvasBlank(canvas) {
  const blank = document.createElement("canvas");
  blank.width = canvas.width;
  blank.height = canvas.height;
  return canvas.toDataURL() === blank.toDataURL();
}

function clearForm() {
  $('#mobile_number').val('');
  $('#guardian_id').val(0);
  $('#guardian_name').val('');
  $('#participants-list').html('');
  $('#mobile_number').prop('readonly', false);
  $('#guardian_name').prop('readonly', false);
}


function dataURLtoBlob(dataURL) {
  let arr = dataURL.split(','), mime = arr[0].match(/:(.*?);/)[1],
      bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
  while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
  }
  return new Blob([u8arr], { type: mime });
}

function reject_form() {
  $("#rejectTermination").modal({ focus: false });
  //snap focus to dialog
    setTimeout(() => {
        $("#reject-reason").focus();
    }, 500);
}

function reject_form_save() {
    let customer_no   = $('#customer_no').val();
    let reject_reason   = $('#reject-reason').val();

    let $saveBtn      = $('#reject-submit');

    let validations = [
      {
        condition: reject_reason == '',
        message: 'Please provide rejection reason.'
      },
    ];

    let hasError = false;

    for (let i = 0; i < validations.length; i++) {
      if (validations[i].condition) {
        hasError = true;

        $.gritter.add({
          title: 'ERROR',
          text: validations[i].message,
          time: '5000',
          class_name: 'info-notice'
        });
      }
    }

    if (hasError) return;

    $saveBtn.prop('disabled', true).text('Processing...');

    let formData = new FormData();
    formData.append('reject_reason', reject_reason);
    formData.append('customer_no', customer_no);

    $.ajax({
        url: base_url + 'chome/reject_form',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                $.gritter.add({ title: 'SUCCESS', text: 'Done save and submitted to administrators.', time: '2000', class_name: 'success-notice' });
                setTimeout(() => location.reload(), 2000);
            } else {
                alert(data.err_msg);
                $saveBtn.prop('disabled', false).text('Save');
            }
        }
    });

}

function saveSignature() {

    $("#tnc_div").css("border", "none");

    let canvas        = document.getElementById('sig-canvas');
    let signer_name   = $('#signer_name').val();
    let signer_ic     = $('#signer_ic').val();
    let customer_no   = $('#customer_no').val();
    let $saveBtn      = $('#sig-submitBtn');
    let $clearBtn     = $('#sig-clearBtn');

    let validations = [
      {
        condition: isCanvasBlank(canvas) && !$('#signature').length,
        message: 'Please provide a signature.'
      },
      {
        condition: signer_name == '',
        message: 'Please provide signer name.'
      },
      {
        condition: signer_ic == '',
        message: 'Please provide signer IC No.'
      }
    ];

    let hasError = false;

    for (let i = 0; i < validations.length; i++) {
      if (validations[i].condition) {
        hasError = true;

        $.gritter.add({
          title: 'ERROR',
          text: validations[i].message,
          time: '5000',
          class_name: 'info-notice'
        });
      }
    }

    if (hasError) return;

    $saveBtn.prop('disabled', true).text('Processing...');
    $clearBtn.prop('disabled', true);

    let tempCanvas = document.createElement('canvas');
    let tCtx = tempCanvas.getContext('2d');
    tempCanvas.width = canvas.width;
    tempCanvas.height = canvas.height;
    tCtx.drawImage(canvas, 0, 0);

    let imgData = tCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
    let data = imgData.data;
    for (let i = 0; i < data.length; i += 4) {
        if (data[i] > 200 && data[i+1] > 200 && data[i+2] > 200) {
            data[i+3] = 0;
        }
    }
    tCtx.putImageData(imgData, 0, 0);

    let dataURL = tempCanvas.toDataURL("image/png");
    let blob = dataURLtoBlob(dataURL);
    let formData = new FormData();
    if(!isCanvasBlank(canvas)){
      formData.append('signature_file', blob, 'signature.png');
    }
    formData.append('signer_name', signer_name);
    formData.append('signer_ic', signer_ic);
    formData.append('customer_no', customer_no);

    $.ajax({
        url: base_url + 'chome/upload_signature',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                $.gritter.add({ title: 'SUCCESS', text: 'Done save and submitted to administrators.', time: '2000', class_name: 'success-notice' });
                setTimeout(() => location.reload(), 2000);
            } else if (data.status === 'no_terms') {
                //error... one of them is forgot to tick terms and conditions, alert the user
                $.gritter.add({
                    title: 'ERROR',
                    text: 'Please read and tick terms and conditions before submitting.',
                    time: '5000',
                    class_name: 'danger-notice'
                });

                $("#editSignatureModal").modal('hide');

                $("#tnc_div").css("border", "1px solid red");

                $saveBtn.prop('disabled', false).text('Save');
                $clearBtn.prop('disabled', false);
            } else {

                alert(data.err_msg);
                $saveBtn.prop('disabled', false).text('Save');
                $clearBtn.prop('disabled', false);
            }
        }
    });
}

function saveEquipment() {
    let $saveBtn = $('#equipment-submitBtn');
    let customer_no = $('#customer_no').val();
    let formData = new FormData();
    let hasData = false;

    let firstType = $('.equipment-row').first().find('select').val();
    if (firstType === '') {
        $.gritter.add({
            title: 'ERROR',
            text: 'Please select the first equipment type.',
            time: '5000',
            class_name: 'info-notice'
        });
        return;
    }

    // Loop all rows to collect data
    $('.equipment-row').each(function(index, element) {
        let typeId = $(element).find('select').val();
        let serialNo = $(element).find('input').val();

        // append if type is selected
        if (typeId !== '') {
            formData.append(`equipment[${index}][type_id]`, typeId);
            formData.append(`equipment[${index}][serial_no]`, serialNo);
            hasData = true;
        }
    });

    formData.append('customer_no', customer_no);

    $saveBtn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: base_url + 'customer/save_equipment_used',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                $.gritter.add({ 
                    title: 'SUCCESS', 
                    text: 'Saved! Reloading...', 
                    time: '2000', 
                    class_name: 'success-notice' 
                });
                setTimeout(() => location.reload(), 2000);
            } else {
                alert(data.err_msg || 'An error occurred');
                $saveBtn.prop('disabled', false).text('Save');
            }
        },
        error: function() {
            alert('Server error. Please try again.');
            $saveBtn.prop('disabled', false).text('Save');
        }
    });
}