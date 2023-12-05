<?php

require_once '../_config/config.php';

header("content-type: text/json");

$pattern = "/[^\/_a-z0-9- ]/i";
$pageparams = preg_replace($pattern, '', $_GET['pageparams']);

$config = array_values(array_filter(explode("/", $pageparams)));

$path_action = isset($config[0]) ? $config[0] : null;
$path_param = isset($config[1]) ? $config[1] : null;


if (isset($path_action)) {
  $db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

  if ($db->connect_error) {
    die($db->connect_error);
  }

  $db->set_charset('utf8');

  $response = [];

  /**
   * Get all Portraits
   *
   */
  if ($path_action == 'portrait' && $path_param === null) {

    $response_data = [];

    $sql = "SELECT title, slug, protagonist, thumbnail, teaser FROM portraits";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_object()) {
      $tmp_response = [];

      $tmp_response['title'] = $row->title;
      $tmp_response['slug'] = $row->slug;
      $tmp_response['protagonist'] = $row->protagonist;
      $tmp_response['thumbnail'] = 'assets/uploads/' . $row->thumbnail;
      $tmp_response['teaser'] = 'assets/uploads/' . $row->teaser;

      $response_data[] = $tmp_response;
    }

    if (!empty($response_data)) {
      $response['code'] = 200;
      $response['message'] = 'OK';
      $response['data'] = $response_data;
    } else {
      $response['code'] = 400;
      $response['message'] = 'Bad Request';
    }
  }

  /**
   * Get Portraits
   *
   */
  if ($path_action == 'portrait' && !empty($path_param)) {

    $response_data = [];

    $sql = "SELECT title, slug, protagonist, thumbnail, portrait, content FROM portraits where slug = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $path_param);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_object()) {
      $response_data['title'] = $row->title;
      $response_data['slug'] = $row->slug;
      $response_data['protagonist'] = $row->protagonist;
      $response_data['thumbnail'] = 'assets/uploads/' . $row->thumbnail;
      $response_data['portrait'] = 'assets/uploads/' .  $row->portrait;
      $response_data['content'] = json_decode($row->content);
      foreach ($response_data['content'] as &$content_element) {
        if (isset($content_element->src)) $content_element->src = 'assets/uploads/' . $content_element->src;
      }
    }

    if (!empty($response_data)) {
      $response['code'] = 200;
      $response['message'] = 'OK';
      $response['data'] = $response_data;
    } else {
      $response['code'] = 400;
      $response['message'] = 'Bad Request';
    }
  }

  /**
   * Submit contact form
   *
   */
  if ($path_action == 'submit') {

    $response_data = [];
    $errors = [];

    if (!isset($_POST['name']) || empty($_POST['name'])) {
      $errors['name'] = 'Please enter your name';
    }
    if (strlen($_POST['name']) < 2 || strlen($_POST['name']) > 50) {
      $errors['name'] = 'Please enter a valid name';
    }

    if (!isset($_POST['email']) || empty($_POST['email'])) {
      $errors['email'] = 'Please enter your email';
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email'] = 'Please enter a valid email';
    }

    if (!isset($_POST['message']) || empty($_POST['message'])) {
      $errors['message'] = 'Please enter a message';
    }
    if (strlen($_POST['message']) < 5 || strlen($_POST['message']) > 1000) {
      $errors['message'] = 'Please enter a valid message';
    }

    $response_data = [
      'sent' => false,
      'errors' => $errors
    ];

    if (empty($errors)) {
      $name = htmlentities($_POST['name']);
      $email = htmlentities($_POST['email']);
      $message = htmlentities($_POST['message']);

      $message = "Hello,\n
      \n
      New message from the One80 Contact Form:\n
      \n
      Name: $name\n
      EMail: $email\n
      Message: $message\n
      \n
      Best Regards\n
      The One80 Website\n 
      ";

      mail(CONTACTFORM_EMAIL, 'One80 Contact Form', $message);

      $response_data = [
        'sent' => true
      ];
    }

    if (!empty($response_data)) {
      $response['code'] = 200;
      $response['message'] = 'OK';
      $response['data'] = $response_data;
    } else {
      $response['code'] = 400;
      $response['message'] = 'Bad Request';
    }
  }

  if (empty($response)) {
    $response['code'] = 400;
    $response['message'] = 'Bad Request';
  }
}

print_r(json_encode($response));
