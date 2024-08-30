<?php
require_once ('login_connection.php');

global $conn;

function listUsers()
{
    global $conn;
    
    $sqlQuery = "SELECT user_id as `ID`, email FROM users ";
    
    if (! empty($_POST["search"]["value"])) {
        $sqlQuery .= 'WHERE (email LIKE "%' . $_POST["search"]["value"] . '%" OR user_id LIKE "%' . $_POST["search"]["value"] . '%") ';
    }
    
    if (! empty($_POST["order"])) {
        $sqlQuery .= 'ORDER BY ' . ($_POST['order']['0']['column'] + 1) . ' ' . $_POST['order']['0']['dir'] . ' ';
    } else {
        $sqlQuery .= 'ORDER BY user_id ASC ';
    }
    
    $stmt = $conn->prepare($sqlQuery);
    $stmt->execute();
    
    $numberRows = $stmt->rowCount();
    
    if ($_POST["length"] != - 1) {
        $sqlQuery .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
    }
    
    $stmt = $conn->prepare($sqlQuery);
    $stmt->execute();
    
    $dataTable = array();
    
    while ($sqlRow = $stmt->fetch()) {
        $dataRow = array();
        
        $dataRow[] = $sqlRow['ID'];
        $dataRow[] = $sqlRow['email'];

        if ($sqlRow['email'] == "ashjianda@vcu.edu" or $sqlRow['email'] == "qjones0319@gmail.com" or $sqlRow['email'] == 'admin@admin.edu') {
            $dataRow[] = 'Can\'t update an admin!'; 
        } else {
            $dataRow[] = '<button type="button" name="update" user_id="' . $sqlRow["ID"] . '" class="btn btn-warning btn-sm update">Update</button>
                          <button type="button" name="delete" user_id="' . $sqlRow["ID"] . '" class="btn btn-danger btn-sm delete" >Delete</button>';
        }
        $dataTable[] = $dataRow;
    }
    
    $output = array(
        "recordsTotal" => $numberRows,
        "recordsFiltered" => $numberRows,
        "data" => $dataTable
    );
    
    echo json_encode($output);
}
    
function getUser()
{
    global $conn;
    
    if ($_POST["ID"]) {
        
        $sqlQuery = "SELECT user_id as `ID`, email FROM users WHERE user_id = :user_id";
        $stmt = $conn->prepare($sqlQuery);
        $stmt->bindValue(':user_id', $_POST["ID"]);
        $stmt->execute();
        
        echo json_encode($stmt->fetch());
    }
}

function updateUser()
{
    global $conn;
    
    if ($_POST['ID']) {
        
        $sqlQuery = "UPDATE users SET email = :email WHERE user_id = :user_id";
        
        $stmt = $conn->prepare($sqlQuery);
        $stmt->bindValue(':email', $_POST["username"]);
        $stmt->bindValue(':user_id', $_POST["ID"]);
        $stmt->execute();
    }
}

function deleteUser()
{
    global $conn;
    
    if ($_POST["ID"]) {
        
        $sqlQuery = "DELETE FROM users WHERE user_id = :user_id";
        
        $stmt = $conn->prepare($sqlQuery);
        $stmt->bindValue(':user_id', $_POST["ID"]);
        $stmt->execute();
    }
}

if(!empty($_POST['action']) && $_POST['action'] == 'listUsers') {
    listUsers();
}
if(!empty($_POST['action']) && $_POST['action'] == 'getUser') {
    getUser();
}
if(!empty($_POST['action']) && $_POST['action'] == 'updateUser') {
    updateUser();
}
if(!empty($_POST['action']) && $_POST['action'] == 'deleteUser') {
    deleteUser();
}

?>