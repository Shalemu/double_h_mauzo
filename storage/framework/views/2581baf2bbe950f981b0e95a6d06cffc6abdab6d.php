<script>
document.addEventListener("DOMContentLoaded", function(){
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: "<?php echo e(session('success')); ?>",
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\fixed_expenses\_index_scripts_2.blade.php ENDPATH**/ ?>