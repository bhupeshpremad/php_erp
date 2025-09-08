
<?php  

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Dashboard</title>

    <!-- Core plugin JavaScript files (jQuery and Bootstrap Bundle) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom fonts for this template-->
    <link href="<?php echo BASE_URL; ?>assets/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo BASE_URL; ?>assets/css/sb-admin-2.css" rel="stylesheet" type="text/css">
    <link href="<?php echo BASE_URL; ?>assets/css/sb-admin-2.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="<?php echo BASE_URL; ?>assets/css/custom-styles.css" rel="stylesheet" type="text/css"> <!-- Include custom styles -->

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>

    <!-- Use font-display: swap to avoid slow network font loading warning -->
    <style>
        @font-face {
            font-family: 'Nunito';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/nunito/v31/XRXV3I6Li01BKofINeaB.woff2') format('woff2');
            font-display: swap;
        }
    </style>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">
    <!-- Use CDN for FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" crossorigin="anonymous" />

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    
    
    
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php
        // Conditionally include sidebar based on user type
        $user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
        
        switch ($user_type) {
            case 'superadmin':
                include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
                break;
            case 'accountsadmin': 
            case 'salesadmin':
            case 'operationadmin':
            case 'productionadmin':
            case 'communicationadmin': 
                $sidebar_path = ROOT_DIR_PATH . str_replace('admin', '', $user_type) . 'admin/sidebar.php';
                if (file_exists($sidebar_path)) {
                    include_once $sidebar_path;
                } else {
                    error_log('Sidebar not found for user type: ' . $user_type . ' at path: ' . $sidebar_path);
                }
                break;
            case 'buyer':
                include_once ROOT_DIR_PATH . 'buyeradmin/sidebar.php';
                break;
            default:
                break;
        }
        ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content" class="d-flex">