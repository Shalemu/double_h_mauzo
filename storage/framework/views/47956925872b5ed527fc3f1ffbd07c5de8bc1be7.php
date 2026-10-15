<li class="nav-item mb-1">
    <a href="<?php echo e(route($route, $params)); ?>" class="nav-link d-flex align-items-center px-3 py-2 <?php echo e($isActive ? 'active' : ''); ?>"
       <?php if($isActive): ?> aria-current="page" <?php endif; ?>>
        <i class="nav-icon bi <?php echo e($icon); ?>"></i><span><?php echo e($label); ?></span>
        <?php if($badge): ?>
            <span class="nav-badge" aria-label="<?php echo e($badge); ?> pending"><?php echo e($badge); ?></span>
        <?php endif; ?>
    </a>
</li>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\components\sidebar_link.blade.php ENDPATH**/ ?>