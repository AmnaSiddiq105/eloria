
<div class="sidebar">

    <!-- BRAND -->
    <div class="sidebar-brand">

        <a href="dashboard.php" class="brand-logo">
            ÉLORIA.
        </a>

        <span class="brand-subtitle">
            Admin Panel
        </span>

    </div>


    <!-- ADMIN PROFILE -->
    <div class="admin-profile">

        <div class="admin-avatar">
            <?= strtoupper(substr($adminName ?? 'A', 0, 1)); ?>
        </div>

        <div class="admin-info">

            <strong>
                <?= htmlspecialchars($adminName ?? 'Admin'); ?>
            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>


    <!-- NAVIGATION -->
    <nav class="sidebar-nav">

        <a href="dashboard.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">

            <span class="nav-icon">⌂</span>

            <span>Dashboard</span>

        </a>


        <a href="orders.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">

            <span class="nav-icon">♡</span>

            <span>Orders</span>

        </a>


        <a href="products.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">

            <span class="nav-icon">◇</span>

            <span>Products</span>

        </a>


        <a href="customers.php"
           class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['customers.php', 'customer-details.php']) ? 'active' : ''; ?>">

            <span class="nav-icon">♧</span>

            <span>Customers</span>

        </a>

    </nav>


    <!-- BOTTOM LINKS -->
    <div class="sidebar-bottom">

        <a href="profile.php"
           class="bottom-link <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">

            <span class="nav-icon">♙</span>

            <span>Profile</span>

        </a>


        <a href="logout.php"
           class="bottom-link logout-link">

            <span class="nav-icon">↪</span>

            <span>Logout</span>

        </a>

    </div>

</div>
