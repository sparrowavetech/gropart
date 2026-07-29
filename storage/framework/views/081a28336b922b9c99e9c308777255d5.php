<?php
    $paymentLogs = $payment->getPaymentLogs()->take(50);
?>

<?php if($paymentLogs->isNotEmpty()): ?>
    <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="mb-0">
                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-history'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-2']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                <?php echo e(trans('plugins/payment::payment.payment_logs')); ?>

            </h3>
            <span class="badge bg-blue text-blue-fg"><?php echo e($paymentLogs->count()); ?> <?php echo e(trans('plugins/payment::payment.log_entries')); ?></span>
        </div>
        <ul class="timeline timeline-simple">
                <?php $__currentLoopData = $paymentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $logType = 'default';
                        $icon = 'ti ti-circle-dot';
                        $iconColor = 'bg-secondary';
                        $title = '';

                        if(isset($log->request['webhook_event_id']) || isset($log->request['webhook_request'])) {
                            $logType = 'webhook';
                            $icon = 'ti ti-webhook';
                            $title = 'Webhook Event';
                        } elseif(isset($log->request['callback_request'])) {
                            $logType = 'callback';
                            $icon = 'ti ti-arrow-back-up';
                            $title = 'Payment Callback';
                        } elseif(isset($log->request['verification_attempt'])) {
                            $logType = 'verification';
                            $icon = 'ti ti-shield-check';
                            $title = 'Signature Verification';
                        }

                        if(isset($log->request['error'])) {
                            $iconColor = 'bg-danger';
                            $icon = 'ti ti-alert-circle';
                        } elseif(isset($log->request['success'])) {
                            $iconColor = 'bg-success';
                            $icon = 'ti ti-circle-check';
                        } elseif(isset($log->request['warning'])) {
                            $iconColor = 'bg-warning';
                            $icon = 'ti ti-alert-triangle';
                        }

                        // Extract meaningful title from log data
                        if(isset($log->request['event_type'])) {
                            $title = str_replace('_', ' ', ucwords($log->request['event_type']));
                        } elseif(isset($log->request['processing_payment'])) {
                            $title = 'Processing Payment';
                        } elseif(isset($log->request['order_lookup'])) {
                            $title = 'Order Lookup';
                        } elseif(isset($log->request['payment_processed_without_signature'])) {
                            $title = 'Payment Processed';
                        } elseif(isset($log->request['webhook_payment_processed'])) {
                            $title = 'Webhook Payment Processed';
                        }
                    ?>

                    <li class="timeline-event">
                        <div class="timeline-event-icon <?php echo e($iconColor); ?>">
                            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => $icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                        </div>
                        <div class="card timeline-event-card">
                            <div class="card-body">
                                <div class="text-secondary float-end">
                                    <small><?php echo e($log->created_at->diffForHumans()); ?></small>
                                </div>
                                <h4 class="mb-2">
                                    <?php echo e($title ?: 'Payment Event'); ?>


                                    <?php if($logType == 'webhook'): ?>
                                        <span class="badge badge-sm bg-orange text-orange-fg ms-2">Webhook</span>
                                    <?php elseif($logType == 'callback'): ?>
                                        <span class="badge badge-sm bg-blue text-blue-fg ms-2">Callback</span>
                                    <?php elseif($logType == 'verification'): ?>
                                        <span class="badge badge-sm bg-cyan text-cyan-fg ms-2">Verification</span>
                                    <?php endif; ?>

                                    <?php if(isset($log->request['event_type'])): ?>
                                        <span class="badge badge-sm bg-purple text-purple-fg ms-2"><?php echo e($log->request['event_type']); ?></span>
                                    <?php endif; ?>

                                    <?php if(isset($log->request['signature_verification']) && $log->request['signature_verification'] === 'success'): ?>
                                        <span class="badge badge-sm bg-green text-green-fg ms-1">
                                            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-shield-check'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['style' => 'width: 12px; height: 12px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                            Verified
                                        </span>
                                    <?php elseif(isset($log->request['webhook_signature_verification']) && $log->request['webhook_signature_verification'] === 'success'): ?>
                                        <span class="badge badge-sm bg-teal text-teal-fg ms-1">
                                            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-webhook'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['style' => 'width: 12px; height: 12px;']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                            Webhook Verified
                                        </span>
                                    <?php endif; ?>
                                </h4>

                                <div class="text-secondary small mb-2">
                                    <?php echo e($log->created_at->format('Y-m-d H:i:s')); ?>

                                    <?php if($log->ip_address): ?>
                                        <span class="ms-2">• IP: <?php echo e($log->ip_address); ?></span>
                                    <?php endif; ?>

                                    <?php if(isset($log->request['error'])): ?>
                                        <span class="badge badge-sm bg-red text-red-fg ms-2">Error</span>
                                    <?php elseif(isset($log->request['success'])): ?>
                                        <span class="badge badge-sm bg-green text-green-fg ms-2">Success</span>
                                    <?php elseif(isset($log->request['warning'])): ?>
                                        <span class="badge badge-sm bg-yellow text-yellow-fg ms-2">Warning</span>
                                    <?php elseif(isset($log->request['info'])): ?>
                                        <span class="badge badge-sm bg-azure text-azure-fg ms-2">Info</span>
                                    <?php endif; ?>
                                </div>

                                <?php if(isset($log->request['error'])): ?>
                                    <div class="alert alert-danger alert-sm mb-2">
                                        <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-alert-circle'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                        <?php echo e(is_array($log->request['error']) ? json_encode($log->request['error']) : $log->request['error']); ?>

                                    </div>
                                <?php elseif(isset($log->request['warning'])): ?>
                                    <div class="alert alert-warning alert-sm mb-2">
                                        <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-alert-triangle'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                        <?php echo e(is_array($log->request['warning']) ? json_encode($log->request['warning']) : $log->request['warning']); ?>

                                    </div>
                                <?php elseif(isset($log->request['success'])): ?>
                                    <div class="alert alert-success alert-sm mb-2">
                                        <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-circle-check'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                        <?php echo e(is_array($log->request['success']) ? json_encode($log->request['success']) : $log->request['success']); ?>

                                    </div>
                                <?php endif; ?>

                                <?php if(isset($log->response['charge_id']) || isset($log->request['charge_id'])): ?>
                                    <div class="mb-2">
                                        <span class="text-muted">Charge ID:</span>
                                        <code class="text-primary"><?php echo e($log->response['charge_id'] ?? $log->request['charge_id']); ?></code>
                                    </div>
                                <?php endif; ?>

                                <?php if(isset($log->response['order_id']) || isset($log->request['order_id'])): ?>
                                    <div class="mb-2">
                                        <span class="text-muted">Order ID:</span>
                                        <code class="text-primary"><?php echo e(is_array($log->response['order_id'] ?? $log->request['order_id']) ? implode(', ', $log->response['order_id'] ?? $log->request['order_id']) : ($log->response['order_id'] ?? $log->request['order_id'])); ?></code>
                                    </div>
                                <?php endif; ?>

                                <?php if(isset($log->response['status'])): ?>
                                    <div class="mb-2">
                                        <span class="text-muted">Status:</span>
                                        <?php
                                            $statusClass = match($log->response['status']) {
                                                'completed', 'success', 'captured', 'paid' => 'bg-green text-green-fg',
                                                'pending', 'processing' => 'bg-yellow text-yellow-fg',
                                                'failed', 'error' => 'bg-red text-red-fg',
                                                'refunded' => 'bg-orange text-orange-fg',
                                                'cancelled', 'canceled' => 'bg-pink text-pink-fg',
                                                default => 'bg-azure text-azure-fg'
                                            };
                                        ?>
                                        <span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($log->response['status'])); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="mt-3">
                                    <a href="#" class="btn btn-sm btn-ghost-secondary" data-bs-toggle="collapse" data-bs-target="#log-details-<?php echo e($index); ?>" aria-expanded="false">
                                        <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-code'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                        View Details
                                    </a>
                                </div>

                                <div class="collapse mt-3" id="log-details-<?php echo e($index); ?>">
                                    <div class="card card-sm mb-3">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0 text-muted"><?php echo e(trans('plugins/payment::payment.request_data')); ?></h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <pre class="m-0 p-3 bg-light" style="max-height: 300px; overflow-y: auto; font-size: 12px; line-height: 1.5; color: #333;"><?php echo e(json_encode($log->request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                                        </div>
                                    </div>

                                    <div class="card card-sm">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0 text-muted"><?php echo e(trans('plugins/payment::payment.response_data')); ?></h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <pre class="m-0 p-3 bg-light" style="max-height: 300px; overflow-y: auto; font-size: 12px; line-height: 1.5; color: #333;"><?php echo e(json_encode($log->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                                        </div>
                                    </div>

                                    <?php if(count(array_filter($log->request, fn($value) => !in_array($key = array_search($value, $log->request), ['event_type', 'webhook_event_id', 'webhook_request', 'callback_request', 'verification_attempt', 'error', 'success', 'warning', 'info', 'signature_verification', 'webhook_signature_verification']))) > 5): ?>
                                        <div class="mt-3 text-center">
                                            <button type="button" class="btn btn-sm btn-ghost-secondary" onclick="this.style.display='none'; document.getElementById('log-full-<?php echo e($index); ?>').style.display='block';">
                                                <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-code-plus'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'me-1']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                                <?php echo e(trans('plugins/payment::payment.show_full_json')); ?>

                                            </button>
                                        </div>
                                        <div id="log-full-<?php echo e($index); ?>" style="display: none;" class="mt-3">
                                            <div class="card card-sm">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0"><?php echo e(trans('plugins/payment::payment.full_log_data')); ?></h5>
                                                    <div class="card-actions">
                                                        <button type="button" class="btn btn-sm btn-ghost-secondary" onclick="copyToClipboard(event, 'log-json-<?php echo e($index); ?>')">
                                                            <?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-copy'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <pre id="log-json-<?php echo e($index); ?>" class="m-0 p-3 bg-light" style="max-height: 500px; overflow-y: auto; font-size: 12px; line-height: 1.5; color: #333;"><?php echo e(json_encode(['timestamp' => $log->created_at->toIso8601String(), 'payment_method' => $log->payment_method, 'ip_address' => $log->ip_address, 'request' => $log->request, 'response' => $log->response], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>

    <script>
        function copyToClipboard(event, elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                const text = element.textContent || element.innerText;
                navigator.clipboard.writeText(text).then(function () {
                    const button = event.target.closest('button');
                    const originalHtml = button.innerHTML;
                    button.innerHTML = `<?php if (isset($component)) { $__componentOriginal73995948b3bd877b76251b40caf28170 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal73995948b3bd877b76251b40caf28170 = $attributes; } ?>
<?php $component = Botble\Icon\View\Components\Icon::resolve(['name' => 'ti ti-check'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('core::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Botble\Icon\View\Components\Icon::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $attributes = $__attributesOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__attributesOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal73995948b3bd877b76251b40caf28170)): ?>
<?php $component = $__componentOriginal73995948b3bd877b76251b40caf28170; ?>
<?php unset($__componentOriginal73995948b3bd877b76251b40caf28170); ?>
<?php endif; ?> Copied!`;
                    button.classList.add('btn-success');
                    button.classList.remove('btn-ghost-secondary');

                    setTimeout(() => {
                        button.innerHTML = originalHtml;
                        button.classList.remove('btn-success');
                        button.classList.add('btn-ghost-secondary');
                    }, 2000);
                }).catch(function(err) {
                    console.error('Failed to copy text: ', err);
                });
            }
        }
    </script>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gropart/platform/plugins/payment/resources/views/partials/payment-logs.blade.php ENDPATH**/ ?>