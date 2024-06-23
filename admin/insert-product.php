<?php
include('../db.php');

// Insert or Update Product Logic
if (isset($_POST['submit'])) {
    $p_cat_id = $_POST['p_cat_id'];
    $cat_id = $_POST['cat_id'];
    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $product_desc = $_POST['product_desc'];

    // File paths
    $product_img1 = $_FILES['product_img1']['name'];
    $product_img2 = $_FILES['product_img2']['name'];

    // Temporary file names
    $temp_name1 = $_FILES['product_img1']['tmp_name'];
    $temp_name2 = $_FILES['product_img2']['tmp_name'];

    // Move uploaded files to the destination directory
    $upload_dir = "../img/products/";
    if ($product_img1) move_uploaded_file($temp_name1, $upload_dir . $product_img1);
    if ($product_img2) move_uploaded_file($temp_name2, $upload_dir . $product_img2);

    if (isset($_POST['edit_id'])) {
        // Fetch the existing images if new ones are not uploaded
        $edit_id = $_POST['edit_id'];
        if (!$product_img1 || !$product_img2) {
            $query = "SELECT product_img1, product_img2 FROM products WHERE products_id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $edit_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $existing_product = mysqli_fetch_assoc($result);
            if (!$product_img1) $product_img1 = $existing_product['product_img1'];
            if (!$product_img2) $product_img2 = $existing_product['product_img2'];
            mysqli_stmt_close($stmt);
        }

        // Update product logic
        $update_product = "UPDATE products SET p_cat_id=?, cat_id=?, product_title=?, product_img1=?, product_img2=?, product_price=?, product_desc=? WHERE products_id=?";
        $stmt = mysqli_prepare($con, $update_product);
        mysqli_stmt_bind_param($stmt, "iisssdsi", $p_cat_id, $cat_id, $product_title, $product_img1, $product_img2, $product_price, $product_desc, $edit_id);
    } else {
        // Insert product logic
        $insert_product = "INSERT INTO products (p_cat_id, cat_id, date, product_title, product_img1, product_img2, product_price, product_desc) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $insert_product);
        mysqli_stmt_bind_param($stmt, "iisssds", $p_cat_id, $cat_id, $product_title, $product_img1, $product_img2, $product_price, $product_desc);
    }

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Check if the insertion/update was successful
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<script>alert('Product " . (isset($_POST['edit_id']) ? "Updated" : "Inserted") . "')</script>";
        echo "<script>window.open('insert-product.php','_self')</script>";
    } else {
        echo "Error: " . mysqli_error($con);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

// Delete Product Logic
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_product = "DELETE FROM products WHERE products_id = ?";
    $stmt = mysqli_prepare($con, $delete_product);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Fetch product details for editing
$product = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $query = "SELECT * FROM products WHERE products_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .top {
            font-size: 28px;
            background-color: #e6e6e6;
            text-align: center;
            padding: 8px 0;
            margin-bottom: 20px;
            box-shadow: 0 -20px 15px -10px rgba(155, 155, 155, 0.3) inset,
                0 20px 15px -10px rgba(155, 155, 155, 0.3) inset,
                0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-lg-12">
            <div class="top">
                <i class="fa fa-dashboard fa-fw"></i> Dashboard
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-money fa-fw"></i> <?php echo isset($product) ? "Edit Product" : "Insert Products"; ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <form method="post" class="form-horizontal" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Title/Name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="product_title" value="<?php echo isset($product) ? $product['product_title'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Category</label>
                            <div class="col-md-6">
                                <select class="form-control" name="p_cat_id">
                                    <option>Select a Product Category</option>
                                    <?php
                                    $get_p_category = "select * from product_categories";
                                    $run_p_category = mysqli_query($con, $get_p_category);
                                    while ($p_cat_row = mysqli_fetch_array($run_p_category)) {
                                        $p_cat_id = $p_cat_row['p_cat_id'];
                                        $p_cat_title = $p_cat_row['p_cat_title'];
                                        echo "<option value='$p_cat_id'" . (isset($product) && $product['p_cat_id'] == $p_cat_id ? " selected" : "") . ">$p_cat_title</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Category</label>
                            <div class="col-md-6">
                                <select class="form-control" name="cat_id">
                                    <option>Select a Category</option>
                                    <?php
                                    $get_category = "select * from category";
                                    $run_category = mysqli_query($con, $get_category);
                                    while ($cat_row = mysqli_fetch_array($run_category)) {
                                        $cat_id = $cat_row['cat_id'];
                                        $cat_title = $cat_row['cat_title'];
                                        echo "<option value='$cat_id'" . (isset($product) && $product['cat_id'] == $cat_id ? " selected" : "") . ">$cat_title</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Image # 1</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" name="product_img1"<?php echo isset($product) ? "" : " required"; ?>>
                                <?php if (isset($product) && $product['product_img1']) { ?>
                                    <img src="../img/products/<?php echo $product['product_img1']; ?>" alt="<?php echo $product['product_title']; ?>" width="100">
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Image # 2</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control" name="product_img2"<?php echo isset($product) ? "" : " required"; ?>>
                                <?php if (isset($product) && $product['product_img2']) { ?>
                                    <img src="../img/products/<?php echo $product['product_img2']; ?>" alt="<?php echo $product['product_title']; ?>" width="100">
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Price</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="product_price" value="<?php echo isset($product) ? $product['product_price'] : ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-3 control-label">Product Description</label>
                            <div class="col-md-6">
                                <textarea class="form-control" name="product_desc" cols="19" rows="6"><?php echo isset($product) ? $product['product_desc'] : ''; ?></textarea>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex;justify-content:center">
                            <div class="col-md-3">
                                <?php if (isset($product)) { ?>
                                    <input type="hidden" name="edit_id" value="<?php echo $product['products_id']; ?>">
                                <?php } ?>
                                <input name="submit" type="submit" class="btn btn-primary form-control" value="<?php echo isset($product) ? "Update Product" : "Insert Product"; ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Display Products -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-list fa-fw"></i> View Products
                    </h3>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Product Title</th>
                                <th>Price</th>
                                <th>Description</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM products";
                        $result = mysqli_query($con, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['product_title']}</td>";
                            echo "<td>{$row['product_price']}</td>";
                            echo "<td>{$row['product_desc']}</td>";
                            echo "<td><a href='insert-product.php?edit_id={$row['products_id']}' class='btn btn-primary'>Edit</a></td>";
                            echo "<td><a href='insert-product.php?delete_id={$row['products_id']}' class='btn btn-danger'>Delete</a></td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
