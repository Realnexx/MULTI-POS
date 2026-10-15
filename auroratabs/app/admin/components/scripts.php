  <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="src/assets/js/sidebarmenu.js"></script>
  <script src="src/assets/js/app.min.js"></script>
  <script src="src/assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="src/assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="src/assets/js/dashboard.js"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="src/assets/js/custom.js"></script>

  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body p-4 text-center">
          <div class="text-danger mb-3">
            <i class="ti ti-logout fs-10"></i>
          </div>
          <h4 class="fw-semibold mb-2">Logout Confirmation</h4>
          <p class="text-muted mb-4">Are you sure you want to log out? Any unsaved changes might be lost.</p>
          <div class="d-flex gap-3 justify-content-center">
            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Stay Logged In</button>
            <a href="logout.php" class="btn btn-danger px-4">Yes, Logout</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Messages Hub Modal -->
  <div class="modal fade" id="messagesModal" tabindex="-1" aria-labelledby="messagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content overflow-hidden">
        <div class="modal-body p-0">
          <div class="d-flex messenger-container">
            <!-- Sidebar: Conversation List -->
            <div class="messenger-sidebar border-end shadow-sm">
              <div class="p-4 border-bottom bg-white d-flex justify-content-between align-items-center">
                <h5 class="fw-bolder mb-0 text-primary">Chats</h5>
                <button class="btn btn-primary btn-sm rounded-circle p-2 d-flex align-items-center shadow-sm">
                  <i class="ti ti-edit fs-4"></i>
                </button>
              </div>
              <div class="p-3">
                <div class="input-group mb-0">
                  <span class="input-group-text bg-light border-0"><i class="ti ti-search text-muted"></i></span>
                  <input type="text" class="form-control bg-light border-0 fs-3" placeholder="Search messenger...">
                </div>
              </div>
              <div class="conversation-list overflow-auto px-2" style="height: 520px;">
                <!-- Chat 1 -->
                <div class="p-3 chat-user-item active border-0 d-flex align-items-center gap-3 cursor-pointer">
                  <div class="position-relative">
                    <img src="src/assets/images/profile/user-2.jpg" class="rounded-circle border border-2 border-white shadow-sm" width="48" height="48">
                    <span class="status-dot status-online"></span>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <h6 class="mb-0 fw-bold text-dark text-truncate">Mark Zuckerberg</h6>
                      <small class="text-primary fw-semibold">12:45 PM</small>
                    </div>
                    <p class="mb-0 text-muted small text-truncate">The tabulation for the Bohol category is ready.</p>
                  </div>
                </div>
                <!-- Chat 2 -->
                <div class="p-3 chat-user-item border-0 d-flex align-items-center gap-3 cursor-pointer">
                  <div class="position-relative">
                    <img src="src/assets/images/profile/user-3.jpg" class="rounded-circle border border-2 border-white shadow-sm" width="48" height="48">
                    <span class="status-dot status-busy"></span>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <h6 class="mb-0 fw-bold text-dark text-truncate">Elon Musk</h6>
                      <small class="text-muted small">Yesterday</small>
                    </div>
                    <p class="mb-0 text-muted small text-truncate">Is the live display working correctly?</p>
                  </div>
                </div>
                <!-- Chat 3 -->
                <div class="p-3 chat-user-item border-0 d-flex align-items-center gap-3 cursor-pointer">
                  <div class="position-relative">
                    <img src="src/assets/images/profile/user-4.jpg" class="rounded-circle border border-2 border-white shadow-sm" width="48" height="48">
                    <span class="status-dot status-online"></span>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <h6 class="mb-0 fw-bold text-dark text-truncate">Bill Gates</h6>
                      <small class="text-muted small">Wed</small>
                    </div>
                    <p class="mb-0 text-muted small text-truncate fw-bold text-dark text-truncate">Great job on the new results page!</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Main Chat Area -->
            <div class="flex-grow-1 d-flex flex-column bg-white chat-area">
              <!-- Chat Header -->
              <div class="p-3 border-bottom d-flex justify-content-between align-items-center chat-area-header shadow-sm">
                <div class="d-flex align-items-center gap-3">
                  <div class="position-relative">
                    <img src="src/assets/images/profile/user-2.jpg" class="rounded-circle border border-2 border-primary shadow-sm" width="45" height="45">
                    <span class="status-dot status-online"></span>
                  </div>
                  <div>
                    <h6 class="mb-0 fw-bolder text-dark fs-4">Mark Zuckerberg</h6>
                    <small class="text-success fw-bold d-flex align-items-center gap-1">
                      <i class="ti ti-circle-filled fs-1"></i> Active now
                    </small>
                  </div>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-light-primary text-primary border-0 btn-sm rounded-circle p-2"><i class="ti ti-phone-call fs-5"></i></button>
                  <button class="btn btn-light-info text-info border-0 btn-sm rounded-circle p-2"><i class="ti ti-video-plus fs-5"></i></button>
                  <button class="btn btn-light-danger text-danger border-0 btn-sm rounded-circle p-2" data-bs-dismiss="modal"><i class="ti ti-square-x fs-6"></i></button>
                </div>
              </div>

              <!-- Message History -->
              <div class="p-4 flex-grow-1 overflow-auto chat-history" style="height: 480px;">
                <div class="text-center mb-5">
                  <span class="badge bg-white text-muted px-4 py-2 border shadow-sm rounded-pill small fw-bold text-uppercase tracking-wider">Today, Jan 15</span>
                </div>

                <!-- Received Message -->
                <div class="d-flex mb-4 gap-3 align-items-end">
                  <img src="src/assets/images/profile/user-2.jpg" class="rounded-circle mb-1" width="28" height="28">
                  <div>
                    <div class="msg-bubble received p-3 rounded-4 shadow-sm">
                      <p class="mb-0">Hello admin! Have you finalized the scoring criteria for the upcoming Queen of Bohol event?</p>
                    </div>
                    <small class="text-muted px-1" style="font-size: 0.75rem;">12:40 PM</small>
                  </div>
                </div>

                <!-- Sent Message -->
                <div class="d-flex mb-4 gap-3 justify-content-end align-items-end">
                  <div class="text-end">
                    <div class="msg-bubble sent p-3 rounded-4 shadow-sm">
                      <p class="mb-0">Yes Mark, the criteria are now locked. You can view them in the criteria management section.</p>
                    </div>
                    <div class="d-flex align-items-center justify-content-end gap-1 px-1 mt-1">
                       <small class="text-muted" style="font-size: 0.75rem;">12:42 PM</small>
                       <i class="ti ti-checks text-primary fs-3"></i>
                    </div>
                  </div>
                </div>

                <!-- Received Message -->
                <div class="d-flex mb-4 gap-3 align-items-end">
                  <img src="src/assets/images/profile/user-2.jpg" class="rounded-circle mb-1" width="28" height="28">
                  <div>
                    <div class="msg-bubble received p-3 rounded-4 shadow-sm">
                      <p class="mb-0">Perfect. I'll check it out. The tabulation system seems robust! 🚀</p>
                    </div>
                    <small class="text-muted px-1" style="font-size: 0.75rem;">12:45 PM</small>
                  </div>
                </div>

                <!-- Typing... (Visual Only) -->
                <div class="d-flex mb-4 gap-3 align-items-end opacity-75">
                  <img src="src/assets/images/profile/user-2.jpg" class="rounded-circle mb-1" width="28" height="28">
                  <div class="bg-white p-2 px-3 rounded-pill shadow-sm border small">
                    <span class="fw-bold">Mark is typing</span>
                    <span class="animate-pulse">...</span>
                  </div>
                </div>
              </div>

              <!-- Chat Input -->
              <div class="chat-input-wrapper">
                <div class="input-group gap-2 align-items-center">
                  <button class="btn btn-light-primary text-primary border-0 rounded-circle p-2" type="button" title="Attach Files"><i class="ti ti-paperclip fs-5"></i></button>
                  <button class="btn btn-light-warning text-warning border-0 rounded-circle p-2" type="button" title="Add Emojis"><i class="ti ti-mood-smile fs-5"></i></button>
                  <input type="text" class="form-control chat-input shadow-none fs-3" placeholder="Type a message here...">
                  <button class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;" type="button" title="Send Message">
                    <i class="ti ti-send fs-5"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

          </div>
        </div>
      </div>
    </div>
  </div>