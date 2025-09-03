<?php
require_once 'includes/config.php';
requireAdminLogin();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['update_contact'])) {
        $contact_type = sanitizeInput($_POST['contact_type']);
        $contact_value = sanitizeInput($_POST['contact_value']);
        $contact_label = sanitizeInput($_POST['contact_label']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if this contact type already exists
        $check_stmt = $conn->prepare("SELECT id FROM contact_info WHERE type = ?");
        $check_stmt->bind_param("s", $contact_type);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            // Update existing
            $stmt = $conn->prepare("UPDATE contact_info SET value = ?, label = ?, is_active = ? WHERE type = ?");
            $stmt->bind_param("ssis", $contact_value, $contact_label, $is_active, $contact_type);
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO contact_info (type, value, label, is_active) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $contact_type, $contact_value, $contact_label, $is_active);
        }
        
        if ($stmt->execute()) {
            $success = "Contact information updated successfully!";
        } else {
            $error = "Error updating contact information.";
        }
    }
    
    if (isset($_POST['add_contact'])) {
        $contact_type = sanitizeInput($_POST['contact_type']);
        $contact_value = sanitizeInput($_POST['contact_value']);
        $contact_label = sanitizeInput($_POST['contact_label']);
        
        $stmt = $conn->prepare("INSERT INTO contact_info (type, value, label) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $contact_type, $contact_value, $contact_label);
        
        if ($stmt->execute()) {
            $success = "Contact information added successfully!";
        } else {
            $error = "Error adding contact information.";
        }
    }
    
    if (isset($_POST['delete_contact'])) {
        $contact_id = (int)$_POST['contact_id'];
        
        $stmt = $conn->prepare("DELETE FROM contact_info WHERE id = ?");
        $stmt->bind_param("i", $contact_id);
        
        if ($stmt->execute()) {
            $success = "Contact information deleted successfully!";
        } else {
            $error = "Error deleting contact information.";
        }
    }
}

// Get all contact information
$contact_info = $conn->query("SELECT * FROM contact_info ORDER BY type, id");

// Get contact info grouped by type
$contact_types = [];
if ($contact_info && $contact_info->num_rows > 0) {
    while ($contact = $contact_info->fetch_assoc()) {
        $contact_types[$contact['type']][] = $contact;
    }
}

