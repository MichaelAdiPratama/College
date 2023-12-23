<?php

require 'Predis/Predis/Autoload.php';

use Predis\Client;

$redis = new Client();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nameToAdd = $_POST['name'];
        
        // Pemeriksaan jumlah nama mahasiswa
        $currentCount = $redis->scard('employee');
        if ($currentCount < 10) {
            $redis->sadd('employee', $nameToAdd);
            // Simpan nama yang baru ditambahkan dalam set 'history'
            $redis->lpush('history', $nameToAdd);
        } else {
            echo '<script>
            alert("Tidak dapat menambahkan lebih dari 10 Data People!!"); 
            </script>';
        }
    } elseif (isset($_POST['remove'])) {
        // Ambil nama terakhir dari set 'history'
        $lastAdded = $redis->lindex('history', 0);
        if ($lastAdded !== false) {
            // Hapus nama terakhir dari set 'employee'
            $redis->srem('employee', $lastAdded);
            // Hapus nama terakhir dari set 'history'
            $redis->lpop('history');
           
        } else {
            echo "No data to remove.";
        }
    }
}

// Display the names in a table
$employeeNames = $redis->smembers('employee');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <style>
        table {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
            width: 50%;
            margin: 20px auto;
            border: 1px solid #dddddd;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        form {
            margin: 0px;
            text-align: left;
        }

        input[type="text"]{
            
            
            font-size: 30px;
            position: relative;
        }

        input[type="submit"]{
            
            font-size: 30px;
            
            
        }
    </style>
</head>
<body>

<table class="table table-striped">
    <tr>
        <th>PEOPLE</th>
    </tr>
    
    <?php foreach ($employeeNames as $name) : ?>
        <tr>
            <td><?= $name ?></td>
        </tr>
        
    <?php endforeach; ?>
    <tfoot>
    
    <td>
    <form method="post" action="" >
        <input type="text" id="name" name="name" >
        <input type="submit" name="add" value="ADD" >
        <input type="submit" name="remove" value="REMOVE" >
    </form>
    </td>
   
    </tfoot>
    
</table>
</body>
</html>
