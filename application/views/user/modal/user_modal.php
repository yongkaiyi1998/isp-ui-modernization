<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:80vh; display:flex; flex-direction:column;">
            <div class="modal-header" style="flex-shrink:0;">
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
                <h4 class="modal-title">User Detail</h4>
            </div>

            <div class="modal-body" id="userModalContent" style="overflow-y:auto; flex:1;"></div>

            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">
                    Cancel
                </button>

                <button id="btSave"
                    class="btn btn-primary"
                    onclick="save_action('user/save_user','user_detail');"
                    <?php echo check_acl_btn('user','M');?>>
                    Save
                </button>

                <button id="btDelete"
                    class="btn btn-danger"
                    onclick="if(confirm('Delete this user?')) delete_action('user/delete_user','username');"
                    <?php echo check_acl_btn('user','D');?>>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>