// Predefined contact types
$predefined_types = [
    'address' => ['icon' => 'fas fa-map-marker-alt', 'label' => 'Address'],
    'phone' => ['icon' => 'fas fa-phone', 'label' => 'Phone'],
    'email' => ['icon' => 'fas fa-envelope', 'label' => 'Email'],
    'website' => ['icon' => 'fas fa-globe', 'label' => 'Website'],
    'social_facebook' => ['icon' => 'fab fa-facebook', 'label' => 'Facebook'],
    'social_twitter' => ['icon' => 'fab fa-twitter', 'label' => 'Twitter'],
    'social_instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
    'hours' => ['icon' => 'fas fa-clock', 'label' => 'Operating Hours']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Information - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin.css" rel="stylesheet">
    <style>
        .contact-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            margin-right: 15px;
        }
        .contact-icon.address { background: linear-gradient(135deg, #007bff, #0056b3); }
        .contact-icon.phone { background: linear-gradient(135deg, #28a745, #1e7e34); }
        .contact-icon.email { background: linear-gradient(135deg, #dc3545, #c82333); }
        .contact-icon.website { background: linear-gradient(135deg, #6f42c1, #5a32a3); }
        .contact-icon.social_facebook { background: linear-gradient(135deg, #3b5998, #2d4373); }
        .contact-icon.social_twitter { background: linear-gradient(135deg, #1da1f2, #0d8bd9); }
        .contact-icon.social_instagram { background: linear-gradient(135deg, #e4405f, #c13584); }
        .contact-icon.hours { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .contact-icon.default { background: linear-gradient(135deg, #6c757d, #545b62); }
        
        .contact-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #495057;
        }
        
        .contact-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .quick-edit-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }
        
        .status-toggle {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-address-book"></i> Contact Information Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                            <i class="fas fa-plus"></i> Add Contact Info
                        </button>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Contact Information Cards -->
                <?php if (!empty($contact_types)): ?>
                    <?php foreach ($predefined_types as $type => $type_info): ?>
                        <?php if (isset($contact_types[$type])): ?>
                            <div class="card contact-card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="<?php echo $type_info['icon']; ?> me-2"></i>
                                        <?php echo $type_info['label']; ?>
                                    </h5>
                                    <span class="badge bg-primary"><?php echo count($contact_types[$type]); ?> entries</span>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($contact_types[$type] as $contact): ?>
                                        <div class="row align-items-center mb-3 pb-3 border-bottom position-relative">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center">
                                                    <div class="contact-icon <?php echo $type; ?>">
                                                        <i class="<?php echo $type_info['icon']; ?>"></i>
                                                    </div>
                                                    <div>
                                                        <div class="contact-value"><?php echo htmlspecialchars($contact['value']); ?></div>
                                                        <div class="contact-label"><?php echo htmlspecialchars($contact['label']); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editContact(<?php echo htmlspecialchars(json_encode($contact)); ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteContact(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['value']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="status-toggle">
                                                <span class="badge bg-<?php echo $contact['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $contact['is_active'] ? 'Active' : 'Hidden'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <!-- Other contact types -->
                    <?php foreach ($contact_types as $type => $contacts): ?>
                        <?php if (!isset($predefined_types[$type])): ?>
                            <div class="card contact-card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                    </h5>
                                    <span class="badge bg-secondary"><?php echo count($contacts); ?> entries</span>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($contacts as $contact): ?>
                                        <div class="row align-items-center mb-3 pb-3 border-bottom position-relative">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center">
                                                    <div class="contact-icon default">
                                                        <i class="fas fa-info-circle"></i>
                                                    </div>
                                                    <div>
                                                        <div class="contact-value"><?php echo htmlspecialchars($contact['value']); ?></div>
                                                        <div class="contact-label"><?php echo htmlspecialchars($contact['label']); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="editContact(<?php echo htmlspecialchars(json_encode($contact)); ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteContact(<?php echo $contact['id']; ?>, '<?php echo htmlspecialchars($contact['value']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="status-toggle">
                                                <span class="badge bg-<?php echo $contact['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $contact['is_active'] ? 'Active' : 'Hidden'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No contact information found</h5>
                        <p class="text-muted">Add your first contact information to get started.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                            <i class="fas fa-plus"></i> Add Contact Information
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Quick Setup Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-rocket"></i> Quick Setup
                        </h6>
                    </div>
                    <div class="card-body">
                        <p>Set up essential contact information for your school:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Essential Information:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> School address</li>
                                    <li><i class="fas fa-check text-success"></i> Primary phone number</li>
                                    <li><i class="fas fa-check text-success"></i> Email address</li>
                                    <li><i class="fas fa-check text-success"></i> Operating hours</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Optional Information:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success"></i> Secondary phone numbers</li>
                                    <li><i class="fas fa-check text-success"></i> Social media links</li>
                                    <li><i class="fas fa-check text-success"></i> Website URL</li>
                                    <li><i class="fas fa-check text-success"></i> Emergency contacts</li>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="setupEssentials()">
                            <i class="fas fa-magic"></i> Setup Essential Information
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div class="modal fade" id="addContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> Add Contact Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="contact_type" class="form-label">Contact Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="contact_type" name="contact_type" required>
                                <option value="">Select Type</option>
                                <?php foreach ($predefined_types as $type => $info): ?>
                                    <option value="<?php echo $type; ?>"><?php echo $info['label']; ?></option>
                                <?php endforeach; ?>
                                <option value="custom">Custom Type</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="custom_type_field" style="display: none;">
                            <label for="custom_type" class="form-label">Custom Type Name</label>
                            <input type="text" class="form-control" id="custom_type" placeholder="e.g., fax, emergency">
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_value" class="form-label">Contact Value <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_value" name="contact_value" 
                                   placeholder="Enter the contact information" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contact_label" class="form-label">Label/Description</label>
                            <input type="text" class="form-control" id="contact_label" name="contact_label" 
                                   placeholder="e.g., Main Office, Emergency Line">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_contact" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal -->
    <div class="modal fade" id="editContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Edit Contact Information
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editContactForm">
                    <input type="hidden" name="contact_type" id="edit_contact_type">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_contact_value" class="form-label">Contact Value <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_contact_value" name="contact_value" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_contact_label" class="form-label">Label/Description</label>
                            <input type="text" class="form-control" id="edit_contact_label" name="contact_label">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    Active (visible on website)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_contact" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Contact
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this contact information?</p>
                    <p class="text-muted"><strong>Contact:</strong> <span id="deleteContactValue"></span></p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="contact_id" id="delete_contact_id">
                        <button type="submit" name="delete_contact" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Contact
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle custom type selection
        document.getElementById('contact_type').addEventListener('change', function() {
            const customField = document.getElementById('custom_type_field');
            const customInput = document.getElementById('custom_type');
            
            if (this.value === 'custom') {
                customField.style.display = 'block';
                customInput.required = true;
            } else {
                customField.style.display = 'none';
                customInput.required = false;
            }
        });

        // Handle form submission for custom types
        document.querySelector('#addContactModal form').addEventListener('submit', function(e) {
            const typeSelect = document.getElementById('contact_type');
            const customInput = document.getElementById('custom_type');
            
            if (typeSelect.value === 'custom' && customInput.value) {
                typeSelect.value = customInput.value;
            }
        });

        function editContact(contact) {
            document.getElementById('edit_contact_type').value = contact.type;
            document.getElementById('edit_contact_value').value = contact.value;
            document.getElementById('edit_contact_label').value = contact.label || '';
            document.getElementById('edit_is_active').checked = contact.is_active == 1;
            
            new bootstrap.Modal(document.getElementById('editContactModal')).show();
        }

        function deleteContact(id, value) {
            document.getElementById('delete_contact_id').value = id;
            document.getElementById('deleteContactValue').textContent = value;
            
            new bootstrap.Modal(document.getElementById('deleteContactModal')).show();
        }

        function setupEssentials() {
            // Pre-fill modal with essential contact types
            const modal = new bootstrap.Modal(document.getElementById('addContactModal'));
            modal.show();
        }

        // Reset forms when modals are hidden
        document.getElementById('addContactModal').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#addContactModal form').reset();
            document.getElementById('custom_type_field').style.display = 'none';
        });

        document.getElementById('editContactModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('editContactForm').reset();
        });
    </script>
</body>
</html>