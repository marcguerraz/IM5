<?php

require_once '../_config/config.php';

session_start();

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
  header("Location: login.php");
  exit();
}

$db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

if (isset($_GET['action']) && isset($_GET['id'])) {
  if ($_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    if ($id > 0) {
      $sql = "SELECT thumbnail, teaser, portrait, content FROM portraits WHERE id = ?";
      $stmt = $db->prepare($sql);
      $stmt->bind_param('i', $_GET['id']);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_object();
      $files_to_delete = [];

      if (isset($row->thumbnail)) {
        $files_to_delete[] = $row->thumbnail;
        $files_to_delete[] = $row->teaser;
        $files_to_delete[] = $row->portrait;
        foreach (json_decode($row->content) as $content_element) {
          if (isset($content_element->src)) {
            $files_to_delete[] = $content_element->src;
          }
        }
      }

      $sql = "DELETE FROM portraits WHERE id = ?";
      $stmt = $db->prepare($sql);
      $stmt->bind_param('i', $_GET['id']);
      $stmt->execute();

      foreach ($files_to_delete as $file_to_delete) unlink(UPLOAD_DIR . '/' . $file_to_delete);

      header('Location: ./');
      exit();
    }
  }
}

$sql = "SELECT id, title, protagonist FROM portraits";
$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Admin – One80</title>
  <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
  <link rel="icon" type="image/ico" href="favicon.ico">
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <!-- fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <!-- stylesheets -->
  <link rel="stylesheet" href="../css/master.css">
</head>

<body>
  <main class="adminPortraits">
    <section class="adminPortraits_header">
      <h1>Portraits</h1>
      <a class="button" href="portrait.php?action=new">New Portrait</a>
    </section>
    <section class="adminPortraits_table">
      <div class="adminPortraits_table_cell adminPortraits_table_header adminPortraits_table_title"><span>Title</span></div>
      <div class="adminPortraits_table_cell adminPortraits_table_header adminPortraits_table_protagonist"><span>Protagonist</span></div>
      <div class="adminPortraits_table_cell adminPortraits_table_header adminPortraits_table_actions"><span>Actions</span></div>
      <?php

      while ($row = $result->fetch_object()) {
      ?>
        <div class="adminPortraits_table_cell adminPortraits_table_title"><span><?php echo $row->title ?></span></div>
        <div class="adminPortraits_table_cell adminPortraits_table_protagonist"><span><?php echo $row->protagonist ?></span></div>
        <div class="adminPortraits_table_cell adminPortraits_table_actions">
          <a href="portrait.php?action=edit&id=<?php echo $row->id ?>">Edit</a>
          <a class="adminPortraits_table_actions_delete" href="?action=delete&id=<?php echo $row->id ?>">Delete</a>
        </div>
      <?php
      }

      ?>
    </section>
  </main>
</body>

</html>