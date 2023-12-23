<?php
require 'Predis/Predis/Autoload.php';

use Predis\Client;

$redis = new Client([]);
if (!isset($_COOKIE['datacookie'])) {
    $CookieValue = 'RAW';
    setcookie('datacookie', $CookieValue, time() + 3600, '/');
}

if (isset($_POST['uploadfile'])) {
    $files = $_FILES['fileUpload']['tmp_name'];
    $manage = fopen($files, "r");
    $redis->executeRaw(['DEL', 'listname']);
    if ($manage !== FALSE) {
        $new_uids = [];
        $uid = fgetcsv($manage, 1000, ",");
        $tmp = 1;
        if ($uid !== FALSE) {
            foreach ($uid as $key) {

                $redis->executeRaw(['DEL', $key]);
                $redis->executeRaw(['TS.CREATE', $key]);
                $aggregate = $key . "_" . "compacted";
                $redis->executeRaw(['DEL', $aggregate]);
                $redis->executeRaw(['TS.CREATE', $aggregate]);
                $redis->rpush('listname', $key);
                $new_uids[] = $key;
            }
            foreach ($new_uids as $key) {
                if (strpos($key, 'Average') !== false) {
                    $aggregate = $key . "_" . "compacted";
                    $redis->executeRaw(['TS.CREATERULE', $key, $aggregate, 'AGGREGATION', 'AVG', 31556952000]);
                }
                if (strpos($key, 'Min') !== false || $key == 'dt') {
                    $aggregate = $key . "_" . "compacted";
                    $redis->executeRaw(['TS.CREATERULE', $key, $aggregate, 'AGGREGATION', 'MIN', 31556952000]);
                }
                if (strpos($key, 'Max') !== false) {
                    $aggregate = $key . "_" . "compacted";
                    $redis->executeRaw(['TS.CREATERULE', $key, $aggregate, 'AGGREGATION', 'MAX', 31556952000]);
                }

            }
            while (($data = fgetcsv($manage, 1000, ",")) !== FALSE) {

                $times = strtotime(str_replace('/', '-', $data[0])) * 1000;
                if ($times < 0) {
                    $times = 0;
                }
                $header = [];
                foreach ($new_uids as $index => $key) {
                    if ($index == 0) {
                        $header[] = $key;
                        $header[] = $times;
                        $header[] = $times;
                    } else {
                        $values = isset($data[$index]) ? floatval($data[$index]) : 0;
                        $header[] = $key;
                        $header[] = $times;
                        $header[] = $values;
                    }
                }
                $redis->executeRaw(array_merge(['TS.MADD'], $header));

                $tmp = $tmp + 1;
            }

        }

        fclose($manage);
    }
}

if (isset($_POST['buttonStatus'])) {
    $buttonStatus = $_POST["buttonStatus"];

    if ($buttonStatus == 'RAW') {
        $cookieValue = 'RAW';
    } else {
        $cookieValue = 'AGG';
    }

    setcookie('datacookie', $cookieValue, time() + 3600, '/');
    echo '<script type="text/javascript">window.location.href = window.location.href;</script>';
}

$uid = $redis->lrange('listname', 0, -1);
ob_start();
?>

<!DOCTYPE html>
<html>

