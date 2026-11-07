<!DOCTYPE html>
<html lang="id" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name')); ?></title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap.min.css')); ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap-icons.min.css')); ?>">
    <style>
        body {
            overflow-x: hidden;
        }

        .main-content {
            padding-top: 4.5rem;
        }

        .nav-link.active {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
        }

        /* Nav pills: scroll on small screens, don't push signout off */
        .nav.nav-pills {
            flex-wrap: nowrap;
        }

        .nav.nav-pills .nav-link {
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .nav.nav-pills .nav-link {
                font-size: 0.8rem;
                padding: 0.3rem 0.5rem;
            }

            .nav.nav-pills .nav-link i {
                display: none;
            }
        }

        /* Override Bootstrap navbar dropdown position: static → absolute (popup) */
        .navbar .dropdown-menu {
            position: absolute;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <script>
        // Dynamically adjust main-content padding to navbar height
        document.addEventListener('DOMContentLoaded', function () {
            var navbar = document.querySelector('.navbar.fixed-top');
            var main = document.querySelector('.main-content');
            if (navbar && main) {
                function adjust() {
                    main.style.paddingTop = (navbar.offsetHeight + 8) + 'px';
                }
                adjust();
                window.addEventListener('resize', adjust);
            }
        });
    </script>
</head>

<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="house-fill" viewBox="0 0 16 16">
            <path
                d="M8.707 1.5a 1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z" />
            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6Z" />
        </symbol>
        <symbol id="box" viewBox="0 0 16 16">
            <path
                d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5 8.186 1.113zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 6.839l-6.5-2.6v7.922l6.5 2.6V6.84z" />
        </symbol>
        <symbol id="truck" viewBox="0 0 16 16">
            <path
                d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" />
        </symbol>
        <symbol id="file-invoice" viewBox="0 0 16 16">
            <path
                d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z" />
            <path d="M3 4h10v1H3V4zm0 2h10v1H3V6zm0 2h10v1H3V8zm0 2h10v1H3v-1zm0 2h10v1H3v-1z" />
        </symbol>
        <symbol id="building" viewBox="0 0 16 16">
            <path
                d="M4 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-6 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm-6 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z" />
        </symbol>
        <symbol id="people" viewBox="0 0 16 16">
            <path
                d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z" />
        </symbol>
        <symbol id="file-earmark" viewBox="0 0 16 16">
            <path
                d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z" />
        </symbol>
        <symbol id="file-earmark-text" viewBox="0 0 16 16">
            <path
                d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z" />
            <path
                d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
        </symbol>
        <symbol id="search" viewBox="0 0 16 16">
            <path
                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
        </symbol>
    </svg>
    <!-- Navbar: brand + nav pills + sign out -->
    <header class="navbar fixed-top shadow-sm" style="background-color: #E3F2FD;" data-bs-theme="light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <img src="<?php echo e(asset('images/logo.png') . '?v=' . filemtime(public_path('images/logo.png'))); ?>"
                    alt="<?php echo e(config('app.name')); ?>" height="32"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <span style="display:none;"><?php echo e(config('app.name')); ?></span>
            </a>
            <nav class="d-flex align-items-center" style="min-width:0; flex:1 1 auto; overflow:hidden;">
                <ul class="nav nav-pills mb-0"
                    style="min-width:0; max-width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; flex-wrap:nowrap;">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>" href="/dashboard"><i
                                class="bi bi-house-fill me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('shipments*') ? 'active' : ''); ?>" href="/shipments"><i
                                class="bi bi-box me-1"></i>Shipments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('manifests*') ? 'active' : ''); ?>" href="/manifests"><i
                                class="bi bi-truck me-1"></i>Manifests</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('invoices*') ? 'active' : ''); ?>" href="/invoices"><i
                                class="bi bi-file-earmark-text me-1"></i>Invoices</a>
                    </li>

                    <?php if(auth()->guard()->check()): ?>
                        <?php if(in_array(auth()->user()->role, ['Admin', 'Management_View', 'Staff_Invoice'])): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('purchase-orders*') ? 'active' : ''); ?>"
                                    href="/purchase-orders"><i class="bi bi-receipt me-1"></i>Purchase Orders</a>
                            </li>
                        <?php endif; ?>
                        
                        
                    <?php endif; ?>
                </ul>
            </nav>
            <ul class="navbar-nav flex-row align-items-center ms-auto" style="flex-shrink:0;">
                <?php if(auth()->guard()->check()): ?>
                    <li class="nav-item me-2 d-none d-md-inline-block">
                        <form action="/shipments" method="GET" class="d-flex">
                            <input type="search" name="q" class="form-control form-control-sm"
                                placeholder="Cari resi..." aria-label="Search">
                        </form>
                    </li>
                    <li class="nav-item">
                        <form action="/logout" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-warning btn-sm"><i
                                    class="bi bi-door-closed me-1"></i>Sign Out</button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </header>
    <main class="main-content px-4">
        <div class="d-flex justify-content-between flex-wrap flex-nowrap align-items-center pt-3 pb-2 mb-3">
            <?php echo $__env->yieldContent('page_header'); ?>
            <div class="btn-toolbar mb-0">
                <?php echo $__env->yieldContent('toolbar'); ?>
            </div>
        </div>
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('warning')): ?>
            <div class="alert alert-warning"><?php echo e(session('warning')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <!-- Bootstrap 5.3.3 JS -->
    <script src="<?php echo e(asset('js/bootstrap.bundle.min.js')); ?>"></script>
    <script>
        // Localize timestamps to browser timezone
        (function () {
            var els = document.querySelectorAll('[data-uts]');
            els.forEach(function (el) {
                var ts = parseInt(el.getAttribute('data-uts'));
                if (!isNaN(ts)) {
                    var d = new Date(ts * 1000);
                    el.textContent = d.toLocaleString(undefined, {
                        year: 'numeric', month: '2-digit', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', hour12: false
                    });
                }
            });
        })();
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html><?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>