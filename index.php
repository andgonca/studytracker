<?php
require_once 'config.php';

// Handle Form Submission (Add or Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subdomain_id = $_POST['subdomain_id'];
    $subject = $_POST['subject'];
    $comments = $_POST['comments'];
    $status = $_POST['status'];
    $links_input = $_POST['links']; // Textarea content
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE subjects SET subdomain_id=?, name=?, comments=?, status=? WHERE id=?");
        $stmt->bind_param("isssi", $subdomain_id, $subject, $comments, $status, $id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO subjects (subdomain_id, name, comments, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $subdomain_id, $subject, $comments, $status);
    }
    
    if ($stmt->execute()) {
        $subject_id = ($id > 0) ? $id : $stmt->insert_id;
        
        // Handle Links: Delete old ones and re-insert
        $conn->query("DELETE FROM links WHERE subject_id=$subject_id");
        
        $lines = explode("\n", $links_input);
        $linkStmt = $conn->prepare("INSERT INTO links (subject_id, url) VALUES (?, ?)");
        foreach ($lines as $line) {
            $url = trim($line);
            if (!empty($url)) {
                $linkStmt->bind_param("is", $subject_id, $url);
                $linkStmt->execute();
            }
        }
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Fetch Data
$sql = "SELECT s.*, sd.name as subdomain, sd.domain_id, d.name as domain, d.certification_id, c.name as certification 
        FROM subjects s 
        JOIN subdomains sd ON s.subdomain_id = sd.id 
        JOIN domains d ON sd.domain_id = d.id 
        JOIN certifications c ON d.certification_id = c.id 
        ORDER BY s.id";
$result = $conn->query($sql);

// Fetch Links for display (Subjects)
$subject_links = [];
$link_res = $conn->query("SELECT * FROM links");
while($l = $link_res->fetch_assoc()) {
    $subject_links[$l['subject_id']][] = $l['url'];
}

// Fetch Links for Domains
$domain_links = [];
$d_link_res = $conn->query("SELECT * FROM domain_links");
if ($d_link_res) {
    while($l = $d_link_res->fetch_assoc()) {
        $domain_links[$l['domain_id']][] = $l['url'];
    }
}

// Fetch Links for Subdomains
$subdomain_links = [];
$sd_link_res = $conn->query("SELECT * FROM subdomain_links");
if ($sd_link_res) {
    while($l = $sd_link_res->fetch_assoc()) {
        $subdomain_links[$l['subdomain_id']][] = $l['url'];
    }
}

// Build Tree Structure and Calculate Rowspans
$tree = [];
$cert_counts = [];
$dom_counts = [];
$sub_counts = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cid = $row['certification_id'];
        $did = $row['domain_id'];
        $sid = $row['subdomain_id'];

        if (!isset($tree[$cid])) $tree[$cid] = ['name' => $row['certification'], 'domains' => []];
        if (!isset($tree[$cid]['domains'][$did])) $tree[$cid]['domains'][$did] = ['name' => $row['domain'], 'subdomains' => []];
        if (!isset($tree[$cid]['domains'][$did]['subdomains'][$sid])) $tree[$cid]['domains'][$did]['subdomains'][$sid] = ['name' => $row['subdomain'], 'subjects' => []];

        $tree[$cid]['domains'][$did]['subdomains'][$sid]['subjects'][] = $row;

        $cert_counts[$cid] = ($cert_counts[$cid] ?? 0) + 1;
        $dom_counts[$did] = ($dom_counts[$did] ?? 0) + 1;
        $sub_counts[$sid] = ($sub_counts[$sid] ?? 0) + 1;
    }
}

