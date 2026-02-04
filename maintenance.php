<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Adds
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_cert'])) {
        $stmt = $conn->prepare("INSERT INTO certifications (name) VALUES (?)");
        $stmt->bind_param("s", $_POST['name']);
        $stmt->execute();
    } elseif (isset($_POST['add_domain'])) {
        $stmt = $conn->prepare("INSERT INTO domains (certification_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $_POST['cert_id'], $_POST['name']);
        $stmt->execute();
        $domain_id = $stmt->insert_id;
        
        if (!empty($_POST['links'])) {
            $lines = explode("\n", $_POST['links']);
            $lstmt = $conn->prepare("INSERT INTO domain_links (domain_id, url) VALUES (?, ?)");
            foreach($lines as $line) {
                $url = trim($line);
                if($url) { $lstmt->bind_param("is", $domain_id, $url); $lstmt->execute(); }
            }
        }
    } elseif (isset($_POST['add_subdomain'])) {
        $stmt = $conn->prepare("INSERT INTO subdomains (domain_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $_POST['domain_id'], $_POST['name']);
        $stmt->execute();
        $sub_id = $stmt->insert_id;
        if (!empty($_POST['links'])) {
            $lines = explode("\n", $_POST['links']);
            $lstmt = $conn->prepare("INSERT INTO subdomain_links (subdomain_id, url) VALUES (?, ?)");
            foreach($lines as $line) {
                $url = trim($line);
                if($url) { $lstmt->bind_param("is", $sub_id, $url); $lstmt->execute(); }
            }
        }
    }
    header("Location: maintenance.php");
    exit();
}

// Handle Deletes
if (isset($_GET['del_cert'])) $conn->query("DELETE FROM certifications WHERE id=" . intval($_GET['del_cert']));
if (isset($_GET['del_domain'])) $conn->query("DELETE FROM domains WHERE id=" . intval($_GET['del_domain']));
if (isset($_GET['del_subdomain'])) $conn->query("DELETE FROM subdomains WHERE id=" . intval($_GET['del_subdomain']));

// Fetch Data
$certs = $conn->query("SELECT * FROM certifications ORDER BY name");
$domains = $conn->query("SELECT d.*, c.name as cert_name FROM domains d JOIN certifications c ON d.certification_id = c.id ORDER BY c.name, d.name");
$subdomains = $conn->query("SELECT s.*, d.name as domain_name, c.name as cert_name FROM subdomains s JOIN domains d ON s.domain_id = d.id JOIN certifications c ON d.certification_id = c.id ORDER BY c.name, d.name, s.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance - Study Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Study Tracker</a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Tracker</a>
                <a class="nav-link active" href="maintenance.php">Maintenance</a>
                <a class="nav-link" href="users.php">Users</a>
                <a class="nav-link text-danger" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <!-- Certifications -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">Certifications</div>
                    <div class="card-body">
                        <form method="POST" class="mb-3 input-group">
                            <input type="text" name="name" class="form-control" placeholder="New Certification" required>
                            <button type="submit" name="add_cert" class="btn btn-success">+</button>
                        </form>
                        <ul class="list-group">
                            <?php foreach($certs as $c): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($c['name']) ?>
                                    <a href="?del_cert=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete cert and ALL related data?')">X</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Domains -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">Domains</div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <select name="cert_id" class="form-select mb-2" required>
                                <option value="">Select Certification...</option>
                                <?php 
                                $certs->data_seek(0); 
                                while($c = $certs->fetch_assoc()): 
                                ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="New Domain" required>
                                <button type="submit" name="add_domain" class="btn btn-success">+</button>
                            </div>
                            <textarea name="links" class="form-control mt-2" rows="2" placeholder="Links (one per line)"></textarea>
                        </form>
                        <ul class="list-group">
                            <?php while($d = $domains->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($d['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($d['cert_name']) ?></small>
                                    </div>
                                    <a href="?del_domain=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete domain?')">X</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Subdomains -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">Subdomains</div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <select name="domain_id" class="form-select mb-2" required>
                                <option value="">Select Domain...</option>
                                <?php 
                                $domains->data_seek(0); 
                                while($d = $domains->fetch_assoc()): 
                                ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['cert_name'] . ' - ' . $d['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="New Subdomain" required>
                                <button type="submit" name="add_subdomain" class="btn btn-success">+</button>
                            </div>
                            <textarea name="links" class="form-control mt-2" rows="2" placeholder="Links (one per line)"></textarea>
                        </form>
                        <ul class="list-group">
                            <?php while($s = $subdomains->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($s['name']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($s['cert_name'] . ' > ' . $s['domain_name']) ?></small>
                                    </div>
                                    <a href="?del_subdomain=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete subdomain?')">X</a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>