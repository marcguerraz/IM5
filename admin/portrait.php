<?php

require_once '../_config/config.php';

session_start();

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== true) {
  header("Location: login.php");
  exit();
}

if (!isset($_GET['action'])) {
  header('Location: ./');
  exit();
}

if ($_GET['action'] === 'new' && isset($_POST['title'])) {
  $errors = [];

  if (!isset($_POST['title']) || empty($_POST['title'])) {
    $errors['title'] = 'Please enter a title';
  }

  if (!isset($_POST['protagonist']) || empty($_POST['protagonist'])) {
    $errors['protagonist'] = 'Please enter a protagonist name';
  }

  if (!isset($_FILES['thumbnail']) || empty($_FILES['thumbnail']['full_path'])) {
    $errors['thumbnail'] = 'Please choose a thumbnail';
  }

  if (!isset($_FILES['teaser']) || empty($_FILES['teaser']['full_path'])) {
    $errors['teaser'] = 'Please choose a teaser video';
  }

  if (!isset($_FILES['portrait']) || empty($_FILES['portrait']['full_path'])) {
    $errors['portrait'] = 'Please choose the portrait video';
  }

  if (empty($errors)) {
    $title = htmlspecialchars($_POST['title']);
    $slug = createSlug($title);
    $protagonist = htmlspecialchars($_POST['protagonist']);

    $thumbnail_file = upload_file($_FILES['thumbnail']);
    if ($thumbnail_file['error'] === true) {
      $errors['thumbnail'] = $thumbnail_file['msg'];
    }
    $thumbnail_file = $thumbnail_file['filename'];

    $teaser_file = upload_file($_FILES['teaser'], ['mp4' => 'video/mp4']);
    if ($teaser_file['error'] === true) {
      $errors['teaser'] = $teaser_file['msg'];
    }
    $teaser_file = $teaser_file['filename'];

    $portrait_file = upload_file($_FILES['portrait'], ['mp4' => 'video/mp4']);
    if ($portrait_file['error'] === true) {
      $errors['portrait'] = $portrait_file['msg'];
    }
    $portrait_file = $portrait_file['filename'];

    if (empty($errors)) {
      $content = [];
      foreach ($_POST['content'] as $elements_uuid => $element_contents) {
        switch ($element_contents['type']) {
          case 'text':
            if (empty($element_contents['content'])) {
              break;
            }
            $content[] =
              [
                'type' => 'text',
                'content' => str_replace("\n", "<br>", htmlspecialchars($element_contents['content']))
              ];
            break;

          case 'image':
            $image_file = upload_file($_FILES['content_' . $elements_uuid]);
            if ($image_file['error'] === true) {
              break;
            }
            $image_file = $image_file['filename'];
            $content[] =
              [
                'type' => 'image',
                'src' => $image_file,
                'alt' => ''
              ];
            break;
        }
      }

      $content = json_encode($content);

      $db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
      if ($db->connect_error) die($db->connect_error);
      $sql = "INSERT INTO portraits (title, slug, protagonist, thumbnail, teaser, portrait, content) VALUES (?,?,?,?,?,?,?)";
      $stmt = $db->prepare($sql);
      $stmt->bind_param('sssssss', $title, $slug, $protagonist, $thumbnail_file, $teaser_file, $portrait_file, $content);
      $stmt->execute();
      $inserted_id = $db->insert_id;

      header("Location: portrait.php?action=edit&id=$inserted_id");
    }
  }
}

