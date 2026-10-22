<?php
    $badge = [
        'pending'   => ['bg-warning text-dark', 'Pending'],
        'approved'  => ['bg-primary', 'Approved'],
        'purchased' => ['bg-success', 'Purchased'],
        'rejected'  => ['bg-danger', 'Rejected'],
    ][$status] ?? ['bg-secondary', ucfirst($status)];
?>
<span class="badge <?php echo e($badge[0]); ?>"><?php echo e($badge[1]); ?></span>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\components\order_status_badge.blade.php ENDPATH**/ ?>