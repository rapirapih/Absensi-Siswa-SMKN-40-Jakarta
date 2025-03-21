<?php
include 'config.php';

$sql = file_get_contents(__DIR__ . '/sql/add_foto_column.sql');

if($conn->query($sql)) {
    echo "Foto column added successfully!";
} else {
    echo "Error adding foto column: " . $conn->error;
}
?>