// Fetch Hierarchy for Dropdowns (JSON)
$hierarchy = [];
$cert_res = $conn->query("SELECT * FROM certifications ORDER BY name");
while($c = $cert_res->fetch_assoc()) {
    $hierarchy[$c['id']] = ['name' => $c['name'], 'domains' => []];
}
$dom_res = $conn->query("SELECT * FROM domains ORDER BY name");
while($d = $dom_res->fetch_assoc()) {
    if(isset($hierarchy[$d['certification_id']])) {
        $hierarchy[$d['certification_id']]['domains'][$d['id']] = ['name' => $d['name'], 'subdomains' => []];
    }
}
$sub_res = $conn->query("SELECT * FROM subdomains ORDER BY name");
while($s = $sub_res->fetch_assoc()) {
    // Find which cert this subdomain belongs to
    foreach($hierarchy as $cid => $cert) {
        foreach($cert['domains'] as $did => $dom) {
            if ($did == $s['domain_id']) {
                $hierarchy[$cid]['domains'][$did]['subdomains'][$s['id']] = $s['name'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certification Study Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .status-confident { background-color: #d1e7dd; color: #0f5132; }
        .status-inprogress { background-color: #fff3cd; color: #664d03; }
        .status-notstarted { background-color: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Study Tracker</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="index.php">Tracker</a>
                <a class="nav-link" href="maintenance.php">Maintenance</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Progress</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="clearForm()">+ Add New Topic</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Cert</th>
                                <th>Domain</th>
                                <th>Subdomain</th>
                                <th>Subject</th>
                                <th>Links & Comments</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tree)): ?>
                                <?php foreach($tree as $cid => $cert): $c_first = true; ?>
                                    <?php foreach($cert['domains'] as $did => $dom): $d_first = true; ?>
                                        <?php foreach($dom['subdomains'] as $sid => $sub): $s_first = true; ?>
                                            <?php foreach($sub['subjects'] as $row): ?>
                                <tr>
                                    <?php if($c_first): ?>
                                        <td rowspan="<?= $cert_counts[$cid] ?>" class="fw-bold bg-white align-top"><?= htmlspecialchars($cert['name']) ?></td>
                                        <?php $c_first = false; ?>
                                    <?php endif; ?>

                                    <?php if($d_first): ?>
                                        <td rowspan="<?= $dom_counts[$did] ?>" class="align-top bg-light">
                                            <div class="fw-bold"><?= htmlspecialchars($dom['name']) ?></div>
                                            <?php if(isset($domain_links[$did])): foreach($domain_links[$did] as $url): ?>
                                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="small text-decoration-none">🔗 Link</a><br>
                                            <?php endforeach; endif; ?>
                                        </td>
                                        <?php $d_first = false; ?>
                                    <?php endif; ?>

                                    <?php if($s_first): ?>
                                        <td rowspan="<?= $sub_counts[$sid] ?>" class="align-top">
                                            <?= htmlspecialchars($sub['name']) ?>
                                            <?php if(isset($subdomain_links[$sid])): foreach($subdomain_links[$sid] as $url): ?>
                                                <br><a href="<?= htmlspecialchars($url) ?>" target="_blank" class="small text-decoration-none">🔗 Link</a>
                                            <?php endforeach; endif; ?>
                                        </td>
                                        <?php $s_first = false; ?>
                                    <?php endif; ?>

                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td>
                                        <?php if(isset($subject_links[$row['id']])): ?>
                                            <?php foreach($subject_links[$row['id']] as $url): ?>
                                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-decoration-none">
                                                    🔗 Link
                                                </a><br>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if($row['comments']): ?>
                                            <small class="text-secondary d-block mt-1"><em><?= htmlspecialchars(substr($row['comments'], 0, 50)) . (strlen($row['comments']) > 50 ? '...' : '') ?></em></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'bg-secondary';
                                            if ($row['status'] == 'Confident') $badgeClass = 'bg-success';
                                            elseif ($row['status'] == 'In Progress') $badgeClass = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $row['status'] ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            // Prepare data for JS edit function
                                            $editData = $row;
                                            $editData['links'] = isset($subject_links[$row['id']]) ? implode("\n", $subject_links[$row['id']]) : '';
                                        ?>
                                        <button class="btn btn-sm btn-outline-primary" 
                                            onclick='editItem(<?= json_encode($editData) ?>)'>Edit</button>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Del</a>
                                    </td>
                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No study items found. Add one!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Add Study Topic</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="itemId">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Certification</label>
                                <select class="form-select" id="certSelect" onchange="updateDomains()" required>
                                    <option value="">Select...</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Domain</label>
                                <select class="form-select" id="domainSelect" onchange="updateSubdomains()" required>
                                    <option value="">Select Cert First...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Sub-domain</label>
                                <select class="form-select" name="subdomain_id" id="subdomainSelect" required>
                                    <option value="">Select Domain First...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" name="subject" id="subject" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Links (One URL per line)</label>
                            <textarea class="form-control" name="links" id="links" rows="3" placeholder="http://example.com&#10;http://another-link.com"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea class="form-control" name="comments" id="comments" rows="3" placeholder="Notes, thoughts, key takeaways..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Progress Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="Not Started">Not Started</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Confident">Confident (Happy with progress)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hierarchy Data from PHP
        const hierarchy = <?= json_encode($hierarchy) ?>;

        function populateCerts() {
            const certSelect = document.getElementById('certSelect');
            certSelect.innerHTML = '<option value="">Select...</option>';
            for (const [id, data] of Object.entries(hierarchy)) {
                certSelect.innerHTML += `<option value="${id}">${data.name}</option>`;
            }
        }

        function updateDomains() {
            const certId = document.getElementById('certSelect').value;
            const domainSelect = document.getElementById('domainSelect');
            const subSelect = document.getElementById('subdomainSelect');
            
            domainSelect.innerHTML = '<option value="">Select...</option>';
            subSelect.innerHTML = '<option value="">Select Domain First...</option>';

            if (certId && hierarchy[certId]) {
                for (const [id, data] of Object.entries(hierarchy[certId].domains)) {
                    domainSelect.innerHTML += `<option value="${id}">${data.name}</option>`;
                }
            }
        }

        function updateSubdomains() {
            const certId = document.getElementById('certSelect').value;
            const domainId = document.getElementById('domainSelect').value;
            const subSelect = document.getElementById('subdomainSelect');
            
            subSelect.innerHTML = '<option value="">Select...</option>';

            if (certId && domainId && hierarchy[certId].domains[domainId]) {
                for (const [id, name] of Object.entries(hierarchy[certId].domains[domainId].subdomains)) {
                    subSelect.innerHTML += `<option value="${id}">${name}</option>`;
                }
            }
        }

        // Initialize on load
        populateCerts();

        function clearForm() {
            document.getElementById('itemId').value = '';
            document.getElementById('certSelect').value = '';
            updateDomains(); // Reset downstream
            document.getElementById('subject').value = '';
            document.getElementById('links').value = '';
            document.getElementById('comments').value = '';
            document.getElementById('status').value = 'Not Started';
            document.getElementById('modalTitle').innerText = 'Add Study Topic';
        }

        function editItem(data) {
            document.getElementById('itemId').value = data.id;
            
            // Set Cascading Dropdowns
            document.getElementById('certSelect').value = data.certification_id;
            updateDomains();
            document.getElementById('domainSelect').value = data.domain_id;
            updateSubdomains();
            document.getElementById('subdomainSelect').value = data.subdomain_id;

            document.getElementById('subject').value = data.name; // Note: 'name' from DB is subject
            document.getElementById('links').value = data.links;
            document.getElementById('comments').value = data.comments;
            document.getElementById('status').value = data.status;
            
            document.getElementById('modalTitle').innerText = 'Edit Study Topic';
            
            var myModal = new bootstrap.Modal(document.getElementById('itemModal'));
            myModal.show();
        }
    </script>
</body>
</html>