<body>
    <p>Select CSV File to Upload:</p>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="fileUpload" accept=".csv">
        <input type="submit" value="UPLOAD" name="uploadfile">
    </form>

    <form action="" method="post">
        <input type="submit" name="buttonStatus" value="RAW" id="RAW">
        <input type="submit" name="buttonStatus" value="AGR" id="AGR">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['fileUpload']['tmp_name'];

            if (($manage = fopen($file_tmp, "r")) !== false) {
                $headers = fgetcsv($manage, 1000, ",");
                echo "<table border='2' id='aggregatedTable'>";
                echo "<th border='2' id='agg'>GLOBAL    LAND    TEMPERATURE</th>";
                echo "<table border='1' id='aggTable'>";
                
                // Menampilkan baris header
                echo "<tr>";
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";

                while (($data = fgetcsv($manage, 1000, ",")) !== false) {
                    echo "<tr>";
                    foreach ($data as $cell) {
                        echo "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    echo "</tr>";
                }
                fclose($manage);
                echo "</table>";
                echo "<br>";
                echo "<button onclick='aggregateByYear()'>Aggregate</button>";
                echo "<div id='existingTable'></div>";
                echo "Data from CSV file displayed.";
            } else {
                echo "Failed to open the CSV file.";
            }
        } else {
            echo "Error uploading the file.";
        }
    }
    ?>

    <?php
    if ($_COOKIE['datacookie'] == 'AGG') {

        $data = [];
        foreach ($uid as $key) {
            if ($_COOKIE['datacookie'] == 'AGG') {
                $key = $key . "_" . "compacted";
            }
            $data[$key] = $redis->executeRaw(['TS.RANGE', $key, '-', '+']);
        }
        if (count($data) > 0) {
            $max_length = max(array_map('count', $data));

            echo "<table border='2' id='aggregatedTable'>";
            echo "<th border='2' id='agg'>GLOBAL    LAND    TEMPERATURE</th>";
            echo "<table border='2' id='aggTable'>";

            echo "<tr border='2'>";
            foreach ($uid as $key) {
                echo "<th border='2'>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";

            for ($i = 0; $i < $max_length; $i++) {
                echo "<tr border='1'>";
                foreach ($uid as $index => $key) {
                    echo "<td border='1'>";
                    if ($_COOKIE['datacookie'] == 'AGG') {
                        $key = $key . "_" . "compacted";
                    }
                    if ($index == 0 && isset($data[$key][$i][0])) {
                        echo date('m-d-Y', $data[$key][$i][0] / 1000);
                    } else {
                        $values = (string) $data[$key][$i][1];
                        echo isset($values) ? sprintf("%.3f",($values)) : '';
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }

            echo "</table>";
        }
    }
    ?>
    <?php
    if ($_COOKIE['datacookie'] == 'RAW') {
        echo "<table border='2' id='rawTable'>";
        
        echo "<th id='agg'>" . "GLOBAL    LAND    TEMPERATURE"."</th>";
        echo "<table border='2' id='RawTable'>";
        echo "<tr border='2'>";
        foreach ($uid as $key) {
            echo "<th border='2'>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";

        $data = [];
        foreach ($uid as $key) {
            $data[$key] = $redis->executeRaw(['TS.RANGE', $key, '-', '+']);
        }

        $max_length = max(array_map('count', $data));

        for ($i = 0; $i < $max_length; $i++) {
            echo "<tr border='1'>";
            foreach ($uid as $index => $key) {
                echo "<td border='1'>";
                if ($index === 0) {
                    $times = isset($data[$key][$i][0]) ? $data[$key][$i][0] : '';
                    $date_format = ($times != '') ? date('m-d-Y', $times / 1000) : '';
                    echo htmlspecialchars($date_format);
                } else {
                    echo isset($data[$key][$i][1]) ? htmlspecialchars($data[$key][$i][1]) : '';
                }
                echo "</td>";
            }
            echo "</tr>";
        }

        echo "</table>";
    }
    ?>

</body>

<script>
    function separateHeaderWithSpaces() {
        var tableHeaders = document.querySelectorAll('th');

        tableHeaders.forEach(function (header) {
            var newText = header.textContent.replace(/([A-Z])/g, ' $1').trim();
            header.textContent = newText;
        });
    }
    window.onload = separateHeaderWithSpaces;
</script>

<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
        word-spacing:6px;
        
    }

    th {
        background-color: #D3D3D3;
        
    }

    table#rawTable th,
    table#aggTable th {
        width: calc(100% /
                <?php echo count($uid); ?>
            );

    }

    th::after {
        content: '';
        display: block;
        border-bottom: 1px solid black;
    }

    #AGR{
        margin-top: 20px;
        margin-bottom: 30px;
        width: 100px;
        font-size: large;

    }
    #agg{
        margin-top: 20px;
        margin-bottom: 30px;
        font-size: large;
        padding: 8px;
        text-align: center;
        word-spacing:25px;
    }
    #RAW{
        margin-top: 20px;
        margin-bottom: 30px;
        width: 100px;
        font-size:large;
    }
</style>


</html>