if ($_GET['action'] === 'edit') {
  if (!isset($_GET['id'])) {
    header('Location: ./');
    exit();
  }

  if (isset($_POST['title'])) {
    $errors = [];

    if (!isset($_POST['title']) || empty($_POST['title'])) {
      $errors['title'] = 'Please enter a title';
    }

    if (!isset($_POST['protagonist']) || empty($_POST['protagonist'])) {
      $errors['protagonist'] = 'Please enter a protagonist name';
    }

    if ((!isset($_FILES['thumbnail']) || empty($_FILES['thumbnail']['full_path'])) && (!isset($_POST['thumbnail_uploaded']) || empty($_POST['thumbnail_uploaded']))) {
      $errors['thumbnail'] = 'Please choose a thumbnail';
    }

    if ((!isset($_FILES['teaser']) || empty($_FILES['teaser']['full_path'])) && (!isset($_POST['teaser_uploaded']) || empty($_POST['teaser_uploaded']))) {
      $errors['teaser'] = 'Please choose a teaser video';
    }

    if ((!isset($_FILES['portrait']) || empty($_FILES['portrait']['full_path'])) && (!isset($_POST['portrait_uploaded']) || empty($_POST['portrait_uploaded']))) {
      $errors['portrait'] = 'Please choose the portrait video';
    }

    if (empty($errors)) {

      $files_to_delete = [];

      $title = htmlspecialchars($_POST['title']);
      $slug = createSlug($title);
      $protagonist = htmlspecialchars($_POST['protagonist']);

      if (!empty($_FILES['thumbnail']['full_path'])) {
        $thumbnail_file = upload_file($_FILES['thumbnail']);
        if ($thumbnail_file['error'] === true) {
          $errors['thumbnail'] = $thumbnail_file['msg'];
        }
        $thumbnail_file = $thumbnail_file['filename'];
        if (file_exists(UPLOAD_DIR . '/' . $_POST['thumbnail_uploaded'])) $files_to_delete[] = UPLOAD_DIR . '/' . $_POST['thumbnail_uploaded'];
      } else if (file_exists(UPLOAD_DIR . '/' . $_POST['thumbnail_uploaded'])) {
        $thumbnail_file = $_POST['thumbnail_uploaded'];
      } else {
        $errors['thumbnail'] = 'Please choose a thumbnail';
      }

      if (!empty($_FILES['teaser']['full_path'])) {
        $teaser_file = upload_file($_FILES['teaser']);
        if ($teaser_file['error'] === true) {
          $errors['teaser'] = $teaser_file['msg'];
        }
        $teaser_file = $teaser_file['filename'];
        if (file_exists(UPLOAD_DIR . '/' . $_POST['teaser_uploaded'])) $files_to_delete[] = UPLOAD_DIR . '/' . $_POST['teaser_uploaded'];
      } else if (file_exists(UPLOAD_DIR . '/' . $_POST['teaser_uploaded'])) {
        $teaser_file = $_POST['teaser_uploaded'];
      } else {
        $errors['teaser'] = 'Please choose a teaser video';
      }

      if (!empty($_FILES['portrait']['full_path'])) {
        $portrait_file = upload_file($_FILES['portrait']);
        if ($portrait_file['error'] === true) {
          $errors['portrait'] = $portrait_file['msg'];
        }
        $portrait_file = $portrait_file['filename'];
        if (file_exists(UPLOAD_DIR . '/' . $_POST['portrait_uploaded'])) $files_to_delete[] = UPLOAD_DIR . '/' . $_POST['portrait_uploaded'];
      } else if (file_exists(UPLOAD_DIR . '/' . $_POST['portrait_uploaded'])) {
        $portrait_file = $_POST['portrait_uploaded'];
      } else {
        $errors['portrait'] = 'Please choose a portrait video';
      }

      if (empty($errors)) {
        $content = [];
        foreach ($_POST['content'] as $elements_uuid => $element_contents) {
          switch ($element_contents['type']) {
            case 'text':
              if (empty($element_contents['content'])) {
                break;
              }
              $content[] =
                [
                  'type' => 'text',
                  'content' => str_replace("\n", "<br>", htmlspecialchars($element_contents['content']))
                ];
              break;

            case 'image':

              if (!empty($_FILES['content_' . $elements_uuid]['full_path'])) {
                $image_file = upload_file($_FILES['content_' . $elements_uuid]);
                if ($image_file['error'] === true) {
                  break;
                }
                $image_file = $image_file['filename'];
              } else if (isset($_POST['content_' . $elements_uuid . '_uploaded']) && file_exists(UPLOAD_DIR . '/' . $_POST['content_' . $elements_uuid . '_uploaded'])) {
                $image_file = $_POST['content_' . $elements_uuid . '_uploaded'];
              } else {
                break;
              }

              $content[] =
                [
                  'type' => 'image',
                  'src' => $image_file,
                  'alt' => ''
                ];
              break;
          }
        }

        $content = json_encode($content);

        $db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
        if ($db->connect_error) die($db->connect_error);

        $sql = "UPDATE portraits SET title = ?, slug = ?, protagonist = ?, thumbnail = ?, teaser = ?, portrait = ?, content = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssssssi', $title, $slug, $protagonist, $thumbnail_file, $teaser_file, $portrait_file, $content, $_GET['id']);
        $stmt->execute();

        foreach ($files_to_delete as $file_to_delete) unlink($file_to_delete);
      }
    }
  }

  $db = new MySQLi(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);
  if ($db->connect_error) die($db->connect_error);
  $sql = "SELECT title, slug, protagonist, thumbnail, teaser, portrait, content FROM portraits where id = ?";
  $stmt = $db->prepare($sql);
  $stmt->bind_param('i', $_GET['id']);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows !== 1) {
    header('Location: ./');
    exit();
  }

  $row = $result->fetch_object();

  $title = $row->title;
  $slug = $row->slug;
  $protagonist = $row->protagonist;
  $thumbnail_file = $row->thumbnail;
  $teaser_file = $row->teaser;
  $portrait_file = $row->portrait;
  $content = json_decode($row->content);

  foreach ($content as &$content_element) {
    if ($content_element->type === 'text') {
      $content_element->content = str_replace('<br>', "\n", $content_element->content);
    }
  }
}

