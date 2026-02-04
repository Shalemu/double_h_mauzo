<!DOCTYPE html>
<html>
<head>
    <title>Products List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h3 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid black;
            padding: 5px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        td {
            vertical-align: top;
        }
    </style>
</head>
<body>
    <h3>Products List</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Min Quantity</th>
                <th>Purchase Price</th>
                <th>Selling Price</th>
                <th>Barcode</th>
                <th>Expire Date</th>
                <th>Size</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($product->name); ?></td>
                <td><?php echo e($product->brand ?? '-'); ?></td>
                <td><?php echo e($product->category ? $product->category->name : '-'); ?></td>
                <td><?php echo e($product->unit ? $product->unit->name : '-'); ?></td>
                <td><?php echo e($product->quantity ?? 0); ?></td>
                <td><?php echo e($product->min_quantity ?? 0); ?></td>
                <td><?php echo e($product->purchase_price ?? 0); ?></td>
                <td><?php echo e($product->selling_price ?? 0); ?></td>
                <td><?php echo e($product->barcode ?? '-'); ?></td>
                <td><?php echo e($product->expire_date ?? '-'); ?></td>
                <td><?php echo e($product->size ?? '-'); ?></td>
                <td><?php echo e($product->color ?? '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/products/pdf.blade.php ENDPATH**/ ?>