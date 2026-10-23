
<?php if($paginator->hasPages()): ?>
    <?php
        $current = $paginator->currentPage();
        $pages = collect([1, $current, $paginator->lastPage()])->unique()->sort()->values();
    ?>
    <nav aria-label="Pagination">
        <ul class="pagination mb-0">
            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
            <?php else: ?>
                <li class="page-item"><a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous page">&lsaquo;</a></li>
            <?php endif; ?>

            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if($i > 0 && $page - $pages[$i - 1] > 1): ?>
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                <?php endif; ?>

                <?php if($page === $current): ?>
                    <li class="page-item active" aria-current="page"><span class="page-link"><?php echo e($page); ?></span></li>
                <?php else: ?>
                    <li class="page-item"><a class="page-link" href="<?php echo e($paginator->url($page)); ?>" aria-label="Page <?php echo e($page); ?>"><?php echo e($page); ?></a></li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item"><a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next page">&rsaquo;</a></li>
            <?php else: ?>
                <li class="page-item disabled" aria-disabled="true"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\vendor\pagination\bootstrap-4.blade.php ENDPATH**/ ?>