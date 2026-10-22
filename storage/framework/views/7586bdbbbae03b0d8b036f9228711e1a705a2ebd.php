<style>
    .product-form-section {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 .25rem .75rem rgba(31, 41, 55, .06);
        overflow: hidden;
        position: relative;
        background: #fff;
    }
    .product-form-section::before {
        content: "";
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 4px;
    }
    .product-form-section.accent-primary::before { background: #4f46e5; }
    .product-form-section.accent-success::before { background: #16a34a; }
    .product-form-section.accent-warning::before { background: #d97706; }
    .product-form-section.accent-info::before { background: #0891b2; }
    .product-form-section-header {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }
    .product-form-section-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .icon-primary { background: rgba(79, 70, 229, .1); color: #4f46e5; }
    .icon-success { background: rgba(22, 163, 74, .1); color: #16a34a; }
    .icon-warning { background: rgba(217, 119, 6, .1); color: #d97706; }
    .icon-info { background: rgba(8, 145, 178, .1); color: #0891b2; }
    .product-form-section-title {
        font-weight: 600;
        margin-bottom: 0;
        font-size: 1rem;
    }
    .product-form-section-subtitle {
        font-size: .78rem;
        color: #6b7280;
        margin-bottom: 0;
    }
    .product-form-section-body {
        padding: 1.5rem;
    }
    .product-image-preview {
        display: none;
        max-height: 120px;
        margin-top: .75rem;
        border-radius: .5rem;
        border: 1px solid #e5e7eb;
    }
</style>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\products\_create_styles.blade.php ENDPATH**/ ?>