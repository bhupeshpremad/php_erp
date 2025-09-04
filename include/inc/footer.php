        </div>
        <!-- End of Main Content -->

    </div>
    <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <!-- Removed duplicate jQuery and Bootstrap includes, they are in header.php -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/jquery.easing.min.js"></script> -->

    <!-- Page level plugins (if still needed and not loaded elsewhere) -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/chart.min.js"></script> -->
    
    <!-- Custom scripts for all pages-->
    <script src="<?php echo BASE_URL; ?>assets/js/sb-admin-2.min.js"></script>

    <!-- Page level custom scripts (if still needed and not loaded elsewhere) -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/chart-area-demo.js"></script> -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/chart-pie-demo.js"></script> -->


    <!-- Add this before the closing body tag -->
<script>
$(document).ready(function() {
    // Check if there's a toast message in session
    <?php if(isset($_SESSION['toast_message'])): ?>
        toastr.<?php echo $_SESSION['toast_type']; ?>('<?php echo $_SESSION['toast_message']; ?>');
        <?php 
        // Clear the message after displaying
        unset($_SESSION['toast_message']); 
        unset($_SESSION['toast_type']);
        ?>
    <?php endif; ?>
});
</script>
</body>

</html>
