<?php $__env->startSection('title', 'Customer Details'); ?>

<?php $__env->startSection('content'); ?>

<?php echo $__env->make('pages.admin.customers._show_styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="container-fluid py-4 customer-detail-page">
        <div class="row g-4">
            <div class="col-12">

                <a href="<?php echo e(route('customers.index')); ?>" class="btn btn-secondary btn-sm mb-3">
                    <i class="fa fa-arrow-left"></i> Back to Customers
                </a>

                
                <div class="card shadow-sm mb-4">
                    <div class="card-header customer-profile-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo e($customer->name); ?></h5>
                        <?php if($totalDebt > 0): ?>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                                <i class="fa fa-credit-card"></i> Reduce Debt
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body customer-profile-body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Phone</div><strong><?php echo e($customer->phone ?? '-'); ?></strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Email</div><strong><?php echo e($customer->email ?? '-'); ?></strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Address</div><strong><?php echo e($customer->address ?? '-'); ?></strong></div></div>
                            <div class="col-sm-6 col-xl-3"><div class="customer-contact-item"><div class="small text-muted mb-1">Shop</div><strong><?php echo e($customer->shop->name ?? '-'); ?></strong></div></div>
                        </div>
                    </div>
                </div>

                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#e9f9ef;color:#16a34a;"><i class="bi bi-bag-check"></i></div>
                                <div><div class="customer-stat-label">Total Purchases</div><h4 class="customer-stat-value text-success"><?php echo e(number_format($totalPurchases ?? 0)); ?></h4></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="bi bi-wallet2"></i></div>
                                <div><div class="customer-stat-label">Total Paid</div><h4 class="customer-stat-value text-primary" id="totalPaid"><?php echo e(number_format($totalPaid ?? 0)); ?></h4></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card summary-card shadow-sm">
                            <div class="d-flex align-items-center gap-3">
                                <div class="customer-stat-icon" style="background:#fdeceb;color:#ef4444;"><i class="bi bi-exclamation-circle"></i></div>
                                <div><div class="customer-stat-label">Total Debt</div><h4 class="customer-stat-value text-danger" id="totalDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></h4></div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card shadow-sm mb-5">
                    <div class="card-header history-header">
                        <h6 class="mb-0">Purchase History</h6>
                    </div>

                    <div class="card-body p-0">
                      <div class="table-responsive history-table-wrap">
                        <table class="table table-bordered table-hover text-center">
                            <thead class="table-warning text-uppercase">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Debt</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($index + 1); ?></td>
                                        <td><?php echo e(\Carbon\Carbon::parse($sale->created_at)->format('Y-m-d')); ?></td>
                                        <td><?php echo e(number_format($sale->total)); ?></td>
                                        <td><?php echo e(number_format($sale->received_amount)); ?></td>
                                        <td><?php echo e(number_format($sale->remaining_amount)); ?></td>
                                        <td>
                                            <?php if($sale->remaining_amount > 0): ?>
                                                <span class="badge bg-danger">Debt</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No purchases found for this customer.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>

            </div>
        </div>
</div>


<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentLabel" aria-hidden="true" style="margin-top: 150px;">
  <div class="modal-dialog">
    <form id="recordPaymentForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentLabel">Record Payment for <?php echo e($customer->name); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Current Debt: <strong id="currentDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></strong></p>
                <div class="mb-3">
                    <label for="amountPaid" class="form-label">Amount Paid</label>
                    <input type="number" class="form-control" name="amount_paid" id="amountPaid" min="0" max="<?php echo e($totalDebt ?? 0); ?>" required>
                </div>
                <p>Remaining Debt after payment: <strong id="remainingDebt"><?php echo e(number_format($totalDebt ?? 0)); ?></strong></p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Submit Payment</button>
            </div>
        </div>
    </form>
  </div>
</div>

<?php echo $__env->make('pages.admin.customers._show_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\admin\customers\show.blade.php ENDPATH**/ ?>