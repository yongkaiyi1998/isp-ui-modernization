$(document).ready(function() {
    $('#is_terms_accepted').on('change', function() {
        let is_checked = $(this).is(':checked') ? 1 : 0;
        let $checkbox = $(this);
        $checkbox.prop('disabled', true); // avoid multiple access

        let current_url = window.location.pathname;
        let url_parts = current_url.split('/');
        let customer_no = url_parts[url_parts.length - 1]; 

        $.ajax({
            type: "POST",
            url: base_url + "customer/update_is_terms_accepted",
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
    let customer_no   = window.location.pathname.split('/').pop();
    $.ajax({
        url: base_url + 'customer/get_form_signature/'+customer_no+'/installation form',
        type: 'GET',
        success: function (response) {
          $('#signer_name').val(response.signer_name || '');
          $('#signer_ic').val(response.signer_ic || '');
          $("#editSignatureModal").modal({ focus: false });
        }
    });
}

function open_equipment_popup() {
    let customer_no = window.location.pathname.split('/').pop();

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

function saveSignature() {
    let canvas        = document.getElementById('sig-canvas');
    let signer_name   = $('#signer_name').val();
    let signer_ic     = $('#signer_ic').val();
    let customer_no   = window.location.pathname.split('/').pop();
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
        url: base_url + 'customer/upload_signature',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let data = typeof response === 'string' ? JSON.parse(response) : response;
            if(data.status === 'success') {
                $.gritter.add({ title: 'SUCCESS', text: 'Saved! Reloading...', time: '2000', class_name: 'success-notice' });
                setTimeout(() => location.reload(), 2000);
            } else {
                alert(data.err_msg);
                $saveBtn.prop('disabled', false).text('Save');
            }
        }
    });
}

function saveEquipment() {
    let $saveBtn = $('#equipment-submitBtn');
    let customer_no = window.location.pathname.split('/').pop();
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