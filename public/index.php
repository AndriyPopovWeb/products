<?php

require "../config.php";
require "../common.php";

$connection = new PDO($dsn, $username, $password, $options);

$sql = "SELECT * 
        FROM products";

$statement = $connection->prepare($sql);
$statement->execute();

$result = $statement->fetchAll();

$viewResult = array();

foreach ($result as $row) {
    $isUpdateView = FALSE;
    foreach ($viewResult as &$viewRow) {
        if($row["product_name"] == $viewRow["product_name"]) {
            $viewRow["qty"] = $row["qty"] + $viewRow["qty"];
            $viewRow["warehouse"] = $viewRow["warehouse"] . ", " . $row["warehouse"];
            $isUpdateView = TRUE;
            break;
        }
    }
    if($isUpdateView == FALSE) {
        array_push($viewResult, $row);
    }
}

if (isset($_POST['submit'])) {
    try  {
        if (($handle = fopen($_FILES["fileToUpload"]["tmp_name"], "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $isUpdate = FALSE;
                $product = array(
                    "product_name" => $data[0],
                    "qty"  => $data[1],
                    "warehouse"     => $data[2]
                );
                foreach ($result as $row) {
                    if($row["product_name"] == $product["product_name"] && $row["warehouse"] == $product["warehouse"]) {
                        $qty = $row["qty"] + $product["qty"];
                        $id = $row["id"];
                        if($qty > 0) {
                            $sql = "UPDATE products
                                    SET qty = $qty
                                    WHERE id = $id";
                            $statement = $connection->prepare($sql);
                            $statement->execute();
                        }
                        else {
                            $sql = "DELETE FROM products
                                    WHERE id = $id";
                            $statement = $connection->prepare($sql);
                            $statement->execute();
                        }
                        $isUpdate = TRUE;
                    }
                }
                if($isUpdate == FALSE && $product["qty"] > 0) {
                    $sql = sprintf(
                            "INSERT INTO %s (%s) values (%s)",
                            "products",
                            implode(", ", array_keys($product)),
                            ":" . implode(", :", array_keys($product))
                    );
                    $statement = $connection->prepare($sql);
                    $statement->execute($product);
                }
            }
            fclose($handle);
        }
        $sql = "SELECT * 
                FROM products";

        $statement = $connection->prepare($sql);
        $statement->execute();

        $result = $statement->fetchAll();

        $viewResult = array();

        foreach ($result as $row) {
            $isUpdateView = FALSE;
            foreach ($viewResult as &$viewRow) {
                if($row["product_name"] == $viewRow["product_name"]) {
                    $viewRow["qty"] = $row["qty"] + $viewRow["qty"];
                    $viewRow["warehouse"] = $viewRow["warehouse"] . ", " . $row["warehouse"];
                    $isUpdateView = TRUE;
                    break;
                }
            }
            if($isUpdateView == FALSE) {
                array_push($viewResult, $row);
            }
        }
    } catch(PDOException $error) {
        echo $sql . "<br>" . $error->getMessage();
    }
}
?>

<?php require "templates/header.php"; ?>

<?php
if ($result && $statement->rowCount() > 0) { ?>
    <h2>Products</h2>

    <table>
        <thead>
            <tr>
                <th>Product name</th>
                <th>Quantity</th>
                <th>Warehouse</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($viewResult as $row) { ?>
        <tr>
            <td><?php echo escape($row["product_name"]); ?></td>
            <td><?php echo escape($row["qty"]); ?></td>
            <td><?php echo escape($row["warehouse"]); ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php } ?>

<h2>Update data</h2>

<form method="post" enctype="multipart/form-data">
    Select a file: <input type="file" name="fileToUpload" id="fileToUpload"><br />
    <input type="submit" name="submit" value="Submit">
</form>

<?php require "templates/footer.php"; ?>
