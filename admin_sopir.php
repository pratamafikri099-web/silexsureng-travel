<?php
session_start();
include 'koneksi.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$mode = 'tambah'; // Default mode
$data_sopir = []; 
$pesan = '';
$pesan_tipe = '';
$error = '';

// ===================================
// 1. PROCESS FETCH DATA FOR EDIT
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'edit') {
    $mode = 'edit';
    $id_sopir = (int) ($_GET['id'] ?? 0);

    if ($id_sopir > 0) {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM sopir WHERE id_sopir = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_sopir);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data_sopir = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$data_sopir) {
            $error = "Driver data not found.";
            $mode = 'tambah';
        }
    }
}

// ===================================
// 2. PROCESS ADD / UPDATE DATA
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? ''); // Password can be empty on edit
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $no_sim   = trim($_POST['no_sim'] ?? '');
    $aktif    = (int) ($_POST['aktif'] ?? 0); 
    $id_sopir_post = (int) ($_POST['id_sopir'] ?? 0);

    // Basic Validation
    if (empty($nama) || empty($username) || empty($no_hp)) {
        $error = "Name, Username, and Phone Number are required.";
    } 
    // Password Validation on Add New
    elseif ($id_sopir_post == 0 && empty($password)) {
        $error = "Password is required for new drivers.";
    }
    else {
        // Check Duplicate Username (to avoid Duplicate Entry error)
        $query_cek = "SELECT id_sopir FROM sopir WHERE username = ? AND id_sopir != ?";
        $stmt_cek = mysqli_prepare($koneksi, $query_cek);
        mysqli_stmt_bind_param($stmt_cek, "si", $username, $id_sopir_post);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Username '$username' is already used by another driver.";
            mysqli_stmt_close($stmt_cek);
        } else {
            mysqli_stmt_close($stmt_cek);

            if ($id_sopir_post > 0) {
                // --- UPDATE MODE ---
                if (!empty($password)) {
                    // Update with new password
                    $sql = "UPDATE sopir SET nama=?, username=?, password=?, no_hp=?, no_sim=?, aktif=? WHERE id_sopir=?";
                    $stmt = mysqli_prepare($koneksi, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssii", $nama, $username, $password, $no_hp, $no_sim, $aktif, $id_sopir_post);
                } else {
                    // Update WITHOUT changing password
                    $sql = "UPDATE sopir SET nama=?, username=?, no_hp=?, no_sim=?, aktif=? WHERE id_sopir=?";
                    $stmt = mysqli_prepare($koneksi, $sql);
                    mysqli_stmt_bind_param($stmt, "ssssii", $nama, $username, $no_hp, $no_sim, $aktif, $id_sopir_post);
                }
                $pesan = "Driver data updated successfully.";
            } else {
                // --- ADD MODE ---
                $sql = "INSERT INTO sopir (nama, username, password, no_hp, no_sim, aktif) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "sssssi", $nama, $username, $password, $no_hp, $no_sim, $aktif);
                $pesan = "New driver added successfully.";
            }

            if (mysqli_stmt_execute($stmt)) {
                // Redirect to clear form (PRG Pattern)
                header("Location: admin_sopir.php?status=success&msg=" . urlencode($pesan));
                exit;
            } else {
                $error = "Failed to save data: " . mysqli_error($koneksi);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ===================================
// 3. PROCESS DELETE DATA
// ===================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'hapus') {
    $id_sopir = (int) ($_GET['id'] ?? 0);

    if ($id_sopir > 0) {
        // Check data dependency
        $check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pemesanan WHERE id_sopir = $id_sopir");
        $count = mysqli_fetch_assoc($check)['total'];

        if ($count > 0) {
            $error = "Delete failed! This driver has $count order history records.";
        } else {
            $stmt = mysqli_prepare($koneksi, "DELETE FROM sopir WHERE id_sopir = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_sopir);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: admin_sopir.php?status=success&msg=" . urlencode("Driver deleted successfully."));
            exit;
        }
    }
}

// Fetch driver data
$sql_list = "SELECT * FROM sopir ORDER BY aktif DESC, nama ASC";
$result_list = mysqli_query($koneksi, $sql_list);

// GET Notification
if(isset($_GET['status']) && isset($_GET['msg'])) {
    $pesan = htmlspecialchars($_GET['msg']);
    $pesan_tipe = htmlspecialchars($_GET['status']);
}

// Default form values
$nama_form     = $data_sopir['nama'] ?? ($_POST['nama'] ?? '');
$username_form = $data_sopir['username'] ?? ($_POST['username'] ?? '');
$no_hp_form    = $data_sopir['no_hp'] ?? ($_POST['no_hp'] ?? '');
$no_sim_form   = $data_sopir['no_sim'] ?? ($_POST['no_sim'] ?? '');
$aktif_form    = $data_sopir['aktif'] ?? (isset($_POST['aktif']) ? (int)$_POST['aktif'] : 1); 
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Manage Drivers | Silexsureng Travel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #ff7b00;
            --dark-bg: #212529;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; }

        /* CONSISTENT NAVBAR */
        .navbar-custom { background-color: var(--dark-bg); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .nav-link { color: rgba(255,255,255,0.7) !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary-color) !important; }
        .nav-link.active { font-weight: 600; border-bottom: 2px solid var(--primary-color); }

        .card-modern { border: none; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        
        .table thead th { 
            background-color: #f8f9fa; 
            border-bottom: 2px solid #e9ecef;
            font-weight: 600; 
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand me-5 fw-bold" href="admin_pemesanan.php">
            Sile<span style="color: var(--primary-color);">X</span>sureng <span class="text-white-50 small fw-normal">Admin</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pemesanan.php">Pemesanan</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pembayaran.php">Pembayaran</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_sopir.php">Sopir</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_rute.php">Rute</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pelanggan.php">Pelanggan</a></li>
            </ul>
            
            <div class="d-flex align-items-center">
                <span class="text-white-50 me-3 small d-none d-lg-inline">Hi, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout_admin.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Manage Driver Data</h4>
            <p class="text-muted small mb-0">Management of travel driver accounts and status.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-modern mb-4 sticky-top" style="top: 80px; z-index: 1;">
                <div class="card-header bg-dark text-white border-0 py-3">
                    <i class="fas fa-user-plus me-1"></i> 
                    <?= ($mode === 'edit') ? 'Edit Driver Data' : 'Add New Driver' ?>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger small p-2"><i class="fas fa-exclamation-triangle me-1"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <?php if ($mode === 'edit'): ?>
                            <input type="hidden" name="id_sopir" value="<?= $data_sopir['id_sopir'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Personal Information</label>
                            <input type="text" name="nama" class="form-control mb-2" placeholder="Full Name" value="<?= htmlspecialchars($nama_form) ?>" required>
                            <input type="text" name="no_hp" class="form-control mb-2" placeholder="Phone / WA" value="<?= htmlspecialchars($no_hp_form) ?>" required>
                            <input type="text" name="no_sim" class="form-control" placeholder="SIM Number (Optional)" value="<?= htmlspecialchars($no_sim_form) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Login Account</label>
                            <input type="text" name="username" class="form-control mb-2" placeholder="Username" value="<?= htmlspecialchars($username_form) ?>" required autocomplete="off">
                            <input type="password" name="password" class="form-control" placeholder="<?= ($mode === 'edit') ? 'Password (Leave empty to keep)' : 'New Password' ?>" <?= ($mode !== 'edit') ? 'required' : '' ?> autocomplete="new-password">
                        </div>

                        <div class="mb-3 form-check bg-light p-2 rounded border">
                            <input type="checkbox" class="form-check-input ms-1" id="aktifCheck" name="aktif" value="1" <?= ($aktif_form == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label small ms-2 fw-bold" for="aktifCheck">Active Account (Can Login)</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning text-dark fw-bold">
                                <i class="fas fa-save me-1"></i> <?= ($mode === 'edit') ? 'Save Changes' : 'Add Driver' ?>
                            </button>
                            <?php if ($mode === 'edit'): ?>
                                <a href="admin_sopir.php" class="btn btn-outline-secondary btn-sm">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-modern">
                <div class="card-body p-4">
                    <?php if ($pesan): ?>
                        <div class="alert alert-<?= $pesan_tipe ?> small py-2"><i class="fas fa-check-circle me-1"></i> <?= $pesan ?></div>
                    <?php endif; ?>

                    <h5 class="mb-3 text-secondary">Registered Drivers List (<?= mysqli_num_rows($result_list) ?>)</h5>
                    
                    <?php if ($result_list && mysqli_num_rows($result_list) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Name / Username</th>
                                        <th>Contact / SIM</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result_list)): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                            <div class="text-muted small"><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($row['username']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark"><i class="fas fa-phone me-1 text-success"></i> <?= htmlspecialchars($row['no_hp']) ?></div>
                                            <div class="text-muted small"><i class="fas fa-id-card me-1 text-secondary"></i> <?= htmlspecialchars($row['no_sim']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($row['aktif'] == 1): ?>
                                                <span class="badge bg-success rounded-pill px-3">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="admin_sopir.php?aksi=edit&id=<?= $row['id_sopir'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="admin_sopir.php?aksi=hapus&id=<?= $row['id_sopir'] ?>" 
                                               class="btn btn-sm btn-outline-danger rounded-circle ms-1"
                                               onclick="return confirm('Delete driver <?= $row['nama'] ?>?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="60" class="mb-3 opacity-25">
                            <p class="text-muted mb-0">No driver data yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>