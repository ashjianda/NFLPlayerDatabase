<html>
	<head>
		<title>NFL Player Database</title>
		<?php require_once('header.php'); ?>
	</head>
	<?php require_once('login_connection.php'); global $conn; ?>
	<body>
		<div class="bg-dark pb-2" style="background-color: #e3f2fd;">
		<a href="logout.php" class="btn btn-outline-light mt-2 ml-2">Logout</a>
		<a href="compare.php" class="btn btn-outline-light mt-2 ml-2">Compare Two Players</a>
		<?php 
			if(isset($_SESSION['admin']) and $_SESSION['admin'] == true){
				echo '<a href="users.php" class="btn btn-outline-light mt-2 ml-2">View Users</a>';
			} ?>
		</div>
		<div class="container" style="max-width: 50%">
			<div class="container-fluid mt-3 mb-3">
				<h1 style="text-align: center">NFL Player Database</h1>
			</div>
			<div class="row">
				<div class="col">
					<input type="text" class="form-control" id="search" autocomplete="off" placeholder="Enter an NFL player's name...">
				</div>
			</div>
			<div class="row">
				<div id="searchResult" class="container"></div>
			</div>
		</div>

		<script>
		$('#search').on('input keyup', function(){
			var input = $(this).val();
			if(input != ''){
				$("#searchResult").css("display", "block");
				$.ajax({
					url: "searchbaraction.php",
					type: "POST",
					data: {input:input},
					success: function(data){
						$("#searchResult").html(data);
					}
				});
			} else {
				$("#searchResult").css("display", "none");
			}
		});
		</script>
	</body>
</html>