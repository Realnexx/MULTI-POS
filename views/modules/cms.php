<?php
$title = "Website CMS & Contact Messages";
ob_start();
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold outfit-font text-dark mb-1">Website CMS & Messages</h4>
        <p class="text-muted small mb-0">Update public website content dynamically and review customer inquiries and order requests.</p>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills custom-tabs mb-4 gap-2" id="cmsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-semibold" id="cms-tab" data-bs-toggle="tab" data-bs-target="#cms" type="button" role="tab">
            <i class="fa-solid fa-pen-to-square me-2"></i>Website Content Editor
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-semibold" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">
            <i class="fa-solid fa-envelope-open-text me-2"></i>Customer Messages
            <?php 
            $unread = count(array_filter($messages, fn($m) => !$m['is_read']));
            if ($unread > 0): 
            ?>
                <span class="badge bg-danger ms-2 rounded-pill"><?= $unread ?></span>
            <?php endif; ?>
        </button>
    </li>
</ul>

<!-- Tabs Content -->
<div class="tab-content" id="cmsTabsContent">
    <!-- CMS Editor Tab -->
    <div class="tab-pane fade show active" id="cms" role="tabpanel">
        <div class="card border-0 rounded-4 shadow-sm p-lg-5 p-4 bg-white border border-light">
            <form action="<?= url('?route=admin/cms') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="update_content">

                <h5 class="fw-bold outfit-font text-dark mb-4 pb-2 border-bottom border-light"><i class="fa-solid fa-house me-2 text-primary"></i>Homepage Hero Section</h5>
                <div class="row gy-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fw-medium small text-dark">Hero Title</label>
                        <input type="text" name="hero_title" class="form-control rounded-pill px-3 py-2 shadow-xs" value="<?= htmlspecialchars($content['hero_title'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small text-dark">Hero Subtitle</label>
                        <input type="text" name="hero_subtitle" class="form-control rounded-pill px-3 py-2 shadow-xs" value="<?= htmlspecialchars($content['hero_subtitle'] ?? '') ?>" required>
                    </div>
                </div>

                <h5 class="fw-bold outfit-font text-dark mb-4 pb-2 border-bottom border-light"><i class="fa-solid fa-circle-info me-2 text-primary"></i>About Us Section</h5>
                <div class="mb-5">
                    <label class="form-label fw-medium small text-dark">About Brand Story</label>
                    <textarea name="about_text" class="form-control rounded-4 p-3 shadow-xs" rows="6" required><?= htmlspecialchars($content['about_text'] ?? '') ?></textarea>
                </div>

                <h5 class="fw-bold outfit-font text-dark mb-4 pb-2 border-bottom border-light"><i class="fa-solid fa-address-book me-2 text-primary"></i>Contact Information</h5>
                <div class="row gy-4 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fw-medium small text-dark">Store Address</label>
                        <input type="text" name="contact_address" class="form-control rounded-pill px-3 py-2 shadow-xs" value="<?= htmlspecialchars($content['contact_address'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium small text-dark">Phone Number</label>
                        <input type="text" name="contact_phone" class="form-control rounded-pill px-3 py-2 shadow-xs" value="<?= htmlspecialchars($content['contact_phone'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium small text-dark">Email Address</label>
                        <input type="email" name="contact_email" class="form-control rounded-pill px-3 py-2 shadow-xs" value="<?= htmlspecialchars($content['contact_email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">Save CMS Content</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Tab -->
    <div class="tab-pane fade" id="messages" role="tabpanel">
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fs-7 text-muted fw-bold outfit-font">
                        <tr>
                            <th class="py-3 px-4 rounded-start-3">Sender</th>
                            <th class="py-3">Subject</th>
                            <th class="py-3">Message</th>
                            <th class="py-3">Date Received</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 px-4 rounded-end-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No messages received yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $m): ?>
                                <tr class="<?= !$m['is_read'] ? 'bg-light-gray' : '' ?>">
                                    <td class="py-3 px-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($m['name']) ?></div>
                                        <div class="fs-7 text-muted"><?= htmlspecialchars($m['email']) ?></div>
                                    </td>
                                    <td class="py-3 fw-semibold text-dark"><?= htmlspecialchars($m['subject']) ?></td>
                                    <td class="py-3 text-muted small" style="max-width: 300px;">
                                        <?= nl2br(htmlspecialchars($m['message'])) ?>
                                    </td>
                                    <td class="py-3 text-muted small"><?= date('M d, Y h:i A', strtotime($m['created_at'])) ?></td>
                                    <td class="py-3">
                                        <?php if ($m['is_read']): ?>
                                            <span class="badge bg-light text-muted border px-3 py-1">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1 fw-bold">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <?php if (!$m['is_read']): ?>
                                            <form action="<?= url('?route=admin/cms') ?>" method="POST" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-medium">Mark Read</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fa-solid fa-check me-1"></i> Done</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
