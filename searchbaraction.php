<?php
require_once ('login_connection.php');
global $conn;

if(isset($_POST['input'])){
    
    $input = $_POST['input'];
    $SQLQuery = "SELECT player_id, name, position FROM player WHERE REPLACE(name, '.', '') LIKE :input OR name LIKE :input ORDER BY name LIMIT 20";
    
    $stmt = $conn->prepare($SQLQuery);
    $stmt->bindValue(':input', '%' . $input . '%');
    $stmt->execute();
    
    $numberRows = $stmt->rowCount();

    if($numberRows > 0){?>
        <table class="table table-bordered table-striped" style="border-collapse: collapse; border-spacing: 0;">
            <tbody>
                <?php
                while ($sqlRow = $stmt->fetch()) {
                    $name = $sqlRow['name'];
                    $pos = $sqlRow['position'];
                    $id = $sqlRow['player_id'];
                    ?>
                    <tr>
                        <th>
                            <button class="btn btn-link p-0 player-name" style="color: inherit" playerid="<?php echo $id;?>"><?php echo $name;?>,  <?php echo $pos;?></button>
                        </th>     
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <form action="stats.php" method="POST" id="statsForm">
            <input type="hidden" name="player_id" id="player_id">
        </form>

        <script>
            document.querySelectorAll('.player-name').forEach(item => {
                item.addEventListener('click', event => {
                    var player_id = event.target.getAttribute('playerid');
                    document.getElementById('player_id').value = player_id;
                    document.getElementById('statsForm').submit();
                });
            });
        </script>
        <?php
    } else {
        echo "No players found";
    }
}
?>