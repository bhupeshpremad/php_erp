
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->
    <form
        class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
        <div class="input-group">
            <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                aria-label="Search" aria-describedby="basic-addon2">
            <div class="input-group-append">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                </button>
            </div>
        </div>
    </form>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small"
                            placeholder="Search for..." aria-label="Search"
                            aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Dynamic Notifications -->
        <?php include rtrim(ROOT_DIR_PATH, '/\\') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'notifications.php'; ?>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php
                    // Display admin type based on session variables
                    if (isset($_SESSION['user_type']) && isset($_SESSION['user_email'])) {
                        if ($_SESSION['user_type'] === 'superadmin') {
                            echo 'Super Admin Dashboard - ' . htmlspecialchars($_SESSION['user_email']);
                        } elseif ($_SESSION['user_type'] === 'salesadmin') {
                            echo 'Sales Admin Dashboard - ' . htmlspecialchars($_SESSION['user_email']);
                        } elseif ($_SESSION['user_type'] === 'accounts') {
                            echo 'Accounts Dashboard - ' . htmlspecialchars($_SESSION['user_email']);
                        } else {
                            echo 'Dashboard';
                        }
                    } else {
                        echo 'Dashboard';
                    }
                    ?>
                </span>
                <img class="img-profile rounded-circle"
                    src="<?php 
                    $profilePicture = BASE_URL . 'assets/images/undraw_profile.svg';
                    if (isset($_SESSION['user_id'])) {
                        try {
                            $stmt = $conn->prepare('SELECT profile_picture FROM admin_users WHERE id = ?');
                            $stmt->execute([$_SESSION['user_id']]);
                            $userPic = $stmt->fetchColumn();
                            if ($userPic) {
                                $profilePicture = BASE_URL . 'assets/images/upload/admin_profiles/' . $userPic;
                            }
                        } catch (Exception $e) {
                            // Use default image
                        }
                    }
                    echo $profilePicture;
                    ?>">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>superadmin/profile.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->