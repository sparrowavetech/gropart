<?php $__env->startSection('content'); ?>
<div class="card table-responsive">
    <div class="card-header pb-0 border-bottom-0">
        <h4 class="card-title">ShipMozo NDRs</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped table-hover mt-3 table-vcenter">
            <thead>
                <tr>
                    <th>AWB Number</th>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $ndrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ndr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(\Illuminate\Support\Arr::get($ndr, 'awb_number', 'N/A')); ?></td>
                    <td><?php echo e(\Illuminate\Support\Arr::get($ndr, 'order_id', 'N/A')); ?></td>
                    <td>
                        <span class="badge bg-warning text-warning-fg">
                            <?php echo e(\Illuminate\Support\Arr::get($ndr, 'status', 'Pending')); ?>

                        </span>
                    </td>
                    <td><?php echo e(\Illuminate\Support\Arr::get($ndr, 'reason', 'N/A')); ?></td>
                    <td><?php echo e(\Illuminate\Support\Arr::get($ndr, 'date', 'N/A')); ?></td>
                    <td class="text-center">
                        <form action="<?php echo e(route('shipmozo.ndr.action', \Illuminate\Support\Arr::get($ndr, 'awb_number'))); ?>" method="POST" class="d-inline" onsubmit="return confirm('Trigger re-attempt for this AWB?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="reattempt">
                            <button class="btn btn-sm btn-primary" type="submit" title="Mark for Re-attempt">Re-attempt</button>
                        </form>

                        <form action="<?php echo e(route('shipmozo.ndr.action', \Illuminate\Support\Arr::get($ndr, 'awb_number'))); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this as RTO?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="rto">
                            <button class="btn btn-sm btn-danger" type="submit" title="Return to Origin">RTO</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No NDR records found based on ShipMozo synchronisation.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(BaseHelper::getAdminMasterLayoutTemplate(), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/shipmozo/resources/views/ndr/index.blade.php ENDPATH**/ ?>