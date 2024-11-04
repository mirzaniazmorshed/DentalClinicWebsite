<?php 
include '../components/connect.php';

if (isset($_COOKIE['admin_id'])) {
    $admin_id = $_COOKIE['admin_id'];
} else {
    $admin_id = '';
    header('location:login.php');
    exit;
}
if (isset($_POST['update'])) {
    $service_id = $_POST['service_id'];
    $service_id = filter_var($service_id, FILTER_SANITIZE_STRING);

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);

    $content = $_POST['content'];
    $content = filter_var($content, FILTER_SANITIZE_STRING);

    $status = $_POST['name'];
    $status = filter_var($status, FILTER_SANITIZE_STRING);

    $update_service = $conn->prepare("UPDATE `services` SET name = ?, price = ?, service_detail = ?, status = ? WHERE id = ?");

    $update_service->execute([$name, $price, $content, $status, $service_id]);

    $success_msg[] = 'service updated successfully';

    $old_image = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../uploaded_files/' . $image;

    $select_image = $conn->prepare("SELECT * FROM `services` WHERE image = ?");
    $select_image->execute([$image]);

    if (!empty($image)) {
        if ($image_size > 2000000) {
            $warning_msg[] = 'image size is too large';
        } elseif ($select_image->rowCount() > 0 AND $image != '') {
            $warning_msg[] = 'please rename your image';
        } else {
            $update_image = $conn->prepare("UPDATE `services` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $service_id]);
    
            move_uploaded_file($image_tmp_name, $image_folder);
    
            if ($old_image != $image AND $old_image != '') {
                unlink('../uploaded_files/' . $old_image);
            }
            $success_msg[] = 'image updated';
        }
    }
    
}


   if(isset($_POST['delete'])){
    $service_id = $_POST['service_id'];
    $service_id = filter_var($service_id, FILTER_SANITIZE_STRING);

    $delete_image = $conn->prepare("SELECT * FROM `services` WHERE id = ?");
    $delete_image->execute([$service_id]);
    $fetch_delete_image = $delete_image->fetch(PDO::FETCH_ASSOC);

    if ($fetch_delete_image[''] != ''){
        unlink('../uploaded_files/'.$fetch_delete_image['image']);      
    }

    $delete_service = $conn->prepare("DELETE FROM `services` WHERE id = ?");
    header('location: view_service.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentiCare - Dental Clinic Website Template</title>

    <!-- Font Awesome CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="icon" href="../image/favicon.ico" type="image/x-icon">
</head>
<body style="padding-left: 0;">
    <div class="main-container">
        <?php include '../components/admin_header.php'; ?>
        
        <section class="post_editor">
            <div class="heading">
                <h1><img src="../image/separator.png">Edit Service<img src="../image/separator.png"></h1>
            </div>
            <div class="container">
                <?php 
                $service_id = $_GET['id'];
                $select_service = $conn->prepare("SELECT * FROM `services` WHERE id = ?");
                $select_service->execute([$service_id]);

                if ($select_service->rowCount() > 0) {
                    while($fetch_service = $select_service->fetch(PDO::FETCH_ASSOC)){
                      
                  
                ?>
            <div class="form_container">
                <form action="" method="post" enctype="multipart/form-data" class="register">
                <input type="hidden" name="old_image" value="<?= $fetch_service['image']; ?>">
                <input type="hidden" name="service_id" value="<?= $fetch_service['id']; ?>">
                <div class="input-field">
                    <p>service status <span>*</span></p>
                    <select name="status" class="box">
                        <option selected value="<?= $fetch_service['status']; ?>"><?= $fetch_service['status']; ?></option>
                        <option value="active">active</option>
                        <option value="deactive">deactive</option>
                    </select>
                </div>
                <div class="input-field">
                <p>service name <span>*</span></p>
                <input type="text" name="name" value="<?= $fetch_service['name']; ?>" class="box">
                </div>
                <div class="input-field">
                <p>service price <span>*</span></p>
                <input type="number" name="price" value="<?= $fetch_service['price']; ?>" class="box">
                </div>
                <div class="input-field">
                <p>service description <span>*</span></p>
                <textarea name="content" class="box"><?= $fetch_service['service_detail']; ?></textarea>
                </div>
                <div class="input-field">
                <p>service image <span>*</span></p>
                <input type="file" name="image" accept="image/*" class="box">
                <?php if($fetch_service['image'] != '') { ?>
                    <img src="../uploaded_files/<?= $fetch_service['image']; ?>" class="image" style="width: 100%;">
                <?php } ?>
                </div>
                <div class="flex-btn">
                    <button type="submit" name="update" class="btn">update service</button>
                    <button type="submit" name="delete" class="btn" onclick="return confirm('delete this service');">delete service</button>
                    <a href="view_service.php?post_id=<?= $fetch_service['id']; ?>" class="btn" style="text-align :center;">go back</a>
                </div>
                </form>
            </div>
                <?php
                  }
                } else {
                    echo '
                    <div class="empty">
                        <p>No services added yet <br> <a href="add_service.php" class="btn" style="margin-top: 1rem;">Add Service</a></p>
                    </div>
                    ';
                }
                ?>
            </div>    
        </section>
    </div>

    <!-- SweetAlert CDN Link -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

    <!-- Custom JS Link -->
    <script type="text/javascript" src="../js/admin_script.js"></script>

    <?php include '../components/alert.php'; ?>
</body>
</html>
