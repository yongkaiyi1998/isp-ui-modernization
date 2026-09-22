<style type="text/css">
    .modal-remark {
        max-width: 250px;
        white-space: nowrap;         
        overflow: hidden;         
        text-overflow: ellipsis;
    }

    @media (max-width: 480px) {
        .modal-remark {
            max-width: 100px;
        }
    }
</style>
<div class="modal fade" id="attachment-remark-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">Add A Remark</h3>
            </div>
      
            <div class="modal-body">
                <input type="hidden" id="tosave" name="tosave" value="0" />
                <div class="row">
                    <div class="col-lg-12 col-xs-12" id="remark-div">
                    </div>
                </div>
            </div>

            <div class="modal-footer">

                <button id="attachment-remark-close" type="button" class="btn btn-information"
                    role="button" aria-disabled="false" style="margin:0.2em;display:none;">
                    Close
                </button>

                <button id="attachment-remark-submit" type="button" class="btn btn-success"
                    role="button" aria-disabled="false" style="margin:0.2em;">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('click', '#attachment-remark-submit', function () {
        /*if ($('#modal_attachment_remark').val() == '') {
            alert('Attachment Remark cannot be empty!');
            return false;
        } else {
            let remark = $('#modal_attachment_remark').val();
            $('#attachment_remark').val(remark);*/
            $('#tosave').val('1');
            $('#attachment-remark-modal').modal('hide');
        //}
    });

    $(document).on('click', '#attachment-remark-close', function () {
        $('#attachment-remark-modal').modal('hide');
    });
</script>
