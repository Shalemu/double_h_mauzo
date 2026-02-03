<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>
    <?php
    $shops = $shops ?? collect();
?>



<?php $__env->startSection('title', 'Dashboard'); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .main-content {
            position: relative;
            top: 50px; 
            margin-left: 70px;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                margin-top: 120px;
            }
        }

        /* Hide sections by default except summary */
        .dashboard-section {
            display: none;
        }
        #shop-summary {
            display: block; /* summary visible by default */
        }
    </style>
</head>
<body>

    <?php echo $__env->make('components.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="container-fluid main-content" id="main-content-area">
        <!-- Sections -->
        <div id="shop-summary" class="dashboard-section">
            <?php echo $__env->make('dashboard.shops.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <div id="product-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.products.index', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- Add more sections as needed -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item a');
            const sections = document.querySelectorAll('.dashboard-section');

            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Prevent default link navigation
                    e.preventDefault();

                    // Remove active from all menu items
                    menuItems.forEach(i => i.parentElement.classList.remove('active'));
                    item.parentElement.classList.add('active');

                    // Hide all sections
                    sections.forEach(sec => sec.style.display = 'none');

                    // Show the section based on data-content attribute
                    const target = item.getAttribute('data-content');
                    if(target) {
                        const section = document.getElementById(target);
                        if(section) section.style.display = 'block';
                    }
                });
            });
        });
    </script>

</body>
</html>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/dashboard.blade.php ENDPATH**/ ?>