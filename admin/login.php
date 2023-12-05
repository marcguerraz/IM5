<?php

require_once '../_config/config.php';

session_start();

if (isset($_SESSION['logged']) && $_SESSION['logged'] === true) {
  header("Location: ./");
  exit();
}

if (isset($_POST['username']) && isset($_POST['password'])) {
  if (empty($_POST['username'])) {
    $username_error = 'Please enter your username';
  }

  if (empty($_POST['password'])) {
    $password_error = 'Please enter your password';
  }

  $db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
  if ($db->connect_error) die($db->connect_error);
  $sql = "SELECT id, username, password FROM users WHERE username = ?";
  $stmt = $db->prepare($sql);
  $stmt->bind_param('s', $_POST['username']);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_object();

    if (password_verify($_POST['password'], $row->password)) {
      $_SESSION['logged'] = true;
      $_SESSION['userid'] = $row->id;
      $_SESSION['username'] = $row->username;

      header("Location: ./");
      exit();
    } else {
      $username_error = 'Wrong username';
      $password_error = 'Or wrong password';
    }
  } else {
    $username_error = 'Wrong username';
    $password_error = 'Or wrong password';
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Login – One80</title>
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
  <main class="adminLogin">
    <section class="adminLogin_form">
      <form action="" method="post">
        <div class="form_field">
          <input type="text" name="username" placeholder="Username" id="">
          <p class="form_field_error"><?php if (isset($username_error)) echo $username_error ?></p>
        </div>
        <div class="form_field">
          <input type="password" name="password" placeholder="Password" id="">
          <p class="form_field_error"><?php if (isset($password_error)) echo $password_error ?></p>
        </div>
        <button type="submit">Login</button>
      </form>
    </section>
  </main>
</body>

</html>