function upload_file($file_array, $allowedfiles = ['jpeg' => 'image/jpg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'])
{
  switch ($file_array['error']) {
    case UPLOAD_ERR_OK:
      break;
    case UPLOAD_ERR_NO_FILE:
      return ['error' => true, 'msg' => 'No file sent'];
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      return ['error' => true, 'msg' => 'Exceeded filesize limit'];
    default:
      return ['error' => true, 'msg' => 'Unknown error'];
  }

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  if (false === $ext = array_search(
    $finfo->file($file_array['tmp_name']),
    $allowedfiles,
    true
  )) {
    return ['error' => true, 'msg' => 'Invalid file format'];
  }

  $new_filename = date('Ymd') . '_' . guidv4() . '.' . $ext;

  if (!move_uploaded_file(
    $file_array['tmp_name'],
    UPLOAD_DIR . '/' . $new_filename
  )) {
    return ['error' => true, 'msg' => 'Failed to move uploaded file.'];
  }

  return ['error' => false, 'filename' => $new_filename];
}

function guidv4()
{
  // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
  $data = random_bytes(16);
  assert(strlen($data) == 16);

  // Set version to 0100
  $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
  // Set bits 6-7 to 10
  $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

  // Output the 36 character UUID.
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function createSlug($str, $delimiter = '-')
{
  $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
  return $slug;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title><?php echo ($_GET['action'] === 'edit' ? 'Edit Portrait' : 'New Portrait') ?> – One80</title>
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
  <main class="adminNewPortraits">
    <form action="" method="post" enctype="multipart/form-data">
      <section class="adminNewPortrait_header">
        <div>
          <a href="./">Go Back</a>
          <h1><?php echo ($_GET['action'] === 'edit' ? 'Edit Portrait' : 'New Portrait') ?></h1>
        </div>
        <button type="submit"><?php echo ($_GET['action'] === 'edit' ? 'Update' : 'Publish') ?></button>
      </section>
      <section class="adminNewPortrait_metaData">
        <div class="form_field">
          <input type="text" name="title" placeholder="Title" value="<?php if (isset($title)) echo $title ?>">
          <p class="form_field_error"><?php if (isset($errors['title'])) echo $errors['title'] ?></p>
        </div>
        <div class="form_field">
          <input type="text" name="protagonist" placeholder="Protagonist" value="<?php if (isset($protagonist)) echo $protagonist ?>">
          <p class="form_field_error"><?php if (isset($errors['protagonist'])) echo $errors['protagonist'] ?></p>
        </div>
      </section>
      <section class="adminNewPortrait_fileSelects">
        <div>
          <div>
            <label for="adminNewPortrait_fileSelects_thumbnail" style="<?php if (isset($thumbnail_file)) echo "background-image: url('../assets/uploads/$thumbnail_file')" ?>">
              <span>Thumbnail</span>
              <span class="button">Choose file</span>
            </label>
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="thumbnail" onchange="showImagePreview(this)" id="adminNewPortrait_fileSelects_thumbnail">
            <?php if (isset($thumbnail_file)) echo '<input type="hidden" name="thumbnail_uploaded" value="' . $thumbnail_file . '">' ?>
          </div>
          <p class="form_field_error"><?php if (isset($errors['thumbnail'])) echo $errors['thumbnail'] ?></p>
        </div>
        <div>
          <div>
            <?php if (isset($teaser_file)) echo '<video src="../assets/uploads/' . $teaser_file . '" muted autoplay playsinline loop></video>' ?>
            <label for="adminNewPortrait_fileSelects_teaser">
              <span>Teaser video</span>
              <span class="button">Choose file</span>
            </label>
            <input type="file" accept="video/mp4" name="teaser" onchange="showImagePreview(this)" id="adminNewPortrait_fileSelects_teaser">
            <?php if (isset($teaser_file)) echo '<input type="hidden" name="teaser_uploaded" value="' . $teaser_file . '">' ?>
          </div>
          <p class="form_field_error"><?php if (isset($errors['teaser'])) echo $errors['teaser'] ?></p>
        </div>
        <div>
          <div>
            <?php if (isset($portrait_file)) echo '<video src="../assets/uploads/' . $portrait_file . '" muted autoplay playsinline loop></video>' ?>
            <label for="adminNewPortrait_fileSelects_portrait">
              <span>Portrait video</span>
              <span class="button">Choose file</span>
            </label>
            <input type="file" accept="video/mp4" name="portrait" onchange="showImagePreview(this)" id="adminNewPortrait_fileSelects_portrait">
            <?php if (isset($portrait_file)) echo '<input type="hidden" name="portrait_uploaded" value="' . $portrait_file . '">' ?>
          </div>
          <p class="form_field_error"><?php if (isset($errors['portrait'])) echo $errors['portrait'] ?></p>
        </div>
      </section>
      <section class="adminNewPortrait_content">
        <div class="adminNewPortrait_content_header">
          <h2>Description</h2>
          <div>
            <button type="button" onclick="add_adminNewPortrait_content_editArea_text()">Add Text</button>
            <button type="button" onclick="add_adminNewPortrait_content_editArea_image()">Add Image</button>
          </div>
        </div>
        <div class="adminNewPortrait_content_editArea">
          <?php

          if (isset($content) && !empty($content)) {
            foreach ($content as $element) {
              $element_uuid = guidv4();
              switch ($element->type) {
                case 'text':
          ?>
                  <div id="<?php echo $element_uuid ?>" class="adminNewPortrait_content_editArea_element adminNewPortrait_content_editArea_text">
                    <textarea name="content[<?php echo $element_uuid ?>][content]" id=""><?php echo $element->content ?></textarea>
                    <input type="hidden" name="content[<?php echo $element_uuid ?>][type]" value="text">
                    <div class="adminNewPortrait_content_editArea_element_actions">
                      <span class="adminNewPortrait_content_editArea_element_actions_move" title="move element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-move">
                          <polyline points="5 9 2 12 5 15"></polyline>
                          <polyline points="9 5 12 2 15 5"></polyline>
                          <polyline points="15 19 12 22 9 19"></polyline>
                          <polyline points="19 9 22 12 19 15"></polyline>
                          <line x1="2" y1="12" x2="22" y2="12"></line>
                          <line x1="12" y1="2" x2="12" y2="22"></line>
                        </svg></span>
                      <button type="button" class="adminNewPortrait_content_editArea_element_actions_delete" onclick="adminNewPortrait_content_editArea_element_actions_delete(this)" title="delete element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg></button>
                    </div>
                  </div>
                <?php
                  break;

                case 'image':
                ?>
                  <div id="<?php echo $element_uuid ?>" class="adminNewPortrait_content_editArea_element adminNewPortrait_content_editArea_image">
                    <label for="<?php echo $element_uuid ?>_file" style="background-image: url('../assets/uploads/<?php echo $element->src ?>')">
                      <span class="button">Choose file</span>
                    </label>
                    <input type="file" accept="image/png, image/jpeg, image/jpg" name="content_<?php echo $element_uuid ?>" onchange="showImagePreview(this)" id="<?php echo $element_uuid ?>_file">
                    <input type="hidden" name="content_<?php echo $element_uuid ?>_uploaded" value="<?php echo $element->src ?>">
                    <input type="hidden" name="content[<?php echo $element_uuid ?>][type]" value="image">
                    <div class="adminNewPortrait_content_editArea_element_actions">
                      <span class="adminNewPortrait_content_editArea_element_actions_move" title="move element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-move">
                          <polyline points="5 9 2 12 5 15"></polyline>
                          <polyline points="9 5 12 2 15 5"></polyline>
                          <polyline points="15 19 12 22 9 19"></polyline>
                          <polyline points="19 9 22 12 19 15"></polyline>
                          <line x1="2" y1="12" x2="22" y2="12"></line>
                          <line x1="12" y1="2" x2="12" y2="22"></line>
                        </svg></span>
                      <button type="button" class="adminNewPortrait_content_editArea_element_actions_delete" onclick="adminNewPortrait_content_editArea_element_actions_delete(this)" title="delete element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash">
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg></button>
                    </div>
                  </div>
            <?php
                  break;
              }
            }
          } else {
            ?>
            <div class="adminNewPortrait_content_editArea_prompt">
              <span>To get started add a new element</span>
            </div>
          <?php
          }

          ?>
        </div>
      </section>
    </form>
  </main>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js" integrity="sha512-57oZ/vW8ANMjR/KQ6Be9v/+/h6bq9/l3f0Oc7vn6qMqyhvPd1cvKBRWWpzu0QoneImqr2SkmO4MSqU+RpHom3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    $(function() {
      $(".adminNewPortrait_content_editArea").sortable({
        handle: ".adminNewPortrait_content_editArea_element_actions_move"
      });
    });

    function adminNewPortrait_content_editArea_element_actions_delete(element) {
      $(element).closest('div.adminNewPortrait_content_editArea_element').remove();
      if ($.trim($('.adminNewPortrait_content_editArea').html()).length === 0) {
        const html = $(`
          <div class="adminNewPortrait_content_editArea_prompt">
            <span>To get started add a new element</span>
          </div>
      `);
        html.appendTo('.adminNewPortrait_content_editArea');
      }
    }

    function add_adminNewPortrait_content_editArea_text() {
      const element_id = guidGenerator();
      const html = $(`
        <div id="` + element_id + `" class="adminNewPortrait_content_editArea_element adminNewPortrait_content_editArea_text">
          <textarea name="content[` + element_id + `][content]" id=""></textarea>
          <input type="hidden" name="content[` + element_id + `][type]" value="text">
          <div class="adminNewPortrait_content_editArea_element_actions">
            <span class="adminNewPortrait_content_editArea_element_actions_move" title="move element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-move">
                <polyline points="5 9 2 12 5 15"></polyline>
                <polyline points="9 5 12 2 15 5"></polyline>
                <polyline points="15 19 12 22 9 19"></polyline>
                <polyline points="19 9 22 12 19 15"></polyline>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <line x1="12" y1="2" x2="12" y2="22"></line>
              </svg></span>
            <button type="button" class="adminNewPortrait_content_editArea_element_actions_delete" onclick="adminNewPortrait_content_editArea_element_actions_delete(this)" title="delete element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg></button>
          </div>
        </div>
      `);
      $('.adminNewPortrait_content_editArea_prompt').remove();
      html.appendTo('.adminNewPortrait_content_editArea');
    }

    function add_adminNewPortrait_content_editArea_image() {
      const element_id = guidGenerator();
      const html = $(`
        <div id="` + element_id + `" class="adminNewPortrait_content_editArea_element adminNewPortrait_content_editArea_image">
          <label for="` + element_id + `_file">
            <span class="button">Choose file</span>
          </label>
          <input type="file" accept="image/png, image/jpeg, image/jpg" name="content_` + element_id + `"  onchange="showImagePreview(this)" id="` + element_id + `_file">
          <input type="hidden" name="content[` + element_id + `][type]" value="image">
          <div class="adminNewPortrait_content_editArea_element_actions">
            <span class="adminNewPortrait_content_editArea_element_actions_move" title="move element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-move">
                <polyline points="5 9 2 12 5 15"></polyline>
                <polyline points="9 5 12 2 15 5"></polyline>
                <polyline points="15 19 12 22 9 19"></polyline>
                <polyline points="19 9 22 12 19 15"></polyline>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <line x1="12" y1="2" x2="12" y2="22"></line>
              </svg></span>
            <button type="button" class="adminNewPortrait_content_editArea_element_actions_delete" onclick="adminNewPortrait_content_editArea_element_actions_delete(this)" title="delete element"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg></button>
          </div>
        </div>
      `);
      $('.adminNewPortrait_content_editArea_prompt').remove();
      html.appendTo('.adminNewPortrait_content_editArea');
    }

    function guidGenerator() {
      var S4 = function() {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
      };
      return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
    }

    function showImagePreview(element) {
      if (element.files[0].type === 'video/mp4') {
        $(element).parent().find('video').remove();
        $('<video src="' + URL.createObjectURL(element.files[0]) + '" muted autoplay playsinline loop></video>').appendTo($(element).parent());
        return;
      }
      $(element).parent().find('label').css('background-image', "url('" + URL.createObjectURL(element.files[0]) + "')");
    }
  </script>
</body>

</html>