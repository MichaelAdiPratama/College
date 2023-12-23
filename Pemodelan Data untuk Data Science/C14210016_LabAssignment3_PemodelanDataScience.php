<?php
$mongoClient = new MongoDB\Driver\Manager("mongodb://localhost:27017");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Data</title>
</head>
<body>

<h2 style="position: center;" id="judul">Restaurant Data</h2>

<form method="post" style="margin-bottom: 20px;">
    <label id="text" style="margin-right: 5px;" for="borough">Borough:</label>
    <select name="borough" id="form" style="background-color: wheat; font-size:larger">
        <?php
        $boroughs = ['Bronx', 'Brooklyn', 'Manhattan', 'Queens', 'Staten Island']; // Get unique borough values from the database
        foreach ($boroughs as $borough) {
            echo "<option value='$borough'>$borough</option>";
        }
        ?>
    </select>

    <label style="margin-right: 5px;" id="text" for="cuisine">Cuisine:</label>
    <input id='form' type="text" name="cuisine" placeholder="Input Cuisine Keywords" style="background-color: wheat; font-size:larger">

    <label style="margin-right: 5px;" id="text" for="score" >Last Grade's Score Less Than:</label>
    <input id='form' type="number" name="score" placeholder="Enter score" style="background-color: wheat; font-size:larger">

    <input id='form' type="submit" style="background-color: lightgreen; font-size:larger" value="Filter" >
    <!-- <button  type="button" class="btn btn-outline-success" style="background-color: lightgreen; font-size:larger" value="Filter">Filter</button> -->
    
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filter = [];

    if (!empty($_POST["borough"])) {
        $filter["borough"] = $_POST["borough"];
    }

    if (!empty($_POST["cuisine"])) {
        $filter["cuisine"] = new MongoDB\BSON\Regex($_POST["cuisine"], 'i');
    }

    if (!empty($_POST["score"])) {
        // $filter["grades.score"] = ['$lt' => (int)$_POST["score"]];
        $filter["grades"] = ['$elemMatch' => ['score' => ['$lt' => (int)$_POST["score"]]]];
    }

    $query = new MongoDB\Driver\Query($filter);
    $cursor = $mongoClient->executeQuery('MichaelAdi.restaurants', $query);

    echo "<table border='3'>";
    echo "<tr id='header' border='2'><th>Name</th><th>Borough</th><th>Cuisine</th><th>Last Grade's Score</th></tr>";

    foreach ($cursor as $document) {
        echo "<tr border='2'>";
        echo "<td border='2'>{$document->name}</td>";
        echo "<td border='2'>{$document->borough}</td>";
        echo "<td border='2'>{$document->cuisine}</td>";
        echo "<td border='2'>{$document->grades[0]->score}</td>";
        echo "</tr>";
    }

    echo "</table>";
}
?>
<style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        #judul{
        text-align: center;
        font-size: x-large;
        }
        #header{
          text-align: center;
        }
        #form{
          margin-right: 15px;
        }
        #text{
          font-size: x-large;
        }
    </style>
</body>
</html>
