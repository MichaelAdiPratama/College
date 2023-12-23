<?php
   use Laudis\Neo4j\Authentication\Authenticate;
   use Laudis\Neo4j\ClientBuilder;

   require_once 'vendor/autoload.php';
   $url = 'neo4j://localhost:7687?database=neo4j';
   $auth = Authenticate::basic('neo4j', 'password');
   $clients = ClientBuilder::create()->withDriver('neo4j', $url, $auth)->build();
   $hasil = "";
   $results = $clients->run("MATCH (c:Supplier) RETURN c");

   echo '<div class="form-group">
         <form method="post">
         <div class="container">
         <div class="form-group">
         <label for="data">Pilih Data:</label>            
         <select id="data" name="selectedData">
         <option value="pilih">Pilih</option>';

   foreach ($results as $nama) {
      $node = $nama->get('c');
      $namaPerusahaan = $node->getProperty('companyName');
      $hasil .= "<option value='$namaPerusahaan'>$namaPerusahaan</option>";
   }
   echo $hasil;
   echo '</select>
         </div>
         <button name="showData">Show Data</button>
         </form>
         </div>
         </div>';

   if (isset($_POST["showData"])) {
      $selectedData = $_POST['selectedData'];
      $company = $selectedData;
      $query = <<<CYPHER
         MATCH (s1:Supplier)-->()-->()<--()<--(s2:Supplier)
         WHERE s1.companyName = \$company
         RETURN s2.companyName as Competitor, count(s2) as NoProducts
         ORDER BY NoProducts DESC
         CYPHER;

      $results = $clients->run($query, ['dbname' => 'neo4j', 'company' => $company]);

      if ($results->count() > 0) {
         echo '<table style="width:30%">';
         echo '<tr><th>Kompetitor</th><th>Jumlah Produk</th></tr>';

         foreach ($results as $record) {
            echo '<tr><td>' . $record->get('Competitor') . '</td><td style="text-align:center">' . $record->get('NoProducts') . '</td></tr>';
         }
         echo '</table>';
      } else {
         echo "Data Not Found";
      }
   }
?>