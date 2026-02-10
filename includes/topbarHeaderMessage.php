<ul class="nav-right">
    <li class="header-notification">
        <div class="dropdown-primary dropdown">
            <div class="dropdown-toggle" data-toggle="dropdown">
                <i class="feather icon-bell"></i>
                <?php
                // Get pending leave count based on role
                $userId = $_SESSION['slogin'];
                $userRole = $_SESSION['srole'];
                $pendingCount = 0;
                
                if ($userRole == 'Admin') {
                    // Admin sees all pending leaves
                    $countQuery = mysqli_query($conn, "SELECT COUNT(*) as count FROM tblleave WHERE leave_status = 0");
                    if ($countQuery) {
                        $countRow = mysqli_fetch_assoc($countQuery);
                        $pendingCount = $countRow['count'];
                    }
                } elseif ($userRole == 'Manager') {
                    // Manager sees pending leaves from direct reports
                    $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM tblleave l 
                                                       JOIN tblemployees e ON l.empid = e.emp_id 
                                                       WHERE l.leave_status = 0 AND e.supervisor_id = ?");
                    mysqli_stmt_bind_param($countStmt, "i", $userId);
                    mysqli_stmt_execute($countStmt);
                    $countResult = mysqli_stmt_get_result($countStmt);
                    if ($countResult) {
                        $countRow = mysqli_fetch_assoc($countResult);
                        $pendingCount = $countRow['count'];
                    }
                    mysqli_stmt_close($countStmt);
                }
                ?>
                <span class="badge bg-c-pink"><?php echo $pendingCount; ?></span>
            </div>
            <ul class="show-notification notification-view dropdown-menu"
                data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                <li>
                    <h6>Notifications</h6>
                    <?php if ($pendingCount > 0): ?>
                        <label class="label label-danger">New</label>
                    <?php endif; ?>
                </li>
                <?php
                // Fetch pending leave notifications based on role
                $notificationQuery = null;
                
                if ($userRole == 'Admin') {
                    $notificationStmt = mysqli_prepare($conn, "SELECT l.id, l.from_date, l.to_date, 
                                                              e.first_name, e.middle_name, e.last_name, e.image_path,
                                                              lt.leave_type, l.created_date
                                                              FROM tblleave l
                                                              JOIN tblemployees e ON l.empid = e.emp_id
                                                              JOIN tblleavetype lt ON l.leave_type_id = lt.id
                                                              WHERE l.leave_status = 0
                                                              ORDER BY l.created_date DESC
                                                              LIMIT 5");
                    mysqli_stmt_execute($notificationStmt);
                    $notificationQuery = mysqli_stmt_get_result($notificationStmt);
                } elseif ($userRole == 'Manager') {
                    $notificationStmt = mysqli_prepare($conn, "SELECT l.id, l.from_date, l.to_date, 
                                                              e.first_name, e.middle_name, e.last_name, e.image_path,
                                                              lt.leave_type, l.created_date
                                                              FROM tblleave l
                                                              JOIN tblemployees e ON l.empid = e.emp_id
                                                              JOIN tblleavetype lt ON l.leave_type_id = lt.id
                                                              WHERE l.leave_status = 0 AND e.supervisor_id = ?
                                                              ORDER BY l.created_date DESC
                                                              LIMIT 5");
                    mysqli_stmt_bind_param($notificationStmt, "i", $userId);
                    mysqli_stmt_execute($notificationStmt);
                    $notificationQuery = mysqli_stmt_get_result($notificationStmt);
                }
                
                if ($notificationQuery && mysqli_num_rows($notificationQuery) > 0):
                    while ($notif = mysqli_fetch_assoc($notificationQuery)):
                        $imageSrc = !empty($notif['image_path']) ? $notif['image_path'] : '..\files\assets\images\avatar-4.jpg';
                        $employeeName = trim($notif['first_name'] . ' ' . $notif['middle_name'] . ' ' . $notif['last_name']);
                        $timeAgo = date('M d, Y', strtotime($notif['created_date']));
                ?>
                <li>
                    <div class="media">
                        <img class="d-flex align-self-center img-radius"
                            src="<?php echo htmlspecialchars($imageSrc); ?>"
                            alt="<?php echo htmlspecialchars($employeeName); ?>">
                        <div class="media-body">
                            <h5 class="notification-user"><?php echo htmlspecialchars($employeeName); ?></h5>
                            <p class="notification-msg">Requested <?php echo htmlspecialchars($notif['leave_type']); ?> 
                               from <?php echo date('M d', strtotime($notif['from_date'])); ?> 
                               to <?php echo date('M d', strtotime($notif['to_date'])); ?></p>
                            <span class="notification-time"><?php echo $timeAgo; ?></span>
                        </div>
                    </div>
                </li>
                <?php 
                    endwhile;
                else:
                ?>
                <li>
                    <div class="media">
                        <div class="media-body">
                            <p class="notification-msg text-center">No pending leave requests</p>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </li>
    <li class="user-profile header-notification">
        <div class="dropdown-primary dropdown">
            <div class="dropdown-toggle" data-toggle="dropdown">
                <?php
                    $image_src = !empty($session_image) ? $session_image : '..\files\assets\images\avatar-4.jpg';
                    echo '<img src="' . $image_src . '" class="img-radius" alt="User-Profile-Image">';
                ?>
                <span><?php echo $session_sfirstname . ' ' . $session_smiddlename . ' ' . $session_slastname; ?></span>
                <i class="feather icon-chevron-down"></i>
            </div>
            <ul class="show-notification profile-notification dropdown-menu"
                data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                <li>
                    <a href="staff_detailed.php?id=<?= $session_id ?>&view=2">
                        <i class="feather icon-user"></i> Profile
                    </a>
                </li>
                <!-- <li>
                    <a href="../lock_screen.php">
                        <i class="feather icon-lock"></i> Lock Screen
                    </a>
                </li> -->
                <li>
                    <a href="../logout.php">
                        <i class="feather icon-log-out"></i> Logout
                    </a>
                </li>
            </ul>

        </div>
    </li>
</ul>