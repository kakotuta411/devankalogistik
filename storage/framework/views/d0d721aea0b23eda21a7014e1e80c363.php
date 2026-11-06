<?php $__env->startSection('title', 'Edit User'); ?>
<?php $__env->startSection('page_header'); ?><h1 class="h2">Edit User: <?php echo e($user->name); ?></h1><?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<form action="/users/<?php echo e($user->id); ?>" method="POST" class="card">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" value="<?php echo e($user->name); ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" value="<?php echo e($user->email); ?>" class="form-control" required>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">Role *</label>
                <select name="role" class="form-select" required>
                    <option value="Admin" <?php echo e($user->role === 'Admin' ? 'selected' : ''); ?>>Admin</option>
                    <option value="Head_of_Branch" <?php echo e($user->role === 'Head_of_Branch' ? 'selected' : ''); ?>>Head of Branch</option>
                    <option value="Leader_Supervisor" <?php echo e($user->role === 'Leader_Supervisor' ? 'selected' : ''); ?>>Leader Supervisor</option>
                    <option value="Executive_Viewer" <?php echo e($user->role === 'Executive_Viewer' ? 'selected' : ''); ?>>Executive Viewer</option>
                    <option value="Staf" <?php echo e($user->role === 'Staf' ? 'selected' : ''); ?>>Staf</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Branch</label>
                <select name="branch_id" class="form-select">
                    <option value="">-- Tidak ada --</option>
                    <?php $__currentLoopData = \App\Models\Branch::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($b->id); ?>" <?php echo e($user->branch_id === $b->id ? 'selected' : ''); ?>><?php echo e($b->branch_code); ?> — <?php echo e($b->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">Telepon</label>
            <input type="text" name="phone_number" value="<?php echo e($user->phone_number); ?>" class="form-control">
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="/users" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/users/edit.blade.php ENDPATH**/ ?>