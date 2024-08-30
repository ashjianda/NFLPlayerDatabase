<html>
	<head>
		<title>NFL Player Database</title>
		<?php require_once('header.php'); ?>
	</head>
	<?php require_once('login_connection.php'); global $conn; ?>
	<body>
		<div class="bg-dark pb-2" style="background-color: #e3f2fd;">
			<a href="searchbar.php" class="btn btn-outline-light mt-2 ml-2">Back</a>
		</div>
		<div class="container" style="max-width: 75%">
			<div class="container-fluid mt-3 mb-3">
				<h1 style="text-align: center">NFL Player Database</h1>
			</div>
			<form action="compare.php" method="POST" id="compareForm">
				<input type="hidden" name="player_id" id="player_id" value="<?php echo isset($_POST['player_id']) ? $_POST['player_id'] : ''; ?>">
				<input type="hidden" name="s_player_id" id="s_player_id" value="<?php echo isset($_POST['s_player_id']) ? $_POST['s_player_id'] : ''; ?>">
			</form>
			<form action="comparestats.php" method="POST" id="statsForm">
				<input type="hidden" name="player_id" id="player_id" value="<?php echo @$_POST['player_id']; ?>">
				<input type="hidden" name="s_player_id" id="s_player_id" value="<?php echo @$_POST['s_player_id']; ?>">
			</form>
			<div class="row">
				<div class="col-5">
					<?php if(isset($_POST['player_id']) and $_POST['player_id'] != '') { 
						$queryone = "SELECT name, position FROM player WHERE player_id = :player_id";
						$stmtone = $conn->prepare($queryone);
						$stmtone->bindValue(':player_id', $_POST['player_id']);
						$stmtone->execute();
						$playerone = $stmtone->fetch();
						?>
						<button id="buttonp1" type="button" class="btn btn-outline-info"><?php echo $playerone['name'] . ', ' . $playerone['position'];?></button>
						<?php
					  }
					  ?>
				</div>
				<div class="col-5">
					<?php if(isset($_POST['s_player_id']) and $_POST['s_player_id'] != '') {
						$querytwo = "SELECT name, position FROM player WHERE player_id = :player_id";
						$stmttwo = $conn->prepare($querytwo);
						$stmttwo->bindValue(':player_id', $_POST['s_player_id']);
						$stmttwo->execute();
						$playertwo = $stmttwo->fetch();
						?>
						<button id="buttonp2" type="button" class="btn btn-outline-info"><?php echo $playertwo['name'] . ', ' . $playertwo['position'];?></button>
						<?php
					  } 
					?>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-5">
					<input type="text" class="form-control" id="first-search" autocomplete="off" placeholder="Enter an NFL player's name...">
				</div>
				<div class="col-5">
					<input type="text" class="form-control" id="second-search" autocomplete="off" placeholder="Enter an NFL player's name...">
				</div>
				<div class="col-2">
					<button id="submitstats" class="btn btn-dark">Search</button>
				</div>
			</div>
			<div class="row">
				<div class="col-5">
					<div id="first-search-result" class="container"></div>
				</div>
				<div class="col-5">
					<div id="second-search-result" class="container"></div>
				</div>
			</div>
		</div>

		<script>
		$('#first-search').on('input keyup', function(){
			var input = $(this).val();
			if(input != ''){
				$("#first-search-result").css("display", "block");
				$.ajax({
					url: "firstcompareaction.php",
					type: "POST",
					data: {input:input},
					success: function(data){
						$("#first-search-result").html(data);
					}
				});
			} else {
				$("#first-search-result").css("display", "none");
			}
		});

		$('#second-search').on('input keyup', function(){
			var input = $(this).val();
			if(input != ''){
				$("#second-search-result").css("display", "block");
				$.ajax({
					url: "secondcompareaction.php",
					type: "POST",
					data: {input:input},
					success: function(data){
						$("#second-search-result").html(data);
					}
				});
			} else {
				$("#second-search-result").css("display", "none");
			}
		});

		document.getElementById('buttonp1').addEventListener('click', function() {
			document.getElementById('player_id').value = '';
			document.getElementById('compareForm').submit();
			this.style.display = 'none';
		});

		document.getElementById('buttonp2').addEventListener('click', function() {
			document.getElementById('s_player_id').value = '';
			document.getElementById('compareForm').submit();
			this.style.display = 'none';
		});

		document.getElementById('submitstats').addEventListener('click', function() {
			if(document.getElementById('player_id').value !== '' && document.getElementById('s_player_id').value !== ''){
				document.getElementById('statsForm').submit();
			}
		});
		</script>
	</body>
</html>