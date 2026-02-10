<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">

        <!-- ================= USER PROFILE ================= -->
        <div class="sidebar-user text-center p-3">

            <?php
                $image_src = !empty($session_image)
                    ? $session_image
                    : '../files/assets/images/avatar-4.jpg';

                $fullName = trim(
                    $session_sfirstname . ' ' .
                    $session_smiddlename . ' ' .
                    $session_slastname
                );
            ?>

            <a href="staff_detailed.php?id=<?= $session_id ?>&view=2">
                <img src="<?= htmlspecialchars($image_src); ?>"
                     class="img-radius mb-2"
                     alt="User Profile"
                     style="width:80px;height:80px;">
            </a>

            <h6 class="mb-0">
                <a href="staff_detailed.php?id=<?= $session_id ?>&view=2"
                   style="color:#fff;font-weight:bold;text-decoration:none;">
                    <?= htmlspecialchars($fullName); ?>
                </a>
            </h6>

            <small class="text-muted"><?= htmlspecialchars($session_role); ?></small>
            <hr>
        </div>
        <!-- ================= END PROFILE ================= -->


        <!-- ================= ADMIN / MANAGER ================= -->
        <?php if ($session_role == 'Manager' || $session_role == 'Admin') : ?>

            <div class="pcoded-navigatio-lavel">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="<?= ($page_name == 'dashboard') ? 'active' : ''; ?>">
                    <a href="index.php">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="pcoded-navigatio-lavel">Applications</div>
            <ul class="pcoded-item pcoded-left-item">

                <?php if ($session_role == 'Admin') : ?>
                <li class="<?= ($page_name == 'department') ? 'active' : ''; ?>">
                    <a href="department.php">
                        <span class="pcoded-micon"><i class="feather icon-monitor"></i></span>
                        <span class="pcoded-mtext">Department</span>
                    </a>
                </li>
                 <li class="pcoded-hasmenu <?php echo ($page_name == 'staff' || $page_name == 'new_staff' || $page_name == 'staff_list') ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                        <span class="pcoded-mtext">Staff</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?php echo ($page_name == 'new_staff') ? 'active' : ''; ?>">
                            <a href="new_staff.php">
                                <span class="pcoded-mtext">New Staff</span>
                            </a>
                        </li>
                        <li class="<?php echo ($page_name == 'staff_list') ? 'active' : ''; ?>">
                            <a href="staff_list.php">
                                <span class="pcoded-mtext">Manage Staff</span>
                            </a>
                        </li>
                    </ul>
                </li>

                  <li class="<?php echo ($page_name == 'leave_type') ? 'active' : ''; ?>">
                    <a href="leave_type.php">
                        <span class="pcoded-micon"><i class="feather icon-shuffle"></i></span>
                        <span class="pcoded-mtext">Leave Type</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($session_role == 'Manager') : ?>
                <li class="<?php echo ($page_name == 'my_team') ? 'active' : ''; ?>">
                    <a href="my_team.php">
                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                        <span class="pcoded-mtext">My Team</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- LEAVE -->
                <li class="pcoded-hasmenu <?= in_array($page_name, ['apply_leave','my_leave','leave_request']) ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-shuffle"></i></span>
                        <span class="pcoded-mtext">Leave</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?= ($page_name == 'apply_leave') ? 'active' : ''; ?>">
                            <a href="apply_leave.php">Apply Leave</a>
                        </li>
                        <li class="<?= ($page_name == 'my_leave') ? 'active' : ''; ?>">
                            <a href="my_leave.php">My Leave</a>
                        </li>
                        <li class="<?= ($page_name == 'leave_request') ? 'active' : ''; ?>">
                            <a href="leave_request.php?leave_status=0">All Leaves</a>
                        </li>
                    </ul>
                </li>

                 <li class="pcoded-hasmenu <?php echo ($page_name == 'task' || $page_name == 'new_task' || $page_name == 'task_list') ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                        <span class="pcoded-mtext">Task Manager</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?php echo ($page_name == 'new_task') ? 'active' : ''; ?>">
                            <a href="new_task.php">
                                <span class="pcoded-mtext">New Task</span>
                            </a>
                        </li>
                        <li class="<?php echo ($page_name == 'task_list') ? 'active' : ''; ?>">
                            <a href="task_list.php">
                                <span class="pcoded-mtext">Task List</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ATTENDANCE -->
                <li class="pcoded-hasmenu <?= in_array($page_name, ['attendance','my_attendance','missing_clockouts']) ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-clock"></i></span>
                        <span class="pcoded-mtext">Attendance</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?= ($page_name == 'attendance') ? 'active' : ''; ?>">
                            <a href="attendance.php">Attendance</a>
                        </li>
                        <li class="<?= ($page_name == 'my_attendance') ? 'active' : ''; ?>">
                            <a href="my_attendance.php">My Attendance</a>
                        </li>
                        <li class="<?= ($page_name == 'missing_clockouts') ? 'active' : ''; ?>">
                            <a href="missing_clockouts.php">Missing Clock-outs</a>
                        </li>
                    </ul>
                </li>

            </ul>
        <?php endif; ?>


        <!-- ================= STAFF ================= -->
        <?php if ($session_role == 'Staff') : ?>

            <div class="pcoded-navigatio-lavel">Navigation</div>
            <ul class="pcoded-item pcoded-left-item">
                <li class="<?= ($page_name == 'dashboard') ? 'active' : ''; ?>">
                    <a href="index.php">
                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                        <span class="pcoded-mtext">Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="pcoded-navigatio-lavel">Applications</div>
            <ul class="pcoded-item pcoded-left-item">

                <!-- LEAVE -->
                <li class="pcoded-hasmenu <?= in_array($page_name, ['apply_leave','my_leave']) ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-shuffle"></i></span>
                        <span class="pcoded-mtext">Leave</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?= ($page_name == 'apply_leave') ? 'active' : ''; ?>">
                            <a href="apply_leave.php">Apply Leave</a>
                        </li>
                        <li class="<?= ($page_name == 'my_leave') ? 'active' : ''; ?>">
                            <a href="my_leave.php">My Leave</a>
                        </li>
                    </ul>
                </li>
                <li class="pcoded-hasmenu <?php echo ($page_name == 'task' || $page_name == 'my_task_list') ? 'active pcoded-trigger' : ''; ?>">
                    <a href="javascript:void(0)">
                        <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                        <span class="pcoded-mtext">Task Manager</span>
                    </a>
                    <ul class="pcoded-submenu">
                        <li class="<?php echo ($page_name == 'my_task_list') ? 'active' : ''; ?>">
                            <a href="my_task_list.php">
                                <span class="pcoded-mtext">My Task</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- ATTENDANCE -->
            <li class="pcoded-hasmenu <?= in_array($page_name, ['attendance','my_attendance']) ? 'active pcoded-trigger' : ''; ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-clock"></i></span>
                    <span class="pcoded-mtext">Attendance</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($page_name == 'attendance') ? 'active' : ''; ?>">
                        <a href="attendance.php">Attendance</a>
                    </li>
                    <li class="<?= ($page_name == 'my_attendance') ? 'active' : ''; ?>">
                        <a href="my_attendance.php">My Attendance</a>
                    </li>
                </ul>
            </li>


            </ul>
        <?php endif; ?>


        <!-- ================= LOGOUT ================= -->
        <div class="p-3">
            <a href="../logout.php"
               class="btn btn-sm btn-outline-danger btn-block"
               onclick="return confirm('Are you sure you want to logout?');">
                <i class="feather icon-log-out"></i> Logout
            </a>
        </div>

    </div>
</nav>
