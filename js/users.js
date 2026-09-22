var editingUser = false;

function initUsersPage() {
	$("#backgroundPopup").click(function(){ disablePopup(); });

	jQuery(document).ready(function($) {
		$(".userRow").click(function() {
		    userClick($(this));
		});
	});
}

function userClick(row) {
	if (!editingUser) {
		userId = row.data("key");
		$(".userRow").removeClass("activeRow");
		row.toggleClass("activeRow");
		$("#edit_container").show("slide", { direction: "left" }, 300).toggleClass("loadingIcon").load(base_url+"users/edituser/"+userId).toggleClass("loadingIcon");
		editingUser = userId;
	}
}

function newUser() {
	if (!editingUser) {
		$(".userRow").removeClass("activeRow");
		$("#edit_container").show("slide", { direction: "left" }, 300).toggleClass("loadingIcon").load(base_url+"users/edituser").toggleClass("loadingIcon");
		editingUser = true;
	}
}

function cancelEditUser() {
	editingUser = false;
	$(".userRow").removeClass("activeRow");
	$("#edit_container").hide('slide', {direction: 'left'}, 300);
}

function delUser(userId) {
	editingUser = false;
	$("#data").load(base_url+'users/deleteuser/'+userId);
}

function saveUser() {
	if ($("#newUserId").value=='') {
		showPopup("User ID cannot be empty!",3000);
	}
	else {
		editingUser = false;
		$("#data").load(base_url+"users/saveuser",$("#edituser").serialize());
	}
}