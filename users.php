<html>
<head>
<title>NFL Database Users</title>
<?php 
session_start();
require_once('header.php'); ?>

<!-- Font Awesome library -->
<script src="https://kit.fontawesome.com/aec5ef1467.js"></script>

<!-- JS libraries for datatables buttons-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables-buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>

$(document).ready(function () {
	var tableUsers = $('#table-users').DataTable({
		"dom": 'lfrtp',
		"autoWidth": false,
		"processing": true,
		"serverSide": true,
		"pageLength": 15,
		"lengthMenu": [[15, 25, 50, 100, -1], [15, 25, 50, 100, "All"]], // Number of rows to show on the table
		"responsive": true,
		"language": { processing: '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>' }, // Loading icon while data is read from the database
		"order": [],
		"ajax": {
			url: "usersaction.php",
			type: "POST",
			data: {
				action: 'listUsers'
			},
			dataType: "json"
		},
	});

	$("#user-modal").on('submit', '#user-form', function (event) {
		event.preventDefault();
		$('#save').attr('disabled', 'disabled');
		$.ajax({
			url: "usersaction.php",
			method: "POST",
			// data: {
			// 	// Copy variables from the modal (popup) to send it to the POST
			// 	ID: $('#ID').val(),
			// 	email: $('#username').val(),
			// 	action: $('#action').val(),
			// },
            data: $(this).serialize(),
			success: function () {
                console.log("User updated successfully");
				$('#user-modal').modal('hide');
				$('#user-form')[0].reset();
				$('#save').attr('disabled', false);
				tableUsers.ajax.reload();
			}
		})
	});

	$("#table-users").on('click', '.update', function () {
		var ID = $(this).attr("user_id");
		var action = 'getUser';
		$.ajax({
			url: 'usersaction.php',
			method: "POST",
			data: { ID: ID, action: action },
			dataType: "json",
			success: function (data) {
				// Copy variables from the returned JSON from the SQL query in getUser into the modal (popup)
				$('#user-modal').modal('show');
				$('#ID').val(ID);
				$('#username').val(data.email);
				$('.modal-title').html("Edit User");
				$('#action').val('updateUser');
				$('#save').val('Save');
			}
		})
	});

	$("#table-users").on('click', '.delete', function () {
		var ID = $(this).attr("user_id");
		var action = "deleteUser";
		if (confirm("Are you sure you want to delete this user?")) {
			$.ajax({
				url: 'usersaction.php',
				method: "POST",
				data: { ID: ID, action: action },
				success: function () {
					tableUsers.ajax.reload();
				}
			})
		} else {
			return false;
		}
	});
});

</script>

<!-- CSS for datatables buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>
</head>

<?php require_once('login_connection.php'); global $conn; ?>

<body>
<div class="bg-dark pb-2" style="background-color: #e3f2fd;">
		<a href="searchbar.php" class="btn btn-outline-light mt-2 ml-2">Back</a>
</div>
<div class="container-fluid mt-3 mb-3">
	<h4>Users</h4>
  	
	<div>
		<table id="table-users" class="table table-bordered table-striped">
			<thead class="thead-dark">
				<tr>
					<th>ID</th>
					<th>Username</th>
                    <th>Actions</th>
				</tr>
			</thead>
		</table>
	</div>
</div>
<div id="user-modal" class="modal fade">
	<div class="modal-dialog">
		<form method="post" id="user-form">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Edit User</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">

						<label>Username</label><input type="text" name="username" class="form-control" id="username" placeholder="Enter username" required>
						
					</div>
				</div>
				<div class="modal-footer">
					<input type="hidden" name="ID" id="ID"/>
					<input type="hidden" name="action" id="action" value=""/>
					<input type="submit" name="save" id="save" class="btn btn-info" value="Save" />
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</form>
	</div>
</div>
</body>
</html>