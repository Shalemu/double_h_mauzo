<script>
document.addEventListener('DOMContentLoaded', function () {
    var imageInput = document.getElementById('image');
    var imagePreview = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            imagePreview.style.display = 'none';
        }
    });
});
</script>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/products/_create_scripts.blade.php ENDPATH**/ ?>